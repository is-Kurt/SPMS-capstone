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
        $userId   = session()->get('user_id');

        $folderModel   = new DocumentFolderModel();
        $documentModel = new DocumentModel();
        $presetModel   = new RoutingPresetModel();
        $userModel     = new UserModel();

        $folders = $folderModel->where('user_id', $userId)->orderBy('created_at', 'DESC')->findAll();

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
                                    'id'   => $adminInfo['id'],
                                    'name' => $adminInfo['first_name'] . ' ' . $adminInfo['last_name'],
                                    'role' => $adminInfo['admin_position'] ?? 'System Administrator'
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

        $templateModel = new TemplateModel();
        
        return view('app_shell', [
            'sidebarFolders'   => $folders,
            'selectedFolderId' => $activeFolder['id'] ?? null,
            'mainView'         => 'document/_doc_rows',
            'templates'        => $templateModel->findAll(),
            'mainData'         => [
                'activeFolder'  => $activeFolder,
                'myDocs'        => $myDocs,
                'groupedGuides' => $groupedGuides,
                'isReadOnly'    => $isReadOnly,
                'presets'       => $presets
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

            $allowedStatuses = [\App\Enums\FolderStatus::DRAFT->value, \App\Enums\FolderStatus::SUBMITTED->value];
            if (!in_array($activeFolder['status'], $allowedStatuses)) {
                throw new \Exception("You cannot cascade this folder because it has already moved past the drafting/submission phase.");
            }
            if (!$activeFolder) throw new \Exception("Folder not found.");

            $members = $presetMemberModel->where('preset_id', $teamId)->findAll();
            if (empty($members)) throw new \Exception("The selected team has no members.");

            $folderModel->db->transStart();

            $folderModel->update($folderId, ['routing_preset_id' => $teamId]);
            $emailsQueued = 0;
            if ($role === 'Admin') {
                foreach ($members as $member) {
                    $exists = $folderModel->where('user_id', $member['user_id'])
                                          ->where('parent_folder_id', $activeFolder['id'])->first();
                    
                    if (!$exists) {
                        $newFolderId = create_unique_row($folderModel, [
                            'title'            => $activeFolder['title'],
                            'user_id'          => $member['user_id'],
                            'parent_folder_id' => $activeFolder['id'],
                            'eval_date_start'  => $activeFolder['eval_date_start'],
                            'eval_date_end'    => $activeFolder['eval_date_end'],
                            'status'           => FolderStatus::DRAFT->value
                        ]);
                    } else {
                        $newFolderId = $exists['id']; 
                    }

                    if (!$exists) {
                        $memberInfo = $userModel->find($member['user_id']);

                        // Defense-in-depth, not just relying on the picker: an Admin
                        // building this team (unlike a Supervisor) isn't filtered by
                        // getEligibleTeamMembers(), so another Admin could technically
                        // end up as a "member" here. Admins don't get evaluated-employee
                        // notifications either way.
                        if ($memberInfo && !$userModel->hasRole($memberInfo['id'], 'Admin')) {
                            $link = site_url("folders/" . $newFolderId);

                            queue_email(
                                $memberInfo['email'],
                                'New Evaluation Folder: Drafting Period Open',
                                render_email('folder_assigned', [
                                    'firstName' => $memberInfo['first_name'],
                                    'link'      => $link,
                                ])
                            );
                            $emailsQueued++;
                        }
                    }
                }
                $message = "Batch evaluation distributed to team members.";
            } else {
                $batchId = $activeFolder['parent_folder_id'] ?? $activeFolder['id'];

                foreach ($members as $member) {
                    $subFolder = $folderModel->where('user_id', $member['user_id'])
                                             ->where('parent_folder_id', $batchId)->first();
                    
                    if ($subFolder) {
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
            $teamId   = $this->request->getPost('team_id');
            $userId   = session()->get('user_id');
            $role     = session()->get('role');

            $folderModel = new DocumentFolderModel();
            $routingModel = new EvaluationRoutingModel();
            $presetMemberModel = new RoutingPresetMemberModel();
            
            $activeFolder = $folderModel->find($folderId);
            $members = $presetMemberModel->where('preset_id', $teamId)->findAll();
            $memberIds = array_column($members, 'user_id');

            $allowedStatuses = [\App\Enums\FolderStatus::DRAFT->value, \App\Enums\FolderStatus::SUBMITTED->value];
            if (!in_array($activeFolder['status'], $allowedStatuses)) {
                throw new \Exception("You cannot revoke the cascade for a folder that is currently being evaluated or is locked.");
            }

            $folderModel->db->transStart();

            // 1. Clear the memory
            $folderModel->update($folderId, ['routing_preset_id' => null]);

            if (!empty($members)) {
                if ($role === 'Admin') {
                    $folderModel->where('parent_folder_id', $activeFolder['id'])
                                ->whereIn('user_id', $memberIds)->delete();
                } else {
                    $batchId = $activeFolder['parent_folder_id'] ?? $activeFolder['id'];
                    $subFolders = $folderModel->whereIn('user_id', $memberIds)
                                            ->where('parent_folder_id', $batchId)->findAll();
                    $subFolderIds = array_column($subFolders, 'id');

                    if (!empty($subFolderIds)) {
                        // Using Model to delete
                        $routingModel->where('evaluator_id', $userId)
                                    ->where('evaluator_folder_id', $folderId)
                                    ->whereIn('folder_id', $subFolderIds)->delete();
                    }
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
        }
    }

    /** POST /folder - Creates a new blank evaluation folder (in Draft) for the current user. */
    public function store() {
        return $this->tryOrFail(function() {
            $documentFolderModel = new DocumentFolderModel();
            $userId = session()->get('user_id');
            $title = trim($this->request->getPost('title')) ?: 'Untitled Evaluation';

            $startDate = $this->request->getPost('eval_date_start');
            $endDate   = $this->request->getPost('eval_date_end');

            $payload = [
                'title'           => resolve_unique_title($title, ['user_id' => $userId], 'title', $documentFolderModel),
                'user_id'         => $userId,
                'eval_date_start' => $startDate ?: null,
                'eval_date_end'   => $endDate ?: null,
                'status'          => FolderStatus::DRAFT->value,
            ];
            
            $newId = create_unique_row($documentFolderModel, $payload);

            if (!$newId) {
                throw new \Exception("Could not generate a unique ID.");
            }

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
            $folderModel->delete($folderId);
        }

        return $this->response->setJSON(['status' => 'success']);
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

            if (session()->get('role') !== 'Admin') throw new \Exception("Unauthorized to edit folders.");

            $title = $this->request->getPost('title');
            $dateStart = $this->request->getPost('eval_date_start');
            $dateEnd = $this->request->getPost('eval_date_end');
            
            $now = date('Y-m-d H:i:s');
            $isNowActive = !empty($dateEnd) && $dateEnd > $now;
            
            // 1. Update the Admin's Master Folder (Title and Dates only)
            $folderData = [
                'title'           => $title,
                'eval_date_start' => $dateStart,
                'eval_date_end'   => $dateEnd,
            ];
            $folderModel->update($folderId, $folderData);

            // 2. Fetch and Process Cascaded Child Folders
            $childFolders = $folderModel->where('parent_folder_id', $folderId)->findAll();
            $routingModel = new EvaluationRoutingModel();
            
            $targetStatuses = [
                FolderStatus::TO_EVALUATE->value,
                FolderStatus::UNEVALUATED->value,
                FolderStatus::EVALUATED->value,
                FolderStatus::APPROVED->value
            ];

            $didResetAny = false;

            if (!empty($childFolders)) {
                foreach ($childFolders as $child) {
                    // Base payload for every child (syncing titles and dates)
                    $childData = [
                        'title'           => $title,
                        'eval_date_start' => $dateStart,
                        'eval_date_end'   => $dateEnd,
                    ];

                    // Check if THIS specific child folder needs a reset
                    $isInTargetStatus = in_array($child['status'], $targetStatuses);
                    $shouldReset = ($isInTargetStatus && $isNowActive);

                    if ($shouldReset) {
                        $didResetAny = true;

                        // Smart Reset Logic: Protect submitted work
                        if (empty($child['submitted_at'])) {
                            $childData['status'] = FolderStatus::DRAFT->value;
                        } else {
                            $childData['status'] = FolderStatus::SUBMITTED->value;
                        }
                        
                        // Wipe evaluation outcomes
                        $childData['final_rating'] = null; 
                        $childData['rated_at']     = null; 

                        // Reset Evaluator Routing Statuses for this specific child
                        $routingModel->where('folder_id', $child['id'])
                                     ->set(['status' => FolderStatus::DRAFT->value])
                                     ->update();
                    }

                    // Apply the update to the child
                    $folderModel->update($child['id'], $childData);
                }
            }

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
                throw new \Exception("This folder cannot be submitted at this time.");
            }

            if (!$folder || $folder['user_id'] != $userId) {
                throw new \Exception("Unauthorized to submit this folder.");
            }

            // --- NEW: Target Document Validation ---
            $documentModel = new DocumentModel();
            $hasTarget = $documentModel->where('document_folder_id', $folderId)
                                       ->where('is_target', 1)
                                       ->countAllResults();
            
            if ($hasTarget == 0) {
                throw new \Exception("Submission Failed: You must set at least one document as the Basis Target (★) before submitting.");
            }
            // ---------------------------------------

            $now = date('Y-m-d H:i:s');

            // If the eval window is already open at submit time, skip straight to
            // "To Evaluate" instead of leaving it at "Submitted" for up to a minute
            // waiting on updateTimeBasedStatuses()'s cron sweep - keep this condition
            // in sync with that method's "Submitted -> To Evaluate" check.
            $windowAlreadyOpen = !empty($folder['eval_date_start']) && !empty($folder['eval_date_end'])
                && $folder['eval_date_start'] <= $now && $folder['eval_date_end'] >= $now;

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
                throw new \Exception("This folder cannot be unsubmitted at this time.");
            }

            if (!$folder || $folder['user_id'] != $userId) throw new \Exception("Unauthorized.");

            if (!empty($folder['eval_date_end']) && date('Y-m-d H:i:s') > $folder['eval_date_end']) {
                throw new \Exception("Cannot unsubmit: Evaluation window has closed.");
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
                throw new \Exception("Unauthorized to evaluate this folder.");
            }

            if (!in_array($folder['status'], [FolderStatus::TO_EVALUATE->value, FolderStatus::REEVALUATE->value])) {
                throw new \Exception("This folder cannot be evaluated at this time.");
            }

            $folderModel->update($folderId, [
                'status'       => FolderStatus::EVALUATED->value,
                'final_rating' => (float) $finalRating,
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
     * POST /folder/return - The current evaluator sends the folder back for revision
     * instead of approving it, then re-checks consensus and emails the owner.
     */
    public function returnRevision() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $routingModel = new EvaluationRoutingModel();

            $routingModel->where('folder_id', $folderId)
                ->where('evaluator_id', session()->get('user_id'))
                ->set(['status' => FolderStatus::REEVALUATE->value, 'updated_at' => date('Y-m-d H:i:s')])
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
}