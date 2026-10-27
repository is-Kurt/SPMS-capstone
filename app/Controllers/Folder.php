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
        $userPos = strtolower(session()->get('position') ?? '');
        $userEmail = strtolower(session()->get('email') ?? '');
        $isPresident = session()->get('is_president') 
                    || (str_contains($userPos, 'president') && !str_contains($userPos, 'vice')) 
                    || str_contains($userEmail, 'president');

        $userId   = session()->get('user_id');
        $role     = session()->get('role');

        if (in_array($role, ['TWG', 'PMT']) || ($role !== 'Admin' && $isPresident)) {
            return redirect()->to(site_url('ratings'));
        }

        $folderModel   = new DocumentFolderModel();
        $folderModel->updateTimeBasedStatuses();
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

        $presets = $presetModel->getPresetsWithDetails($userId);

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
            if (!$activeFolder) {
                // If the folder no longer exists (e.g. revoked cascade or deleted cycle),
                // remove any stale notifications referencing it
                (new \App\Models\NotificationModel())
                    ->groupStart()
                        ->like('link', 'folders/' . $folderId)
                        ->orLike('link', 'ratings/show/' . $folderId)
                    ->groupEnd()
                    ->delete();

                session()->remove('active_folder_id');
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }

            if ($activeFolder['user_id'] != $userId) {
                session()->remove('active_folder_id');

                // Route authorized evaluators to ratings view
                $routingModel = new \App\Models\EvaluationRoutingModel();
                $isAuthorizedEvaluator = in_array($role, ['Admin', 'PMT', 'TWG']) || 
                    ($routingModel->where('folder_id', $activeFolder['id'])->where('evaluator_id', $userId)->countAllResults() > 0);

                if ($isAuthorizedEvaluator) {
                    if (in_array($activeFolder['status'], [FolderStatus::DRAFT->value, FolderStatus::DRAFT_TARGET->value])) {
                        session()->setFlashdata('error', 'This folder is currently being drafted by the employee and has not yet been submitted for evaluation.');
                        return redirect()->to(site_url('ratings'));
                    }
                    return redirect()->to(site_url('ratings/show/' . $activeFolder['id']));
                }

                // Security guard: If user is neither owner nor an authorized evaluator, block with 404 (prevents user/email enumeration)
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }

            $isReadOnly = false;
            $activeFolder = $folderModel->enrichFolderWithDeadlines($activeFolder);

            $isFolderOpcr = $this->isOpcrFolder($activeFolder, $userId);

            // The cascaded team may have since been archived (soft-deleted), in which
            // case it no longer shows up in $presets above - inject it back in just for
            // this folder so the "Cascade Management" panel can still show/select its
            // name and Revoke Cascade keeps working, without making an archived team
            // pickable for any other folder's "Cascade to Team" dropdown.
            if ($activeFolder['routing_preset_id'] && !in_array($activeFolder['routing_preset_id'], array_column($presets, 'id'))) {
                $archivedPreset = $presetModel->withDeleted()->find($activeFolder['routing_preset_id']);
                if ($archivedPreset) {
                    $rpmModel = new \App\Models\RoutingPresetMemberModel();
                    $archivedPreset['member_count'] = $rpmModel->where('preset_id', $archivedPreset['id'])->countAllResults();
                    $presets[] = $archivedPreset;
                }
            }


            // Reconcile unsubmitted folders that were previously auto-approved for Admin or OPCR
            if ($activeFolder && $activeFolder['status'] === FolderStatus::TARGET_APPROVED->value && empty($activeFolder['target_submitted_at'])) {
                $folderModel->update($folderId, [
                    'status' => FolderStatus::DRAFT_TARGET->value,
                    'target_approved_at' => null
                ]);
                $activeFolder['status'] = FolderStatus::DRAFT_TARGET->value;
            }

            if ($isFolderOpcr) {
                $this->ensureAdminRoutingForExecutiveFolder($folderId, (int) $userId);
            }

            // Ensure the user's official performance paper exists automatically based on their profile doc_type (Admins only initiate cycles)
            if ($role !== 'Admin') {
                $this->ensureUserDocumentExists($folderId, $userId, $activeFolder['title']);
            }

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
        $isOwnerDean = false;
        $isOwnerChair = false;
        if ($activeFolder) {
            $owner = $userModel->find($activeFolder['user_id']);
            $ownerDocType = $owner['doc_type'] ?? null;
            $ownerPlantilla = $userModel->getActivePlantillaDetails($activeFolder['user_id']);
            $posTitle = strtolower($ownerPlantilla['position'] ?? '');
            $userEmail = strtolower($owner['email'] ?? '');
            $isOwnerDean = str_contains($posTitle, 'dean') || str_contains($userEmail, 'dean');
            $isOwnerChair = str_contains($posTitle, 'chair') || str_contains($posTitle, 'head') || str_contains($userEmail, 'chair');
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

                $myPrimaryDoc = $documentModel->where('document_folder_id', $activeFolder['id'])->first();
                $myDocTitleUpper = strtoupper($myPrimaryDoc['title'] ?? '');
                $isMyDocOpcr = str_contains($myDocTitleUpper, 'OPCR') || str_contains($myDocTitleUpper, 'OFFICE') || (strtoupper($myPrimaryDoc['doc_type'] ?? '') === 'OPCR');
                $candidateBasis = $documentModel->where('document_folder_id', $parentFolder['id'])->first();
                $candidateTitleUpper = strtoupper($candidateBasis['title'] ?? '');
                $isParentDocOpcr = str_contains($candidateTitleUpper, 'OPCR') || str_contains($candidateTitleUpper, 'OFFICE') || (strtoupper($candidateBasis['doc_type'] ?? '') === 'OPCR');

                if ($isParentAdmin || $isMyDocOpcr) {
                    $isParentTargetApproved = true;
                    $parentFolder = null;
                    $basisDoc = null;
                } else {
                    // Under CSC SPMS guidelines, subordinates require their superior's approved targets as a basis
                    $isParentTargetApproved = ($parentFolder['status'] === FolderStatus::TARGET_APPROVED->value);
                    $basisDoc = $candidateBasis;
                }
            }
        }

        $cascadedChildren = [];
        $pendingTeamMembers = [];
        $userPosLower = strtolower(session()->get('position') ?? '');
        $isUserSupervisorOrChair = in_array($role, ['Admin', 'Supervisor']) 
                                || $isOwnerChair 
                                || str_contains($userPosLower, 'chair')
                                || str_contains($userPosLower, 'head')
                                || str_contains($userPosLower, 'dean')
                                || str_contains($userPosLower, 'president')
                                || str_contains($userPosLower, 'vpaa')
                                || !empty($isMyDocOpcr);
        if ($activeFolder && $isUserSupervisorOrChair) {
            $routingModel = new \App\Models\EvaluationRoutingModel();
            $routedFolderIds = $routingModel->where('evaluator_folder_id', $activeFolder['id'])
                ->findColumn('folder_id') ?: [];

            $builder = $folderModel->where('document_folders.deleted_at IS NULL')
                ->select("document_folders.id, document_folders.user_id, document_folders.status, document_folders.target_submitted_at, users.first_name, users.last_name, users.email, pos.title as position")
                ->join('users', 'users.id = document_folders.user_id')
                ->join('plantillas p', 'p.user_id = users.id AND p.ended_at IS NULL', 'left')
                ->join('positions pos', 'pos.id = p.position_id', 'left');

            if (!empty($routedFolderIds)) {
                $builder->groupStart()
                    ->where('document_folders.parent_folder_id', $activeFolder['id'])
                    ->orWhereIn('document_folders.id', $routedFolderIds)
                ->groupEnd();
            } else {
                $builder->where('document_folders.parent_folder_id', $activeFolder['id']);
            }
            $cascadedChildren = $builder->findAll();

            if (!empty($activeFolder['routing_preset_id'])) {
                $rpmModel = new \App\Models\RoutingPresetMemberModel();
                $teamMembers = $rpmModel->where('preset_id', $activeFolder['routing_preset_id'])->findAll();
                $cascadedUserIds = array_map('intval', array_column($cascadedChildren, 'user_id'));

                foreach ($teamMembers as $tm) {
                    $tUserId = (int)$tm['user_id'];
                    if (in_array($tUserId, $cascadedUserIds, true)) {
                        continue;
                    }

                    $pUser = $userModel->find($tUserId);
                    if ($pUser) {
                        $pPlantilla = $userModel->getActivePlantillaDetails($tUserId);
                        $pendingTeamMembers[] = [
                            'user_id'    => $tUserId,
                            'first_name' => $pUser['first_name'],
                            'last_name'  => $pUser['last_name'],
                            'email'      => $pUser['email'],
                            'position'   => $pPlantilla['position'] ?? ''
                        ];
                    }
                }
            }
        }

        // 1. Resolve assigned TWG for this folder owner's college/unit
        $assignedTwg = (new \App\Models\TwgUnitAssignmentModel())->resolveAssignedTwgForFolder($activeFolder);

        $rawFolderStatus = $activeFolder['status'] ?? FolderStatus::DRAFT_TARGET->value;
        $isTargetApprovedFolder = in_array($rawFolderStatus, ['target_approved', 'draft_evaluation', 'eval_submitted', 'eval_returned', 'eval_approved', 'unevaluated', 'to evaluate', 'to_evaluate', 'draft', 'submitted', 'evaluated', 'reevaluate', 'approved', 'twg_approved', 'twg_disapproved'], true)
            || !empty($activeFolder['target_approved_at']);
        $isTargetReturnedFolder = in_array($rawFolderStatus, ['target_returned', 'target_missed', 'target_unapproved', 'target_twg_disapproved'], true);
        $isInEvaluationPhase = in_array($rawFolderStatus, ['draft_evaluation', 'eval_submitted', 'eval_returned', 'eval_approved', 'unevaluated', 'to evaluate', 'to_evaluate', 'draft', 'submitted', 'evaluated', 'reevaluate', 'approved', 'twg_approved', 'twg_disapproved'], true);
        $isEvalApprovedFolder = in_array($rawFolderStatus, ['eval_approved', 'approved', 'twg_approved'], true);
        $isEvalReturnedFolder = in_array($rawFolderStatus, ['eval_returned', 'reevaluate', 'twg_disapproved'], true);

        // Gather all supervising authorities separated by phase
        $targetSupervisors    = [];
        $evalSupervisors      = [];
        $evaluatorSupervisors = [];

        if (!empty($activeFolder['id'])) {
            $routingModel = new \App\Models\EvaluationRoutingModel();
            $folderRoutes = $routingModel->getEvaluatorsForFolder((string)$activeFolder['id']);

            $seenEvaluatorIds = [];
            foreach ($folderRoutes as $r) {
                $eId = (int)$r['evaluator_id'];
                if (in_array($eId, $seenEvaluatorIds, true)) continue;
                if ($eId === (int)($activeFolder['user_id'] ?? 0)) continue;
                if ($userModel->hasRole($eId, 'Admin')) continue;

                $seenEvaluatorIds[] = $eId;

                $eName = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
                $ePos  = $r['evaluator_position'] ?? '';
                if (empty($ePos)) {
                    $p = $userModel->getActivePlantillaDetails($eId);
                    $ePos = $p['position'] ?? 'Immediate Supervisor';
                }

                $rStatus = strtolower(trim((string)($r['status'] ?? 'pending')));

                // Target Phase Status (Layer 2)
                if ($rStatus === 'approved' || $rStatus === 'target_approved' || $isTargetApprovedFolder) {
                    $tStatus = 'approved';
                    $tLabel  = 'Approved';
                } elseif ($rStatus === 'returned' || $rStatus === 'target_returned' || $rStatus === 'target_unapproved' || $isTargetReturnedFolder) {
                    $tStatus = 'returned';
                    $tLabel  = 'Returned';
                } elseif ($rawFolderStatus === 'target_submitted' || $rawFolderStatus === 'pending_target_approval') {
                    $tStatus = 'pending';
                    $tLabel  = 'Pending Review';
                } else {
                    $tStatus = 'draft';
                    $tLabel  = 'Awaiting Submission';
                }

                // Evaluation Phase Status (Layer 2)
                if (!$isInEvaluationPhase) {
                    $eStatus = 'draft';
                    $eLabel  = 'Awaiting Eval Window';
                } else {
                    if ($isEvalApprovedFolder || $rStatus === 'approved') {
                        $eStatus = 'approved';
                        $eLabel  = 'Approved';
                    } elseif ($isEvalReturnedFolder || $rStatus === 'returned' || $rStatus === 'reevaluate') {
                        $eStatus = 'returned';
                        $eLabel  = 'Returned';
                    } elseif ($rawFolderStatus === 'eval_submitted' || $rawFolderStatus === 'submitted' || $rawFolderStatus === 'evaluated') {
                        $eStatus = 'pending';
                        $eLabel  = 'Pending Review';
                    } else {
                        $eStatus = 'draft';
                        $eLabel  = 'Awaiting Submission';
                    }
                }

                $baseSupData = [
                    'id'         => $eId,
                    'name'       => $eName,
                    'position'   => $ePos,
                    'updated_at' => $r['updated_at'] ?? $r['created_at'] ?? null,
                    'folder_id'  => $r['evaluator_folder_id'] ?? null,
                    'is_twg'     => false
                ];

                $targetSupervisors[] = array_merge($baseSupData, [
                    'status'       => $tStatus,
                    'status_label' => $tLabel
                ]);

                $evalSupervisors[] = array_merge($baseSupData, [
                    'status'       => $eStatus,
                    'status_label' => $eLabel
                ]);

                $evaluatorSupervisors[] = array_merge($baseSupData, [
                    'status'       => $isInEvaluationPhase ? $eStatus : $tStatus,
                    'status_label' => $isInEvaluationPhase ? $eLabel : $tLabel
                ]);
            }

            // Fallback for OPCR or direct parent folder if no evaluation_routings
            if (empty($evaluatorSupervisors)) {
                $fOwnerId = (int)($activeFolder['user_id'] ?? 0);
                if (!empty($activeFolder['parent_folder_id'])) {
                    $pFold = $folderModel->find($activeFolder['parent_folder_id']);
                    if ($pFold && !empty($pFold['user_id']) && (int)$pFold['user_id'] !== $fOwnerId && !$userModel->hasRole((int)$pFold['user_id'], 'Admin')) {
                        $pUser = $userModel->find($pFold['user_id']);
                        if ($pUser) {
                            $pPlan = $userModel->getActivePlantillaDetails($pFold['user_id']);
                            $pName = trim(($pUser['first_name'] ?? '') . ' ' . ($pUser['last_name'] ?? ''));
                            $pPos  = $pPlan['position'] ?? 'Immediate Supervisor';

                            if ($isTargetApprovedFolder) {
                                $tStatus = 'approved'; $tLabel = 'Approved';
                            } elseif ($isTargetReturnedFolder) {
                                $tStatus = 'returned'; $tLabel = 'Returned';
                            } elseif ($rawFolderStatus === 'target_submitted' || $rawFolderStatus === 'pending_target_approval') {
                                $tStatus = 'pending'; $tLabel = 'Pending Review';
                            } else {
                                $tStatus = 'draft'; $tLabel = 'Awaiting Submission';
                            }

                            if (!$isInEvaluationPhase) {
                                $eStatus = 'draft'; $eLabel = 'Awaiting Eval Window';
                            } elseif ($isEvalApprovedFolder) {
                                $eStatus = 'approved'; $eLabel = 'Approved';
                            } elseif ($isEvalReturnedFolder) {
                                $eStatus = 'returned'; $eLabel = 'Returned';
                            } elseif ($rawFolderStatus === 'eval_submitted' || $rawFolderStatus === 'submitted' || $rawFolderStatus === 'evaluated') {
                                $eStatus = 'pending'; $eLabel = 'Pending Review';
                            } else {
                                $eStatus = 'draft'; $eLabel = 'Awaiting Submission';
                            }

                            $basePData = [
                                'id'         => (int)$pFold['user_id'],
                                'name'       => $pName,
                                'position'   => $pPos,
                                'updated_at' => $activeFolder['target_approved_at'] ?? null,
                                'folder_id'  => $pFold['id'],
                                'is_twg'     => false
                            ];

                            $targetSupervisors[] = array_merge($basePData, ['status' => $tStatus, 'status_label' => $tLabel]);
                            $evalSupervisors[]   = array_merge($basePData, ['status' => $eStatus, 'status_label' => $eLabel]);
                            $evaluatorSupervisors[] = array_merge($basePData, [
                                'status'       => $isInEvaluationPhase ? $eStatus : $tStatus,
                                'status_label' => $isInEvaluationPhase ? $eLabel : $tLabel
                            ]);
                        }
                    }
                }
                
                if (empty($evaluatorSupervisors) && (!empty($activeFolder['parent_folder_id']) || !empty($activeFolder['approving_authority_name']) || !empty($isMyDocOpcr))) {
                    $approverTitle = $activeFolder['approving_authority_name'] ?: 'University President';
                    $approverPos   = $activeFolder['approving_authority_position'] ?: 'University President';

                    if ($isTargetApprovedFolder) {
                        $tStatus = 'approved'; $tLabel = 'Approved';
                    } elseif ($isTargetReturnedFolder) {
                        $tStatus = 'returned'; $tLabel = 'Returned';
                    } elseif ($rawFolderStatus === 'target_submitted' || $rawFolderStatus === 'pending_target_approval') {
                        $tStatus = 'pending'; $tLabel = 'Pending Review';
                    } else {
                        $tStatus = 'draft'; $tLabel = 'Awaiting Submission';
                    }

                    if (!$isInEvaluationPhase) {
                        $eStatus = 'draft'; $eLabel = 'Awaiting Eval Window';
                    } elseif ($isEvalApprovedFolder) {
                        $eStatus = 'approved'; $eLabel = 'Approved';
                    } elseif ($isEvalReturnedFolder) {
                        $eStatus = 'returned'; $eLabel = 'Returned';
                    } elseif ($rawFolderStatus === 'eval_submitted' || $rawFolderStatus === 'submitted' || $rawFolderStatus === 'evaluated') {
                        $eStatus = 'pending'; $eLabel = 'Pending Review';
                    } else {
                        $eStatus = 'draft'; $eLabel = 'Awaiting Submission';
                    }

                    $baseAdminData = [
                        'id'           => null,
                        'name'         => $approverTitle,
                        'position'     => $approverPos,
                        'updated_at'   => $activeFolder['target_approved_at'] ?? null,
                        'folder_id'    => null,
                        'is_twg'       => false
                    ];

                    $targetSupervisors[] = array_merge($baseAdminData, ['status' => $tStatus, 'status_label' => $tLabel]);
                    $evalSupervisors[]   = array_merge($baseAdminData, ['status' => $eStatus, 'status_label' => $eLabel]);
                    $evaluatorSupervisors[] = array_merge($baseAdminData, [
                        'status'       => $isInEvaluationPhase ? $eStatus : $tStatus,
                        'status_label' => $isInEvaluationPhase ? $eLabel : $tLabel
                    ]);
                }
            }

            // Append Assigned TWG to both phases if present
            if ($assignedTwg) {
                $isTwgTargetVerified = in_array($activeFolder['target_approver_role'] ?? '', ['TWG', 'PMT'], true);
                $areAllSupervisorsApproved = !empty($targetSupervisors) && empty(array_filter($targetSupervisors, fn($s) => $s['status'] !== 'approved'));

                if ($isTwgTargetVerified) {
                    $twgTargetStatus = 'approved';
                    $twgTargetLabel  = 'Approved';
                } elseif ($isTargetReturnedFolder) {
                    $twgTargetStatus = 'returned';
                    $twgTargetLabel  = 'Returned';
                } else {
                    $twgTargetStatus = 'pending';
                    $twgTargetLabel  = 'Pending Verification';
                }

                $targetSupervisors[] = [
                    'id'           => $assignedTwg['id'],
                    'name'         => $assignedTwg['name'],
                    'position'     => $assignedTwg['position'],
                    'status'       => $twgTargetStatus,
                    'status_label' => $twgTargetLabel,
                    'updated_at'   => $isTwgTargetVerified ? ($activeFolder['target_approved_at'] ?? null) : null,
                    'folder_id'    => null,
                    'is_twg'       => true
                ];

                if ($rawFolderStatus === 'eval_approved' || $rawFolderStatus === 'twg_approved') {
                    $twgEvalStatus = 'approved';
                    $twgEvalLabel  = 'Approved';
                } elseif ($rawFolderStatus === 'eval_returned' || $rawFolderStatus === 'twg_disapproved') {
                    $twgEvalStatus = 'returned';
                    $twgEvalLabel  = 'Returned';
                } else {
                    $twgEvalStatus = 'pending';
                    $twgEvalLabel  = 'Pending Verification';
                }

                $evalSupervisors[] = [
                    'id'           => $assignedTwg['id'],
                    'name'         => $assignedTwg['name'],
                    'position'     => $assignedTwg['position'],
                    'status'       => $twgEvalStatus,
                    'status_label' => $twgEvalLabel,
                    'updated_at'   => null,
                    'folder_id'    => null,
                    'is_twg'       => true
                ];

                $evaluatorSupervisors[] = [
                    'id'           => $assignedTwg['id'],
                    'name'         => $assignedTwg['name'],
                    'position'     => $assignedTwg['position'],
                    'status'       => $isInEvaluationPhase ? $twgEvalStatus : $twgTargetStatus,
                    'status_label' => $isInEvaluationPhase ? $twgEvalLabel : $twgTargetLabel,
                    'updated_at'   => $isInEvaluationPhase ? null : ($isTwgTargetVerified ? ($activeFolder['target_approved_at'] ?? null) : null),
                    'folder_id'    => null,
                    'is_twg'       => true
                ];
            }
        }

        $templateModel = new TemplateModel();
        
        return view('components/app_shell', [
            'sidebarFolders'   => $folders,
            'selectedFolderId' => $activeFolder['id'] ?? null,
            'mainView'         => 'document/_doc_rows',
            'templates'        => $templateModel->findAll(),
            'mainData'         => [
                'activeFolder'            => $activeFolder,
                'parentFolder'            => $parentFolder,
                'isParentTargetApproved'  => $isParentTargetApproved,
                'basisDoc'                => $basisDoc,
                'ownerDocType'            => $ownerDocType,
                'isOwnerDean'             => $isOwnerDean,
                'isOwnerChair'            => $isOwnerChair,
                'myDocs'                  => $myDocs,
                'groupedGuides'           => $groupedGuides,
                'isReadOnly'              => $isReadOnly,
                'presets'                 => $presets,
                'cascadedChildren'        => $cascadedChildren,
                'pendingTeamMembers'      => $pendingTeamMembers,
                'pendingTeamMembersCount' => count($pendingTeamMembers),
                'evaluatorSupervisors'    => $evaluatorSupervisors,
                'targetSupervisors'       => $targetSupervisors,
                'evalSupervisors'         => $evalSupervisors,
                'assignedTwg'             => $assignedTwg
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

            // --- SPMS CASCADE GATING ---
            // Allow cascading during the target setting phase (drafting, submitted, or approved targets)
            $allowedStatuses = [
                \App\Enums\FolderStatus::DRAFT_TARGET->value, 
                \App\Enums\FolderStatus::PENDING_TARGET_APPROVAL->value,
                \App\Enums\FolderStatus::TARGET_APPROVED->value,
                \App\Enums\FolderStatus::DRAFT->value
            ];
            if (!in_array($activeFolder['status'], $allowedStatuses)) {
                return $this->respondError("You cannot cascade this folder because it has already moved past the target setting phase.", 400);
            }

            $presetModel = new RoutingPresetModel();

            if (empty($teamId) || $teamId === 'org') {
                return $this->respondError("Please select a team to cascade to.", 400);
            }


            // Verify team exists and is accessible
            $preset = $presetModel->where('id', $teamId)->first();
            if (!$preset) {
                return $this->respondError("The selected team was not found.", 404);
            }
            if ($preset['owner_id'] != $userId && $role !== 'Admin') {
                return $this->respondError("You do not have permission to cascade using this team.", 403);
            }

            $members = $presetMemberModel->where('preset_id', $teamId)->findAll();
            if (empty($members)) {
                return $this->respondError("The selected team has no members. Please add members to this team in the Teams tab first.", 400);
            }

            $folderModel->db->transStart();

            // Detect if this folder is OPCR (Requires target approval before cascading to subordinates)
            $isFolderOpcr = $this->isOpcrFolder($activeFolder, $userId);
            if ($role !== 'Admin' && $isFolderOpcr && $activeFolder['status'] !== FolderStatus::TARGET_APPROVED->value) {
                return $this->respondError("Cannot cascade yet: Your OPCR target commitments must be submitted to and approved before cascading to subordinates.", 400);
            }

            $updateFolderPayload = ['routing_preset_id' => $teamId];
            $folderModel->update($folderId, $updateFolderPayload);
            $emailsQueued = 0;
            if ($role === 'Admin') {
                foreach ($members as $member) {
                    $exists = $folderModel->where('user_id', $member['user_id'])
                                          ->where('parent_folder_id', $activeFolder['id'])
                                          ->where('deleted_at IS NULL')->first();
                    
                    if (!$exists) {
                        $cycleFolderId = $activeFolder['cycle_folder_id'] ?? $folderModel->getRootCycleFolderId($activeFolder['id']);
                        $newFolderId = create_unique_row($folderModel, [
                            'title'                        => $activeFolder['title'],
                            'user_id'                      => $member['user_id'],
                            'parent_folder_id'             => $activeFolder['id'],
                            'cycle_folder_id'              => $cycleFolderId,
                            'approving_authority_name'     => $activeFolder['approving_authority_name'] ?? null,
                            'approving_authority_position' => $activeFolder['approving_authority_position'] ?? null,
                            'status'                       => \App\Enums\FolderStatus::DRAFT_TARGET->value
                        ]);
                    } else {
                        $newFolderId = $exists['id']; 
                    }

                    // Pre-generate the member's official evaluation paper based on profile doc_type
                    $this->ensureUserDocumentExists($newFolderId, $member['user_id'], $activeFolder['title']);

                    // Remove any stale routing to a separate president user so Admin who cascaded is the sole evaluator
                    $presidentUser = $userModel->where('email', 'president@test.com')->first();
                    if ($presidentUser && (int)$presidentUser['id'] !== (int)$userId) {
                        $routingModel->where('folder_id', $newFolderId)
                                      ->where('evaluator_id', (int)$presidentUser['id'])
                                      ->delete();
                    }

                    // Register evaluator routing so member's submission routes to Admin (who cascaded it)
                    $routingExists = $routingModel->where('folder_id', $newFolderId)
                                                  ->where('evaluator_id', $userId)->first();
                    if (!$routingExists) {
                        $routingModel->insert([
                            'folder_id'           => $newFolderId,
                            'evaluator_id'        => $userId,
                            'evaluator_folder_id' => $folderId,
                            'status'              => FolderStatus::DRAFT->value
                        ]);
                    }

                    if (!$exists) {
                        notify_user((int) $member['user_id'], [
                            'sender_id' => $userId,
                            'type'      => 'target_assigned',
                            'title'     => 'Evaluation Target Assigned',
                            'message'   => "You have been assigned to prepare the institutional OPCR for \"{$activeFolder['title']}\".",
                            'link'      => 'folders/' . $newFolderId,
                            'icon'      => 'file'
                        ]);
                    }
                }
                $message = (count($members) === 1)
                    ? "Evaluation cycle successfully released to the Vice President."
                    : "Evaluation cycle successfully released to the Vice Presidents (Executive Team).";
            } else {
                $batchId = $activeFolder['id'];

                // Detect supervisor position/tier (e.g. Department Chair cascading to faculty)
                $callerPlantilla = $userModel->getActivePlantillaDetails($userId);
                $callerPos = strtolower($callerPlantilla['position'] ?? '');
                $callerEmail = strtolower(session()->get('email') ?? '');
                $isCallerChair = str_contains($callerPos, 'chair') 
                              || str_contains($callerPos, 'head') 
                              || str_contains($callerEmail, 'chair');

                $cascadedCount = 0;

                foreach ($members as $member) {
                    $subFolder = $folderModel->where('user_id', $member['user_id'])
                                             ->where('parent_folder_id', $batchId)
                                             ->where('deleted_at IS NULL')->first();
                    
                    // If member already has a folder for this cycle, attach to it to support multiple supervisor cascades
                    if (!$subFolder) {
                        $existingCycleFolder = $folderModel->where('user_id', $member['user_id'])
                                                           ->where('title', $activeFolder['title'])
                                                           ->where('deleted_at IS NULL')->first();
                        if ($existingCycleFolder) {
                            $subFolder = $existingCycleFolder;
                        }
                    }

                    if (!$subFolder) {
                        $cycleFolderId = $activeFolder['cycle_folder_id'] ?? $folderModel->getRootCycleFolderId($activeFolder['id']);
                        $newFolderId = create_unique_row($folderModel, [
                            'title'                        => $activeFolder['title'],
                            'user_id'                      => $member['user_id'],
                            'parent_folder_id'             => $batchId,
                            'cycle_folder_id'              => $cycleFolderId,
                            'approving_authority_name'     => $activeFolder['approving_authority_name'] ?? null,
                            'approving_authority_position' => $activeFolder['approving_authority_position'] ?? null,
                            'status'                       => \App\Enums\FolderStatus::DRAFT_TARGET->value
                        ]);
                        $subFolder = $folderModel->find($newFolderId);
                    }

                    if ($subFolder) {
                        // Register the direct cascading supervisor in evaluation_routings first
                        $exists = $routingModel->where('folder_id', $subFolder['id'])
                                               ->where('evaluator_id', $userId)->first();

                        if (!$exists) {
                            $routingModel->insert([
                                'folder_id'           => $subFolder['id'],
                                'evaluator_id'        => $userId,
                                'evaluator_folder_id' => $folderId,
                                'status'              => FolderStatus::DRAFT->value
                            ]);

                            notify_user((int) $member['user_id'], [
                                'sender_id' => $userId,
                                'type'      => 'target_assigned',
                                'title'     => 'Evaluation Target Assigned',
                                'message'   => "You have been assigned to prepare target commitments for \"{$activeFolder['title']}\".",
                                'link'      => 'folders/' . $subFolder['id'],
                                'icon'      => 'file'
                            ]);
                        }

                        // Pre-generate the member's official evaluation paper or merge newly cascaded supervisor goals
                        $this->ensureUserDocumentExists($subFolder['id'], $member['user_id'], $activeFolder['title']);

                        $cascadedCount++;
                    }
                }
                $message = "Goals successfully cascaded to your team ({$cascadedCount} member" . ($cascadedCount === 1 ? '' : 's') . ").";
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

            $folderModel       = new DocumentFolderModel();
            $routingModel      = new EvaluationRoutingModel();
            $presetMemberModel = new RoutingPresetMemberModel();
            $presetModel       = new RoutingPresetModel();
            $documentModel     = new \App\Models\DocumentModel();

            $activeFolder = $folderModel->find($folderId);
            if (!$activeFolder) {
                return $this->respondError("Folder not found.", 404);
            }

            // Permissions: Must be folder owner or Admin
            if ($activeFolder['user_id'] != $userId && $role !== 'Admin') {
                return $this->respondError("You do not have permission to revoke the cascade for this folder.", 403);
            }

            $allowedStatuses = [
                \App\Enums\FolderStatus::DRAFT_TARGET->value,
                \App\Enums\FolderStatus::PENDING_TARGET_APPROVAL->value,
                \App\Enums\FolderStatus::TARGET_APPROVED->value,
                \App\Enums\FolderStatus::TARGET_RETURNED->value,
                \App\Enums\FolderStatus::TARGET_UNAPPROVED->value,
                \App\Enums\FolderStatus::DRAFT->value
            ];
            if ($role !== 'Admin' && !in_array($activeFolder['status'], $allowedStatuses)) {
                return $this->respondError("You cannot revoke the cascade for a folder that has moved past the target setting phase or is locked.", 400);
            }

            $teamId = $activeFolder['routing_preset_id'] ?? null;

            // Recursively collect all descendant folders (children, grandchildren, etc.) level-by-level
            $allFolderIds = [];
            $currentLevel = [$folderId];
            $levels = [];
            while (!empty($currentLevel)) {
                $nextLevel = $folderModel->whereIn('parent_folder_id', $currentLevel)
                                         ->where('deleted_at IS NULL')
                                         ->findColumn('id');
                if (!empty($nextLevel)) {
                    $levels[] = $nextLevel;
                    $allFolderIds = array_merge($allFolderIds, $nextLevel);
                    $currentLevel = $nextLevel;
                } else {
                    break;
                }
            }
            $allFolderIds = array_unique($allFolderIds);

            if (!$teamId && empty($allFolderIds)) {
                return $this->respondError("This folder isn't currently cascaded to any team or subordinates.", 400);
            }

            $folderModel->db->transStart();

            // 1. Clear the routing_preset_id on this active folder
            $uncascadeUpdate = ['routing_preset_id' => null];
            $folderModel->update($folderId, $uncascadeUpdate);

            if (!empty($allFolderIds)) {
                $in = "'" . implode("','", $allFolderIds) . "'";

                // 2. Foreign Key Safety: Delete document attachments inside descendant folders
                $folderModel->db->query("DELETE FROM document_attachments WHERE document_id IN (SELECT id FROM documents WHERE document_folder_id IN ($in))");

                // 3. Foreign Key Safety: Delete documents inside descendant folders
                $documentModel->whereIn('document_folder_id', $allFolderIds)->delete();

                // 4. Foreign Key Safety: Delete evaluation routings referencing any descendant folder
                $folderModel->db->query("DELETE FROM evaluation_routings WHERE folder_id IN ($in) OR evaluator_folder_id IN ($in)");

                // 5. Clean up notifications referencing any of the removed folders
                foreach ($allFolderIds as $delId) {
                    $folderModel->db->query("DELETE FROM notifications WHERE link LIKE '%folders/{$delId}%' OR link LIKE '%ratings/show/{$delId}%'");
                }

                // 6. Delete descendant folders in leaf-to-root order (deepest levels first) to prevent SQLite FK constraint errors
                $levelsReversed = array_reverse($levels);
                foreach ($levelsReversed as $levelIds) {
                    $folderModel->whereIn('id', $levelIds)->delete();
                }
            }

            // 6. Clean up the routing preset if it was soft-deleted and is no longer in use anywhere
            if ($teamId) {
                $inUseCount = $folderModel->where('routing_preset_id', $teamId)->countAllResults();
                if ($inUseCount === 0) {
                    $deletedPreset = $presetModel->onlyDeleted()->find($teamId);
                    if ($deletedPreset) {
                        $presetModel->delete($teamId, true); // Hard delete
                    }
                }
            }

            $folderModel->db->transComplete();
            $isUserAdmin = (session()->get('role') === 'Admin');
            $successMsg = $isUserAdmin ? 'Cycle release revoked successfully.' : 'Cascade revoked successfully.';
            return $this->respond(['status' => 'success', 'message' => $successMsg]);
        });
    }

    /** POST /folder/sync-team-cascade - Synchronizes missing team members into an already cascaded folder without touching existing folders. */
    public function syncTeamCascade() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $userId   = session()->get('user_id');
            $role     = session()->get('role');

            $folderModel       = new DocumentFolderModel();
            $routingModel      = new EvaluationRoutingModel();
            $presetMemberModel = new RoutingPresetMemberModel();
            $presetModel       = new RoutingPresetModel();
            $userModel         = new UserModel();

            $activeFolder = $folderModel->find($folderId);
            if (!$activeFolder) {
                return $this->respondError("Folder not found.", 400);
            }

            // Gating: Target setting phase
            $allowedStatuses = [
                \App\Enums\FolderStatus::DRAFT_TARGET->value, 
                \App\Enums\FolderStatus::PENDING_TARGET_APPROVAL->value,
                \App\Enums\FolderStatus::TARGET_APPROVED->value,
                \App\Enums\FolderStatus::DRAFT->value
            ];
            if ($role !== 'Admin' && !in_array($activeFolder['status'], $allowedStatuses)) {
                return $this->respondError("Cannot sync cascade: this folder has already moved past the target setting phase.", 400);
            }

            $teamId = $activeFolder['routing_preset_id'] ?? null;
            if (!$teamId) {
                return $this->respondError("This folder is not currently cascaded to any team.", 400);
            }

            $preset = $presetModel->find($teamId);
            if (!$preset) {
                return $this->respondError("The cascaded team was not found.", 404);
            }
            if ($preset['owner_id'] != $userId && $role !== 'Admin') {
                return $this->respondError("You do not have permission to sync this team.", 403);
            }

            $members = $presetMemberModel->where('preset_id', $teamId)->findAll();
            if (empty($members)) {
                return $this->respondError("The team currently has no members. Add members in the Teams tab first.", 400);
            }

            // Detect caller position
            $callerPlantilla = $userModel->getActivePlantillaDetails($userId);
            $callerPos = strtolower($callerPlantilla['position'] ?? '');
            $callerEmail = strtolower(session()->get('email') ?? '');
            $isCallerChair = str_contains($callerPos, 'chair') 
                          || str_contains($callerPos, 'head') 
                          || str_contains($callerEmail, 'chair');

            $folderModel->db->transStart();

            $addedCount = 0;
            $skippedNonTeachingCount = 0;

            foreach ($members as $member) {
                $targetUserId = (int)$member['user_id'];

                // Check if child folder or routing already exists for this member
                $exists = $folderModel->where('user_id', $targetUserId)
                                      ->where('parent_folder_id', $activeFolder['id'])
                                      ->where('deleted_at IS NULL')
                                      ->first();
                if (!$exists) {
                    $routedFolderIds = $routingModel->where('evaluator_folder_id', $activeFolder['id'])
                        ->findColumn('folder_id') ?: [];
                    if (!empty($routedFolderIds)) {
                        $exists = $folderModel->where('user_id', $targetUserId)
                            ->whereIn('id', $routedFolderIds)
                            ->where('deleted_at IS NULL')
                            ->first();
                    }
                }
                if ($exists) {
                    // Already cascaded! Do not touch or overwrite existing drafts!
                    continue;
                }

                // Check if member already has a folder for this cycle/title (cascaded by another supervisor)
                $existingCycleFolder = $folderModel->where('user_id', $targetUserId)
                                                   ->where('title', $activeFolder['title'])
                                                   ->where('deleted_at IS NULL')
                                                   ->first();
                if ($existingCycleFolder) {
                    $routingExists = $routingModel->where('folder_id', $existingCycleFolder['id'])
                                                  ->where('evaluator_id', $userId)
                                                  ->first();
                    if (!$routingExists) {
                        $routingModel->insert([
                            'folder_id'           => $existingCycleFolder['id'],
                            'evaluator_id'        => $userId,
                            'evaluator_folder_id' => $activeFolder['id'],
                            'status'              => FolderStatus::DRAFT->value
                        ]);
                    }
                    $this->ensureUserDocumentExists($existingCycleFolder['id'], $targetUserId, $activeFolder['title']);
                    $addedCount++;
                    continue;
                }

                // Create new child folder for this missing member
                $cycleFolderId = $activeFolder['cycle_folder_id'] ?? $folderModel->getRootCycleFolderId($activeFolder['id']);
                $newFolderId = create_unique_row($folderModel, [
                    'title'               => $activeFolder['title'],
                    'user_id'             => $targetUserId,
                    'parent_folder_id'    => $activeFolder['id'],
                    'cycle_folder_id'     => $cycleFolderId,
                    'status'              => \App\Enums\FolderStatus::DRAFT_TARGET->value
                ]);

                // Register evaluator routing
                $routingExists = $routingModel->where('folder_id', $newFolderId)
                                              ->where('evaluator_id', $userId)
                                              ->first();
                if (!$routingExists) {
                    $routingModel->insert([
                        'folder_id'           => $newFolderId,
                        'evaluator_id'        => $userId,
                        'evaluator_folder_id' => $activeFolder['id'],
                        'status'              => FolderStatus::DRAFT->value
                    ]);
                }

                // Pre-generate official evaluation paper
                $this->ensureUserDocumentExists($newFolderId, $targetUserId, $activeFolder['title']);

                // In-app notification
                $targetUser = $userModel->find($targetUserId);
                $paperType = strtoupper($targetUser['doc_type'] ?? 'IPCR');
                notify_user($targetUserId, [
                    'sender_id' => $userId,
                    'type'      => 'target_assigned',
                    'title'     => 'Evaluation Target Assigned',
                    'message'   => "You have been assigned to prepare your {$paperType} for \"{$activeFolder['title']}\".",
                    'link'      => 'folders/' . $newFolderId,
                    'icon'      => 'file'
                ]);

                $addedCount++;
            }

            $folderModel->db->transComplete();

            if ($addedCount === 0) {
                return $this->respond([
                    'status'      => 'success',
                    'message'     => 'Team is already fully synchronized. All members have target folders.',
                    'added_count' => 0
                ]);
            }

            return $this->respond([
                'status'      => 'success',
                'message'     => "Successfully synchronized {$addedCount} new member" . ($addedCount === 1 ? '' : 's') . " to this evaluation cycle.",
                'added_count' => $addedCount
            ]);
        });
    }

    /** POST /folder/remove-subordinate-cascade - Removes an individual cascaded subordinate and their folder/documents. */
    public function removeSubordinateCascade() {
        return $this->tryOrFail(function() {
            $childFolderId  = $this->request->getPost('child_folder_id');
            $parentFolderId = $this->request->getPost('parent_folder_id');
            $userId         = session()->get('user_id');
            $role           = session()->get('role');

            $folderModel       = new DocumentFolderModel();
            $routingModel      = new EvaluationRoutingModel();
            $presetMemberModel = new RoutingPresetMemberModel();
            $documentModel     = new DocumentModel();

            $parentFolder = $folderModel->find($parentFolderId);
            if (!$parentFolder) {
                return $this->respondError("Parent evaluation cycle not found.", 404);
            }

            if ($parentFolder['user_id'] != $userId && $role !== 'Admin') {
                return $this->respondError("You do not have permission to modify subordinates in this folder.", 403);
            }

            // Gating: Target setting phase only
            $allowedStatuses = [
                \App\Enums\FolderStatus::DRAFT_TARGET->value,
                \App\Enums\FolderStatus::PENDING_TARGET_APPROVAL->value,
                \App\Enums\FolderStatus::TARGET_APPROVED->value,
                \App\Enums\FolderStatus::TARGET_RETURNED->value,
                \App\Enums\FolderStatus::TARGET_UNAPPROVED->value,
                \App\Enums\FolderStatus::DRAFT->value
            ];
            if ($role !== 'Admin' && !in_array($parentFolder['status'], $allowedStatuses)) {
                return $this->respondError("Cannot remove subordinate: this evaluation cycle has already moved past the target setting phase.", 400);
            }

            $childFolder = $folderModel->find($childFolderId);
            if (!$childFolder || $childFolder['parent_folder_id'] != $parentFolderId) {
                return $this->respondError("Subordinate folder not found under this evaluation cycle.", 404);
            }

            $folderModel->db->transStart();

            // 1. Recursively find any descendants under this child folder (e.g. if Chair cascaded to faculty)
            $allFolderIds = [$childFolderId];
            $currentLevel = [$childFolderId];
            $levels = [[$childFolderId]];
            while (!empty($currentLevel)) {
                $nextLevel = $folderModel->whereIn('parent_folder_id', $currentLevel)->findColumn('id');
                if (!empty($nextLevel)) {
                    $levels[] = $nextLevel;
                    $allFolderIds = array_merge($allFolderIds, $nextLevel);
                    $currentLevel = $nextLevel;
                } else {
                    break;
                }
            }
            $allFolderIds = array_unique($allFolderIds);

            // 2. Clean up foreign key dependents: attachments, documents, routings, folders
            $in = "'" . implode("','", $allFolderIds) . "'";
            $folderModel->db->query("DELETE FROM document_attachments WHERE document_id IN (SELECT id FROM documents WHERE document_folder_id IN ($in))");
            $documentModel->whereIn('document_folder_id', $allFolderIds)->delete();
            $folderModel->db->query("DELETE FROM evaluation_routings WHERE folder_id IN ($in) OR evaluator_folder_id IN ($in)");
            
            // Clean up notifications referencing any of the removed folders
            foreach ($allFolderIds as $delId) {
                $folderModel->db->query("DELETE FROM notifications WHERE link LIKE '%folders/{$delId}%' OR link LIKE '%ratings/show/{$delId}%'");
            }

            // Delete folders leaf-first
            $levelsReversed = array_reverse($levels);
            foreach ($levelsReversed as $levelIds) {
                $folderModel->whereIn('id', $levelIds)->delete();
            }

            // 3. Keep preset sync consistent: if parent folder is cascaded to a team preset, also remove user from preset members
            $teamId = $parentFolder['routing_preset_id'] ?? null;
            if ($teamId) {
                $presetMemberModel->where('preset_id', $teamId)
                                  ->where('user_id', $childFolder['user_id'])
                                  ->delete();
            }

            // 4. Notify the removed user
            notify_user((int)$childFolder['user_id'], [
                'sender_id' => $userId,
                'type'      => 'target_revoked',
                'title'     => 'Evaluation Assignment Removed',
                'message'   => ($role === 'Admin')
                    ? "Your evaluation assignment for \"{$parentFolder['title']}\" has been revoked by the Administrator."
                    : "Your evaluation target assignment for \"{$parentFolder['title']}\" has been removed by your supervisor.",
                'link'      => 'folders',
                'icon'      => 'user-minus'
            ]);

            // 5. If no cascaded children remain under parent, clear the routing_preset_id
            $remainingCount = $folderModel->where('parent_folder_id', $parentFolderId)
                                          ->where('deleted_at IS NULL')
                                          ->countAllResults();
            if ($remainingCount === 0) {
                $folderModel->update($parentFolderId, ['routing_preset_id' => null]);
            }

            $folderModel->db->transComplete();

            $successMsg = ($role === 'Admin')
                ? 'Recipient removed from this evaluation cycle successfully.'
                : 'Subordinate removed from this evaluation cycle successfully.';
            return $this->respond([
                'status'  => 'success',
                'message' => $successMsg
            ]);
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
            $st = strtolower(trim((string)($r['status'] ?? 'pending')));
            if ($st === 'returned' || $st === FolderStatus::REEVALUATE->value || $st === 'eval_returned') {
                $hasRevision = true;
            }
            if ($st !== 'approved' && $st !== FolderStatus::APPROVED->value && $st !== FolderStatus::EVAL_APPROVED->value) {
                $allApproved = false;
            }
        }

        if ($hasRevision) {
            $folderModel->update($folderId, ['status' => FolderStatus::EVAL_RETURNED->value]);
        } elseif ($allApproved) {
            $folderModel->update($folderId, ['status' => FolderStatus::EVAL_APPROVED->value, 'rated_at' => date('Y-m-d H:i:s')]);
        } else {
            $folderModel->update($folderId, ['status' => FolderStatus::EVAL_SUBMITTED->value, 'rated_at' => null]);
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
        if (!$user) return null;

        $targetUserIsAdmin = $userModel->hasRole($userId, 'Admin');

        // Check if user is an Executive (VPAA, VP, President), Dean, or Department Chair
        $plantilla = $userModel->getActivePlantillaDetails($userId);
        $posTitle = strtolower($plantilla['position'] ?? '');
        $userEmail = strtolower($user['email'] ?? '');
        $isExecutive = str_contains($posTitle, 'vpaa') 
                    || str_contains($posTitle, 'vice president') 
                    || str_contains($posTitle, 'president')
                    || str_contains($userEmail, 'vpaa')
                    || str_contains($userEmail, 'cao');
        $isDean = str_contains($posTitle, 'dean') || str_contains($userEmail, 'dean');
        $isChair = str_contains($posTitle, 'chair') 
                || str_contains($posTitle, 'head')
                || str_contains($userEmail, 'chair');

        $defaultDoc = 'IPCR';
        if ($targetUserIsAdmin || $isExecutive) {
            $defaultDoc = 'OPCR';
        } elseif ($isDean || $isChair) {
            $defaultDoc = 'DPCR';
        } elseif (($plantilla['is_teaching'] ?? 1) == 0) {
            $defaultDoc = 'IPERF';
        }

        $docType = $overrideDocType 
                ?: (!empty($user['doc_type']) 
                    ? strtoupper(trim($user['doc_type'])) 
                    : $defaultDoc);

        // Check if document already exists in this folder
        $existing = $documentModel->where('document_folder_id', $folderId)->first();
        if ($existing) {
            $folderModel = new DocumentFolderModel();
            $folder = $folderModel->find($folderId);
            $status = $folder['status'] ?? 'draft_target';

            // Check if folder is mid target setting
            $isMidTargetSetting = in_array($status, [
                FolderStatus::DRAFT_TARGET->value,
                FolderStatus::PENDING_TARGET_APPROVAL->value,
                FolderStatus::TARGET_RETURNED->value,
                FolderStatus::TARGET_UNAPPROVED->value,
                FolderStatus::DRAFT->value,
                FolderStatus::UNEVALUATED->value,
            ]);

            // If account changed paper type mid target setting, synchronize their paper to match
            if ($isMidTargetSetting) {
                $changed = convert_document_paper_type($existing['id'], $docType);
                if ($changed && $status === FolderStatus::PENDING_TARGET_APPROVAL->value) {
                    $folderModel->update($folderId, [
                        'status'              => FolderStatus::DRAFT_TARGET->value,
                        'target_submitted_at' => null
                    ]);
                }
                // Merge targets and rubrics from any new cascading supervisors
                $this->mergeCascadedSupervisorTargetsIntoExistingDoc($existing['id'], $folderId);
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

        // Resolve cascaded targets and rubrics from supervisor(s) if this is a child or routed folder
        $folderModel = new DocumentFolderModel();
        $targetFolder = $folderModel->find($folderId);
        $cascadedCategories = [
            'core'      => [],
            'strategic' => [],
            'support'   => []
        ];
        $cascadedRubrics = [];
        $cascadedCurrency = '₱';
        $cascadedWeights = null;

        if ($targetFolder && !empty($targetFolder['parent_folder_id'])) {
            $routingModel = new \App\Models\EvaluationRoutingModel();
            $cascadedRoutes = $routingModel->getEvaluatorsForFolder((string)$folderId);
            $supervisorFolderIds = [];

            foreach ($cascadedRoutes as $r) {
                if (!empty($r['evaluator_folder_id']) && !in_array($r['evaluator_folder_id'], $supervisorFolderIds)) {
                    $supervisorFolderIds[] = $r['evaluator_folder_id'];
                }
            }

            // Fallback: If no evaluator routes recorded yet, use immediate parent folder only
            if (empty($supervisorFolderIds) && !empty($targetFolder['parent_folder_id'])) {
                $pf = $folderModel->find($targetFolder['parent_folder_id']);
                if ($pf && !empty($pf['parent_folder_id']) && !in_array($targetFolder['parent_folder_id'], $supervisorFolderIds)) {
                    $supervisorFolderIds[] = $targetFolder['parent_folder_id'];
                }
            }

            $isTargetIperf = (strtoupper($docType) === 'IPERF');
            $seenKeys = [];

            foreach ($supervisorFolderIds as $sFid) {
                $sFolder = $folderModel->find($sFid);
                // When Admin cascades or if this is the root institutional cycle, skip targets (Admin only initiates cycles)
                if (!$sFolder || empty($sFolder['parent_folder_id']) || $userModel->hasRole($sFolder['user_id'], 'Admin')) {
                    continue;
                }

                $sDoc = $documentModel->where('document_folder_id', $sFid)->first();
                if (!$sDoc || empty($sDoc['tabs'])) continue;

                $sTabs = is_string($sDoc['tabs']) ? json_decode($sDoc['tabs'], true) : $sDoc['tabs'];
                if (empty($sTabs) || !is_array($sTabs)) continue;

                $sFormData = $sTabs[0]['formData'] ?? [];
                $sCats = $sFormData['categories'] ?? [];
                $sRubs = $sFormData['rubrics'] ?? [];
                if (!empty($sFormData['currency'])) $cascadedCurrency = $sFormData['currency'];
                // Category percentage weights are intentionally left empty when cascaded to avoid conflicts across multiple supervisors

                foreach (['core', 'strategic', 'support'] as $catKey) {
                    if (empty($sCats[$catKey]) || !is_array($sCats[$catKey])) continue;

                    foreach ($sCats[$catKey] as $sIdx => $sRow) {
                        $mfo = trim((string)($sRow['mfo'] ?? ($sRow['office_ppa'] ?? ($sRow['paps'] ?? ($sRow['mfo_title'] ?? '')))));
                        $ind = trim((string)($sRow['indicators'] ?? ($sRow['success_indicators'] ?? ($sRow['expected_outputs'] ?? ($sRow['success_indicator'] ?? '')))));
                        if ($mfo === '' && $ind === '') continue;

                        $dKey = md5(strtolower($mfo . '||' . $ind));
                        if (isset($seenKeys[$dKey])) continue;
                        $seenKeys[$dKey] = true;

                        $destCat = $isTargetIperf ? 'core' : $catKey;
                        $newRowId = 'row_' . $destCat . '_cascaded_' . $sIdx . '_' . substr(md5(uniqid()), 0, 6);

                        $cascadedCategories[$destCat][] = [
                            'row_id'          => $newRowId,
                            'mfo'             => $mfo,
                            'indicators'      => $ind,
                            'budget'          => $sRow['budget'] ?? ($sRow['allotted_budget'] ?? ''),
                            'budget_currency' => $sRow['budget_currency'] ?? $cascadedCurrency,
                            'accountable'     => $sRow['accountable'] ?? ($sRow['division'] ?? ''),
                            'accomplishments' => '',
                            'q'               => $sRow['q'] ?? '',
                            't'               => $sRow['t'] ?? '',
                            'e'               => $sRow['e'] ?? '',
                            'q_self'          => ($sRow['q'] === 'X' || $sRow['q'] === 'N/A') ? 'X' : ($sRow['q_self'] ?? ''),
                            't_self'          => ($sRow['t'] === 'X' || $sRow['t'] === 'N/A') ? 'X' : ($sRow['t_self'] ?? ''),
                            'e_self'          => ($sRow['e'] === 'X' || $sRow['e'] === 'N/A') ? 'X' : ($sRow['e_self'] ?? ''),
                            'remarks'         => '',
                            'twg_comment'     => '',
                            'std_5'           => $sRow['std_5'] ?? '',
                            'std_4'           => $sRow['std_4'] ?? '',
                            'std_3'           => $sRow['std_3'] ?? '',
                            'std_2'           => $sRow['std_2'] ?? '',
                            'std_1'           => $sRow['std_1'] ?? ''
                        ];

                        $origRowId = $sRow['row_id'] ?? ('row_' . $catKey . '_' . $sIdx);
                        if (!empty($sRubs[$origRowId]) && is_array($sRubs[$origRowId])) {
                            $cascadedRubrics[$newRowId] = $sRubs[$origRowId];
                        } elseif (!empty($sRow['std_5']) || !empty($sRow['std_4']) || !empty($sRow['std_3'])) {
                            $cascadedRubrics[$newRowId] = [
                                '5' => ['q' => $sRow['std_5'] ?? '', 't' => '', 'e' => ''],
                                '4' => ['q' => $sRow['std_4'] ?? '', 't' => '', 'e' => ''],
                                '3' => ['q' => $sRow['std_3'] ?? '', 't' => '', 'e' => ''],
                                '2' => ['q' => $sRow['std_2'] ?? '', 't' => '', 'e' => ''],
                                '1' => ['q' => $sRow['std_1'] ?? '', 't' => '', 'e' => '']
                            ];
                        }
                    }
                }
            }
        }

        if (!empty($cascadedCategories['core']) || !empty($cascadedCategories['strategic']) || !empty($cascadedCategories['support'])) {
            if (!isset($initialTabs[0]['formData']) || !is_array($initialTabs[0]['formData'])) {
                $initialTabs[0]['formData'] = [];
            }
            $initialTabs[0]['formData']['categories'] = $cascadedCategories;
            $initialTabs[0]['formData']['rubrics']    = $cascadedRubrics;
            $initialTabs[0]['formData']['currency']   = $cascadedCurrency;
            // Weights are left empty for the ratee to define their own category percentages without supervisor conflicts
            $initialTabs[0]['formData']['weights']    = null;
        }

        return create_unique_row($documentModel, [
            'title'              => $docType,
            'document_folder_id' => $folderId,
            'tabs'               => $initialTabs
        ]);
    }

    /**
     * Merges cascaded targets and rubrics from all active evaluating supervisors into an existing document.
     * Only appends new goals that do not already exist in the document (matching on normalized mfo + indicators).
     */
    private function mergeCascadedSupervisorTargetsIntoExistingDoc(string $docId, string $folderId): void {
        $documentModel = new \App\Models\DocumentModel();
        $folderModel   = new DocumentFolderModel();
        $routingModel  = new \App\Models\EvaluationRoutingModel();
        $userModel     = new \App\Models\UserModel();

        $doc = $documentModel->find($docId);
        if (!$doc || empty($doc['tabs'])) return;

        $tabs = is_string($doc['tabs']) ? json_decode($doc['tabs'], true) : $doc['tabs'];
        if (empty($tabs) || !is_array($tabs) || !isset($tabs[0])) return;

        $formData = $tabs[0]['formData'] ?? [];
        $existingCats = $formData['categories'] ?? ['core' => [], 'strategic' => [], 'support' => []];
        $existingRubs = $formData['rubrics'] ?? [];

        $docType = strtoupper($formData['doc_type'] ?? ($doc['title'] ?? 'IPCR'));
        $isTargetIperf = ($docType === 'IPERF');

        $cascadedRoutes = $routingModel->getEvaluatorsForFolder((string)$folderId);
        $supervisorFolderIds = [];
        foreach ($cascadedRoutes as $r) {
            if (!empty($r['evaluator_folder_id']) && !in_array($r['evaluator_folder_id'], $supervisorFolderIds)) {
                $supervisorFolderIds[] = $r['evaluator_folder_id'];
            }
        }
        $targetFolder = $folderModel->find($folderId);
        if (empty($supervisorFolderIds) && !empty($targetFolder['parent_folder_id'])) {
            $pf = $folderModel->find($targetFolder['parent_folder_id']);
            if ($pf && !empty($pf['parent_folder_id'])) {
                $supervisorFolderIds[] = $targetFolder['parent_folder_id'];
            }
        }

        if (empty($supervisorFolderIds)) return;

        $hasChanges = false;
        $currency = $formData['currency'] ?? '₱';

        foreach ($supervisorFolderIds as $sFid) {
            $sFolder = $folderModel->find($sFid);
            if (!$sFolder || empty($sFolder['parent_folder_id']) || $userModel->hasRole($sFolder['user_id'], 'Admin')) {
                continue;
            }

            $sDoc = $documentModel->where('document_folder_id', $sFid)->first();
            if (!$sDoc || empty($sDoc['tabs'])) continue;

            $sTabs = is_string($sDoc['tabs']) ? json_decode($sDoc['tabs'], true) : $sDoc['tabs'];
            if (empty($sTabs) || !is_array($sTabs) || !isset($sTabs[0]['formData'])) continue;

            $sFormData = $sTabs[0]['formData'];
            $sCats = $sFormData['categories'] ?? [];
            $sRubs = $sFormData['rubrics'] ?? [];

            foreach (['core', 'strategic', 'support'] as $catKey) {
                if (empty($sCats[$catKey]) || !is_array($sCats[$catKey])) continue;

                foreach ($sCats[$catKey] as $sIdx => $sRow) {
                    $mfo = trim((string)($sRow['mfo'] ?? ($sRow['office_ppa'] ?? ($sRow['paps'] ?? ($sRow['mfo_title'] ?? '')))));
                    $ind = trim((string)($sRow['indicators'] ?? ($sRow['success_indicators'] ?? ($sRow['expected_outputs'] ?? ($sRow['success_indicator'] ?? '')))));
                    if ($mfo === '' && $ind === '') continue;

                    $destCat = $isTargetIperf ? 'core' : $catKey;
                    if (!isset($existingCats[$destCat]) || !is_array($existingCats[$destCat])) {
                        $existingCats[$destCat] = [];
                    }

                    // Check if already exists in subordinate's document
                    $alreadyExists = false;
                    foreach ($existingCats[$destCat] as $exRow) {
                        $exMfo = trim((string)($exRow['mfo'] ?? ($exRow['office_ppa'] ?? ($exRow['paps'] ?? ($exRow['mfo_title'] ?? '')))));
                        $exInd = trim((string)($exRow['indicators'] ?? ($exRow['success_indicators'] ?? ($exRow['expected_outputs'] ?? ($exRow['success_indicator'] ?? '')))));
                        if (strcasecmp($exMfo, $mfo) === 0 && strcasecmp($exInd, $ind) === 0) {
                            $alreadyExists = true;
                            break;
                        }
                    }

                    if (!$alreadyExists) {
                        $newRowId = 'row_' . $destCat . '_cascaded_' . $sIdx . '_' . substr(md5(uniqid()), 0, 6);
                        $existingCats[$destCat][] = [
                            'row_id'          => $newRowId,
                            'mfo'             => $mfo,
                            'indicators'      => $ind,
                            'budget'          => $sRow['budget'] ?? ($sRow['allotted_budget'] ?? ''),
                            'budget_currency' => $sRow['budget_currency'] ?? $currency,
                            'accountable'     => $sRow['accountable'] ?? ($sRow['division'] ?? ''),
                            'accomplishments' => '',
                            'q'               => $sRow['q'] ?? '',
                            't'               => $sRow['t'] ?? '',
                            'e'               => $sRow['e'] ?? '',
                            'q_self'          => ($sRow['q'] === 'X' || $sRow['q'] === 'N/A') ? 'X' : ($sRow['q_self'] ?? ''),
                            't_self'          => ($sRow['t'] === 'X' || $sRow['t'] === 'N/A') ? 'X' : ($sRow['t_self'] ?? ''),
                            'e_self'          => ($sRow['e'] === 'X' || $sRow['e'] === 'N/A') ? 'X' : ($sRow['e_self'] ?? ''),
                            'remarks'         => '',
                            'twg_comment'     => '',
                            'std_5'           => $sRow['std_5'] ?? '',
                            'std_4'           => $sRow['std_4'] ?? '',
                            'std_3'           => $sRow['std_3'] ?? '',
                            'std_2'           => $sRow['std_2'] ?? '',
                            'std_1'           => $sRow['std_1'] ?? ''
                        ];

                        $origRowId = $sRow['row_id'] ?? ('row_' . $catKey . '_' . $sIdx);
                        if (!empty($sRubs[$origRowId]) && is_array($sRubs[$origRowId])) {
                            $existingRubs[$newRowId] = $sRubs[$origRowId];
                        } elseif (!empty($sRow['std_5']) || !empty($sRow['std_4']) || !empty($sRow['std_3'])) {
                            $existingRubs[$newRowId] = [
                                '5' => ['q' => $sRow['std_5'] ?? '', 't' => '', 'e' => ''],
                                '4' => ['q' => $sRow['std_4'] ?? '', 't' => '', 'e' => ''],
                                '3' => ['q' => $sRow['std_3'] ?? '', 't' => '', 'e' => ''],
                                '2' => ['q' => $sRow['std_2'] ?? '', 't' => '', 'e' => ''],
                                '1' => ['q' => $sRow['std_1'] ?? '', 't' => '', 'e' => ''],
                            ];
                        }

                        $hasChanges = true;
                    }
                }
            }
        }

        if ($hasChanges) {
            $tabs[0]['formData']['categories'] = $existingCats;
            $tabs[0]['formData']['rubrics']    = $existingRubs;
            $documentModel->update($docId, [
                'tabs' => is_string($doc['tabs']) ? json_encode($tabs) : $tabs
            ]);
        }
    }

    /** POST /folder - Creates a new blank evaluation folder (in Draft) for the current user. */
    public function store() {
        return $this->tryOrFail(function() {
            $documentFolderModel = new DocumentFolderModel();
            $userId = session()->get('user_id');
            $title = trim($this->request->getPost('title')) ?: 'Untitled Evaluation';

            $approvingName = trim($this->request->getPost('approving_authority_name') ?? '');
            $approvingPos  = trim($this->request->getPost('approving_authority_position') ?? '');

            $payload = [
                'title'                        => resolve_unique_title($title, ['user_id' => $userId], 'title', $documentFolderModel),
                'user_id'                      => $userId,
                'approving_authority_name'     => $approvingName ?: null,
                'approving_authority_position' => $approvingPos ?: null,
                'status'                       => FolderStatus::DRAFT_TARGET->value,
                'target_approved_at'           => null,
            ];

            $newId = create_unique_row($documentFolderModel, $payload);

            if (!$newId) {
                return $this->respondError("Could not generate a unique ID.", 400);
            }

            // Set cycle_folder_id to self (root cycle folder)
            $documentFolderModel->update($newId, ['cycle_folder_id' => $newId]);

            // Save deadlines to cycle_deadlines table
            $cycleDeadlineModel = new \App\Models\CycleDeadlineModel();
            $cycleDeadlineModel->saveDeadlinesFromRequest($newId, $this->request);

            // Create the owner's official evaluation paper (Admins initiate cycles and do not fill forms)
            $role = session()->get('role');
            if ($role !== 'Admin') {
                $this->ensureUserDocumentExists($newId, $userId, $title);
            }

            return $this->respond(['status' => 'success', 'id' => $newId]);
        });
    }

    /** POST /folder/delete - Deletes a folder. Only the owning Admin can actually remove it. */
    public function destroy() {
        $folderId = $this->request->getVar('doc_id') ?? $this->request->getVar('folder_id') ?? $this->request->getPost('doc_id');
        $folderModel = new DocumentFolderModel();
        
        $userId = session()->get('user_id');
        $role   = session()->get('role');

        $folder = $folderModel->find($folderId);

        if (!$folder) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Folder not found']);
        }

        if ($role !== 'Admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized: Only Administrators can permanently delete evaluation folders.']);
        }

        // Collect all folders in the subtree (this folder and all its child/grandchild folders)
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

        if (!empty($allFolderIds)) {
            $documentModel = new \App\Models\DocumentModel();
            
            // Clean up attachments
            $docs = $documentModel->select('id')->whereIn('document_folder_id', $allFolderIds)->findAll();
            if (!empty($docs)) {
                $docIds = array_column($docs, 'id');
                if ($folderModel->db->tableExists('document_attachments')) {
                    $folderModel->db->table('document_attachments')->whereIn('document_id', $docIds)->delete();
                }
            }

            // Clean up documents and routings
            $documentModel->whereIn('document_folder_id', $allFolderIds)->delete();
            $folderModel->db->table('evaluation_routings')->whereIn('folder_id', $allFolderIds)->delete();
            $folderModel->db->table('evaluation_routings')->whereIn('evaluator_folder_id', $allFolderIds)->delete();
            
            foreach ($allFolderIds as $delId) {
                $folderModel->db->query("DELETE FROM notifications WHERE link LIKE '%folders/{$delId}%' OR link LIKE '%ratings/show/{$delId}%'");
            }

            // Delete child folders first before deleting the parent folder
            $childrenIds = array_diff($allFolderIds, [$folderId]);
            if (!empty($childrenIds)) {
                $folderModel->whereIn('id', $childrenIds)->delete();
            }
        }
        $teamIds = array_unique($teamIds);

        // Delete master folder
        $folderModel->delete($folderId);

        // Any of those teams that were already archived and are no longer referenced
        // by any remaining folder are now truly orphaned - purge them
        $presetModel = new RoutingPresetModel();
        foreach ($teamIds as $teamId) {
            $archived = $presetModel->onlyDeleted()->find($teamId);
            if ($archived && $folderModel->where('routing_preset_id', $teamId)->countAllResults() === 0) {
                $presetModel->delete($teamId, true);
            }
        }

        if (session()->get('active_folder_id') == $folderId) {
            session()->remove('active_folder_id');
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Folder and all associated evaluation records deleted successfully.']);
    }

    /**
     * GET /folders/archived or /folders/archived/{folderId} - Displays archived folders.
     */
    public function archived($folderId = null) {
        if (session()->get('role') !== 'Admin') {
            return redirect()->to(site_url('folders'));
        }

        $userId = session()->get('user_id');
        $role   = session()->get('role');

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
        $presetModel = new RoutingPresetModel();
        $presets = $presetModel->getPresetsWithDetails($userId);
        $cascadedChildren = [];
        $pendingTeamMembers = [];

        if ($folderId) {
            $activeFolder = $folderModel->find($folderId);
            if ($activeFolder && $activeFolder['user_id'] == $userId) {
                $activeFolder = $folderModel->enrichFolderWithDeadlines($activeFolder);
                if ($role !== 'Admin') {
                    $this->ensureUserDocumentExists($folderId, $userId, $activeFolder['title']);
                }
                $myDocs = $documentModel->where('document_folder_id', $folderId)->findAll();
                $owner = $userModel->find($activeFolder['user_id']);
                $ownerDocType = $owner['doc_type'] ?? null;

                // Ensure the assigned team preset is included in $presets even if soft-deleted
                if (!empty($activeFolder['routing_preset_id']) && !in_array($activeFolder['routing_preset_id'], array_column($presets, 'id'))) {
                    $archivedPreset = $presetModel->withDeleted()->find($activeFolder['routing_preset_id']);
                    if ($archivedPreset) {
                        $rpmModel = new \App\Models\RoutingPresetMemberModel();
                        $archivedPreset['member_count'] = $rpmModel->where('preset_id', $archivedPreset['id'])->countAllResults();
                        $presets[] = $archivedPreset;
                    }
                }

                // Query child folders released from this cycle (they have deleted_at IS NOT NULL because the cycle was archived)
                $routingModel = new \App\Models\EvaluationRoutingModel();
                $routedFolderIds = $routingModel->where('evaluator_folder_id', $activeFolder['id'])
                    ->findColumn('folder_id') ?: [];

                $builder = $folderModel
                    ->select("document_folders.id, document_folders.user_id, document_folders.status, document_folders.target_submitted_at, users.first_name, users.last_name, users.email, pos.title as position")
                    ->join('users', 'users.id = document_folders.user_id')
                    ->join('plantillas p', 'p.user_id = users.id AND p.ended_at IS NULL', 'left')
                    ->join('positions pos', 'pos.id = p.position_id', 'left');

                if (!empty($routedFolderIds)) {
                    $builder->groupStart()
                        ->where('document_folders.parent_folder_id', $activeFolder['id'])
                        ->orWhereIn('document_folders.id', $routedFolderIds)
                    ->groupEnd();
                } else {
                    $builder->where('document_folders.parent_folder_id', $activeFolder['id']);
                }
                $cascadedChildren = $builder->findAll();

                if (!empty($activeFolder['routing_preset_id'])) {
                    $rpmModel = new \App\Models\RoutingPresetMemberModel();
                    $teamMembers = $rpmModel->where('preset_id', $activeFolder['routing_preset_id'])->findAll();
                    $cascadedUserIds = array_map('intval', array_column($cascadedChildren, 'user_id'));

                    foreach ($teamMembers as $tm) {
                        $tUserId = (int)$tm['user_id'];
                        if (in_array($tUserId, $cascadedUserIds, true)) {
                            continue;
                        }

                        $pUser = $userModel->find($tUserId);
                        if ($pUser) {
                            $pPlantilla = $userModel->getActivePlantillaDetails($tUserId);
                            $pendingTeamMembers[] = [
                                'user_id'    => $tUserId,
                                'first_name' => $pUser['first_name'],
                                'last_name'  => $pUser['last_name'],
                                'email'      => $pUser['email'],
                                'position'   => $pPlantilla['position'] ?? ''
                            ];
                        }
                    }
                }
            }
        }

        return view('components/app_shell', [
            'sidebarFolders'   => $folders,
            'selectedFolderId' => $activeFolder['id'] ?? null,
            'mainView'         => 'document/_doc_rows',
            'templates'        => $templateModel->findAll(),
            'mainData'         => [
                'activeFolder'            => $activeFolder,
                'ownerDocType'            => $ownerDocType,
                'myDocs'                  => $myDocs,
                'groupedGuides'           => [],
                'isReadOnly'              => true,
                'presets'                 => $presets,
                'cascadedChildren'        => $cascadedChildren,
                'pendingTeamMembers'      => $pendingTeamMembers,
                'pendingTeamMembersCount' => count($pendingTeamMembers),
                'isArchivedView'          => true
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

            if ($role !== 'Admin') {
                return $this->respondError('Unauthorized. Only administrators can archive evaluation folders.', 403);
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
                'message' => 'Evaluation cycle successfully closed and archived. All ratings and scores are now frozen.'
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

            if ($role !== 'Admin') {
                return $this->respondError('Unauthorized. Only administrators can restore evaluation folders.', 403);
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

            $masterFolder = $folderModel->find($folderId);
            if (!$masterFolder) {
                return $this->respondError("Folder not found.", 404);
            }

            $userRole = session()->get('role');
            $userId   = session()->get('user_id');

            // Only administrators can edit cycle dates and retitle folders
            if ($userRole !== 'Admin') {
                return $this->respondError("Unauthorized. Only administrators can edit evaluation folders.", 403);
            }

            $title = $this->request->getPost('title');
            
            $scheduleMode   = $this->request->getPost('schedule_mode');
            $genTargetStart = $this->request->getPost('general_target_start');
            $genTargetEnd   = $this->request->getPost('general_target_end');
            $genEvalStart   = $this->request->getPost('general_eval_start');
            $genEvalEnd     = $this->request->getPost('general_eval_end');

            $docTypes = ['ipcr', 'cdpcr', 'dpcr', 'opcr', 'iperf'];
            $approvingName = trim($this->request->getPost('approving_authority_name') ?? '');
            $approvingPos  = trim($this->request->getPost('approving_authority_position') ?? '');
            $folderData = [
                'title'                        => $title,
                'approving_authority_name'     => $approvingName ?: null,
                'approving_authority_position' => $approvingPos ?: null
            ];
            
            // 1. Update the Master Folder
            $folderModel->update($folderId, $folderData);

            // 2. Save deadlines to cycle_deadlines table
            $cycleDeadlineModel = new \App\Models\CycleDeadlineModel();
            $cycleDeadlineModel->saveDeadlinesFromRequest($folderId, $this->request);
            $cycleDeadlines = $cycleDeadlineModel->getCycleDatesFlat($folderId);

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

            // 3. Fetch and Process All Descendant Cascaded Folders (all tiers: Deans, Chairs, Faculty)
            $db = \Config\Database::connect();
            $allDescendantIds = [];
            $toVisit = [$folderId];
            while (!empty($toVisit)) {
                $curr = array_pop($toVisit);
                $children = $folderModel->where('parent_folder_id', $curr)->where('deleted_at IS NULL')->findAll();
                foreach ($children as $c) {
                    $allDescendantIds[] = $c['id'];
                    $toVisit[] = $c['id'];
                }
            }

            $childFolders = [];
            if (!empty($allDescendantIds)) {
                $childFolders = $db->table('document_folders df')
                    ->select('df.*, u.doc_type, u.email, pos.title as position')
                    ->join('users u', 'u.id = df.user_id')
                    ->join('plantillas p', 'p.user_id = u.id AND p.ended_at IS NULL', 'left')
                    ->join('positions pos', 'pos.id = p.position_id', 'left')
                    ->whereIn('df.id', array_unique($allDescendantIds))
                    ->where('df.deleted_at IS NULL')
                    ->get()->getResultArray();
            }

            if (!empty($childFolders)) {
                foreach ($childFolders as $child) {
                    $childData = ['title' => $title];
                    if (empty($child['cycle_folder_id'])) {
                        $childData['cycle_folder_id'] = $folderId;
                    }
                    
                    $userDocType = strtolower($child['doc_type'] ?? 'ipcr');
                    $pos = strtolower($child['position'] ?? '');
                    $email = strtolower($child['email'] ?? '');
                    $isDean = str_contains($pos, 'dean') || str_contains($email, 'dean');

                    if (($userDocType === 'dpcr' || $userDocType === 'cdpcr') && $isDean) {
                        $effectiveType = (!empty($cycleDeadlines['cdpcr_target_start']) || !empty($cycleDeadlines['cdpcr_target_end']) || !empty($cycleDeadlines['cdpcr_eval_start']) || !empty($cycleDeadlines['cdpcr_eval_end'])) ? 'cdpcr' : 'dpcr';
                    } else {
                        $effectiveType = in_array($userDocType, $docTypes) ? $userDocType : 'ipcr';
                    }

                    $targetStart = $cycleDeadlines["{$effectiveType}_target_start"] ?? $cycleDeadlines['dpcr_target_start'];
                    $targetEnd   = $cycleDeadlines["{$effectiveType}_target_end"] ?? $cycleDeadlines['dpcr_target_end'];
                    $dateStart   = $cycleDeadlines["{$effectiveType}_eval_start"] ?? $cycleDeadlines['dpcr_eval_start'];
                    $dateEnd     = $cycleDeadlines["{$effectiveType}_eval_end"] ?? $cycleDeadlines['dpcr_eval_end'];
                    
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

    /** POST /folder/submit - Owner submits for evaluation (actual accomplishments & self-rating). */
    public function submit() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $userId = session()->get('user_id');
            $folderModel = new DocumentFolderModel();

            $folder = $folderModel->find($folderId);

            $validSubmitStatuses = [
                FolderStatus::DRAFT_EVALUATION->value,
                FolderStatus::EVAL_RETURNED->value,
                FolderStatus::DRAFT->value,
                FolderStatus::TO_EVALUATE->value,
                FolderStatus::REEVALUATE->value
            ];
            if (!$folder || !in_array($folder['status'], $validSubmitStatuses, true)) {
                return $this->respondError("This folder cannot be submitted at this time.", 400);
            }

            if ($folder['user_id'] != $userId) {
                return $this->respondError("Unauthorized to submit this folder.", 400);
            }

            // --- Document Validation ---
            $documentModel = new DocumentModel();
            $hasDoc = $documentModel->where('document_folder_id', $folderId)->countAllResults();
            
            if ($hasDoc == 0) {
                return $this->respondError("Submission Failed: No document found in this folder.", 400);
            }
            // ---------------------------------------

            $now = date('Y-m-d H:i:s');

            $folderModel->update($folderId, [
                'status'       => FolderStatus::EVAL_SUBMITTED->value,
                'submitted_at' => $now
            ]);

            // Sync all evaluator routes to pending review
            $routingModel = new \App\Models\EvaluationRoutingModel();
            $routingModel->where('folder_id', $folderId)
                         ->set(['status' => 'pending', 'updated_at' => $now])
                         ->update();

            audit_log('ACCOMPLISHMENT_SUBMITTED', 'RATING', 'document_folder', (int) $folderId, "Actual accomplishments submitted for evaluation: {$folder['title']}");

            $subUser = (new UserModel())->find($userId);
            $subName = trim(($subUser['first_name'] ?? '') . ' ' . ($subUser['last_name'] ?? '')) ?: 'Employee';
            $this->notifyFolderEvaluators(
                $folder,
                'Accomplishments Submitted for Evaluation',
                "{$subName} submitted actual accomplishments for \"{$folder['title']}\" for your rating and evaluation.",
                'eval_submitted',
                'file'
            );

            return $this->respond(['status' => 'success', 'message' => 'Folder submitted for evaluation.']);
        });
    }

    /** POST /folder/unsubmit - Owner recalls an eval_submitted folder back to draft_evaluation while the eval window is still open. */
    public function unsubmit() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $userId = session()->get('user_id');
            $folderModel = new DocumentFolderModel();

            $folder = $folderModel->find($folderId);

            if (!$folder || !in_array($folder['status'], [FolderStatus::EVAL_SUBMITTED->value, FolderStatus::EVALUATED->value, FolderStatus::SUBMITTED->value])) {
                return $this->respondError("This folder cannot be unsubmitted at this time.", 400);
            }

            if ($folder['user_id'] != $userId) return $this->respondError("Unauthorized to revoke evaluation for this folder.", 403);

            $dates = $folderModel->getFolderDates($folder);
            if (!empty($dates['eval_date_end']) && date('Y-m-d H:i:s') > $dates['eval_date_end']) {
                return $this->respondError("Cannot unsubmit: Evaluation window has already closed.", 400);
            }

            $routingModel = new \App\Models\EvaluationRoutingModel();
            $hasApproval = $routingModel->where('folder_id', $folderId)
                                        ->whereIn('status', ['approved', FolderStatus::APPROVED->value])
                                        ->countAllResults() > 0;
            if ($hasApproval) {
                return $this->respondError("Cannot unsubmit: An evaluator has already approved your rating.", 400);
            }

            $now = date('Y-m-d H:i:s');
            $folderModel->update($folderId, [
                'status'       => FolderStatus::DRAFT_EVALUATION->value,
                'final_rating' => null,
                'rated_at'     => null,
                'submitted_at' => null
            ]);

            // Sync all evaluator routes back to draft
            $routingModel->where('folder_id', $folderId)
                         ->set(['status' => 'draft', 'updated_at' => $now])
                         ->update();

            audit_log('EVALUATION_REVOKED', 'RATING', 'document_folder', (int) $folderId, "Self-evaluation submission unsubmitted back to drafting: {$folder['title']}");

            $subUser = (new UserModel())->find($userId);
            $subName = trim(($subUser['first_name'] ?? '') . ' ' . ($subUser['last_name'] ?? '')) ?: 'Employee';
            $this->notifyFolderEvaluators(
                $folder,
                'Self-Evaluation Unsubmitted',
                "{$subName} unsubmitted their evaluation for \"{$folder['title']}\" and returned it to drafting.",
                'eval_unsubmitted',
                'file'
            );

            return $this->respond(['status' => 'success', 'message' => 'Evaluation unsubmitted successfully.']);
        });
    }

    /**
     * POST /folder/evaluate - Owner locks in their self-rating (final_rating) on the
     * target document and submits accomplishments for evaluator review.
     */
    public function evaluate() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $finalRating = $this->request->getPost('final_rating');
            $userId = session()->get('user_id');
            $folderModel = new DocumentFolderModel();
            $folderModel->updateTimeBasedStatuses();

            $folder = $folderModel->find($folderId);

            if (!$folder || $folder['user_id'] != $userId) {
                return $this->respondError("Unauthorized to evaluate this folder.", 400);
            }

            $dates = $folderModel->getFolderDates($folder);
            $now = date('Y-m-d H:i:s');
            $isEvalWindowOpen = !empty($dates['eval_date_start']) && !empty($dates['eval_date_end'])
                && $dates['eval_date_start'] <= $now && $dates['eval_date_end'] >= $now;

            $validEvalStatuses = [
                FolderStatus::DRAFT_EVALUATION->value, 
                FolderStatus::EVAL_RETURNED->value,
                FolderStatus::TO_EVALUATE->value, 
                FolderStatus::REEVALUATE->value
            ];
            if ($isEvalWindowOpen) {
                $validEvalStatuses[] = FolderStatus::TARGET_APPROVED->value;
            }

            if (!in_array($folder['status'], $validEvalStatuses, true)) {
                return $this->respondError("This folder cannot be evaluated at this time.", 400);
            }

            $folderModel->update($folderId, [
                'status'       => FolderStatus::EVAL_SUBMITTED->value,
                'final_rating' => $finalRating !== '' && $finalRating !== null ? (float) $finalRating : null,
                'rated_at'     => $now,
                'submitted_at' => $now
            ]);

            // Sync all evaluator routes to pending review
            $routingModel = new EvaluationRoutingModel();
            $routingModel->where('folder_id', $folderId)
                         ->set(['status' => 'pending', 'updated_at' => $now])
                         ->update();

            $userModel   = new UserModel();
            $subordinate = $userModel->find($folder['user_id']);
            $routings    = $routingModel->where('folder_id', $folderId)->findAll();
            
            foreach ($routings as $route) {
                $evaluator = $userModel->find($route['evaluator_id']);

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

            return $this->respond(['status' => 'success', 'message' => 'Evaluation submitted successfully.']);
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

            $dates = $folderModel->getFolderDates($folder);

            if (!empty($dates['target_date_end']) && date('Y-m-d H:i:s') > $dates['target_date_end']) {
                return $this->respondError("The target setting period has already ended.", 400);
            }

            $validSubmitStatuses = [
                FolderStatus::DRAFT_TARGET->value,
                FolderStatus::DRAFT->value,
                FolderStatus::TARGET_RETURNED->value,
                FolderStatus::TARGET_UNAPPROVED->value,
                FolderStatus::TARGET_TWG_DISAPPROVED->value
            ];
            if (!in_array($folder['status'], $validSubmitStatuses, true)) {
                return $this->respondError("Targets cannot be submitted at this stage (current status: " . esc($folder['status']) . ").", 400);
            }

            $isFolderOpcr = $this->isOpcrFolder($folder, $userId);

            // --- STRICT SPMS MODE: Parent Basis Target Validation ---
            if (!$isFolderOpcr && !empty($folder['parent_folder_id'])) {
                $parentFolder = $folderModel->find($folder['parent_folder_id']);
                if ($parentFolder) {
                    $parentDoc = (new \App\Models\DocumentModel())->where('document_folder_id', $parentFolder['id'])->first();
                    $isParentDocOpcr = false;
                    if ($parentDoc) {
                        $parentDocTitleUpper = strtoupper($parentDoc['title'] ?? '');
                        $isParentDocOpcr = str_contains($parentDocTitleUpper, 'OPCR') || str_contains($parentDocTitleUpper, 'OFFICE') || (strtoupper($parentDoc['doc_type'] ?? '') === 'OPCR');
                    }
                    if (!$isParentDocOpcr && $this->isOpcrFolder($parentFolder)) {
                        $isParentDocOpcr = true;
                    }

                    if ($parentFolder['status'] !== FolderStatus::TARGET_APPROVED->value) {
                        $parentTitle = $parentFolder['title'] ?? 'Superior';
                        return $this->respondError("Cannot submit targets yet: The superior basis commitments (\"{$parentTitle}\") have not been approved by the higher-up yet. Under SPMS cascading rules, individual commitments require approved superior targets as a basis.", 400);
                    }
                }
            }

            // --- Target Document Validation ---
            $documentModel = new \App\Models\DocumentModel();
            $targetDoc = $documentModel->where('document_folder_id', $folderId)->first();
            
            if (!$targetDoc) {
                return $this->respondError("Submission Failed: No document found in this folder.", 400);
            }

            // Category percentage weights are required on non-IPERF documents (must total 100%)
            $docType = strtolower($targetDoc['doc_type'] ?? 'ipcr');
            $docTitle = strtoupper($targetDoc['title'] ?? '');
            $isIperfDoc = ($docType === 'iperf' || str_contains($docTitle, 'IPERF') || str_contains($docTitle, 'JOB ORDER') || str_contains($docTitle, 'CONTRACT OF SERVICE'));

            if (!$isIperfDoc) {
                $targetTabs = is_string($targetDoc['tabs'] ?? null) ? json_decode($targetDoc['tabs'], true) : ($targetDoc['tabs'] ?? []);
                if (!is_array($targetTabs)) $targetTabs = [];
                $weights = $targetTabs[0]['formData']['weights'] ?? null;
                $core = isset($weights['core']) ? (float)$weights['core'] : 0.0;
                $strat = isset($weights['strategic']) ? (float)$weights['strategic'] : 0.0;
                $supp = isset($weights['support']) ? (float)$weights['support'] : 0.0;
                $total = (int) round(($core + $strat + $supp) * 100);

                if (empty($weights) || $core <= 0 || $total !== 100) {
                    return $this->respondError("Submission Failed: Category percentage weights (Core, Strategic, Support) are required and must total 100% exactly.", 400);
                }
            }
            // ---------------------------------------

            $folderModel->update($folderId, [
                'status'              => FolderStatus::TARGET_SUBMITTED->value,
                'target_submitted_at' => date('Y-m-d H:i:s')
            ]);
            
            // Sync all evaluator routes to pending review
            $routingModel = new \App\Models\EvaluationRoutingModel();
            $routingModel->where('folder_id', $folderId)
                         ->set(['status' => 'pending', 'updated_at' => date('Y-m-d H:i:s')])
                         ->update();

            audit_log('TARGET_SUBMITTED', 'TARGET', 'document_folder', (int) $folderId, "Targets submitted for approval: {$folder['title']}");

            $user = (new UserModel())->find($userId);
            $name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: 'Employee';
            $this->notifyFolderEvaluators(
                $folder,
                'Targets Submitted for Approval',
                "{$name} submitted target commitments for \"{$folder['title']}\" for your approval.",
                'target_submitted',
                'file'
            );

            $submitMsg = $isFolderOpcr 
                ? 'OPCR target commitments submitted to the University President / Admin for approval.' 
                : 'Targets submitted for approval.';

            return $this->respond(['status' => 'success', 'message' => $submitMsg]);
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

            $dates = $folderModel->getFolderDates($folder);

            if (!empty($dates['target_date_end']) && date('Y-m-d H:i:s') > $dates['target_date_end']) {
                return $this->respondError("The target setting period has already ended.", 400);
            }

            if (!in_array($folder['status'], [FolderStatus::TARGET_SUBMITTED->value, FolderStatus::PENDING_TARGET_APPROVAL->value], true)) {
                return $this->respondError("Only pending targets can be unsubmitted.", 400);
            }

            $routingModel = new \App\Models\EvaluationRoutingModel();
            $hasApproval = $routingModel->where('folder_id', $folderId)
                                        ->whereIn('status', ['approved', FolderStatus::APPROVED->value, 'target_approved'])
                                        ->countAllResults() > 0;
            if ($hasApproval) {
                return $this->respondError("Cannot unsubmit: A supervisor has already approved your targets.", 400);
            }

            $folderModel->update($folderId, [
                'status' => FolderStatus::DRAFT_TARGET->value,
                'target_submitted_at' => null
            ]);

            // Sync all evaluator routes back to draft
            $routingModel->where('folder_id', $folderId)
                         ->set(['status' => 'draft', 'updated_at' => date('Y-m-d H:i:s')])
                         ->update();

            audit_log('TARGET_REVOKED', 'TARGET', 'document_folder', (int) $folderId, "Target submission unsubmitted back to draft: {$folder['title']}");

            $sender = (new UserModel())->find($userId);
            $senderName = $sender ? trim(($sender['first_name'] ?? '') . ' ' . ($sender['last_name'] ?? '')) : 'The employee';
            $this->notifyFolderEvaluators(
                $folder,
                'Target Submission Unsubmitted',
                "{$senderName} unsubmitted their target commitments for \"{$folder['title']}\" and returned it to draft.",
                'target_unsubmitted',
                'file'
            );
            
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
            $releaseScope = $this->request->getPost('release_scope');
            $releaseToDeans = (bool) $this->request->getPost('release_to_deans') || ($releaseScope === 'deans');
            $releaseToChairs = ($releaseScope === 'chairs');
            $releaseToFaculty = ($releaseScope === 'faculty') || ($releaseScope === 'subordinates');
            $folderModel = new DocumentFolderModel();
            
            $folder = $folderModel->find($folderId);
            if (!$folder) return $this->respondError("Folder not found.", 400);

            $evalStatuses = [
                FolderStatus::EVAL_SUBMITTED->value,
                FolderStatus::EVAL_APPROVED->value,
                FolderStatus::EVAL_RETURNED->value,
                FolderStatus::EVALUATED->value, 
                FolderStatus::APPROVED->value, 
                FolderStatus::TWG_APPROVED->value, 
                FolderStatus::TWG_DISAPPROVED->value
            ];

            $userRole = session()->get('role');
            $isTwgReviewer = in_array($userRole, ['TWG', 'PMT'], true);

            $allowedStatuses = [
                FolderStatus::TARGET_SUBMITTED->value, 
                FolderStatus::PENDING_TARGET_APPROVAL->value, 
                ...$evalStatuses
            ];
            // TWG, PMT, and Admin are authorized to verify targets after supervising authority approval
            if ($isTwgReviewer || $userRole === 'Admin') {
                $allowedStatuses[] = FolderStatus::TARGET_APPROVED->value;
            }

            if (!in_array($folder['status'], $allowedStatuses, true)) {
                return $this->respondError("Cannot approve targets: The employee has revoked or unsubmitted their targets. Please refresh the page.", 409);
            }

            if ($userRole === 'TWG') {
                $twgAssignmentModel = new \App\Models\TwgUnitAssignmentModel();
                $assignedUnits = $twgAssignmentModel->getAssignedUnitIds(session()->get('user_id'));
                if (!empty($assignedUnits) && !$twgAssignmentModel->isTwgAssignedToFolder(session()->get('user_id'), $folderId)) {
                    return $this->respondError('Unauthorized: This folder is outside your assigned unit jurisdiction.', 403);
                }
            }

            $dates = $folderModel->getFolderDates($folder);
            
            if (!empty($dates['target_date_end']) && date('Y-m-d H:i:s') > $dates['target_date_end']) {
                return $this->respondError("The target setting period has already ended.", 400);
            }
            
            $targetStatus = in_array($folder['status'], $evalStatuses) ? $folder['status'] : FolderStatus::TARGET_APPROVED->value;

            $currentUserId = (int)session()->get('user_id');
            $userModel     = new \App\Models\UserModel();
            $routingModel  = new \App\Models\EvaluationRoutingModel();

            // Find all evaluation routes for this folder
            $allRoutes = $routingModel->where('folder_id', $folderId)->findAll();
            $supervisorRoutes = [];
            foreach ($allRoutes as $r) {
                $eId = (int)$r['evaluator_id'];
                if ($eId === (int)$folder['user_id']) continue;
                if ($userModel->hasRole($eId, 'Admin')) continue;
                $supervisorRoutes[] = $r;
            }

            // If current user is one of the routed evaluators, update their specific route to approved
            $isRoutedEvaluator = false;
            foreach ($supervisorRoutes as $r) {
                if ((int)$r['evaluator_id'] === $currentUserId) {
                    $isRoutedEvaluator = true;
                    break;
                }
            }

            if ($isRoutedEvaluator) {
                $routingModel->where('folder_id', $folderId)
                             ->where('evaluator_id', $currentUserId)
                             ->set([
                                 'status'     => 'approved',
                                 'updated_at' => date('Y-m-d H:i:s')
                             ])
                             ->update();
            }

            // Inspect all supervisor routes to check if all supervising authorities have approved
            $updatedRoutes = $routingModel->where('folder_id', $folderId)->findAll();
            $totalSupervisors = 0;
            $approvedSupervisors = 0;
            $unapprovedNames = [];

            foreach ($updatedRoutes as $r) {
                $eId = (int)$r['evaluator_id'];
                if ($eId === (int)$folder['user_id']) continue;
                if ($userModel->hasRole($eId, 'Admin')) continue;

                $totalSupervisors++;
                $st = strtolower(trim((string)$r['status']));
                if ($st === 'approved' || $st === FolderStatus::TARGET_APPROVED->value) {
                    $approvedSupervisors++;
                } else {
                    $u = $userModel->find($eId);
                    $unapprovedNames[] = $u ? trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')) : 'Supervisor';
                }
            }

            // If current reviewer is TWG / PMT:
            if ($isTwgReviewer) {
                // TWG can ONLY approve after all routed supervisors have approved!
                if ($totalSupervisors > 0 && $approvedSupervisors < $totalSupervisors) {
                    return $this->respondError("Cannot verify targets yet: All supervising authorities must approve first (currently {$approvedSupervisors} of {$totalSupervisors} approved).", 403);
                }

                // TWG is the final approver!
                $folderModel->update($folderId, [
                    'status'               => $targetStatus,
                    'target_approved_at'   => date('Y-m-d H:i:s'),
                    'target_approved_by'   => $currentUserId,
                    'target_approver_role' => $userRole
                ]);

                // In-app notification to the employee whose target was verified
                notify_user((int) $folder['user_id'], [
                    'sender_id' => $currentUserId,
                    'type'      => 'target_approved',
                    'title'     => 'Target Commitments Verified',
                    'message'   => "Your target commitments for \"{$folder['title']}\" have been verified and approved by {$userRole}.",
                    'link'      => 'folders/' . $folderId,
                    'icon'      => 'check',
                ]);

                audit_log('TARGET_APPROVED', 'TARGET', 'document_folder', (int) $folderId, "Target commitments verified and approved by {$userRole}: " . $folder['title']);

                return $this->respond(['status' => 'success', 'message' => "Targets verified and approved by {$userRole} successfully."]);
            }

            $approverRole = (session()->get('is_president') || session()->get('role') === 'Admin') 
                ? 'University President' 
                : (session()->get('role') ?: 'Supervisor');

            // If there are multiple supervisors and NOT all have approved yet:
            if ($totalSupervisors > 1 && $approvedSupervisors < $totalSupervisors) {
                $folderModel->update($folderId, [
                    'status' => FolderStatus::TARGET_SUBMITTED->value
                ]);

                audit_log('TARGET_APPROVED_PARTIAL', 'TARGET', 'document_folder', (int) $folderId, "Target commitment approved by " . (session()->get('name') ?: 'supervisor') . " ({$approvedSupervisors}/{$totalSupervisors} supervisors approved): {$folder['title']}");

                $msg = "Targets approved by you ({$approvedSupervisors} of {$totalSupervisors} supervisors approved). Awaiting approval from: " . implode(', ', $unapprovedNames) . ".";
                return $this->respond(['status' => 'success', 'message' => $msg]);
            }

            // All supervising authorities have approved! Check if an assigned TWG exists for this folder / unit:
            $twgAssignmentModel = new \App\Models\TwgUnitAssignmentModel();
            $assignedTwg = $twgAssignmentModel->resolveAssignedTwgForFolder($folder);

            if ($assignedTwg) {
                // Keep status at TARGET_SUBMITTED so TWG performs the final verification
                $folderModel->update($folderId, [
                    'status' => FolderStatus::TARGET_SUBMITTED->value
                ]);

                // Notify TWG
                notify_user((int) $assignedTwg['id'], [
                    'sender_id' => $currentUserId,
                    'type'      => 'twg_target_pending',
                    'title'     => 'Targets Ready for TWG Verification',
                    'message'   => "All supervising authorities have approved targets for \"{$folder['title']}\". Ready for your final TWG verification.",
                    'link'      => 'folders/' . $folderId,
                    'icon'      => 'clipboard-check',
                ]);

                // Notify employee
                notify_user((int) $folder['user_id'], [
                    'sender_id' => $currentUserId,
                    'type'      => 'target_supervisors_approved',
                    'title'     => 'Supervising Authorities Approved Targets',
                    'message'   => "All supervising authorities have approved your target commitments for \"{$folder['title']}\". The document has been forwarded to TWG for final verification.",
                    'link'      => 'folders/' . $folderId,
                    'icon'      => 'check',
                ]);

                audit_log('TARGET_APPROVED_SUPERVISORS', 'TARGET', 'document_folder', (int) $folderId, "All supervising authorities approved targets for {$folder['title']}. Forwarded to TWG ({$assignedTwg['name']}).");

                $msg = "Targets approved by you. All supervising authorities have approved; the document has been forwarded to the Technical Working Group (TWG) for final verification.";
                return $this->respond(['status' => 'success', 'message' => $msg]);
            }

            // When ALL supervising authorities have approved AND no assigned TWG exists (or direct OPCR)
            $folderModel->update($folderId, [
                'status'               => $targetStatus,
                'target_approved_at'   => date('Y-m-d H:i:s'),
                'target_approved_by'   => $currentUserId,
                'target_approver_role' => $approverRole
            ]);

            $unitModel = new \App\Models\UnitModel();
            $releasedCount = 0;
            $cycleFolderId = $folder['cycle_folder_id'] ?? $folderModel->getRootCycleFolderId($folder['id']);

            // --- TIER 1 -> TIER 2: APPROVE & RELEASE TO COLLEGE DEANS (FROM OPCR) ---
            if ($releaseToDeans) {
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
                                            ->where('user_id', $dean['id'])
                                            ->where('deleted_at IS NULL')->first();
                    if (!$existing) {
                        $newFolderId = create_unique_row($folderModel, [
                            'title'               => $folder['title'],
                            'user_id'             => $dean['id'],
                            'parent_folder_id'    => $folder['id'],
                            'cycle_folder_id'     => $cycleFolderId,
                            'status'              => FolderStatus::DRAFT_TARGET->value,
                        ]);
                        
                        // Force DPCR paper generation for the Dean
                        $this->ensureUserDocumentExists($newFolderId, $dean['id'], $folder['title'], 'DPCR');

                        // Route Dean to VPAA / Root authority
                        $rExists = $routingModel->where('folder_id', $newFolderId)->where('evaluator_id', $folder['user_id'])->first();
                        if (!$rExists) {
                            $routingModel->insert([
                                'folder_id'           => $newFolderId,
                                'evaluator_id'        => $folder['user_id'],
                                'evaluator_folder_id' => $folder['id'],
                                'status'              => FolderStatus::DRAFT->value
                            ]);
                        }

                        notify_user((int) $dean['id'], [
                            'sender_id' => session()->get('user_id'),
                            'type'      => 'target_released',
                            'title'     => 'Institutional OPCR Basis Released',
                            'message'   => "Institutional OPCR targets for \"{$folder['title']}\" have been approved and released to all colleges as basis for your collegiate DPCR.",
                            'link'      => 'folders/' . $newFolderId,
                            'icon'      => 'file',
                        ]);

                        $releasedCount++;
                    }
                }
            }

            // --- TIER 2 -> TIER 3: APPROVE & RELEASE TO DEPARTMENT CHAIRS (FROM DEAN DPCR) ---
            if ($releaseToChairs) {
                $deanPlantilla = $userModel->getActivePlantillaDetails($folder['user_id']);
                $collegeUnitId = $deanPlantilla['unit_id'] ?? null;
                $chairs = $collegeUnitId ? $unitModel->getChairsForCollege((int) $collegeUnitId) : [];

                foreach ($chairs as $chair) {
                    $existing = $folderModel->where('parent_folder_id', $folder['id'])
                                            ->where('user_id', $chair['user_id'])
                                            ->where('deleted_at IS NULL')->first();
                    if (!$existing) {
                        $newFolderId = create_unique_row($folderModel, [
                            'title'               => $folder['title'],
                            'user_id'             => $chair['user_id'],
                            'parent_folder_id'    => $folder['id'],
                            'cycle_folder_id'     => $cycleFolderId,
                            'status'              => FolderStatus::DRAFT_TARGET->value,
                        ]);

                        // Force DPCR paper generation for the Department Chair
                        $this->ensureUserDocumentExists($newFolderId, $chair['user_id'], $folder['title'], 'DPCR');

                        // Immediate Evaluator: College Dean
                        $r1 = $routingModel->where('folder_id', $newFolderId)->where('evaluator_id', $folder['user_id'])->first();
                        if (!$r1) {
                            $routingModel->insert([
                                'folder_id'           => $newFolderId,
                                'evaluator_id'        => $folder['user_id'],
                                'evaluator_folder_id' => $folder['id'],
                                'status'              => FolderStatus::DRAFT->value
                            ]);
                        }

                        // Next-in-Rank Calibrator: VPAA (parent folder owner)
                        if (!empty($folder['parent_folder_id'])) {
                            $vpaaFolder = $folderModel->find($folder['parent_folder_id']);
                            if ($vpaaFolder && !empty($vpaaFolder['user_id']) && $vpaaFolder['user_id'] != $folder['user_id']) {
                                $r2 = $routingModel->where('folder_id', $newFolderId)->where('evaluator_id', $vpaaFolder['user_id'])->first();
                                if (!$r2) {
                                    $routingModel->insert([
                                        'folder_id'           => $newFolderId,
                                        'evaluator_id'        => $vpaaFolder['user_id'],
                                        'evaluator_folder_id' => $vpaaFolder['id'],
                                        'status'              => FolderStatus::DRAFT->value
                                    ]);
                                }
                            }
                        }

                        notify_user((int) $chair['user_id'], [
                            'sender_id' => session()->get('user_id'),
                            'type'      => 'target_released',
                            'title'     => 'Collegiate DPCR Basis Released',
                            'message'   => "Collegiate DPCR targets for \"{$folder['title']}\" have been approved and released to your department as basis for your DPCR.",
                            'link'      => 'folders/' . $newFolderId,
                            'icon'      => 'file',
                        ]);

                        $releasedCount++;
                    }
                }

                // Skip-level: personnel directly assigned to the college (not under any department with a chair)
                $chairUserIds = array_column($chairs, 'user_id');
                $directPersonnel = $collegeUnitId ? $unitModel->getPersonnelForDepartment((int) $collegeUnitId, (int) $folder['user_id']) : [];
                foreach ($directPersonnel as $dp) {
                    if (in_array($dp['user_id'], $chairUserIds)) continue;
                    $existing = $folderModel->where('parent_folder_id', $folder['id'])
                                            ->where('user_id', $dp['user_id'])
                                            ->where('deleted_at IS NULL')->first();
                    if (!$existing) {
                        $newFolderId = create_unique_row($folderModel, [
                            'title'               => $folder['title'],
                            'user_id'             => $dp['user_id'],
                            'parent_folder_id'    => $folder['id'],
                            'cycle_folder_id'     => $cycleFolderId,
                            'status'              => FolderStatus::DRAFT_TARGET->value,
                        ]);
                        $this->ensureUserDocumentExists($newFolderId, $dp['user_id'], $folder['title'], 'IPCR');

                        $r = $routingModel->where('folder_id', $newFolderId)->where('evaluator_id', $folder['user_id'])->first();
                        if (!$r) {
                            $routingModel->insert([
                                'folder_id'           => $newFolderId,
                                'evaluator_id'        => $folder['user_id'],
                                'evaluator_folder_id' => $folder['id'],
                                'status'              => FolderStatus::DRAFT->value
                            ]);
                        }
                        $releasedCount++;
                    }
                }
            }

            // --- TIER 3 -> TIER 4: APPROVE & RELEASE TO FACULTY/STAFF (FROM CHAIR DPCR) ---
            if ($releaseToFaculty) {
                $chairPlantilla = $userModel->getActivePlantillaDetails($folder['user_id']);
                $deptUnitId = $chairPlantilla['unit_id'] ?? null;
                $facultyList = $deptUnitId ? $unitModel->getPersonnelForDepartment((int) $deptUnitId, (int) $folder['user_id']) : [];

                // Also include any team members if this folder was cascaded to a team preset
                if (!empty($folder['routing_preset_id'])) {
                    $teamMembers = (new \App\Models\RoutingPresetMemberModel())->where('preset_id', $folder['routing_preset_id'])->findAll();
                    $existingFacultyUserIds = array_column($facultyList, 'user_id');
                    foreach ($teamMembers as $tm) {
                        if ($tm['user_id'] != $folder['user_id'] && !in_array($tm['user_id'], $existingFacultyUserIds)) {
                            $facultyList[] = ['user_id' => $tm['user_id']];
                        }
                    }
                }

                // Resolve Dean for Secondary Evaluator / Next-in-Rank Calibrator
                $deanUserId = null;
                $deanFolderId = null;
                if (!empty($folder['parent_folder_id'])) {
                    $parentFolder = $folderModel->find($folder['parent_folder_id']);
                    if ($parentFolder && !empty($parentFolder['user_id'])) {
                        $deanUserId = $parentFolder['user_id'];
                        $deanFolderId = $parentFolder['id'];
                    }
                }

                foreach ($facultyList as $faculty) {
                    $existing = $folderModel->where('parent_folder_id', $folder['id'])
                                            ->where('user_id', $faculty['user_id'])
                                            ->where('deleted_at IS NULL')->first();
                    if (!$existing) {
                        $newFolderId = create_unique_row($folderModel, [
                            'title'               => $folder['title'],
                            'user_id'             => $faculty['user_id'],
                            'parent_folder_id'    => $folder['id'],
                            'cycle_folder_id'     => $cycleFolderId,
                            'status'              => FolderStatus::DRAFT_TARGET->value,
                        ]);

                        // Ensure IPCR paper exists for Faculty
                        $this->ensureUserDocumentExists($newFolderId, $faculty['user_id'], $folder['title'], 'IPCR');

                        // Immediate Evaluator: Department Chair
                        $r1 = $routingModel->where('folder_id', $newFolderId)->where('evaluator_id', $folder['user_id'])->first();
                        if (!$r1) {
                            $routingModel->insert([
                                'folder_id'           => $newFolderId,
                                'evaluator_id'        => $folder['user_id'],
                                'evaluator_folder_id' => $folder['id'],
                                'status'              => FolderStatus::DRAFT->value
                            ]);
                        }

                        // Secondary Evaluator: College Dean
                        if ($deanUserId && $deanUserId != $folder['user_id']) {
                            $r2 = $routingModel->where('folder_id', $newFolderId)->where('evaluator_id', $deanUserId)->first();
                            if (!$r2) {
                                $routingModel->insert([
                                    'folder_id'           => $newFolderId,
                                    'evaluator_id'        => $deanUserId,
                                    'evaluator_folder_id' => $deanFolderId,
                                    'status'              => FolderStatus::DRAFT->value
                                ]);
                            }
                        }

                        notify_user((int) $faculty['user_id'], [
                            'sender_id' => session()->get('user_id'),
                            'type'      => 'target_released',
                            'title'     => 'Department Targets Released',
                            'message'   => "Department targets for \"{$folder['title']}\" have been approved and released. You may now craft and submit your IPCR.",
                            'link'      => 'folders/' . $newFolderId,
                            'icon'      => 'file',
                        ]);

                        $releasedCount++;
                    }
                }
            }

            // In-app notification to the employee whose target was approved
            notify_user((int) $folder['user_id'], [
                'sender_id' => session()->get('user_id'),
                'type'      => 'target_approved',
                'title'     => $isTwgReviewer ? 'Target Commitments Verified' : 'Target Commitments Approved',
                'message'   => $isTwgReviewer 
                    ? "Your target commitments for \"{$folder['title']}\" have been verified by {$approverRole}."
                    : "Your target commitments for \"{$folder['title']}\" have been approved.",
                'link'      => 'folders/' . $folderId,
                'icon'      => 'check',
            ]);

            $logAction = $isTwgReviewer
                ? "Target commitments verified by {$approverRole}: "
                : ($releaseToDeans 
                    ? "Target commitments approved and released to Deans: " 
                    : ($releaseToChairs 
                        ? "Target commitments approved and released to Department Chairs: " 
                        : ($releaseToFaculty 
                            ? "Target commitments approved and released to Faculty: " 
                            : "Target commitments approved: ")));
            audit_log('TARGET_APPROVED', 'TARGET', 'document_folder', (int) $folderId, $logAction . $folder['title']);

            if ($isTwgReviewer) {
                $msg = "Targets verified and approved by {$approverRole} successfully.";
            } elseif ($releaseToDeans) {
                $msg = "OPCR targets approved and successfully released to {$releasedCount} College Dean(s).";
            } elseif ($releaseToChairs) {
                $msg = "Collegiate DPCR targets approved and successfully released to {$releasedCount} Department Chair(s).";
            } elseif ($releaseToFaculty) {
                $msg = "Department DPCR targets approved and successfully released to {$releasedCount} Faculty member(s).";
            } else {
                $msg = "Targets approved successfully.";
            }
            
            return $this->respond(['status' => 'success', 'message' => $msg]);
        });
    }

    public function unapproveTarget() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $folderModel = new DocumentFolderModel();
            
            $folder = $folderModel->find($folderId);
            if (!$folder) {
                return $this->respondError("Folder not found.", 404);
            }

            $dates = $folderModel->getFolderDates($folder);
            if (!empty($dates['target_date_end']) && date('Y-m-d H:i:s') > $dates['target_date_end']) {
                return $this->respondError("Cannot remove approval: The target setting window has already closed.", 400);
            }
            
            $currentUserId = (int)session()->get('user_id');
            $userRole      = session()->get('role');
            $isTwgReviewer = in_array($userRole, ['TWG', 'PMT'], true);
            $isAdmin       = ($userRole === 'Admin');
            $routingModel  = new \App\Models\EvaluationRoutingModel();
            
            if ($isTwgReviewer) {
                if ($folder['status'] !== FolderStatus::TARGET_APPROVED->value || !in_array($folder['target_approver_role'] ?? '', ['TWG', 'PMT'], true)) {
                    return $this->respondError("This folder is not currently approved by TWG.", 400);
                }

                // TWG / PMT un-verifies the folder: revert target_approver_role and status back to pending verification
                $folderModel->update($folderId, [
                    'status'               => FolderStatus::TARGET_SUBMITTED->value,
                    'target_approved_at'   => null,
                    'target_approved_by'   => null,
                    'target_approver_role' => null
                ]);

                audit_log('TWG_TARGET_UNVERIFIED', 'TARGET', 'document_folder', (int) $folderId, "Target verification revoked by {$userRole}: {$folder['title']}");

                notify_user((int) $folder['user_id'], [
                    'sender_id' => $currentUserId,
                    'type'      => 'target_unapproved',
                    'title'     => 'Target Verification Revoked',
                    'message'   => "Target verification for \"{$folder['title']}\" was revoked by {$userRole} and reverted to pending verification.",
                    'link'      => 'folders/' . $folderId,
                    'icon'      => 'alert',
                ]);

                return $this->respond(['status' => 'success', 'message' => "Target verification removed successfully. Reverted to pending verification."]);
            }
            
            // Supervisor / Admin unapproving their routing
            $myRoute = $routingModel->where('folder_id', $folderId)
                                    ->where('evaluator_id', $currentUserId)
                                    ->first();
            $hasRouteApproval = ($myRoute && in_array(strtolower(trim($myRoute['status'] ?? '')), ['approved', 'target_approved'], true));
            $isDirectApprover = ($folder['target_approved_by'] == $currentUserId);
            $isFolderApproved = ($folder['status'] === FolderStatus::TARGET_APPROVED->value);

            if (!$hasRouteApproval && !$isDirectApprover && !$isFolderApproved && !$isAdmin) {
                return $this->respondError("You do not have an active approval on this folder to remove.", 400);
            }

            // Revert this evaluator's specific route back to pending
            if ($myRoute) {
                $routingModel->where('folder_id', $folderId)
                             ->where('evaluator_id', $currentUserId)
                             ->set([
                                 'status'     => 'pending',
                                 'updated_at' => date('Y-m-d H:i:s')
                             ])
                             ->update();
            }

            // Always revert folder status to TARGET_SUBMITTED and clear target approval sign-offs
            $folderModel->update($folderId, [
                'status'               => FolderStatus::TARGET_SUBMITTED->value,
                'target_approved_at'   => null,
                'target_approved_by'   => null,
                'target_approver_role' => null
            ]);
            
            notify_user((int) $folder['user_id'], [
                'sender_id' => $currentUserId,
                'type'      => 'target_unapproved',
                'title'     => 'Target Approval Revoked',
                'message'   => "Target commitment approval for \"{$folder['title']}\" was revoked by " . (session()->get('name') ?: 'supervisor') . " and returned to pending review.",
                'link'      => 'folders/' . $folderId,
                'icon'      => 'alert',
            ]);

            audit_log('TARGET_UNAPPROVED', 'TARGET', 'document_folder', (int) $folderId, "Target commitment approval revoked by " . (session()->get('name') ?: 'supervisor') . ": {$folder['title']}");

            return $this->respond(['status' => 'success', 'message' => 'Approval removed successfully. Reverted to pending review.']);
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
            if (!$folder) return $this->respondError("Folder not found.", 400);

            if (!in_array($folder['status'], [FolderStatus::TARGET_SUBMITTED->value, FolderStatus::PENDING_TARGET_APPROVAL->value], true)) {
                return $this->respondError("Cannot return targets: The employee has already revoked or unsubmitted their targets. Please refresh the page.", 409);
            }

            $dates = $folderModel->getFolderDates($folder);
            if (!empty($dates['target_date_end']) && date('Y-m-d H:i:s') > $dates['target_date_end']) {
                return $this->respondError("The target setting period has already ended.", 400);
            }
            
            $currentUserId = (int)session()->get('user_id');
            $userRole      = session()->get('role');
            $isTwgReviewer = in_array($userRole, ['TWG', 'PMT'], true);
            $routingModel  = new \App\Models\EvaluationRoutingModel();
            $userModel     = new \App\Models\UserModel();

            if ($isTwgReviewer) {
                // TWG can only return targets if all supervising authorities have completed their review
                $allRoutes = $routingModel->where('folder_id', $folderId)->findAll();
                $totalSupervisors = 0;
                $approvedSupervisors = 0;
                foreach ($allRoutes as $r) {
                    $eId = (int)$r['evaluator_id'];
                    if ($eId === (int)$folder['user_id']) continue;
                    if ($userModel->hasRole($eId, 'Admin')) continue;
                    $totalSupervisors++;
                    $st = strtolower(trim((string)$r['status']));
                    if ($st === 'approved' || $st === FolderStatus::TARGET_APPROVED->value) {
                        $approvedSupervisors++;
                    }
                }

                if ($totalSupervisors > 0 && $approvedSupervisors < $totalSupervisors) {
                    return $this->respondError("Cannot return targets yet: All supervising authorities must approve first before TWG verification.", 403);
                }
            } else {
                // Mark this supervisor's specific route as returned
                $routingModel->where('folder_id', $folderId)
                             ->where('evaluator_id', $currentUserId)
                             ->set([
                                 'status'     => 'returned',
                                 'updated_at' => date('Y-m-d H:i:s')
                             ])
                             ->update();
            }

            $folderModel->update($folderId, [
                'status' => FolderStatus::TARGET_RETURNED->value
            ]);
            
            if (!empty($reason)) {
                $this->appendDocumentRevisionTrail($folderId, $reason, 'target');
            }

            $revMsg = !empty($reason)
                ? "Your target commitments for \"{$folder['title']}\" were returned for revision: {$reason}"
                : "Your target commitments for \"{$folder['title']}\" were returned for revision.";
            notify_user((int) $folder['user_id'], [
                'sender_id' => session()->get('user_id'),
                'type'      => 'target_returned',
                'title'     => 'Action Required: Targets Returned',
                'message'   => $revMsg,
                'link'      => 'folders/' . $folderId,
                'icon'      => 'alert',
            ]);
            
            audit_log('TARGET_RETURNED', 'TARGET', 'document_folder', (int) $folderId, "Target commitments returned for revision: " . (!empty($reason) ? $reason : 'No remarks specified'));

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
            $folderModel = new DocumentFolderModel();
            
            $folder = $folderModel->find($folderId);
            if (!$folder || !empty($folder['deleted_at'])) {
                return $this->respondError("Cannot approve: This evaluation cycle is closed/archived.", 400);
            }

            $validEvalStatuses = [
                FolderStatus::EVAL_SUBMITTED->value,
                FolderStatus::EVALUATED->value,
                FolderStatus::SUBMITTED->value,
                FolderStatus::EVAL_APPROVED->value,
                FolderStatus::APPROVED->value,
                FolderStatus::TWG_APPROVED->value,
                FolderStatus::TWG_DISAPPROVED->value
            ];
            if (!in_array($folder['status'], $validEvalStatuses, true)) {
                return $this->respondError("Cannot approve evaluation: The employee has revoked their self-rating submission. Please refresh the page.", 409);
            }

            $isAdmin = (new UserModel())->hasRole(session()->get('user_id'), 'Admin');
            $finalScore = $this->request->getPost('final_score') ?? $this->request->getPost('final_rating');

            if ($isAdmin) {
                $routingModel->where('folder_id', $folderId)
                    ->set(['status' => 'approved', 'updated_at' => date('Y-m-d H:i:s')])
                    ->update();
                $updateData = [
                    'status'   => FolderStatus::EVAL_APPROVED->value,
                    'rated_at' => date('Y-m-d H:i:s')
                ];
                if ($finalScore !== null && $finalScore !== '' && is_numeric($finalScore)) {
                    $updateData['final_rating'] = (float) $finalScore;
                }
                $folderModel->update($folderId, $updateData);
            } else {
                $routingModel->where('folder_id', $folderId)
                    ->where('evaluator_id', session()->get('user_id'))
                    ->set(['status' => 'approved', 'updated_at' => date('Y-m-d H:i:s')])
                    ->update();
                if ($finalScore !== null && $finalScore !== '' && is_numeric($finalScore)) {
                    $folderModel->update($folderId, ['final_rating' => (float) $finalScore]);
                }
                $this->updateFolderConsensus($folderId);
            }

            $userModel = new UserModel();
            $folderModel = new DocumentFolderModel();
            $folder = $folderModel->find($folderId);
            $subordinate = $userModel->find($folder['user_id']);
            $supervisor = $userModel->find(session()->get('user_id'));
            $link = site_url("folders/" . $folderId);

            if (!$userModel->hasRole($subordinate['id'], 'Admin')) {
                queue_email($subordinate['email'], 'Folder Approved: ' . $folder['title'], render_email('folder_approved', [
                    'firstName'           => $subordinate['first_name'],
                    'supervisorFirstName' => $supervisor['first_name'],
                    'supervisorLastName'  => $supervisor['last_name'],
                    'folderTitle'         => $folder['title'],
                    'link'                => $link,
                ]));
            }

            // In-app notification to the employee
            $supName = trim(($supervisor['first_name'] ?? '') . ' ' . ($supervisor['last_name'] ?? '')) ?: 'Your supervisor';
            notify_user((int) $folder['user_id'], [
                'sender_id' => session()->get('user_id'),
                'type'      => 'eval_approved',
                'title'     => 'Evaluation Approved',
                'message'   => "Your performance evaluation for \"{$folder['title']}\" has been rated and approved by {$supName}.",
                'link'      => 'folders/' . $folderId,
                'icon'      => 'check',
            ]);

            audit_log('EVALUATION_APPROVED', 'RATING', 'document_folder', (int) $folderId, "Performance evaluation rated and approved: {$folder['title']}");

            return $this->respond(['status' => 'success', 'message' => 'Approved!']);
        });
    }

    /**
     * POST /folder/unapprove - Reverts the current evaluator's approval back to pending review.
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
                    ->set(['status' => 'pending', 'updated_at' => date('Y-m-d H:i:s')])
                    ->update();
            } else {
                // Check if actually approved by this user
                $routing = $routingModel->where('folder_id', $folderId)->where('evaluator_id', session()->get('user_id'))->first();
                $rSt = strtolower(trim((string)($routing['status'] ?? '')));
                if (!$routing || ($rSt !== 'approved' && $rSt !== FolderStatus::APPROVED->value)) {
                    return $this->respondError("You have not approved this folder.", 400);
                }

                $routingModel->where('folder_id', $folderId)
                    ->where('evaluator_id', session()->get('user_id'))
                    ->set(['status' => 'pending', 'updated_at' => date('Y-m-d H:i:s')])
                    ->update();
            }

            $this->updateFolderConsensus($folderId);

            audit_log('EVALUATION_UNAPPROVED', 'RATING', 'document_folder', (int) $folderId, "Performance evaluation approval revoked: {$folder['title']}");

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

            if (!empty($folder['deleted_at']) || $folderModel->isFolderLocked($folder)) {
                return $this->respondError("This evaluation cycle has been closed/archived. Scores are frozen.", 403);
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

            audit_log('SCORE_UPDATED', 'RATING', 'document_folder', (int) $folderId, "Final rating score manually updated to {$score} for {$folder['title']}");

            return $this->respond(['status' => 'success', 'message' => 'Score updated successfully.']);
        });
    }

    /**
     * POST /folder/twg_approve - TWG marks the folder with twg status
     */
    public function twgApprove() {
        return $this->tryOrFail(function() {
            $role = session()->get('role');
            if (!in_array($role, ['TWG', 'PMT', 'Admin'])) {
                return $this->respondError('Unauthorized. Only Technical Working Group (TWG) / PMT members or Administrators can perform this action.', 403);
            }

            $folderId = $this->request->getPost('folder_id');
            $status = $this->request->getPost('status');

            if ($role === 'TWG') {
                $twgAssignmentModel = new \App\Models\TwgUnitAssignmentModel();
                $assignedUnits = $twgAssignmentModel->getAssignedUnitIds(session()->get('user_id'));
                if (!empty($assignedUnits) && !$twgAssignmentModel->isTwgAssignedToFolder(session()->get('user_id'), $folderId)) {
                    return $this->respondError('Unauthorized: This folder is outside your assigned unit jurisdiction.', 403);
                }
            }
            
            $validPostStatuses = [
                FolderStatus::EVAL_APPROVED->value,
                FolderStatus::EVAL_RETURNED->value,
                FolderStatus::TWG_APPROVED->value, 
                FolderStatus::TWG_DISAPPROVED->value, 
                'revoke'
            ];
            if (!in_array($status, $validPostStatuses, true)) {
                return $this->fail('Invalid status');
            }
            
            $folderModel = new DocumentFolderModel();
            $folder = $folderModel->find($folderId);
            if (!$folder || !empty($folder['deleted_at'])) {
                return $this->respondError("Cannot perform TWG review: This evaluation cycle is closed/archived.", 400);
            }

            $validTwgStatuses = [
                FolderStatus::EVAL_SUBMITTED->value,
                FolderStatus::EVAL_APPROVED->value,
                FolderStatus::EVAL_RETURNED->value,
                FolderStatus::EVALUATED->value,
                FolderStatus::APPROVED->value,
                FolderStatus::TWG_APPROVED->value,
                FolderStatus::TWG_DISAPPROVED->value
            ];
            if ($status !== 'revoke' && !in_array($folder['status'], $validTwgStatuses, true)) {
                return $this->respondError("Cannot perform TWG review: The employee's submission is no longer under evaluation. Please refresh the page.", 409);
            }

            if ($status === 'revoke') {
                $this->updateFolderConsensus($folderId);
                audit_log('TWG_REVOKED', 'RATING', 'document_folder', (int) $folderId, "TWG approval/disapproval revoked for {$folder['title']}");
                return $this->respond(['status' => 'success', 'message' => 'TWG decision revoked successfully!']);
            }

            $canonicalStatus = ($status === FolderStatus::TWG_DISAPPROVED->value || $status === FolderStatus::EVAL_RETURNED->value)
                ? FolderStatus::EVAL_RETURNED->value
                : FolderStatus::EVAL_APPROVED->value;

            $folderModel->update($folderId, [
                'status'     => $canonicalStatus, 
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $folder = $folderModel->find($folderId);
            $isApproved = ($canonicalStatus === FolderStatus::EVAL_APPROVED->value);
            notify_user((int) $folder['user_id'], [
                'sender_id' => session()->get('user_id'),
                'type'      => $isApproved ? 'twg_approved' : 'twg_disapproved',
                'title'     => $isApproved ? 'SPMS Rating Verified by TWG' : 'SPMS Rating Returned by TWG',
                'message'   => $isApproved 
                    ? "Official SPMS performance ratings for \"{$folder['title']}\" have been verified and finalized by the Technical Working Group."
                    : "Official SPMS performance ratings for \"{$folder['title']}\" were returned for revision by the Technical Working Group.",
                'link'      => 'folders/' . $folderId,
                'icon'      => $isApproved ? 'award' : 'alert',
            ]);

            audit_log($isApproved ? 'TWG_APPROVED' : 'TWG_DISAPPROVED', 'RATING', 'document_folder', (int) $folderId, "Official SPMS rating " . ($isApproved ? 'verified and approved' : 'returned') . " by TWG for {$folder['title']}");

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
            $folderModel  = new DocumentFolderModel();

            $folder = $folderModel->find($folderId);
            if (!$folder || !empty($folder['deleted_at'])) {
                return $this->respondError("Cannot return evaluation: This evaluation cycle is closed/archived.", 400);
            }

            $validEvalStatuses = [
                FolderStatus::EVAL_SUBMITTED->value,
                FolderStatus::EVAL_APPROVED->value,
                FolderStatus::EVALUATED->value,
                FolderStatus::SUBMITTED->value,
                FolderStatus::APPROVED->value
            ];
            if (!in_array($folder['status'], $validEvalStatuses, true)) {
                return $this->respondError("Cannot return evaluation: The employee has already revoked their self-rating submission. Please refresh the page.", 409);
            }

            $routingModel->where('folder_id', $folderId)
                ->where('evaluator_id', session()->get('user_id'))
                ->set(['status' => 'returned', 'updated_at' => date('Y-m-d H:i:s')])
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

            // In-app notification to the subordinate
            $supName = trim(($supervisor['first_name'] ?? '') . ' ' . ($supervisor['last_name'] ?? '')) ?: 'Your supervisor';
            $revMsg = !empty($reason) 
                ? "Your evaluation for \"{$folder['title']}\" was returned for revision by {$supName}: {$reason}"
                : "Your evaluation for \"{$folder['title']}\" was returned for revision by {$supName}.";
            notify_user((int) $folder['user_id'], [
                'sender_id' => session()->get('user_id'),
                'type'      => 'eval_returned',
                'title'     => 'Action Required: Evaluation Returned',
                'message'   => $revMsg,
                'link'      => 'folders/' . $folderId,
                'icon'      => 'alert',
            ]);

            audit_log('EVALUATION_RETURNED', 'RATING', 'document_folder', (int) $folderId, "Evaluation returned for revision: " . (!empty($reason) ? $reason : 'No remarks specified'));

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

        $doc = $documentModel->where('document_folder_id', $folderId)->first();

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

    /**
     * Helper to send an in-app notification to evaluators/supervisors of a folder.
     */
    private function notifyFolderEvaluators($folder, $title, $message, $type, $icon = 'bell') {
        if (!empty($folder['user_id'])) {
            $this->ensureAdminRoutingForExecutiveFolder($folder['id'], (int) $folder['user_id']);
        }
        $routingModel = new EvaluationRoutingModel();
        $routings = $routingModel->where('folder_id', $folder['id'])->findAll();
        $senderId = session()->get('user_id');

        $targetLink = in_array($type, ['target_unsubmitted', 'eval_unsubmitted']) ? 'ratings' : 'ratings/show/' . $folder['id'];

        $notifiedIds = [];
        foreach ($routings as $r) {
            $evalId = (int) $r['evaluator_id'];
            if (!in_array($evalId, $notifiedIds)) {
                notify_user($evalId, [
                    'sender_id' => $senderId,
                    'type'      => $type,
                    'title'     => $title,
                    'message'   => $message,
                    'link'      => $targetLink,
                    'icon'      => $icon,
                ]);
                $notifiedIds[] = $evalId;
            }
        }

        // If parent folder exists and owner is not yet notified
        if (!empty($folder['parent_folder_id'])) {
            $folderModel = new DocumentFolderModel();
            $parentFolder = $folderModel->find($folder['parent_folder_id']);
            if ($parentFolder && !empty($parentFolder['user_id'])) {
                $pUserId = (int) $parentFolder['user_id'];
                if (!in_array($pUserId, $notifiedIds)) {
                    notify_user($pUserId, [
                        'sender_id' => $senderId,
                        'type'      => $type,
                        'title'     => $title,
                        'message'   => $message,
                        'link'      => $targetLink,
                        'icon'      => $icon,
                    ]);
                    $notifiedIds[] = $pUserId;
                }
            }
        }

        // If no evaluators or parents found, notify primary Admin only (avoiding multi-admin inbox spam)
        if (empty($notifiedIds) && session()->get('role') !== 'Admin') {
            $userRoleModel = new \App\Models\UserRoleModel();
            $roleModel = new \App\Models\RoleModel();
            $adminRole = $roleModel->where('name', 'Admin')->first();
            if ($adminRole) {
                $primaryAdmin = $userRoleModel->where('role_id', $adminRole['id'])->first();
                if ($primaryAdmin) {
                    $adminId = (int) $primaryAdmin['user_id'];
                    if (!in_array($adminId, $notifiedIds) && $adminId !== $senderId) {
                        notify_user($adminId, [
                            'sender_id' => $senderId,
                            'type'      => $type,
                            'title'     => $title,
                            'message'   => $message,
                            'link'      => $targetLink,
                            'icon'      => $icon,
                        ]);
                        $notifiedIds[] = $adminId;
                    }
                }
            }
        }
    }

    /**
     * Helper to detect whether a folder represents an OPCR (Office Performance Commitment and Review).
     * Under CSC SPMS guidelines, OPCR is the apex institutional commitment paper that requires no superior approval.
     */
    private function isOpcrFolder(array $folder, ?int $userId = null): bool
    {
        if (str_contains(strtoupper($folder['title'] ?? ''), 'OPCR')) {
            return true;
        }

        $ownerId = $userId ?? $folder['user_id'] ?? null;
        if ($ownerId) {
            $userModel = new UserModel();
            $owner = $userModel->find($ownerId);
            if ($owner && strtoupper($owner['doc_type'] ?? '') === 'OPCR') {
                return true;
            }
            $plantilla = $userModel->getActivePlantillaDetails($ownerId);
            $pos = strtolower($plantilla['position'] ?? '');
            if (str_contains($pos, 'vice president') || str_contains($pos, 'president') || str_contains($pos, 'vpaa')) {
                return true;
            }
        }

        if (!empty($folder['id'])) {
            $doc = (new \App\Models\DocumentModel())->where('document_folder_id', $folder['id'])->first();
            if ($doc) {
                $docTitleUpper = strtoupper($doc['title'] ?? '');
                if (str_contains($docTitleUpper, 'OPCR') || str_contains($docTitleUpper, 'OFFICE PERFORMANCE') || (strtoupper($doc['doc_type'] ?? '') === 'OPCR')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Helper to ensure that Vice Presidential / executive OPCR folders are routed to the Admin evaluator.
     */
    private function ensureAdminRoutingForExecutiveFolder(int|string $folderId, int $folderUserId): void
    {
        $folderModel = new DocumentFolderModel();
        $folder = $folderModel->find($folderId);
        if (!$folder) return;

        $userModel = new UserModel();
        $routingModel = new EvaluationRoutingModel();

        // ONLY executive OPCR folders (Vice Presidents / CAO / OPCR) report directly to the President/Administrator
        $owner = $userModel->find($folderUserId);
        if (!$owner) return;

        $plantilla = $userModel->getActivePlantillaDetails($folderUserId);
        $posTitle = strtolower($plantilla['position'] ?? '');
        $docType = strtolower($owner['doc_type'] ?? '');
        $isExecutive = ($docType === 'opcr') 
                    || str_contains($posTitle, 'vice president') 
                    || str_contains($posTitle, 'chief administrative officer')
                    || str_contains(strtolower($owner['email'] ?? ''), 'vp')
                    || str_contains(strtolower($owner['email'] ?? ''), 'cao');

        // If this folder is not an executive folder, ensure no Admin routing is inadvertently attached
        if (!$isExecutive) {
            $adminRoleRows = $userModel->db->table('user_roles ur')
                ->select('ur.user_id')
                ->join('roles r', 'r.id = ur.role_id')
                ->where('r.name', 'Admin')
                ->get()->getResultArray();
            $adminIds = array_column($adminRoleRows, 'user_id');
            if (!empty($adminIds)) {
                $routingModel->where('folder_id', $folderId)
                             ->whereIn('evaluator_id', $adminIds)
                             ->delete();
            }
            return;
        }

        // Identify the Admin evaluator (parent folder owner or active Admin)
        $adminUserId = null;
        if (!empty($folder['parent_folder_id'])) {
            $parentFolder = $folderModel->find($folder['parent_folder_id']);
            if ($parentFolder && !empty($parentFolder['user_id'])) {
                $pUser = $userModel->find($parentFolder['user_id']);
                if ($pUser && $userModel->hasRole($pUser['id'], 'Admin')) {
                    $adminUserId = (int)$pUser['id'];
                }
            }
        }

        if (!$adminUserId) {
            $adminUser = $userModel->select('users.id')
                ->join('user_roles ur', 'ur.user_id = users.id')
                ->join('roles r', 'r.id = ur.role_id')
                ->where('r.name', 'Admin')
                ->where('users.is_active', 1)
                ->first();
            if ($adminUser) {
                $adminUserId = (int)$adminUser['id'];
            }
        }

        if ($adminUserId && $adminUserId !== $folderUserId) {
            // Remove any stale routing to a separate president account if distinct from admin
            $presidentUser = $userModel->where('email', 'president@test.com')->first();
            if ($presidentUser && (int)$presidentUser['id'] !== $adminUserId) {
                $routingModel->where('folder_id', $folderId)
                             ->where('evaluator_id', (int)$presidentUser['id'])
                             ->delete();
            }

            $exists = $routingModel->where('folder_id', $folderId)
                                  ->where('evaluator_id', $adminUserId)
                                  ->first();
            if (!$exists) {
                $evalFolderId = $folder['parent_folder_id'] ?? $folderId;
                $routingModel->insert([
                    'folder_id'           => $folderId,
                    'evaluator_id'        => $adminUserId,
                    'evaluator_folder_id' => $evalFolderId,
                    'status'              => FolderStatus::DRAFT->value,
                    'created_at'          => date('Y-m-d H:i:s'),
                    'updated_at'          => date('Y-m-d H:i:s')
                ]);
            }
        }
    }
}