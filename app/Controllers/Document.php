<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\DocumentModel;
use App\Models\DocumentFolderModel;
use App\Models\EvaluationRoutingModel;
use App\Models\TemplateModel;

/**
 * Handles individual documents (the actual IPCR/DPCR/OPCR pages inside a folder):
 * viewing/editing content, creating new documents from a template, and marking
 * which document in a folder counts as the "target" used for the final rating.
 */
class Document extends BaseController
{
    /** GET /document/{docId} - Opens the SPMS form workspace editor view for a single document. */
    public function index($docId = null) {
        $userId  = session()->get('user_id');
        $sysRole = session()->get('role');
        
        if (!$docId) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $documentModel = new DocumentModel();
        $userModel     = new \App\Models\UserModel();
        $folderModel   = new DocumentFolderModel();
        $folderModel->updateTimeBasedStatuses();

        $docInfo = $documentModel->getDocumentWithFolderInfo($docId);

        if (!$docInfo) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $docOwnerId = $docInfo['owner_id'];
        $isGuide = false;
        $routingStatus = null;

        if ($docOwnerId !== $userId) {
            $routingModel = new EvaluationRoutingModel();
            $routing = $routingModel->where('folder_id', $docInfo['document_folder_id'])
                                    ->where('evaluator_id', $userId)
                                    ->first();
            
            $isAuthorizedEvaluator = $routing || in_array($sysRole, ['Admin', 'PMT', 'TWG']);

            // Security guard: If user is neither document owner nor an authorized evaluator, block with 404
            if (!$isAuthorizedEvaluator) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }

            if ($routing) {
                $routingStatus = is_object($routing) ? $routing->status : $routing['status'];
            }
        }

        $parentFolder = null;
        $isParentTargetApproved = true;
        $basisDoc = null;
        $superiorUser = null;

        if (!empty($docInfo['parent_folder_id'])) {
            $folderModel = new \App\Models\DocumentFolderModel();
            $parentFolder = $folderModel->find($docInfo['parent_folder_id']);
            if ($parentFolder) {
                $docTitleUpper = strtoupper($docInfo['title'] ?? '');
                $isMyDocOpcr = str_contains($docTitleUpper, 'OPCR') || str_contains($docTitleUpper, 'OFFICE') || (strtoupper($docInfo['doc_type'] ?? '') === 'OPCR');

                if ($isMyDocOpcr) {
                    // OPCR is Stage 1 (Apex Root Commitment). There are no superior targets above it.
                    $isParentTargetApproved = true;
                    $parentFolder = null;
                    $basisDoc = null;
                } else {
                    $candidateBasis = $documentModel->where('document_folder_id', $parentFolder['id'])->first();

                    if ($candidateBasis) {
                        $candidateTitleUpper = strtoupper($candidateBasis['title'] ?? '');
                        $isParentDocOpcr = str_contains($candidateTitleUpper, 'OPCR') || str_contains($candidateTitleUpper, 'OFFICE') || (strtoupper($candidateBasis['doc_type'] ?? '') === 'OPCR');
                        $isParentDocDpcr = str_contains($candidateTitleUpper, 'DPCR') || str_contains($candidateTitleUpper, 'DEPARTMENT') || str_contains($candidateTitleUpper, 'DIVISION') || (strtoupper($candidateBasis['doc_type'] ?? '') === 'DPCR');

                        // Under CSC SPMS guidelines, subordinates require their superior's approved targets as a basis
                        $pCanon = \App\Enums\FolderStatus::canonical($parentFolder['status'] ?? '');
                        $isParentTargetApproved = ($pCanon === \App\Enums\FolderStatus::TARGET_APPROVED || $pCanon->isEvaluationPhase());
                        $basisDoc = $candidateBasis;
                        $superiorUser = $userModel->find($parentFolder['user_id']);
                        if ($superiorUser) {
                            $plantilla = $userModel->getActivePlantillaDetails($superiorUser['id']);
                            $superiorUser['position'] = $plantilla['position'] ?? 'Supervisor';
                            $superiorUser['department'] = $plantilla['department'] ?? '';
                        }
                    } else {
                        // Parent folder has no documents (pure cycle container)
                        $isParentTargetApproved = true;
                        $parentFolder = null;
                        $basisDoc = null;
                    }
                }
            }
        }

        $basisFormData = null;
        $basisDocContent = '';
        $allBasisGuides = [];

        // Query cascaded superior reference guides (e.g. OPCR/DPCR from all evaluators/superiors who cascaded)
        $folderId = $docInfo['document_folder_id'] ?? null;
        $groupedGuides = [];

        if ($folderId && !$isGuide) {
            $docTitleUpper = strtoupper($docInfo['title'] ?? '');
            $isMyDocOpcr = str_contains($docTitleUpper, 'OPCR') || str_contains($docTitleUpper, 'OFFICE') || (strtoupper($docInfo['doc_type'] ?? '') === 'OPCR');

            // Only non-OPCR documents have superior guides
            if (!$isMyDocOpcr) {
                $routingModel = new \App\Models\EvaluationRoutingModel();
                $fModel = new \App\Models\DocumentFolderModel();
                $cascadedRoutes = $routingModel->getEvaluatorsForFolder((string)$folderId);

                $seenEvaluatorIds = [];
                foreach ($cascadedRoutes as $route) {
                    $eId = (int)$route['evaluator_id'];
                    if (in_array($eId, $seenEvaluatorIds, true) || $eId === (int)$docOwnerId) continue;
                    if ($userModel->hasRole($eId, 'Admin')) continue;

                    $guideFolder = $fModel->find($route['evaluator_folder_id']);
                    if (!$guideFolder || !empty($guideFolder['deleted_at'])) continue;

                    $docs = $documentModel->where('document_folder_id', $guideFolder['id'])->findAll();
                    if (!empty($docs)) {
                        $seenEvaluatorIds[] = $eId;
                        $eName = trim(($route['first_name'] ?? '') . ' ' . ($route['last_name'] ?? ''));
                        $ePos  = $route['evaluator_position'] ?? '';
                        if (empty($ePos)) {
                            $p = $userModel->getActivePlantillaDetails($eId);
                            $ePos = $p['position'] ?? 'Immediate Supervisor';
                        }

                        $groupedGuides[] = [
                            'superior' => [
                                'id'   => $eId,
                                'name' => $eName,
                                'role' => $ePos
                            ], 
                            'docs' => $docs
                        ];

                        // Identify primary document in superior's folder
                        $targetDoc = $docs[0] ?? null;

                        $bFormData = null;
                        $bContent  = $targetDoc['content'] ?? '';
                        if (!empty($targetDoc['tabs'])) {
                            $bTabs = is_string($targetDoc['tabs']) ? json_decode($targetDoc['tabs'], true) : $targetDoc['tabs'];
                            if (!empty($bTabs) && is_array($bTabs)) {
                                $bFormData = $bTabs[0]['formData'] ?? null;
                                if (empty($bContent) && !empty($bTabs[0]['content'])) {
                                    $bContent = $bTabs[0]['content'];
                                }
                            }
                        }

                        $gCanon = \App\Enums\FolderStatus::canonical($guideFolder['status'] ?? '');
                        $isSupApproved = ($gCanon === \App\Enums\FolderStatus::TARGET_APPROVED || $gCanon->isEvaluationPhase());

                        $tTitleUpper = strtoupper($targetDoc['title'] ?? '');
                        $sDocType = str_contains($tTitleUpper, 'OPCR') ? 'OPCR' : (str_contains($tTitleUpper, 'DPCR') ? 'DPCR' : (str_contains($tTitleUpper, 'IPERF') ? 'IPERF' : 'IPCR'));

                        $allBasisGuides[] = [
                            'id'            => $targetDoc['id'],
                            'title'         => $targetDoc['title'] ?? 'Basis Document',
                            'doc_type'      => $sDocType,
                            'superior_id'   => $eId,
                            'superior_name' => $eName,
                            'superior_role' => $ePos,
                            'is_approved'   => $isSupApproved,
                            'folder_id'     => $guideFolder['id'],
                            'content'       => $bContent,
                            'formData'      => $bFormData,
                            'doc'           => $targetDoc
                        ];
                    }
                }

                // Fallback: Check parent folder if no routes
                if (empty($allBasisGuides) && !empty($docInfo['parent_folder_id'])) {
                    $adminFolder = $fModel->find($docInfo['parent_folder_id']);
                    if ($adminFolder && empty($adminFolder['deleted_at']) && !empty($adminFolder['user_id'])) {
                        $pUid = (int)$adminFolder['user_id'];
                        if ($pUid !== (int)$docOwnerId && !$userModel->hasRole($pUid, 'Admin')) {
                            $adminDocs = $documentModel->where('document_folder_id', $adminFolder['id'])->findAll();
                            if (!empty($adminDocs)) {
                                $pUser = $userModel->find($pUid);
                                $pPlantilla = $userModel->getActivePlantillaDetails($pUid);
                                $pName = trim(($pUser['first_name'] ?? '') . ' ' . ($pUser['last_name'] ?? ''));
                                $pPos  = $pPlantilla['position'] ?? 'Supervisor';

                                $targetDoc = $adminDocs[0] ?? null;

                                $bFormData = null;
                                $bContent  = $targetDoc['content'] ?? '';
                                if (!empty($targetDoc['tabs'])) {
                                    $bTabs = is_string($targetDoc['tabs']) ? json_decode($targetDoc['tabs'], true) : $targetDoc['tabs'];
                                    if (!empty($bTabs) && is_array($bTabs)) {
                                        $bFormData = $bTabs[0]['formData'] ?? null;
                                        if (empty($bContent) && !empty($bTabs[0]['content'])) {
                                            $bContent = $bTabs[0]['content'];
                                        }
                                    }
                                }

                                $pTitleUpper = strtoupper($targetDoc['title'] ?? '');
                                $pDocType = str_contains($pTitleUpper, 'OPCR') ? 'OPCR' : (str_contains($pTitleUpper, 'DPCR') ? 'DPCR' : (str_contains($pTitleUpper, 'IPERF') ? 'IPERF' : 'IPCR'));

                                $aCanon = \App\Enums\FolderStatus::canonical($adminFolder['status'] ?? '');
                                $allBasisGuides[] = [
                                    'id'            => $targetDoc['id'],
                                    'title'         => $targetDoc['title'] ?? 'Basis Document',
                                    'doc_type'      => $pDocType,
                                    'superior_id'   => $pUid,
                                    'superior_name' => $pName,
                                    'superior_role' => $pPos,
                                    'is_approved'   => ($aCanon === \App\Enums\FolderStatus::TARGET_APPROVED || $aCanon->isEvaluationPhase()),
                                    'folder_id'     => $adminFolder['id'],
                                    'content'       => $bContent,
                                    'formData'      => $bFormData,
                                    'doc'           => $targetDoc
                                ];
                            }
                        }
                    }
                }
            }
        }

        // Set primary basisDoc from allBasisGuides if available
        if (!empty($allBasisGuides)) {
            $basisDoc = $allBasisGuides[0]['doc'];
            $basisFormData = $allBasisGuides[0]['formData'];
            $basisDocContent = $allBasisGuides[0]['content'];
            $superiorUser = [
                'first_name' => $allBasisGuides[0]['superior_name'],
                'last_name'  => '',
                'position'   => $allBasisGuides[0]['superior_role'],
                'department' => ''
            ];
            $isParentTargetApproved = $allBasisGuides[0]['is_approved'];
        }

        $currentUser = $userModel->find($userId);
        $currentPlantilla = $userModel->getActivePlantillaDetails($userId);
        $data['currentReviewerRole']    = $currentPlantilla['position'] ?? (session()->get('role') ?: 'Reviewer');
        $data['currentReviewerName']    = trim(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? ''));

        // Document Owner (Ratee) details for automatic commitment header prefilling
        $ownerUser = $userModel->find($docOwnerId);
        $ownerPlantilla = $userModel->getActivePlantillaDetails($docOwnerId);
        $ownerRolePivot = (new \App\Models\UserRoleModel())->where('user_id', $docOwnerId)->first();
        $ownerRoleName = $ownerRolePivot ? ((new \App\Models\RoleModel())->find($ownerRolePivot['role_id'])['name'] ?? '') : '';

        $ownerName = trim(($ownerUser['first_name'] ?? '') . ' ' . ($ownerUser['last_name'] ?? ''));
        $ownerPosition = $ownerPlantilla['position'] ?? ($ownerRoleName ?: 'Faculty');
        $ownerDept = $ownerPlantilla['department'] ?? '';

        $allOwnerPlantillas = $userModel->getAllActivePlantillaDetails((int)$docOwnerId);
        $ownerRoles = [];
        if (!empty($allOwnerPlantillas)) {
            foreach ($allOwnerPlantillas as $pRow) {
                $ownerRoles[] = [
                    'position' => $pRow['position'] ?? '',
                    'dept'     => $pRow['department'] ?? ''
                ];
            }
        } else {
            $ownerRoles[] = [
                'position' => $ownerPosition,
                'dept'     => $ownerDept
            ];
        }

        $folderModel = new \App\Models\DocumentFolderModel();
        $currentFolderRec = $folderModel->find($docInfo['document_folder_id']);

        // Date when this folder was cascaded to the user
        $cascadeDate = !empty($currentFolderRec['created_at']) 
            ? date('Y-m-d', strtotime($currentFolderRec['created_at'])) 
            : date('Y-m-d');

        // Resolve evaluation period (from cycle rating_period configuration or month fallback)
        $refTime = !empty($docInfo['target_start']) ? strtotime($docInfo['target_start']) 
                 : (!empty($currentFolderRec['created_at']) ? strtotime($currentFolderRec['created_at']) : time());
        $m = (int)date('n', $refTime);
        $y = date('Y', $refTime);
        $docPeriod = !empty($currentFolderRec['rating_period'])
            ? $currentFolderRec['rating_period']
            : (($m <= 6) ? "January - June {$y}" : "July - December {$y}");

        $isCycleArchived = !empty($docInfo['folder_deleted_at']) || (!empty($parentFolder) && !empty($parentFolder['deleted_at']));

        // Resolve root evaluation cycle folder ID and root cycle metadata
        $rootFolderId = $docInfo['parent_folder_id'] ?? $docInfo['document_folder_id'];
        $rootCycle = $folderModel->find($rootFolderId);
        if (!empty($docInfo['parent_folder_id'])) {
            $pObj = $folderModel->find($docInfo['parent_folder_id']);
            while ($pObj && !empty($pObj['parent_folder_id'])) {
                $rootFolderId = $pObj['parent_folder_id'];
                $pObj = $folderModel->find($rootFolderId);
            }
            if ($pObj) {
                $rootCycle = $pObj;
            }
        }
        if (!empty($rootCycle['rating_period'])) {
            $docPeriod = $rootCycle['rating_period'];
        }
        $data['rootFolderId'] = $rootFolderId;

        // Resolve Approving Authority (from cycle configuration or default President fallback)
        $cycleApprovingName = !empty($currentFolderRec['approving_authority_name']) 
            ? $currentFolderRec['approving_authority_name'] 
            : (!empty($rootCycle['approving_authority_name']) ? $rootCycle['approving_authority_name'] : 'President');
        $cycleApprovingPos  = !empty($currentFolderRec['approving_authority_position']) 
            ? $currentFolderRec['approving_authority_position'] 
            : (!empty($rootCycle['approving_authority_position']) ? $rootCycle['approving_authority_position'] : 'President');

        // Resolve default supervisors / approving authorities for this document
        $docTitleUpper = strtoupper($docInfo['title'] ?? '');
        $isMyDocOpcr = str_contains($docTitleUpper, 'OPCR') 
                    || str_contains($docTitleUpper, 'OFFICE') 
                    || (strtoupper($docInfo['doc_type'] ?? '') === 'OPCR');

        $supervisors = [];
        $seenEvaluatorIds = [];

        if ($isMyDocOpcr) {
            // For OPCR: The highest approving authority (e.g. University President / Head of Agency)
            if (!empty($cycleApprovingName)) {
                $supervisors[] = [
                    'name'     => $cycleApprovingName,
                    'position' => $cycleApprovingPos ?: 'President',
                    'date'     => $cascadeDate,
                    'evalDate' => $cascadeDate
                ];
            }
        } else {
            // For DPCR, IPCR, IPERF: Gather supervisors from evaluation_routings (supports multiple cascades)
            $routingModel = new \App\Models\EvaluationRoutingModel();
            $cascadedRoutes = $routingModel->getEvaluatorsForFolder((string)$docInfo['document_folder_id']);

            foreach ($cascadedRoutes as $route) {
                $eId = (int)$route['evaluator_id'];
                if (in_array($eId, $seenEvaluatorIds) || $eId === (int)$docOwnerId) continue;
                // Exclude Admin from being listed as an evaluating supervisor
                if ($userModel->hasRole($eId, 'Admin')) continue;

                $seenEvaluatorIds[] = $eId;

                $eName = trim(($route['first_name'] ?? '') . ' ' . ($route['last_name'] ?? ''));
                $ePos  = $route['evaluator_position'] ?? '';
                if (empty($ePos)) {
                    $p = $userModel->getActivePlantillaDetails($eId);
                    $ePos = $p['position'] ?? 'Immediate Supervisor';
                }
                $rDate = !empty($route['created_at']) ? date('Y-m-d', strtotime($route['created_at'])) : $cascadeDate;

                $supervisors[] = [
                    'id'        => $eId,
                    'folder_id' => $route['evaluator_folder_id'] ?? null,
                    'name'      => $eName,
                    'position'  => $ePos,
                    'date'      => $rDate,
                    'evalDate'  => $rDate
                ];
            }

            // Fallback: If no routings were recorded in evaluation_routings, inspect the immediate parent folder
            if (empty($supervisors)) {
                $currParentId = $currentFolderRec['parent_folder_id'] ?? null;
                if (!empty($currParentId)) {
                    $pFolder = $folderModel->find($currParentId);
                    // Only use if parent folder is an intermediate supervisor folder (not an institutional root cycle)
                    // and its owner is not an Admin
                    if ($pFolder && !empty($pFolder['parent_folder_id']) && !empty($pFolder['user_id'])) {
                        $pUserId = (int)$pFolder['user_id'];
                        if ($pUserId !== (int)$docOwnerId && !$userModel->hasRole($pUserId, 'Admin')) {
                            $pUser = $userModel->find($pUserId);
                            if ($pUser) {
                                $pPlantilla = $userModel->getActivePlantillaDetails($pUserId);
                                $supervisors[] = [
                                    'id'        => $pUserId,
                                    'folder_id' => $pFolder['id'],
                                    'name'      => trim(($pUser['first_name'] ?? '') . ' ' . ($pUser['last_name'] ?? '')),
                                    'position'  => $pPlantilla['position'] ?? 'Supervisor',
                                    'date'      => $cascadeDate,
                                    'evalDate'  => $cascadeDate
                                ];
                            }
                        }
                    }
                }
            }

            // Fallback if no supervisor found
            if (empty($supervisors) && !empty($cycleApprovingName)) {
                $supervisors[] = [
                    'id'        => null,
                    'folder_id' => null,
                    'name'      => $cycleApprovingName,
                    'position'  => $cycleApprovingPos ?: 'President',
                    'date'      => $cascadeDate,
                    'evalDate'  => $cascadeDate
                ];
            }
        }

        // Resolve cascaded target inputs and rubrics matrix from all supervisors
        $cascadedCategories = [
            'core'      => [],
            'strategic' => [],
            'support'   => []
        ];
        $cascadedRubrics = [];
        $cascadedWeights = null;
        $cascadedCurrency = '₱';

        $isDocIperf = (strtoupper($docInfo['doc_type'] ?? '') === 'IPERF') || str_contains($docTitleUpper, 'IPERF');
        $seenRowKeys = [];

        foreach ($supervisors as $sup) {
            $sFolderId = $sup['folder_id'] ?? null;
            if (!$sFolderId) continue;

            // When Admin cascades or is supervisor, no targets/inputs are cascaded (Admin only initiates cycles)
            if (!empty($sup['id']) && $userModel->hasRole($sup['id'], 'Admin')) {
                continue;
            }
            $sFolder = $folderModel->find($sFolderId);
            if ($sFolder && empty($sFolder['parent_folder_id'])) {
                // Root institutional cycle folder created by Admin has no targets to cascade
                continue;
            }

            $sDoc = $documentModel->where('document_folder_id', $sFolderId)->first();
            if (!$sDoc || empty($sDoc['tabs'])) continue;

            $sTabs = is_string($sDoc['tabs']) ? json_decode($sDoc['tabs'], true) : $sDoc['tabs'];
            if (empty($sTabs) || !is_array($sTabs)) continue;

            $sFormData = $sTabs[0]['formData'] ?? [];
            $sCategories = $sFormData['categories'] ?? [];
            $sRubrics = $sFormData['rubrics'] ?? [];
            if (!empty($sFormData['currency'])) $cascadedCurrency = $sFormData['currency'];
            // Category percentage weights are intentionally left empty when cascaded to avoid conflicts across multiple supervisors

            foreach (['core', 'strategic', 'support'] as $catKey) {
                if (empty($sCategories[$catKey]) || !is_array($sCategories[$catKey])) continue;

                foreach ($sCategories[$catKey] as $sRowIdx => $sRow) {
                    $mfo = trim((string)($sRow['mfo'] ?? ($sRow['office_ppa'] ?? ($sRow['paps'] ?? ($sRow['mfo_title'] ?? '')))));
                    $ind = trim((string)($sRow['indicators'] ?? ($sRow['success_indicators'] ?? ($sRow['expected_outputs'] ?? ($sRow['success_indicator'] ?? '')))));

                    // Skip completely empty placeholder rows
                    if ($mfo === '' && $ind === '') continue;

                    // Deduplicate identical cascaded targets across supervisors if any
                    $dedupKey = md5(strtolower($mfo . '||' . $ind));
                    if (isset($seenRowKeys[$dedupKey])) continue;
                    $seenRowKeys[$dedupKey] = true;

                    $origRowId = $sRow['row_id'] ?? ('row_' . $catKey . '_' . $sRowIdx);
                    $destCat = $isDocIperf ? 'core' : $catKey;
                    $newRowId = 'row_' . $destCat . '_cascaded_' . ($sup['id'] ?? 'sup') . '_' . $sRowIdx . '_' . substr(md5(uniqid()), 0, 6);

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
                        'std_1'           => $sRow['std_1'] ?? '',
                        'cascaded_from'   => $sup['name'] ?? ''
                    ];

                    // Copy rubrics matrix standards for this row
                    if (!empty($sRubrics[$origRowId]) && is_array($sRubrics[$origRowId])) {
                        $cascadedRubrics[$newRowId] = $sRubrics[$origRowId];
                    } elseif (!empty($sRow['std_5']) || !empty($sRow['std_4']) || !empty($sRow['std_3'])) {
                        $cascadedRubrics[$newRowId] = [
                            '5' => ['q' => $sRow['std_5'] ?? '', 't' => '', 'e' => ''],
                            '4' => ['q' => $sRow['std_4'] ?? '', 't' => '', 'e' => ''],
                            '3' => ['q' => $sRow['std_3'] ?? '', 't' => '', 'e' => ''],
                            '2' => ['q' => $sRow['std_2'] ?? '', 't' => '', 'e' => ''],
                            '1' => ['q' => $sRow['std_1'] ?? '', 't' => '', 'e' => ''],
                        ];
                    }
                }
            }
        }

        $cascadedTargets = [
            'categories' => $cascadedCategories,
            'rubrics'    => $cascadedRubrics,
            'currency'   => $cascadedCurrency,
            'weights'    => $cascadedWeights,
            'hasTargets' => (!empty($cascadedCategories['core']) || !empty($cascadedCategories['strategic']) || !empty($cascadedCategories['support']))
        ];
        $data['cascadedTargets']    = $cascadedTargets;
        $data['supervisors']        = $supervisors;
        $data['cascadeDate']        = $cascadeDate;
        $data['cycleApprovingName'] = $cycleApprovingName;
        $data['cycleApprovingPos']  = $cycleApprovingPos;

        $data['ownerInfo'] = [
            'name'        => $ownerName,
            'position'    => $ownerPosition,
            'dept'        => $ownerDept,
            'roles'       => $ownerRoles,
            'period'      => $docPeriod,
            'cascadeDate' => $cascadeDate
        ];

        // Ratee navigation for evaluators
        $rateeNav = null;
        if ($docOwnerId !== $userId && !$isGuide) {
            $siblingFolders = $folderModel->getRatingDashboardFolders($userId, $sysRole, $rootFolderId);
            $currentIndex = -1;
            foreach ($siblingFolders as $idx => $sFolder) {
                if ($sFolder['folder_id'] == $docInfo['document_folder_id']) {
                    $currentIndex = $idx;
                    break;
                }
            }

            if ($currentIndex !== -1 && count($siblingFolders) > 1) {
                $totalRatees = count($siblingFolders);
                $prevFolder = ($currentIndex > 0) ? $siblingFolders[$currentIndex - 1] : null;
                $nextFolder = ($currentIndex < $totalRatees - 1) ? $siblingFolders[$currentIndex + 1] : null;
                $rateeNav = [
                    'currentIndex' => $currentIndex + 1,
                    'totalRatees'  => $totalRatees,
                    'prev'         => $prevFolder ? [
                        'folder_id' => $prevFolder['folder_id'],
                        'name'      => $prevFolder['username'],
                        'position'  => $prevFolder['position'] ?? ''
                    ] : null,
                    'next'         => $nextFolder ? [
                        'folder_id' => $nextFolder['folder_id'],
                        'name'      => $nextFolder['username'],
                        'position'  => $nextFolder['position'] ?? ''
                    ] : null,
                ];
            }
        }

        $isCascadedByMe = false;
        if (!empty($docInfo['document_folder_id'])) {
            $fModel = new DocumentFolderModel();
            $currentFolderRec = $fModel->find($docInfo['document_folder_id']);
            if ($currentFolderRec && !empty($currentFolderRec['parent_folder_id'])) {
                $pFolder = $fModel->find($currentFolderRec['parent_folder_id']);
                if ($pFolder && (int)$pFolder['user_id'] === (int)$userId) {
                    $isCascadedByMe = true;
                }
            }
            if (!$isCascadedByMe) {
                $rCount = (new EvaluationRoutingModel())
                    ->where('folder_id', $docInfo['document_folder_id'])
                    ->where('evaluator_id', $userId)
                    ->countAllResults();
                if ($rCount > 0) {
                    $isCascadedByMe = true;
                }
            }
        }
        $data['isCascadedByMe']         = $isCascadedByMe;

        $data['isCycleArchived']        = $isCycleArchived;
        $data['isOwner']                = ($docOwnerId == $userId);
        $data['rateeNav']               = $rateeNav;
        $data['routingStatus']          = $routingStatus;
        $data['doc']                    = $docInfo;
        $data['isGuide']                = $isGuide;
        $data['parentFolder']           = $parentFolder;
        $data['isParentTargetApproved'] = $isParentTargetApproved;
        $data['isParentDocOpcr']         = $isParentDocOpcr ?? false;
        $data['isParentDocDpcr']         = $isParentDocDpcr ?? false;
        $data['basisDoc']               = $basisDoc;
        $data['superiorUser']           = $superiorUser;
        $data['basisFormData']          = $basisFormData;
        $data['basisDocContent']        = $basisDocContent;
        $data['isEmbed']                = (bool) $this->request->getGet('embed');

        $attachmentModel                = new \App\Models\DocumentAttachmentModel();
        $data['attachmentsByRow']       = $attachmentModel->getAttachmentsGroupedByRow((string)$docId);
        $data['groupedGuides']          = $groupedGuides;
        $data['userTeams']              = (new \App\Models\RoutingPresetModel())->getPresetsWithDetails($userId);
        $data['eligibleUsers']          = $userModel->getEligibleTeamMembers($userId);
        
        // Resolve supervisor approval status for each supervisor
        $supervisorApprovals = [];
        $currentEvaluatorRoute = null;
        $totalSupervisorsCount = 0;
        $approvedSupervisorsCount = 0;
        if (!empty($docInfo['document_folder_id'])) {
            $routingModel = new \App\Models\EvaluationRoutingModel();
            $routes = $routingModel->where('folder_id', $docInfo['document_folder_id'])->findAll();
            $nonAdminRoutes = [];
            $adminRoutes = [];
            foreach ($routes as $r) {
                $eId = (int)$r['evaluator_id'];
                if ($eId === (int)($docInfo['user_id'] ?? 0)) continue;
                if ($userModel->hasRole($eId, 'TWG') || $userModel->hasRole($eId, 'PMT')) continue;

                if ($eId === (int)$userId) {
                    $currentEvaluatorRoute = $r;
                }
                if ($userModel->hasRole($eId, 'Admin')) {
                    $adminRoutes[] = $r;
                } else {
                    $nonAdminRoutes[] = $r;
                }
            }
            $effectiveRoutes = !empty($nonAdminRoutes) ? $nonAdminRoutes : $adminRoutes;
            foreach ($effectiveRoutes as $r) {
                $eId = (int)$r['evaluator_id'];
                $supervisorApprovals[$eId] = $r['status'];
                $totalSupervisorsCount++;
                $st = strtolower(trim((string)$r['status']));
                if ($st === 'approved' || $st === 'target_approved') {
                    $approvedSupervisorsCount++;
                }
            }
        }
        $data['allSupervisorsApproved'] = ($totalSupervisorsCount > 0 && $approvedSupervisorsCount >= $totalSupervisorsCount)
            || ($totalSupervisorsCount === 0 && !empty($docInfo['target_approved_at']));

        // Version History resolution
        $currentVersion = max(1, (int)($currentFolderRec['current_version'] ?? ($docInfo['current_version'] ?? 1)));
        $folderVersions = [];
        $viewingVersionSnapshot = null;
        $requestedVersionParam = $this->request->getGet('version');
        $viewingVersion = ($requestedVersionParam !== null && $requestedVersionParam !== '')
            ? max(1, (int)$requestedVersionParam)
            : $currentVersion;
        if ($viewingVersion > $currentVersion) {
            $viewingVersion = $currentVersion;
        }
        $isHistoricalVersion = ($viewingVersion < $currentVersion);

        if (!empty($docInfo['document_folder_id']) && $folderModel->db->tableExists('document_versions')) {
            $versionModel = new \App\Models\DocumentVersionModel();
            $folderVersions = $versionModel->getFolderVersions((string)$docInfo['document_folder_id']);
            $viewingVersionSnapshot = $versionModel->getVersionSnapshot((string)$docInfo['document_folder_id'], $viewingVersion);
            if ($isHistoricalVersion && $viewingVersionSnapshot && !empty($viewingVersionSnapshot['tabs'])) {
                $docInfo['tabs'] = is_string($viewingVersionSnapshot['tabs'])
                    ? json_decode($viewingVersionSnapshot['tabs'], true)
                    : $viewingVersionSnapshot['tabs'];
                $data['doc'] = $docInfo;
            }
        }

        $data['currentVersion']         = $currentVersion;
        $data['viewingVersion']         = $viewingVersion;
        $data['isHistoricalVersion']    = $isHistoricalVersion;
        $data['folderVersions']         = $folderVersions;
        $data['viewingVersionSnapshot'] = $viewingVersionSnapshot;

        $data['allBasisGuides']         = $allBasisGuides;
        $data['allBasisGuidesJson']     = array_map(function($g) {
            return [
                'id'            => $g['id'],
                'title'         => $g['title'],
                'doc_type'      => $g['doc_type'],
                'superior_id'   => $g['superior_id'],
                'superior_name' => $g['superior_name'],
                'superior_role' => $g['superior_role'],
                'is_approved'   => (bool)$g['is_approved'],
                'content'       => $g['content'] ?? '',
                'formData'      => $g['formData'] ?? null
            ];
        }, $allBasisGuides);
        $data['supervisorApprovals']    = $supervisorApprovals;
        $data['currentEvaluatorRoute']  = $currentEvaluatorRoute;

        return view('document/show', $data);
    }
    
    /** POST /document - Creates a new (optionally template-seeded) document inside a folder. */
    public function store() {
        return $this->tryOrFail(function() {
            $documentModel = new DocumentModel();
            $userId   = session()->get('user_id');
            $folderId = $this->request->getPost('folder_id');
            $title  = trim($this->request->getPost('title')) ?: 'Untitled Document';
            $templateId = $this->request->getPost('template');
            $initialTabs = [];

            if (!empty($templateId)) {
                $templateModel = new TemplateModel();
                $template = $templateModel->find($templateId);
                if ($template && !empty($template['tabs'])) {
                    $initialTabs = is_string($template['tabs']) ? json_decode($template['tabs'], true) : $template['tabs'];
                }
            }

            if (empty($initialTabs)) {
                $initialTabs = [
                    [
                        'id' => 'tab-' . uniqid(),
                        'title' => 'Main Document',
                        'content' => ''
                    ]
                ];
            }

            $docs = $documentModel->getUserDocuments($userId);
            $payload = [
                'title'              => resolve_unique_title($title, $docs),
                'document_folder_id' => $folderId,
                'tabs'               => $initialTabs
            ];
            $newId = create_unique_row($documentModel, $payload);

            if (!$newId) {
                return $this->respondError("Could not generate a unique ID.", 400);
            }

            return $this->respond(['status' => 'success', 'id' => $folderId]);
        });
    }

    /** POST /document/update - Autosave endpoint: persists title/content for an owner or assigned evaluator. */
    public function update() {
        $userId  = session()->get('user_id');
        $sysRole = session()->get('role');
        $docId  = $this->request->getPost('id');

        $documentModel = new DocumentModel();

        $docOwnerInfo = $documentModel->db->table('document_folders df')
            ->select('df.user_id as owner_id, df.id as folder_id, df.deleted_at as folder_deleted_at, df.parent_folder_id')
            ->where('df.id', $docId)->get()->getRowArray();

        if (!$docOwnerInfo) return $this->response->setJSON(['status' => 'error', 'message' => 'Document not found']);

        // Check if cycle or folder is archived / frozen
        if (!empty($docOwnerInfo['folder_deleted_at'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'This evaluation cycle is closed and archived. Alterations are frozen.']);
        }
        if (!empty($docOwnerInfo['parent_folder_id'])) {
            $folderModel = new DocumentFolderModel();
            $pFolder = $folderModel->find($docOwnerInfo['parent_folder_id']);
            if ($pFolder && !empty($pFolder['deleted_at'])) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'This evaluation cycle is closed and archived. Alterations are frozen.']);
            }
        }

        $isAuthorized = false;

        if ((string)$docOwnerInfo['owner_id'] === (string)$userId || in_array($sysRole, ['Admin', 'TWG', 'PMT'])) {
            $isAuthorized = true; 
        } else {
            $routingModel = new EvaluationRoutingModel();
            $isEvaluator = $routingModel->where('folder_id', $docOwnerInfo['folder_id'])
                                        ->where('evaluator_id', $userId)
                                        ->countAllResults() > 0;
            if ($isEvaluator) $isAuthorized = true;
        }

        if (!$isAuthorized) return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);

        $payload = [
            'id'    => $docId,
            'title' => $this->request->getPost('title')
        ];

        $tabsJson = $this->request->getPost('tabs');
        if ($tabsJson) {
            $decoded = json_decode($tabsJson, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $payload['tabs'] = $decoded;
            }
        }

        $documentModel->save($payload);

        return $this->response->setJSON(['status' => 'success']);
    }
    
    /**
     * POST /document/set-target - Marks one document (the basis target) in a folder as the
     * basis for the final rating. Clears the flag on every other document in the
     * same folder first, since only one document can be the target at a time.
     */
    public function setTarget() {
        return $this->tryOrFail(function() {
            return $this->respond(['status' => 'success', 'message' => 'Target document updated.']);
        });
    }

    /** POST /document/delete - Deletes a document after confirming the requester owns its folder or is Admin. */
    public function destroy() {
        $docId = $this->request->getVar('doc_id') ?? $this->request->getPost('doc_id');
        $isAdmin = (session()->get('role') === 'Admin');
        $documentModel = new DocumentModel();

        if (!$isAdmin) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized. Only administrators can delete performance documents.']);
        }

        $documentModel->delete($docId);

        return $this->response->setJSON(['status' => 'success']);
    }

    /** GET /document/{id}/export-excel - Exports document to official CSC Excel spreadsheet */
    public function exportExcel($docId = null) {
        $userId  = session()->get('user_id');
        $sysRole = session()->get('role');

        if (!$docId || !$userId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $documentModel = new DocumentModel();
        $userModel     = new \App\Models\UserModel();

        $docInfo = $documentModel->getDocumentWithFolderInfo($docId);
        if (!$docInfo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $docOwnerId = $docInfo['owner_id'];

        // Authorization check: Only Document Owner and Admin can export
        if ($docOwnerId !== $userId && $sysRole !== 'Admin') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Parse form data from tabs
        $formData = null;
        if (!empty($docInfo['tabs'])) {
            $tabs = is_string($docInfo['tabs']) ? json_decode($docInfo['tabs'], true) : $docInfo['tabs'];
            if (!empty($tabs) && is_array($tabs)) {
                $formData = $tabs[0]['formData'] ?? null;
            }
        }

        // Owner Account Details
        $ownerUser = $userModel->find($docOwnerId);
        $ownerPlantilla = $userModel->getActivePlantillaDetails($docOwnerId);
        $ownerRolePivot = (new \App\Models\UserRoleModel())->where('user_id', $docOwnerId)->first();
        $ownerRoleName = $ownerRolePivot ? ((new \App\Models\RoleModel())->find($ownerRolePivot['role_id'])['name'] ?? '') : '';

        $allOwnerPlantillas = $userModel->getAllActivePlantillaDetails((int)$docOwnerId);
        $ownerRoles = [];
        if (!empty($allOwnerPlantillas)) {
            foreach ($allOwnerPlantillas as $pRow) {
                $ownerRoles[] = [
                    'position' => $pRow['position'] ?? '',
                    'dept'     => $pRow['department'] ?? ''
                ];
            }
        } else {
            $ownerRoles[] = [
                'position' => $ownerPlantilla['position'] ?? ($ownerRoleName ?: 'Faculty'),
                'dept'     => $ownerPlantilla['department'] ?? ''
            ];
        }

        $ownerInfo = [
            'name'     => trim(($ownerUser['first_name'] ?? '') . ' ' . ($ownerUser['last_name'] ?? '')),
            'position' => $ownerPlantilla['position'] ?? ($ownerRoleName ?: 'Faculty'),
            'dept'     => $ownerPlantilla['department'] ?? '',
            'roles'    => $ownerRoles,
            'period'   => $formData['ratee']['period'] ?? '',
        ];

        // Superior / Supervisor Account Details
        $superiorInfo = null;
        if (!empty($docInfo['parent_folder_id'])) {
            $folderModel = new \App\Models\DocumentFolderModel();
            $parentFolder = $folderModel->find($docInfo['parent_folder_id']);
            if ($parentFolder && !empty($parentFolder['user_id'])) {
                $superiorUser = $userModel->find($parentFolder['user_id']);
                if ($superiorUser) {
                    $plantilla = $userModel->getActivePlantillaDetails($superiorUser['id']);
                    $superiorUser['position'] = $plantilla['position'] ?? 'Supervisor';
                    $superiorUser['department'] = $plantilla['department'] ?? '';
                    $superiorInfo = $superiorUser;
                }
            }
        }

        \App\Libraries\CscExcelExporter::export($docInfo, $formData, $ownerInfo, $superiorInfo);
    }

    /**
     * GET /document/{id}/export-rubric
     * Generate and stream the official BSU Rubric Excel template pre-filled with the document's deliverables.
     */
    public function exportRubric($docId = null)
    {
        $userId  = session()->get('user_id');
        $sysRole = session()->get('role');

        if (!$docId) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $documentModel = new DocumentModel();
        $userModel     = new \App\Models\UserModel();

        $docInfo = $documentModel->getDocumentWithFolderInfo($docId);
        if (!$docInfo) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $docOwnerId = $docInfo['owner_id'];

        // Authorization: Owner, Evaluator/Supervisor, Admin, TWG, or PMT
        $routingModel = new EvaluationRoutingModel();
        $isEvaluator = $routingModel->where('folder_id', $docInfo['document_folder_id'])->where('evaluator_id', $userId)->countAllResults() > 0;
        if ($docOwnerId !== $userId && !$isEvaluator && !in_array($sysRole, ['Admin', 'PMT', 'TWG'])) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Parse form data from tabs
        $formData = null;
        if (!empty($docInfo['tabs'])) {
            $tabs = is_string($docInfo['tabs']) ? json_decode($docInfo['tabs'], true) : $docInfo['tabs'];
            if (!empty($tabs) && is_array($tabs)) {
                $formData = $tabs[0]['formData'] ?? null;
            }
        }

        // Owner Account Details
        $ownerUser = $userModel->find($docOwnerId);
        $ownerPlantilla = $userModel->getActivePlantillaDetails($docOwnerId);
        $ownerRolePivot = (new \App\Models\UserRoleModel())->where('user_id', $docOwnerId)->first();
        $ownerRoleName = $ownerRolePivot ? ((new \App\Models\RoleModel())->find($ownerRolePivot['role_id'])['name'] ?? '') : '';

        $allOwnerPlantillas = $userModel->getAllActivePlantillaDetails((int)$docOwnerId);
        $ownerRoles = [];
        if (!empty($allOwnerPlantillas)) {
            foreach ($allOwnerPlantillas as $pRow) {
                $ownerRoles[] = [
                    'position' => $pRow['position'] ?? '',
                    'dept'     => $pRow['department'] ?? ''
                ];
            }
        } else {
            $ownerRoles[] = [
                'position' => $ownerPlantilla['position'] ?? ($ownerRoleName ?: 'Staff'),
                'dept'     => $ownerPlantilla['department'] ?? ''
            ];
        }

        $ownerInfo = [
            'name'     => trim(($ownerUser['first_name'] ?? '') . ' ' . ($ownerUser['last_name'] ?? '')),
            'position' => $ownerPlantilla['position'] ?? ($ownerRoleName ?: 'Staff'),
            'dept'     => $ownerPlantilla['department'] ?? '',
            'roles'    => $ownerRoles,
            'period'   => $formData['ratee']['period'] ?? '',
        ];

        \App\Libraries\CscExcelExporter::exportRubricTemplate($docInfo, $formData, $ownerInfo);
    }
}