<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Enums\FolderStatus;
use App\Models\DocumentFolderModel;
use App\Models\EvaluationRoutingModel;
use App\Models\RoutingPresetMemberModel;
use App\Models\DocumentModel;
use App\Models\TemplateModel;
use App\Models\UserModel;
use App\Models\RoutingPresetModel;

/**
 * Handles evaluation folders: the top-level container that groups a person's
 * IPCR/DPCR/OPCR documents for one evaluation period, plus the drafting ->
 * submission -> evaluation -> approval lifecycle and team "cascading" (assigning
 * a distribution-list team as evaluators/subordinates for a folder).
 */
class Folder extends BaseController
{
    /**
     * GET /folders/{folderId} - Main folder workspace. Resolves which folder to
     * show (last-viewed, or the newest owned folder, if none given), checks the
     * viewer's access level (owner / Admin / cascaded evaluator / supervisor),
     * and builds the "guide" documents from superiors so subordinates can see
     * their commitments alongside their own.
     */
    public function index($folderId = null) {
        if (session()->get('role') === 'TWG') {
            return redirect()->to(site_url('ratings'));
        }

        $userId   = session()->get('user_id');
        $role     = session()->get('role');

        $folderModel   = new DocumentFolderModel();
        $documentModel = new DocumentModel();
        $presetModel   = new RoutingPresetModel();
        $userModel     = new UserModel();

        $folders = $folderModel->where('user_id', $userId)->where('deleted_at IS NULL')->orderBy('created_at', 'DESC')->findAll();

        if (!$folderId) {
            $lastId = session()->get('active_folder_id');
            if ($lastId && array_search($lastId, array_column($folders, 'id')) !== false) {
                return redirect()->to('folders/' . $lastId);
            } elseif (!empty($folders)) {
                return redirect()->to('folders/' . $folders[0]['id']);
            }
        } else {
            session()->set('active_folder_id', $folderId);
        }

        $activeFolder = null;
        $myDocs = [];
        $groupedGuides = []; 
        $isReadOnly = true; 

        $presets = $presetModel->where('owner_id', $userId)->orderBy('name', 'ASC')->findAll();

        if ($folderId) {
            $activeFolder = $folderModel->find($folderId);

            if ($activeFolder && !empty($activeFolder['deleted_at'])) {
                return redirect()->to('folders/archived/' . $activeFolder['id']);
            }

            // This route is always "my own workspace" - every "Open Your Folder" email
            // link points here and is always addressed to the folder's owner, so a
            // mismatch means the browser's active session belongs to someone else
            // (e.g. a different account was already logged in). Rather than silently
            // bouncing them to their own folder list with no explanation, send them
            // to a screen that names the account the link was actually for and offers
            // to switch. (Evaluators/Admins/Supervisors viewing someone else's folder
            // for rating purposes go through Rating::show() instead, which has its
            // own proper authorization check.)
            if (!$activeFolder || $activeFolder['user_id'] != $userId) {
                session()->remove('active_folder_id');

                if ($activeFolder) {
                    $owner = $userModel->find($activeFolder['user_id']);
                    if ($owner) {
                        session()->setFlashdata('mismatch_detected', true);
                        session()->setFlashdata('mismatch_target_email', $owner['email']);
                        return redirect()->to('account-mismatch');
                    }
                }

                return redirect()->to('folders');
            }

            $isReadOnly = false;

            // The cascaded team may have since been archived (soft-deleted), in which
            // case it no longer shows up in $presets above - inject it back in just for
            // this folder so the "Cascade Management" panel can still show/select its
            // name and Revoke Cascade keeps working, without making an archived team
            // pickable for any other folder's "Cascade to Team" dropdown.
            if ($activeFolder['routing_preset_id'] && !in_array($activeFolder['routing_preset_id'], array_column($presets, 'id'))) {
                $archivedPreset = $presetModel->withDeleted()->find($activeFolder['routing_preset_id']);
                if ($archivedPreset) {
                    $presets[] = $archivedPreset;
                }
            }

            // If the folder belongs to Admin, it is the master institutional cycle and is auto-approved
            if ($activeFolder && $role === 'Admin' && $activeFolder['status'] === FolderStatus::DRAFT_TARGET->value) {
                $folderModel->update($folderId, [
                    'status' => FolderStatus::TARGET_APPROVED->value,
                    'target_approved_at' => date('Y-m-d H:i:s')
                ]);
                $activeFolder['status'] = FolderStatus::TARGET_APPROVED->value;
            }

            // Ensure the user's official performance paper exists automatically based on their profile doc_type
            $this->ensureUserDocumentExists($folderId, $userId, $activeFolder['title']);

            $myDocs = $documentModel->where('document_folder_id', $folderId)->findAll();
            $routingModel = new EvaluationRoutingModel();
            
            $cascadedRoutes = $routingModel->getEvaluatorsForFolder($folderId);

            foreach ($cascadedRoutes as $route) {
                $guideFolder = $folderModel->find($route['evaluator_folder_id']);
                if ($guideFolder) {
                    $docs = $documentModel->where('document_folder_id', $guideFolder['id'])->findAll();
                    if (!empty($docs)) {
                        $groupedGuides[] = [
                            'superior' => [
                                'id'   => $route['evaluator_id'],
                                'name' => $route['first_name'] . ' ' . $route['last_name'],
                                'role' => $route['evaluator_position'] ?? 'Evaluator' 
                            ], 
                            'docs' => $docs
                        ];
                    }
                }
            }

            if (!empty($activeFolder['parent_folder_id'])) {
                $adminFolder = $folderModel->find($activeFolder['parent_folder_id']);
                
                if ($adminFolder) {
                    $adminDocs = $documentModel->where('document_folder_id', $adminFolder['id'])->findAll();
                    
                    if (!empty($adminDocs)) {
                        $adminInfo = $userModel->getAdminPosition($adminFolder['user_id']);

                        if ($adminInfo) {
                            $groupedGuides[] = [
                                'superior' => [
                                    'id'       => $adminInfo['id'],
                                    'name'     => $adminInfo['first_name'] . ' ' . $adminInfo['last_name'],
                                    'role'     => $adminInfo['admin_position'] ?? 'System Administrator',
                                    'is_admin' => true
                                ],
                                'docs' => $adminDocs
                            ];
                        }
                    }
                }
            }

            $mergedGuides = [];
            foreach ($groupedGuides as $guide) {
                $key = $guide['superior']['name']; 
                if (!isset($mergedGuides[$key])) {
                    $mergedGuides[$key] = $guide;
                } else {
                    $existingRoles = $mergedGuides[$key]['superior']['role'];
                    $newRole       = $guide['superior']['role'];
                    if (strpos($existingRoles, $newRole) === false) {
                        $mergedGuides[$key]['superior']['role'] .= ', ' . $newRole;
                    }
                }
            }
            $groupedGuides = array_values($mergedGuides);
        }

        $ownerDocType = null;
        if ($activeFolder) {
            $owner = $userModel->find($activeFolder['user_id']);
            $ownerDocType = $owner['doc_type'] ?? null;
        }

        $parentFolder = null;
        $isParentTargetApproved = true;
        $basisDoc = null;
        if ($activeFolder && !empty($activeFolder['parent_folder_id'])) {
            $parentFolder = $folderModel->find($activeFolder['parent_folder_id']);
            if ($parentFolder) {
                $parentRolePivot = (new \App\Models\UserRoleModel())->where('user_id', $parentFolder['user_id'])->first();
                $parentRoleName = $parentRolePivot ? ((new \App\Models\RoleModel())->find($parentRolePivot['role_id'])['name'] ?? '') : '';
                $isParentAdmin = ($parentRoleName === 'Admin');

                $myPrimaryDoc = $documentModel->where('document_folder_id', $activeFolder['id'])->where('is_target', 1)->first()
                             ?? $documentModel->where('document_folder_id', $activeFolder['id'])->first();
                $isMyDocOpcr = (strtoupper($myPrimaryDoc['title'] ?? '') === 'OPCR');

                if ($isParentAdmin || $isMyDocOpcr) {
                    $isParentTargetApproved = true;
                    $parentFolder = null;
                    $basisDoc = null;
                } else {
                    $isParentTargetApproved = ($parentFolder['status'] === FolderStatus::TARGET_APPROVED->value);
                    $basisDoc = $documentModel->where('document_folder_id', $parentFolder['id'])->where('is_target', 1)->first()
                             ?? $documentModel->where('document_folder_id', $parentFolder['id'])->first();
                }
            }
        }

        $cascadedChildren = [];
        if ($activeFolder && $role === 'Admin') {
            $cascadedChildren = $folderModel->where('parent_folder_id', $activeFolder['id'])
                ->select("document_folders.id, document_folders.status, document_folders.target_submitted_at, users.first_name, users.last_name, users.email, pos.title as position")
                ->join('users', 'users.id = document_folders.user_id')
                ->join('plantillas p', 'p.user_id = users.id AND p.ended_at IS NULL', 'left')
                ->join('positions pos', 'pos.id = p.position_id', 'left')
                ->findAll();
        }

        $templateModel = new TemplateModel();
        
        return view('components/app_shell', [
            'sidebarFolders'   => $folders,
            'selectedFolderId' => $activeFolder['id'] ?? null,
            'mainView'         => 'document/_doc_rows',
            'templates'        => $templateModel->findAll(),
            'mainData'         => [
                'activeFolder'           => $activeFolder,
                'parentFolder'           => $parentFolder,
                'isParentTargetApproved' => $isParentTargetApproved,
                'basisDoc'               => $basisDoc,
                'ownerDocType'           => $ownerDocType,
                'myDocs'                 => $myDocs,
                'groupedGuides'          => $groupedGuides,
                'isReadOnly'             => $isReadOnly,
                'presets'                => $presets,
                'cascadedChildren'       => $cascadedChildren
            ]
        ]);
    }

    /**
     * POST /folder/cascade-team - Assigns a saved team (routing preset) to a folder.
     * Admins get a distribution-list meaning: creates a child folder for every
     * team member. Everyone else gets an evaluator meaning: registers themself
     * as the evaluator/reviewer for each member's matching sub-folder.
     */
    public function cascadeTeam() {
        return $this->tryOrFail(function() {

            $folderId = $this->request->getPost('folder_id'); 
            $teamId   = $this->request->getPost('team_id');
            $userId   = session()->get('user_id');
            $role     = session()->get('role');

            $folderModel       = new DocumentFolderModel();
            $routingModel      = new EvaluationRoutingModel();
            $presetMemberModel = new RoutingPresetMemberModel();
            $userModel         = new UserModel(); 

            $activeFolder = $folderModel->find($folderId);
            if (!$activeFolder) return $this->respondError("Folder not found.", 400);

            // --- STRICT SPMS CASCADE GATING ---
            // Supervisors/Chairs/Deans must have their own targets approved first before cascading as a basis
            if ($role !== 'Admin') {
                if ($activeFolder['status'] !== \App\Enums\FolderStatus::TARGET_APPROVED->value) {
                    return $this->respondError("Cannot cascade yet: Your target commitments must be approved by your higher-up first before cascading them as a basis for your subordinates.", 400);
                }
            } else {
                $allowedStatuses = [
                    \App\Enums\FolderStatus::DRAFT_TARGET->value, 
                    \App\Enums\FolderStatus::TARGET_APPROVED->value,
                    \App\Enums\FolderStatus::DRAFT->value
                ];
                if (!in_array($activeFolder['status'], $allowedStatuses)) {
                    return $this->respondError("You cannot cascade this folder because it has already moved past the target setting phase.", 400);
                }
            }

            $members = $presetMemberModel->where('preset_id', $teamId)->findAll();
            if (empty($members)) return $this->respondError("The selected team has no members.", 400);

            $folderModel->db->transStart();

            $folderModel->update($folderId, ['routing_preset_id' => $teamId]);
            $emailsQueued = 0;
            if ($role === 'Admin') {
                foreach ($members as $member) {
                    $exists = $folderModel->where('user_id', $member['user_id'])
                                          ->where('parent_folder_id', $activeFolder['id'])->first();
                    
                    if (!$exists) {
                        $newFolderId = create_unique_row($folderModel, [
                            'title'               => $activeFolder['title'],
                            'user_id'             => $member['user_id'],
                            'parent_folder_id'    => $activeFolder['id'],
                            
                            'ipcr_target_start'   => $activeFolder['ipcr_target_start'],
                            'ipcr_target_end'     => $activeFolder['ipcr_target_end'],
                            'ipcr_eval_start'     => $activeFolder['ipcr_eval_start'],
                            'ipcr_eval_end'       => $activeFolder['ipcr_eval_end'],
                            
                            'dpcr_target_start'   => $activeFolder['dpcr_target_start'],
                            'dpcr_target_end'     => $activeFolder['dpcr_target_end'],
                            'dpcr_eval_start'     => $activeFolder['dpcr_eval_start'],
                            'dpcr_eval_end'       => $activeFolder['dpcr_eval_end'],
                            
                            'opcr_target_start'   => $activeFolder['opcr_target_start'],
                            'opcr_target_end'     => $activeFolder['opcr_target_end'],
                            'opcr_eval_start'     => $activeFolder['opcr_eval_start'],
                            'opcr_eval_end'       => $activeFolder['opcr_eval_end'],
                            
                            'iperf_target_start'  => $activeFolder['iperf_target_start'],
                            'iperf_target_end'    => $activeFolder['iperf_target_end'],
                            'iperf_eval_start'    => $activeFolder['iperf_eval_start'],
                            'iperf_eval_end'      => $activeFolder['iperf_eval_end'],
                            
                            'status'              => \App\Enums\FolderStatus::DRAFT_TARGET->value
                        ]);
                    } else {
                        $newFolderId = $exists['id']; 
                    }

                    // Pre-generate the member's official evaluation paper based on profile doc_type
                    $this->ensureUserDocumentExists($newFolderId, $member['user_id'], $activeFolder['title']);

                    if (!$exists) {
                        $memberInfo = $userModel->find($member['user_id']);
                    }
                }
                $message = "Batch evaluation distributed to team members.";
            } else {
                $batchId = $activeFolder['id'];

                foreach ($members as $member) {
                    $subFolder = $folderModel->where('user_id', $member['user_id'])
                                             ->where('parent_folder_id', $batchId)->first();
                    
                    if (!$subFolder) {
                        $newFolderId = create_unique_row($folderModel, [
                            'title'               => $activeFolder['title'],
                            'user_id'             => $member['user_id'],
                            'parent_folder_id'    => $batchId,
                            
                            'ipcr_target_start'   => $activeFolder['ipcr_target_start'],
                            'ipcr_target_end'     => $activeFolder['ipcr_target_end'],
                            'ipcr_eval_start'     => $activeFolder['ipcr_eval_start'],
                            'ipcr_eval_end'       => $activeFolder['ipcr_eval_end'],
                            
                            'dpcr_target_start'   => $activeFolder['dpcr_target_start'],
                            'dpcr_target_end'     => $activeFolder['dpcr_target_end'],
                            'dpcr_eval_start'     => $activeFolder['dpcr_eval_start'],
                            'dpcr_eval_end'       => $activeFolder['dpcr_eval_end'],
                            
                            'opcr_target_start'   => $activeFolder['opcr_target_start'],
                            'opcr_target_end'     => $activeFolder['opcr_target_end'],
                            'opcr_eval_start'     => $activeFolder['opcr_eval_start'],
                            'opcr_eval_end'       => $activeFolder['opcr_eval_end'],
                            
                            'iperf_target_start'  => $activeFolder['iperf_target_start'],
                            'iperf_target_end'    => $activeFolder['iperf_target_end'],
                            'iperf_eval_start'    => $activeFolder['iperf_eval_start'],
                            'iperf_eval_end'      => $activeFolder['iperf_eval_end'],
                            
                            'status'              => \App\Enums\FolderStatus::DRAFT_TARGET->value
                        ]);
                        $subFolder = $folderModel->find($newFolderId);
                    }

                    if ($subFolder) {
                        // Pre-generate the member's official evaluation paper based on profile doc_type
                        $this->ensureUserDocumentExists($subFolder['id'], $member['user_id'], $activeFolder['title']);

                        $exists = $routingModel->where('folder_id', $subFolder['id'])
                                               ->where('evaluator_id', $userId)->first();

                        if (!$exists) {
                            $routingModel->insert([
                                'folder_id'           => $subFolder['id'],
                                'evaluator_id'        => $userId,
                                'evaluator_folder_id' => $folderId,
                                'status'              => FolderStatus::DRAFT->value
                            ]);
                        }
                    }
                }
                $message = "Goals successfully cascaded to your team.";
            }

            $folderModel->db->transComplete();

            return $this->respond(['status' => 'success', 'message' => $message]);
        });
    }

    /** POST /folder/uncascade-team - Reverses cascadeTeam(): removes the team's child folders/evaluator routings. */
    public function uncascadeTeam() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $userId   = session()->get('user_id');
            $role     = session()->get('role');

            $folderModel = new DocumentFolderModel();
            $routingModel = new EvaluationRoutingModel();
            $presetMemberModel = new RoutingPresetMemberModel();
            $presetModel = new RoutingPresetModel();

            $activeFolder = $folderModel->find($folderId);

            // Use the folder's own stored reference rather than trusting a client-submitted
            // team_id - the cascade-select dropdown's value isn't reliable once the cascaded
            // team is missing/archived, so this must be authoritative, not client-supplied.
            $teamId = $activeFolder['routing_preset_id'] ?? null;
            if (!$teamId) {
                return $this->respondError("This folder isn't currently cascaded to a team.", 400);
            }

            $members = $presetMemberModel->where('preset_id', $teamId)->findAll();
            $memberIds = array_column($members, 'user_id');

            $allowedStatuses = [
                \App\Enums\FolderStatus::DRAFT_TARGET->value,
                \App\Enums\FolderStatus::PENDING_TARGET_APPROVAL->value,
                \App\Enums\FolderStatus::TARGET_APPROVED->value,
                \App\Enums\FolderStatus::TARGET_RETURNED->value,
                \App\Enums\FolderStatus::TARGET_UNAPPROVED->value,
                \App\Enums\FolderStatus::DRAFT->value
            ];
            if (!in_array($activeFolder['status'], $allowedStatuses)) {
                return $this->respondError("You cannot revoke the cascade for a folder that has moved past the target setting phase or is locked.", 400);
            }

            $folderModel->db->transStart();

            // 1. Clear the memory
            $folderModel->update($folderId, ['routing_preset_id' => null]);

            if (!empty($members)) {
                $batchId = $activeFolder['parent_folder_id'] ?? $activeFolder['id'];
                $subFolders = $folderModel->whereIn('user_id', $memberIds)
                                          ->where('parent_folder_id', $batchId)->findAll();
                $subFolderIds = array_column($subFolders, 'id');

                if (!empty($subFolderIds)) {
                    // SQLite / MySQL foreign key safety: Delete documents inside child folders first
                    $documentModel = new \App\Models\DocumentModel();
                    $documentModel->whereIn('document_folder_id', $subFolderIds)->delete();

                    // Delete routings
                    $routingModel->whereIn('folder_id', $subFolderIds)->delete();

                    // Delete child folders
                    $folderModel->whereIn('id', $subFolderIds)->delete();
                }
            }

            // 2. Clean up the routing preset if it was soft-deleted and is no longer in use anywhere
            $inUseCount = $folderModel->where('routing_preset_id', $teamId)->countAllResults();
            if ($inUseCount === 0) {
                $deletedPreset = $presetModel->onlyDeleted()->find($teamId);
                if ($deletedPreset) {
                    $presetModel->delete($teamId, true); // Hard delete
                }
            }

            $folderModel->db->transComplete();
            return $this->respond(['status' => 'success', 'message' => 'Cascade revoked successfully.']);
        });
    }

    /**
     * Recomputes a folder's status from the combined verdict of all its evaluators:
     * any single "return for revision" sends the whole folder back, otherwise it
     * only becomes "Approved" once every assigned evaluator has approved it.
     */
    private function updateFolderConsensus($folderId) {
        $folderModel = new DocumentFolderModel();
        $routingModel = new EvaluationRoutingModel();
        
        $routings = $routingModel->where('folder_id', $folderId)->findAll();
                    
        if (empty($routings)) return;

        $hasRevision = false;
        $allApproved = true;

        foreach ($routings as $r) {
            if ($r['status'] === FolderStatus::REEVALUATE->value) $hasRevision = true;
            if ($r['status'] !== FolderStatus::APPROVED->value) $allApproved = false;
        }

        if ($hasRevision) {
            $folderModel->update($folderId, ['status' => FolderStatus::REEVALUATE->value]);
        } elseif ($allApproved) {
            $folderModel->update($folderId, ['status' => FolderStatus::APPROVED->value, 'rated_at' => date('Y-m-d H:i:s')]);
        } else {
            $folderModel->update($folderId, ['status' => FolderStatus::TO_EVALUATE->value, 'rated_at' => null]);
        }
    }

    /**
     * Automatically ensures the user's official performance paper exists inside a folder
     * based on their profile doc_type (e.g. OPCR, IPCR, DPCR, IPERF).
     */
    private function ensureUserDocumentExists($folderId, $userId, $folderTitle = '', $overrideDocType = null) {
        $documentModel = new DocumentModel();
        $userModel     = new UserModel();
        $templateModel = new TemplateModel();

        $user = $userModel->find($userId);
        $role = session()->get('role');

        // Check if user is an Executive (VPAA, VP, President) or Dean
        $plantilla = $userModel->getActivePlantillaDetails($userId);
        $posTitle = strtolower($plantilla['position'] ?? '');
        $userEmail = strtolower($user['email'] ?? '');
        $isExecutive = str_contains($posTitle, 'vpaa') 
                    || str_contains($posTitle, 'vice president') 
                    || str_contains($posTitle, 'president')
                    || str_contains($userEmail, 'vpaa');
        $isDean = str_contains($posTitle, 'dean') || str_contains($userEmail, 'dean');

        $defaultDoc = 'IPCR';
        if ($role === 'Admin' || $isExecutive) {
            $defaultDoc = 'OPCR';
        } elseif ($isDean) {
            $defaultDoc = 'DPCR';
        }

        $docType = $overrideDocType 
                ?: ((!empty($user['doc_type']) && $user['doc_type'] !== 'IPCR') 
                    ? strtoupper(trim($user['doc_type'])) 
                    : $defaultDoc);

        // Check if document already exists in this folder
        $existing = $documentModel->where('document_folder_id', $folderId)->first();
        if ($existing) {
            // Auto-upgrade from IPCR to OPCR if the user is an Executive (like VPAA)
            if ($isExecutive && strtoupper($existing['title']) === 'IPCR') {
                $opcrTemplate = $templateModel->where('title', 'OPCR')->first() ?? $templateModel->first();
                $opcrTabs = !empty($opcrTemplate['tabs']) ? (is_string($opcrTemplate['tabs']) ? json_decode($opcrTemplate['tabs'], true) : $opcrTemplate['tabs']) : [];
                $documentModel->update($existing['id'], [
                    'title' => 'OPCR',
                    'tabs'  => !empty($opcrTabs) ? $opcrTabs : $existing['tabs']
                ]);
            }
            return $existing['id'];
        }

        // Find official template for user's doc_type (IPCR, DPCR, OPCR, IPERF)
        $template = $templateModel->where('title', $docType)->first() 
                 ?? $templateModel->like('title', $docType)->first() 
                 ?? $templateModel->first();

        $initialTabs = [];
        if ($template && !empty($template['tabs'])) {
            $initialTabs = is_string($template['tabs']) ? json_decode($template['tabs'], true) : $template['tabs'];
        }

        if (empty($initialTabs)) {
            $initialTabs = [
                [
                    'id' => 'tab-' . uniqid(),
                    'title' => 'Target Form',
                    'formData' => [
                        'doc_type' => $docType,
                        'title' => $docType
                    ],
                    'content' => ''
                ]
            ];
        }

        return create_unique_row($documentModel, [
            'title'              => $docType,
            'document_folder_id' => $folderId,
            'tabs'               => $initialTabs,
            'is_target'          => 1
        ]);
    }

    /** POST /folder - Creates a new blank evaluation folder (in Draft) for the current user. */
    public function store() {
        return $this->tryOrFail(function() {
            $documentFolderModel = new DocumentFolderModel();
            $userId = session()->get('user_id');
            $title = trim($this->request->getPost('title')) ?: 'Untitled Evaluation';

            $role = session()->get('role');
            $status = ($role === 'Admin') ? FolderStatus::TARGET_APPROVED->value : FolderStatus::DRAFT_TARGET->value;

            $payload = [
                'title'              => resolve_unique_title($title, ['user_id' => $userId], 'title', $documentFolderModel),
                'user_id'            => $userId,
                'status'             => $status,
                'target_approved_at' => ($role === 'Admin') ? date('Y-m-d H:i:s') : null,
            ];

            $docTypes = ['ipcr', 'dpcr', 'opcr', 'iperf'];
            foreach ($docTypes as $type) {
                $payload["{$type}_target_start"] = str_replace('T', ' ', $this->request->getPost("{$type}_target_start")) ?: null;
                $payload["{$type}_target_end"]   = str_replace('T', ' ', $this->request->getPost("{$type}_target_end")) ?: null;
                $payload["{$type}_eval_start"]   = str_replace('T', ' ', $this->request->getPost("{$type}_eval_start")) ?: null;
                $payload["{$type}_eval_end"]     = str_replace('T', ' ', $this->request->getPost("{$type}_eval_end")) ?: null;
            }
            
            $newId = create_unique_row($documentFolderModel, $payload);

            if (!$newId) {
                return $this->respondError("Could not generate a unique ID.", 400);
            }

            // Immediately create the owner's official evaluation paper based on profile doc_type (OPCR for Admin)
            $role = session()->get('role');
            $overrideDocType = ($role === 'Admin') ? 'OPCR' : null;
            $this->ensureUserDocumentExists($newId, $userId, $title, $overrideDocType);

            return $this->respond(['status' => 'success', 'id' => $newId]);
        });
    }

    /** POST /folder/delete - Deletes a folder. Only the owning Admin can actually remove it. */
    public function destroy() {
        $folderId = $this->request->getPost('doc_id');
        $folderModel = new DocumentFolderModel();
        
        $userId = session()->get('user_id');
        $role   = session()->get('role');

        $folder = $folderModel->find($folderId);

        if (!$folder || $folder['user_id'] != $userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($role === 'Admin') {
            // evaluation_routings.folder_id and document_folders.parent_folder_id are
            // both ON DELETE CASCADE, so deleting the folder below already wipes the
            // whole subtree (children, grandchildren, etc.) and their eval routing in
            // one shot - nothing extra needed for that part.
            //
            // But that cascade happens at the SQL level, so it silently takes any
            // descendant's OWN routing_preset_id with it too - e.g. a Supervisor's own
            // cascaded team on their own folder, nested somewhere under this tree.
            // Walk the whole subtree first and collect every team reference in it, not
            // just this folder's own, so none of them get missed for the orphan check.
            $teamIds = [];
            $allFolderIds = [];
            $toVisit = [$folderId];
            
            while (!empty($toVisit)) {
                $currentId = array_pop($toVisit);
                $allFolderIds[] = $currentId;
                
                $current = $folderModel->find($currentId);
                if ($current && $current['routing_preset_id']) {
                    $teamIds[] = $current['routing_preset_id'];
                }
                foreach ($folderModel->where('parent_folder_id', $currentId)->findAll() as $child) {
                    $toVisit[] = $child['id'];
                }
            }

            // SQLite loses ON DELETE CASCADE for documents table when columns were dropped in migrations,
            // so we manually purge all documents in the folder subtree before deleting the folders.
            if (!empty($allFolderIds)) {
                $documentModel = new \App\Models\DocumentModel();
                $documentModel->whereIn('document_folder_id', $allFolderIds)->delete();
            }
            $teamIds = array_unique($teamIds);

            $folderModel->delete($folderId);

            // Any of those teams that were already archived and are no longer referenced
            // by any remaining folder are now truly orphaned - purge them for real.
            // routing_preset_members cascades with each one automatically.
            $presetModel = new RoutingPresetModel();
            foreach ($teamIds as $teamId) {
                $archived = $presetModel->onlyDeleted()->find($teamId);
                if ($archived && $folderModel->where('routing_preset_id', $teamId)->countAllResults() === 0) {
                    $presetModel->delete($teamId, true);
                }
            }
        }

        return $this->response->setJSON(['status' => 'success']);
    }

    /**
     * GET /folders/archived or /folders/archived/{folderId} - Displays archived folders.
     */
    public function archived($folderId = null) {
        if (session()->get('role') === 'TWG') {
            return redirect()->to(site_url('ratings'));
        }

        $userId = session()->get('user_id');

        $folderModel   = new DocumentFolderModel();
        $documentModel = new DocumentModel();
        $userModel     = new UserModel();
        $templateModel = new TemplateModel();

        $folders = $folderModel->where('user_id', $userId)
                               ->where('deleted_at IS NOT NULL')
                               ->orderBy('deleted_at', 'DESC')
                               ->findAll();

        if (!$folderId && !empty($folders)) {
            return redirect()->to('folders/archived/' . $folders[0]['id']);
        }

        $activeFolder = null;
        $myDocs = [];
        $ownerDocType = null;

        if ($folderId) {
            $activeFolder = $folderModel->find($folderId);
            if ($activeFolder && $activeFolder['user_id'] == $userId) {
                $this->ensureUserDocumentExists($folderId, $userId, $activeFolder['title']);
                $myDocs = $documentModel->where('document_folder_id', $folderId)->findAll();
                $owner = $userModel->find($activeFolder['user_id']);
                $ownerDocType = $owner['doc_type'] ?? null;
            }
        }

        return view('components/app_shell', [
            'sidebarFolders'   => $folders,
            'selectedFolderId' => $activeFolder['id'] ?? null,
            'mainView'         => 'document/_doc_rows',
            'templates'        => $templateModel->findAll(),
            'mainData'         => [
                'activeFolder'  => $activeFolder,
                'ownerDocType'  => $ownerDocType,
                'myDocs'        => $myDocs,
                'groupedGuides' => [],
                'isReadOnly'    => true,
                'presets'       => [],
                'isArchivedView'=> true
            ]
        ]);
    }

    /**
     * POST /folder/archive - Soft-deletes a folder (marks deleted_at timestamp).
     */
    public function archive() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $userId   = session()->get('user_id');
            $role     = session()->get('role');

            $folderModel = new DocumentFolderModel();
            $folder = $folderModel->find($folderId);

            if (!$folder) {
                return $this->respondError('Folder not found', 404);
            }

            if ($role !== 'Admin' && $folder['user_id'] != $userId) {
                return $this->respondError('Unauthorized', 403);
            }

            $now = date('Y-m-d H:i:s');
            
            // Soft delete (archive) this folder and its child folders
            $toVisit = [$folderId];
            $allIds = [];
            while (!empty($toVisit)) {
                $curr = array_pop($toVisit);
                $allIds[] = $curr;
                $children = $folderModel->where('parent_folder_id', $curr)->findAll();
                foreach ($children as $child) {
                    $toVisit[] = $child['id'];
                }
            }

            foreach ($allIds as $fId) {
                $folderModel->update($fId, ['deleted_at' => $now]);
            }

            session()->remove('active_folder_id');

            return $this->respond([
                'status'  => 'success',
                'message' => 'Folder archived successfully.'
            ]);
        });
    }

    /**
     * POST /folder/unarchive - Restores an archived folder (clears deleted_at timestamp).
     */
    public function unarchive() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $userId   = session()->get('user_id');
            $role     = session()->get('role');

            $folderModel = new DocumentFolderModel();
            $folder = $folderModel->find($folderId);

            if (!$folder) {
                return $this->respondError('Folder not found', 404);
            }

            if ($role !== 'Admin' && $folder['user_id'] != $userId) {
                return $this->respondError('Unauthorized', 403);
            }

            // Restore this folder and its child folders
            $toVisit = [$folderId];
            $allIds = [];
            while (!empty($toVisit)) {
                $curr = array_pop($toVisit);
                $allIds[] = $curr;
                $children = $folderModel->where('parent_folder_id', $curr)->findAll();
                foreach ($children as $child) {
                    $toVisit[] = $child['id'];
                }
            }

            foreach ($allIds as $fId) {
                $folderModel->update($fId, ['deleted_at' => null]);
            }

            session()->set('active_folder_id', $folderId);

            return $this->respond([
                'status'  => 'success',
                'message' => 'Folder restored from archive successfully.'
            ]);
        });
    }

    /**
     * POST /folder/update - Admin-only: retitles/reschedules a master folder and syncs
     * the same title/dates to every cascaded child folder. If the new window makes a
     * child active again, that child's evaluation progress is safely reset (submitted
     * work is preserved as "Submitted" rather than wiped back to "Draft").
     */
    public function update() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $folderModel = new DocumentFolderModel();

            if (session()->get('role') !== 'Admin') return $this->respondError("Unauthorized to edit folders.", 400);

            $title = $this->request->getPost('title');
            
            $docTypes = ['ipcr', 'dpcr', 'opcr', 'iperf'];
            $folderData = ['title' => $title];
            
            foreach ($docTypes as $type) {
                $folderData["{$type}_target_start"] = str_replace('T', ' ', $this->request->getPost("{$type}_target_start")) ?: null;
                $folderData["{$type}_target_end"]   = str_replace('T', ' ', $this->request->getPost("{$type}_target_end")) ?: null;
                $folderData["{$type}_eval_start"]   = str_replace('T', ' ', $this->request->getPost("{$type}_eval_start")) ?: null;
                $folderData["{$type}_eval_end"]     = str_replace('T', ' ', $this->request->getPost("{$type}_eval_end")) ?: null;
            }

            $now = date('Y-m-d H:i:s');
            $routingModel = new EvaluationRoutingModel();
            
            $targetStatuses = [
                FolderStatus::TARGET_APPROVED->value,
                FolderStatus::SUBMITTED->value,
                FolderStatus::TO_EVALUATE->value,
                FolderStatus::UNEVALUATED->value,
                FolderStatus::EVALUATED->value,
                FolderStatus::APPROVED->value
            ];

            $didResetAny = false;

            // 1. Update the Admin's Master Folder
            $masterFolder = $folderModel->find($folderId);
            $folderModel->update($folderId, $folderData);

            // 2. Fetch and Process Cascaded Child Folders
            // We join users to get the child's doc_type
            $db = \Config\Database::connect();
            $childFolders = $db->table('document_folders df')
                ->select('df.*, u.doc_type')
                ->join('users u', 'u.id = df.user_id')
                ->where('df.parent_folder_id', $folderId)
                ->get()->getResultArray();

            if (!empty($childFolders)) {
                foreach ($childFolders as $child) {
                    $childData = $folderData;
                    
                    $userDocType = strtolower($child['doc_type'] ?? 'ipcr');
                    if (!in_array($userDocType, $docTypes)) $userDocType = 'ipcr';

                    $targetStart = $folderData["{$userDocType}_target_start"];
                    $targetEnd   = $folderData["{$userDocType}_target_end"];
                    $dateStart   = $folderData["{$userDocType}_eval_start"];
                    $dateEnd     = $folderData["{$userDocType}_eval_end"];
                    
                    $isEvalFuture  = !empty($dateStart) && $dateStart > $now;
                    $isEvalOpen    = !empty($dateStart) && !empty($dateEnd) && $dateStart <= $now && $dateEnd >= $now;
                    $isEvalExpired = !empty($dateEnd) && $dateEnd < $now;
                    $isTargetNowActive = !empty($targetEnd) && $targetEnd >= $now;

                    $isInTargetStatus = in_array($child['status'], $targetStatuses);
                    
                    if ($child['status'] === FolderStatus::TARGET_UNAPPROVED->value && $isTargetNowActive) {
                        $childData['status'] = FolderStatus::DRAFT_TARGET->value;
                    }

                    if ($isInTargetStatus) {
                        if ($isEvalOpen) {
                            $didResetAny = true;
                            $childData['status'] = FolderStatus::TO_EVALUATE->value;
                            $childData['final_rating'] = null; 
                            $childData['rated_at']     = null; 
                            $routingModel->where('folder_id', $child['id'])
                                         ->set(['status' => FolderStatus::DRAFT->value])
                                         ->update();
                        } elseif ($isEvalFuture) {
                            $didResetAny = true;
                            $childData['status'] = FolderStatus::TARGET_APPROVED->value;
                            $childData['final_rating'] = null; 
                            $childData['rated_at']     = null; 
                            $routingModel->where('folder_id', $child['id'])
                                         ->set(['status' => FolderStatus::DRAFT->value])
                                         ->update();
                        } elseif ($isEvalExpired && in_array($child['status'], [FolderStatus::TARGET_APPROVED->value, FolderStatus::TO_EVALUATE->value, FolderStatus::SUBMITTED->value])) {
                            $childData['status'] = FolderStatus::UNEVALUATED->value;
                        }
                    }

                    $folderModel->update($child['id'], $childData);
                }
            }

            // Instantly trigger cronjob logic to handle shortened due dates securely
            $folderModel->updateTimeBasedStatuses();

            // 3. Set the dynamic success message
            $message = 'Folder updated and synced.';
            if ($didResetAny) {
                $message .= " The timeline was adjusted: active/expired cascaded folders have been safely reset.";
            }

            return $this->respond(['status' => 'success', 'message' => $message]);
        });
    }

    /** POST /folder/submit - Owner submits a Draft folder for evaluation (requires a Basis Target document set). */
    public function submit() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $userId = session()->get('user_id');
            $folderModel = new DocumentFolderModel();

            $folder = $folderModel->find($folderId);

            if (!$folder || $folder['status'] !== FolderStatus::DRAFT->value) {
                return $this->respondError("This folder cannot be submitted at this time.", 400);
            }

            if (!$folder || $folder['user_id'] != $userId) {
                return $this->respondError("Unauthorized to submit this folder.", 400);
            }

            // --- NEW: Target Document Validation ---
            $documentModel = new DocumentModel();
            $hasTarget = $documentModel->where('document_folder_id', $folderId)
                                       ->where('is_target', 1)
                                       ->countAllResults();
            
            if ($hasTarget == 0) {
                return $this->respondError("Submission Failed: You must set at least one document as the Basis Target (★) before submitting.", 400);
            }
            // ---------------------------------------

            $now = date('Y-m-d H:i:s');

            // If the eval window is already open at submit time, skip straight to
            // "To Evaluate" instead of leaving it at "Submitted" for up to a minute
            // waiting on updateTimeBasedStatuses()'s cron sweep - keep this condition
            // in sync with that method's "Submitted -> To Evaluate" check.
            $dates = $folderModel->getFolderDates($folder);
            $windowAlreadyOpen = !empty($dates['eval_date_start']) && !empty($dates['eval_date_end'])
                && $dates['eval_date_start'] <= $now && $dates['eval_date_end'] >= $now;

            $folderModel->update($folderId, [
                'status'       => $windowAlreadyOpen ? FolderStatus::TO_EVALUATE->value : FolderStatus::SUBMITTED->value,
                'submitted_at' => $now
            ]);

            $message = $windowAlreadyOpen
                ? 'Folder submitted - the evaluation period is already open, so it has moved straight to evaluation.'
                : 'Folder submitted for evaluation.';

            $response = $this->respond(['status' => 'success', 'message' => $message]);

            if ($windowAlreadyOpen) {
                $userModel = new UserModel();
                if (!$userModel->hasRole($userId, 'Admin')) {
                    $owner = $userModel->find($userId);
                    queue_email(
                        $owner['email'],
                        'Action Required: Evaluation Period Open',
                        render_email('evaluation_period_open', [
                            'firstName' => $owner['first_name'],
                            'title'     => $folder['title'],
                            'link'      => site_url("folders/" . $folderId),
                        ])
                    );
                    return dispatch_email_now($response, 1);
                }
            }

            return $response;
        });
    }

    /** POST /folder/unsubmit - Owner recalls a Submitted folder back to Draft, only while the eval window is still open. */
    public function unsubmit() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $userId = session()->get('user_id');
            $folderModel = new DocumentFolderModel();

            $folder = $folderModel->find($folderId);

            if (!$folder || $folder['status'] !== FolderStatus::SUBMITTED->value) {
                return $this->respondError("This folder cannot be unsubmitted at this time.", 400);
            }

            if (!$folder || $folder['user_id'] != $userId) return $this->respondError("Unauthorized.", 400);

            $dates = $folderModel->getFolderDates($folder);
            if (!empty($dates['eval_date_end']) && date('Y-m-d H:i:s') > $dates['eval_date_end']) {
                return $this->respondError("Cannot unsubmit: Evaluation window has closed.", 400);
            }

            $folderModel->update($folderId, ['status' => FolderStatus::DRAFT->value, 'submitted_at' => null]);
            return $this->respond(['status' => 'success', 'message' => 'Submission revoked.']);
        });
    }

    /**
     * POST /folder/evaluate - Owner locks in their self-rating (final_rating) on the
     * target document, then emails every evaluator assigned to this folder so they
     * know it's ready for their review.
     */
    public function evaluate() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $finalRating = $this->request->getPost('final_rating');
            $userId = session()->get('user_id');
            $folderModel = new DocumentFolderModel();

            $folder = $folderModel->find($folderId);

            if (!$folder || $folder['user_id'] != $userId) {
                return $this->respondError("Unauthorized to evaluate this folder.", 400);
            }

            if (!in_array($folder['status'], [FolderStatus::TO_EVALUATE->value, FolderStatus::REEVALUATE->value])) {
                return $this->respondError("This folder cannot be evaluated at this time.", 400);
            }

            $folderModel->update($folderId, [
                'status'       => FolderStatus::EVALUATED->value,
                'final_rating' => $finalRating !== '' && $finalRating !== null ? (float) $finalRating : null,
                'rated_at'     => date('Y-m-d H:i:s')
            ]);

            $userModel    = new UserModel();
            $routingModel = new EvaluationRoutingModel();

            $subordinate = $userModel->find($folder['user_id']);
            $routings = $routingModel->where('folder_id', $folderId)->findAll();
            
            foreach ($routings as $route) {
                $evaluator = $userModel->find($route['evaluator_id']);

                // Admins already see every folder on their dashboard without needing
                // a nudge, and oversee the whole system rather than being a specific
                // assigned reviewer - so they're skipped even if routed as one.
                if ($evaluator && !$userModel->hasRole($evaluator['id'], 'Admin')) {
                    $link = site_url("ratings/show/" . $folderId);

                    queue_email(
                        $evaluator['email'],
                        'Pending Review: ' . $subordinate['first_name'] . ' has evaluated their folder',
                        render_email('pending_review', [
                            'evaluatorFirstName'    => $evaluator['first_name'],
                            'subordinateFirstName'  => $subordinate['first_name'],
                            'subordinateLastName'   => $subordinate['last_name'],
                            'folderTitle'           => $folder['title'],
                            'link'                  => $link,
                        ])
                    );
                }
            }

            return $this->respond(['status' => 'success', 'message' => 'Folder successfully evaluated and locked.']);
        });
    }

    /**
     * POST /folder/submit_target - The employee submits their targets for approval.
     */
    public function submitTarget() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $userId = session()->get('user_id');
            $folderModel = new DocumentFolderModel();
            
            $folder = $folderModel->find($folderId);

            if (!$folder || $folder['user_id'] != $userId) {
                return $this->respondError("Unauthorized to submit targets for this folder.", 400);
            }

            if (!empty($folder['target_date_end']) && date('Y-m-d H:i:s') > $folder['target_date_end']) {
                return $this->respondError("The target setting period has already ended.", 400);
            }

            // --- STRICT SPMS MODE: Parent Basis Target Validation ---
            if (!empty($folder['parent_folder_id'])) {
                $parentFolder = $folderModel->find($folder['parent_folder_id']);
                $parentRolePivot = (new \App\Models\UserRoleModel())->where('user_id', $parentFolder['user_id'])->first();
                $parentRoleName = $parentRolePivot ? ((new \App\Models\RoleModel())->find($parentRolePivot['role_id'])['name'] ?? '') : '';
                $isParentAdmin = ($parentRoleName === 'Admin');

                $myDoc = (new \App\Models\DocumentModel())->where('document_folder_id', $folderId)->where('is_target', 1)->first()
                      ?? (new \App\Models\DocumentModel())->where('document_folder_id', $folderId)->first();
                $isMyDocOpcr = (strtoupper($myDoc['title'] ?? '') === 'OPCR');

                if (!$isParentAdmin && !$isMyDocOpcr) {
                    if ($parentFolder && $parentFolder['status'] !== FolderStatus::TARGET_APPROVED->value) {
                        $parentTitle = $parentFolder['title'] ?? 'Superior';
                        return $this->respondError("Cannot submit targets yet: The superior basis commitments (\"{$parentTitle}\") have not been approved by the higher-up yet. Under SPMS cascading rules, individual commitments require approved superior targets as a basis.", 400);
                    }
                }
            }

            // --- Target Document Validation ---
            $documentModel = new \App\Models\DocumentModel();
            $hasTarget = $documentModel->where('document_folder_id', $folderId)
                                       ->where('is_target', 1)
                                       ->countAllResults();
            
            if ($hasTarget == 0) {
                return $this->respondError("Submission Failed: You must set at least one document as the Basis Target (★) before submitting targets.", 400);
            }
            // ---------------------------------------

            $folderModel->update($folderId, [
                'status' => FolderStatus::PENDING_TARGET_APPROVAL->value,
                'target_submitted_at' => date('Y-m-d H:i:s')
            ]);
            
            return $this->respond(['status' => 'success', 'message' => 'Targets submitted for approval.']);
        });
    }

    /**
     * POST /folder/unsubmit_target - The employee un-submits targets to revise them.
     */
    public function unsubmitTarget() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $userId = session()->get('user_id');
            $folderModel = new DocumentFolderModel();
            
            $folder = $folderModel->find($folderId);

            if (!$folder || $folder['user_id'] != $userId) {
                return $this->respondError("Unauthorized to unsubmit targets for this folder.", 400);
            }

            if (!empty($folder['target_date_end']) && date('Y-m-d H:i:s') > $folder['target_date_end']) {
                return $this->respondError("The target setting period has already ended.", 400);
            }

            if ($folder['status'] !== FolderStatus::PENDING_TARGET_APPROVAL->value) {
                return $this->respondError("Only pending targets can be unsubmitted.", 400);
            }

            $folderModel->update($folderId, [
                'status' => FolderStatus::DRAFT_TARGET->value,
                'target_submitted_at' => null
            ]);
            
            return $this->respond(['status' => 'success', 'message' => 'Targets unsubmitted successfully.']);
        });
    }

    /**
     * POST /folder/approve_target - The supervisor or HRDO/Admin approves the targets.
     * Supports optional 'release_to_deans' parameter to automatically spawn collegiate DPCRs for all Deans.
     */
    public function approveTarget() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $releaseToDeans = (bool) $this->request->getPost('release_to_deans');
            $folderModel = new DocumentFolderModel();
            
            $folder = $folderModel->find($folderId);
            if (!$folder) return $this->respondError("Folder not found.", 400);

            $dates = $folderModel->getFolderDates($folder);
            
            if (!empty($dates['target_date_end']) && date('Y-m-d H:i:s') > $dates['target_date_end']) {
                return $this->respondError("The target setting period has already ended.", 400);
            }
            
            $folderModel->update($folderId, [
                'status' => FolderStatus::TARGET_APPROVED->value,
                'target_approved_at' => date('Y-m-d H:i:s')
            ]);

            // Clean up temporary target review notes so the approved commitment starts fresh for evaluation
            $documentModel = new \App\Models\DocumentModel();
            $folderDocs = $documentModel->where('document_folder_id', $folderId)->findAll();
            foreach ($folderDocs as $fDoc) {
                $tabs = $fDoc['tabs'] ?? [];
                if (is_string($tabs)) $tabs = json_decode($tabs, true) ?: [];
                $modified = false;
                foreach ($tabs as &$tab) {
                    if (!empty($tab['formData']['categories'])) {
                        foreach (['core', 'strategic', 'support'] as $cat) {
                            if (!empty($tab['formData']['categories'][$cat])) {
                                foreach ($tab['formData']['categories'][$cat] as &$row) {
                                    if (isset($row['remarks']) && $row['remarks'] !== '') {
                                        $row['remarks'] = '';
                                        $modified = true;
                                    }
                                }
                            }
                        }
                    }
                }
                if ($modified) {
                    $documentModel->update($fDoc['id'], ['tabs' => $tabs]);
                }
            }
            
            // --- AUTOMATIC "APPROVE & RELEASE" TO ALL COLLEGE DEANS ---
            $releasedCount = 0;
            if ($releaseToDeans) {
                $userModel = new \App\Models\UserModel();
                
                // Find all active accounts holding a Dean position (or dean test accounts)
                $deanUsers = $userModel
                    ->select('users.id, users.first_name, users.last_name, users.doc_type')
                    ->join('plantillas p', 'p.user_id = users.id AND p.ended_at IS NULL', 'left')
                    ->join('positions pos', 'pos.id = p.position_id', 'left')
                    ->where('users.is_active', 1)
                    ->groupStart()
                        ->like('pos.title', 'Dean', 'both')
                        ->orLike('users.email', 'dean', 'both')
                    ->groupEnd()
                    ->groupBy('users.id')
                    ->findAll();

                foreach ($deanUsers as $dean) {
                    $existing = $folderModel->where('parent_folder_id', $folder['id'])
                                            ->where('user_id', $dean['id'])->first();
                    if (!$existing) {
                        $newFolderId = create_unique_row($folderModel, [
                            'title'               => $folder['title'],
                            'user_id'             => $dean['id'],
                            'parent_folder_id'    => $folder['id'],
                            'status'              => FolderStatus::DRAFT_TARGET->value,
                            'ipcr_target_start'   => $folder['ipcr_target_start'],
                            'ipcr_target_end'     => $folder['ipcr_target_end'],
                            'ipcr_eval_start'     => $folder['ipcr_eval_start'],
                            'ipcr_eval_end'       => $folder['ipcr_eval_end'],
                            'dpcr_target_start'   => $folder['dpcr_target_start'],
                            'dpcr_target_end'     => $folder['dpcr_target_end'],
                            'dpcr_eval_start'     => $folder['dpcr_eval_start'],
                            'dpcr_eval_end'       => $folder['dpcr_eval_end'],
                            'opcr_target_start'   => $folder['opcr_target_start'],
                            'opcr_target_end'     => $folder['opcr_target_end'],
                            'opcr_eval_start'     => $folder['opcr_eval_start'],
                            'opcr_eval_end'       => $folder['opcr_eval_end'],
                            'iperf_target_start'  => $folder['iperf_target_start'],
                            'iperf_target_end'    => $folder['iperf_target_end'],
                            'iperf_eval_start'    => $folder['iperf_eval_start'],
                            'iperf_eval_end'      => $folder['iperf_eval_end'],
                        ]);
                        
                        // Force DPCR paper generation for the Dean
                        $this->ensureUserDocumentExists($newFolderId, $dean['id'], $folder['title'], 'DPCR');
                        $releasedCount++;
                    }
                }
            }

            $msg = $releaseToDeans 
                ? "OPCR targets approved and successfully released to {$releasedCount} College Dean(s)." 
                : "Targets approved successfully.";
            
            return $this->respond(['status' => 'success', 'message' => $msg]);
        });
    }

    public function unapproveTarget() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $folderModel = new DocumentFolderModel();
            
            $folder = $folderModel->find($folderId);
            $dates = $folderModel->getFolderDates($folder);
            
            if (!empty($dates['target_date_end']) && date('Y-m-d H:i:s') > $dates['target_date_end']) {
                return $this->respondError("Cannot remove approval: The target setting window has already closed.", 400);
            }
            
            if ($folder['status'] !== FolderStatus::TARGET_APPROVED->value) {
                return $this->respondError("This folder is not currently approved.", 400);
            }
            
            $folderModel->update($folderId, [
                'status' => FolderStatus::PENDING_TARGET_APPROVAL->value,
                'target_approved_at' => null
            ]);
            
            return $this->respond(['status' => 'success', 'message' => 'Approval removed.']);
        });
    }

    /**
     * POST /folder/return_target - The supervisor returns targets for revision.
     */
    public function returnTarget() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $reason   = trim($this->request->getPost('reason') ?? '');
            $folderModel = new DocumentFolderModel();
            
            $folder = $folderModel->find($folderId);
            if (!empty($folder['target_date_end']) && date('Y-m-d H:i:s') > $folder['target_date_end']) {
                return $this->respondError("The target setting period has already ended.", 400);
            }
            
            $folderModel->update($folderId, [
                'status' => FolderStatus::TARGET_RETURNED->value
            ]);
            
            if (!empty($reason)) {
                $this->appendDocumentRevisionTrail($folderId, $reason, 'target');
            }
            
            return $this->respond(['status' => 'success', 'message' => 'Targets returned for revision.']);
        });
    }

    /**
     * POST /folder/approve - The current evaluator approves their assigned folder,
     * then re-checks the folder's overall consensus status and emails the owner.
     */
    public function approve() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $routingModel = new EvaluationRoutingModel();

            $routingModel->where('folder_id', $folderId)
                ->where('evaluator_id', session()->get('user_id'))
                ->set(['status' => FolderStatus::APPROVED->value, 'updated_at' => date('Y-m-d H:i:s')])
                ->update();

            $this->updateFolderConsensus($folderId);

            $userModel = new UserModel();
            $folderModel = new DocumentFolderModel();
            $folder = $folderModel->find($folderId);
            $subordinate = $userModel->find($folder['user_id']);
            $supervisor = $userModel->find(session()->get('user_id'));
            $link = site_url("folders/" . $folderId);

            // Admins oversee the whole system rather than being an evaluated
            // employee, so they don't get this notification even if it's their
            // own folder that just got approved.
            if (!$userModel->hasRole($subordinate['id'], 'Admin')) {
                queue_email($subordinate['email'], 'Folder Approved: ' . $folder['title'], render_email('folder_approved', [
                    'firstName'           => $subordinate['first_name'],
                    'supervisorFirstName' => $supervisor['first_name'],
                    'supervisorLastName'  => $supervisor['last_name'],
                    'folderTitle'         => $folder['title'],
                    'link'                => $link,
                ]));
            }

            return $this->respond(['status' => 'success', 'message' => 'Approved!']);
        });
    }

    /**
     * POST /folder/unapprove - Reverts the current evaluator's approval back to TO_EVALUATE.
     */
    public function unapprove() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $routingModel = new EvaluationRoutingModel();
            $folderModel = new DocumentFolderModel();
            
            $folder = $folderModel->find($folderId);
            $dates = $folderModel->getFolderDates($folder);
            
            if (!empty($dates['eval_date_end']) && date('Y-m-d H:i:s') > $dates['eval_date_end']) {
                return $this->respondError("Cannot remove approval: The evaluation window has already closed.", 400);
            }

            $isAdmin = (new UserModel())->hasRole(session()->get('user_id'), 'Admin');

            if ($isAdmin) {
                $routingModel->where('folder_id', $folderId)
                    ->set(['status' => FolderStatus::TO_EVALUATE->value, 'updated_at' => date('Y-m-d H:i:s')])
                    ->update();
            } else {
                // Check if actually approved by this user
                $routing = $routingModel->where('folder_id', $folderId)->where('evaluator_id', session()->get('user_id'))->first();
                if (!$routing || $routing['status'] !== FolderStatus::APPROVED->value) {
                    return $this->respondError("You have not approved this folder.", 400);
                }

                $routingModel->where('folder_id', $folderId)
                    ->where('evaluator_id', session()->get('user_id'))
                    ->set(['status' => FolderStatus::TO_EVALUATE->value, 'updated_at' => date('Y-m-d H:i:s')])
                    ->update();
            }

            $this->updateFolderConsensus($folderId);

            return $this->respond(['status' => 'success', 'message' => 'Approval removed!']);
        });
    }

    /**
     * POST /folder/update_score - Admin or supervisor manually edits the final score
     */
    public function updateScore() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $score = $this->request->getPost('score');
            $userId = session()->get('user_id');

            $folderModel = new DocumentFolderModel();
            $folder = $folderModel->find($folderId);
            
            if (!$folder) {
                return $this->respondError("Folder not found.", 404);
            }

            $userModel = new UserModel();
            $isAdmin = $userModel->hasRole($userId, 'Admin');

            $routingModel = new EvaluationRoutingModel();
            $isEvaluator = $routingModel->where('folder_id', $folderId)->where('evaluator_id', $userId)->countAllResults() > 0;

            if (!$isAdmin && !$isEvaluator) {
                return $this->respondError("Unauthorized to update this score.", 403);
            }

            $folderModel->update($folderId, [
                'final_rating' => $score !== '' && $score !== null ? (float) $score : null,
                'updated_at'   => date('Y-m-d H:i:s')
            ]);

            return $this->respond(['status' => 'success', 'message' => 'Score updated successfully.']);
        });
    }

    /**
     * POST /folder/twg_approve - TWG marks the folder with twg status
     */
    public function twgApprove() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $status = $this->request->getPost('status');
            
            if (!in_array($status, [FolderStatus::TWG_APPROVED->value, FolderStatus::TWG_DISAPPROVED->value])) {
                return $this->fail('Invalid status');
            }
            
            $folderModel = new DocumentFolderModel();
            
            $folderModel->update($folderId, [
                'status' => $status, 
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            return $this->respond(['status' => 'success', 'message' => 'Status updated successfully!']);
        });
    }

    /**
     * POST /folder/return - The current evaluator sends the folder back for revision
     * instead of approving it, then re-checks consensus and emails the owner.
     */
    public function returnRevision() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $reason   = trim($this->request->getPost('reason') ?? '');
            $routingModel = new EvaluationRoutingModel();

            $routingModel->where('folder_id', $folderId)
                ->where('evaluator_id', session()->get('user_id'))
                ->set(['status' => FolderStatus::REEVALUATE->value, 'updated_at' => date('Y-m-d H:i:s')])
                ->update();

            $this->updateFolderConsensus($folderId);

            if (!empty($reason)) {
                $this->appendDocumentRevisionTrail($folderId, $reason, 'evaluation');
            }

            $userModel = new UserModel();
            $folderModel = new DocumentFolderModel();
            $folder = $folderModel->find($folderId);
            $subordinate = $userModel->find($folder['user_id']);
            $supervisor = $userModel->find(session()->get('user_id'));
            $link = site_url("folders/" . $folderId);

            // Admins oversee the whole system rather than being an evaluated
            // employee, so they don't get this notification even if it's their
            // own folder that just got sent back for revision.
            if (!$userModel->hasRole($subordinate['id'], 'Admin')) {
                queue_email($subordinate['email'], 'Action Required: Folder Returned for Revision', render_email('folder_returned', [
                    'firstName'           => $subordinate['first_name'],
                    'supervisorFirstName' => $supervisor['first_name'],
                    'supervisorLastName'  => $supervisor['last_name'],
                    'folderTitle'         => $folder['title'],
                    'link'                => $link,
                ]));
            }

            return $this->respond(['status' => 'success', 'message' => 'Returned for revision.']);
        });
    }

    /**
     * Appends a structured revision reason / feedback to the primary document's formData.
     */
    private function appendDocumentRevisionTrail($folderId, $reason, $phase = 'target') {
        if (empty(trim($reason))) return;

        $documentModel = new DocumentModel();
        $userModel = new UserModel();
        $reviewerId = session()->get('user_id');
        $reviewer = $userModel->find($reviewerId);
        $plantilla = $userModel->getActivePlantillaDetails($reviewerId);
        
        $reviewerName = trim(($reviewer['first_name'] ?? '') . ' ' . ($reviewer['last_name'] ?? ''));
        $reviewerRole = $plantilla['position'] ?? (session()->get('role') ?: 'Reviewer');

        $doc = $documentModel->where('document_folder_id', $folderId)->where('is_target', 1)->first()
            ?? $documentModel->where('document_folder_id', $folderId)->first();

        if ($doc) {
            $tabs = $doc['tabs'] ?? [];
            if (is_string($tabs)) $tabs = json_decode($tabs, true) ?: [];
            if (!empty($tabs)) {
                if (!isset($tabs[0]['formData'])) $tabs[0]['formData'] = [];
                if (!isset($tabs[0]['formData']['revisionHistory'])) $tabs[0]['formData']['revisionHistory'] = [];

                $tabs[0]['formData']['revisionHistory'][] = [
                    'id'            => uniqid('rev_'),
                    'reviewer_id'   => $reviewerId,
                    'reviewer_name' => $reviewerName,
                    'reviewer_role' => $reviewerRole,
                    'phase'         => $phase,
                    'reason'        => trim($reason),
                    'created_at'    => date('M d, Y h:i A')
                ];

                $documentModel->update($doc['id'], ['tabs' => $tabs]);
            }
        }
    }
}