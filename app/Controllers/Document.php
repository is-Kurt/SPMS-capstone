<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\DocumentModel;
use App\Models\DocumentFolderModel;
use App\Models\EvaluationRoutingModel;
use App\Models\TemplateModel;
use App\Models\DefaultFunctionWeightModel;
use App\Enums\FolderStatus;

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
                    'date'     => '',
                    'evalDate' => ''
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

                $supervisors[] = [
                    'id'        => $eId,
                    'folder_id' => $route['evaluator_folder_id'] ?? null,
                    'name'      => $eName,
                    'position'  => $ePos,
                    'date'      => '',
                    'evalDate'  => ''
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
                                    'date'      => '',
                                    'evalDate'  => ''
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
                    'date'      => '',
                    'evalDate'  => ''
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
                    // Skip completely empty rows (do not cascade if the whole row is empty)
                    if (is_spms_row_empty($sRow)) continue;

                    $mfo = clean_spms_text($sRow['mfo'] ?? ($sRow['office_ppa'] ?? ($sRow['paps'] ?? ($sRow['mfo_title'] ?? ''))));
                    $ind = clean_spms_text($sRow['indicators'] ?? ($sRow['success_indicators'] ?? ($sRow['expected_outputs'] ?? ($sRow['success_indicator'] ?? ''))));

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
            'cascadeDate' => $cascadeDate,
            // Heads a unit with sub-units (e.g. a college): picks the DPCR signature labels (2026-11-07).
            'headsSubunits' => \App\Libraries\OrgStructure::instance()->headsUnitWithSubunits((int) $docOwnerId),
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

        // Determine whether user entered from Ratings or Folders
        $fromParam  = $this->request->getGet('from');
        $referer    = (string) ($this->request->getServer('HTTP_REFERER') ?? '');
        $isDocOwner = ((int)$docOwnerId === (int)$userId);
        $entrySource = null;

        if ($fromParam === 'dashboard') {
            $entrySource = 'dashboard';
            session()->set('doc_entry_source_' . $docId, 'dashboard');
            if (!empty($referer) && str_contains($referer, '/dashboard')) {
                session()->set('doc_dashboard_referer', $referer);
            }
        } elseif ($fromParam === 'ratings') {
            $entrySource = 'ratings';
            session()->set('doc_entry_source_' . $docId, 'ratings');
            if (!empty($referer) && str_contains($referer, '/ratings')) {
                session()->set('doc_ratings_referer', $referer);
            }
        } elseif ($fromParam === 'folders') {
            $entrySource = 'folders';
            session()->set('doc_entry_source_' . $docId, 'folders');
        } elseif (!empty($referer) && str_contains($referer, '/dashboard')) {
            $entrySource = 'dashboard';
            session()->set('doc_entry_source_' . $docId, 'dashboard');
            session()->set('doc_dashboard_referer', $referer);
        } elseif (!empty($referer) && str_contains($referer, '/ratings')) {
            $entrySource = 'ratings';
            session()->set('doc_entry_source_' . $docId, 'ratings');
            session()->set('doc_ratings_referer', $referer);
        } elseif (!empty($referer) && str_contains($referer, '/folders')) {
            $entrySource = 'folders';
            session()->set('doc_entry_source_' . $docId, 'folders');
        } else {
            $entrySource = session()->get('doc_entry_source_' . $docId);
            if (!$entrySource) {
                // If viewing another user's submission as a reviewer/supervisor/admin or if rateeNav is active, return to ratings
                $entrySource = (!$isDocOwner || in_array($sysRole, ['TWG', 'PMT'], true) || !empty($rateeNav)) ? 'ratings' : 'folders';
            }
        }

        if ($entrySource === 'dashboard') {
            $dashReferer = session()->get('doc_dashboard_referer');
            $homeUrl = (!empty($dashReferer) && str_contains($dashReferer, '/dashboard')) ? $dashReferer : site_url('dashboard');
        } elseif ($entrySource === 'ratings') {
            $ratingsReferer = session()->get('doc_ratings_referer');
            $homeUrl = (!empty($ratingsReferer) && str_contains($ratingsReferer, '/ratings')) ? $ratingsReferer : site_url('ratings');
        } else {
            $homeUrl = site_url('folders');
        }

        $data['entrySource']            = $entrySource;
        $data['homeUrl']                = $homeUrl;
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
            $docCanonStatus = \App\Enums\FolderStatus::canonical($currentFolderRec['status'] ?? ($docInfo['status'] ?? ''));
            $isDocEvalPhase = $docCanonStatus->isEvaluationPhase();

            $effectiveRoutes = !empty($nonAdminRoutes) ? $nonAdminRoutes : $adminRoutes;
            foreach ($effectiveRoutes as $r) {
                $eId = (int)$r['evaluator_id'];
                $supervisorApprovals[$eId] = $r['status'];
                $totalSupervisorsCount++;
                $st = strtolower(trim((string)$r['status']));
                if ($isDocEvalPhase) {
                    if ($st === 'approved' || $st === 'eval_approved') {
                        $approvedSupervisorsCount++;
                    }
                } else {
                    if ($st === 'approved' || $st === 'target_approved') {
                        $approvedSupervisorsCount++;
                    }
                }
            }
        }
        $data['allSupervisorsApproved'] = $isDocEvalPhase
            ? (($totalSupervisorsCount > 0 && $approvedSupervisorsCount >= $totalSupervisorsCount) || ($totalSupervisorsCount === 0 && (!empty($currentFolderRec['rated_at']) || !empty($docInfo['eval_approved_at']))))
            : (($totalSupervisorsCount > 0 && $approvedSupervisorsCount >= $totalSupervisorsCount) || ($totalSupervisorsCount === 0 && !empty($docInfo['target_approved_at'])));

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
        $data['systemDefaultWeights']   = (new DefaultFunctionWeightModel())->getAllWeightsKeyed();

        // Content as stored right now; the editor sends it back with each save so update() can detect conflicts
        $storedContent = $documentModel->db->table('document_folders')->select('tabs, title')->where('id', $docId)->get()->getRowArray();
        $data['contentFingerprint'] = $storedContent ? self::contentFingerprint($storedContent) : '';

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
            ->select('df.user_id as owner_id, df.id as folder_id, df.deleted_at as folder_deleted_at, df.parent_folder_id, df.status, df.tabs')
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

        // Who may save, and when. The editor page locks the form the same way (document/show.php), but that
        // lock is only in the browser, so the save itself must check it too (2026-11-06):
        //  - the owner only while the form is theirs to edit: target draft, targets approved (accomplishments)
        //    and evaluation draft. Once it is submitted, or after the final approval, it is read-only for them;
        //  - a reviewer (routed supervisor, TWG assigned to the unit, PMT) only while the form is with the
        //    reviewers: targets or evaluation submitted, and approved targets (approver names);
        //  - Admin as before.
        // Inside those windows applyEditRules() keeps the parts the saver may not change.
        $isOwner   = (string) $docOwnerInfo['owner_id'] === (string) $userId;
        $topStatus = FolderStatus::canonical((string) ($docOwnerInfo['status'] ?? ''));
        if ($sysRole === 'Admin') {
            $editMode = 'admin';
        } elseif ($isOwner) {
            if (!in_array($topStatus, [FolderStatus::DRAFT_TARGET, FolderStatus::TARGET_APPROVED, FolderStatus::DRAFT_EVALUATION], true)) {
                return $this->respondError('This form has been submitted, so it can no longer be changed. Reload the page to see its current state.', 403);
            }
            $editMode = 'owner';
        } else {
            if (!$this->isReviewerOf(['id' => $docOwnerInfo['folder_id'], 'user_id' => $docOwnerInfo['owner_id']])) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
            }
            if (!in_array($topStatus, [FolderStatus::TARGET_SUBMITTED, FolderStatus::TARGET_UNAPPROVED, FolderStatus::TARGET_APPROVED,
                                       FolderStatus::EVAL_SUBMITTED, FolderStatus::EVAL_UNRATED], true)) {
                return $this->respondError('This form is not open for review right now, so your changes were not saved. Reload the page to see its current state.', 403);
            }
            $editMode = 'reviewer';
        }

        $payload = [
            'id'    => $docId,
            'title' => $this->request->getPost('title')
        ];

        $tabsJson = $this->request->getPost('tabs');
        if ($tabsJson) {
            $decoded = json_decode($tabsJson, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                if (!$this->hasSafeIdentifiers($decoded)) {
                    return $this->respondError('The document contains invalid row identifiers. Please reload the page and try again.', 422);
                }
                $storedTabs = json_decode((string) ($docOwnerInfo['tabs'] ?? ''), true);
                $payload['tabs'] = $this->applyEditRules($decoded, is_array($storedTabs) ? $storedTabs : [], $editMode, $topStatus, (string) $docId);
            }
        }

        // Two people (e.g. the owner and the evaluator) can have the same document open. The page sends the
        // fingerprint of the content it last loaded or saved; if the stored content no longer matches, someone
        // else saved in between, so this save is refused instead of silently overwriting their work. The update
        // itself is conditional on the content just checked, so two saves arriving together cannot both pass.
        // Pages from before this check send no fingerprint and keep the old last-save-wins behaviour.
        $baseFingerprint = (string) $this->request->getPost('base_fingerprint');
        if ($baseFingerprint !== '') {
            $current = $documentModel->db->table('document_folders')->select('tabs, title')->where('id', $docId)->get()->getRowArray();
            $conflict = ['status' => 'error', 'code' => 'conflict',
                         'message' => 'Someone else saved this document after you opened it, so your latest edits were not saved.'];
            if (!$current || !hash_equals(self::contentFingerprint($current), $baseFingerprint)) {
                return $this->response->setJSON($conflict);
            }
            unset($payload['id']);
            $documentModel->where('tabs', $current['tabs'])->where('title', $current['title'])->update($docId, $payload);
            if ($documentModel->db->affectedRows() < 1) {
                return $this->response->setJSON($conflict);
            }
        } else {
            $documentModel->save($payload);
        }

        $saved = $documentModel->db->table('document_folders')->select('tabs, title')->where('id', $docId)->get()->getRowArray();
        return $this->response->setJSON(['status' => 'success', 'fingerprint' => $saved ? self::contentFingerprint($saved) : '']);
    }

    /** Row fields that make up the targets: set by the owner while drafting targets, then fixed. */
    private const TARGET_ROW_FIELDS = ['mfo', 'indicators', 'budget', 'accountable', 'std_1', 'std_2', 'std_3', 'std_4', 'std_5'];
    /** Row text written by reviewers: the owner never changes it. */
    private const REVIEWER_ROW_FIELDS = ['remarks', 'twg_comment'];
    /** The ratee's own self-rating: reviewers never change it (their scores go in the _head / _twg fields). */
    private const RATEE_ROW_FIELDS = ['q_self', 't_self', 'e_self'];
    /** Evaluation-phase row fields: reviewers cannot change them while the targets are under review. */
    private const EVAL_ROW_FIELDS = ['accomplishments', 'q', 't', 'e', 'q_head', 't_head', 'e_head', 'q_twg', 't_twg', 'e_twg',
                                     'last_rated_by_q', 'last_rated_by_t', 'last_rated_by_e'];
    /** Form-level parts that belong to the ratee: reviewers never change them. */
    private const RATEE_FORM_FIELDS = ['ratee', 'rateeSign', 'doc_type'];
    /**
     * Settings the page fills in with defaults when they are empty (routing approver, default weights, peso
     * sign, rubric matrix). The first save stores them, so they may be filled in while empty, but a stored
     * value cannot be changed by someone who may not edit it.
     */
    private const TARGET_FORM_DEFAULTS = ['approver', 'approvers', 'weights', 'currency', 'budget_currency', 'rubrics'];
    private const RATEE_FORM_DEFAULTS  = ['weights', 'currency', 'budget_currency', 'rubrics'];
    private const TARGET_ROW_DEFAULTS  = ['budget_currency'];

    /**
     * Applies what the editor page locks to a save, using the stored form for every part the saver may not
     * change. The page sends the whole form on every save with the locked parts unchanged (checked against
     * the real editor for each role and stage), so this only matters for a save sent by hand.
     *  - owner: the reviewers' remarks stay as stored; outside the target draft the targets are fixed (stored
     *    rows kept with their target fields; a new row is kept only as a blank row, as the page adds one to an
     *    empty category; approvers and other settings only filled in while empty).
     *  - reviewer: the same fixed targets, and the ratee's self-ratings and details stay as stored; while the
     *    targets are under review the accomplishments and ratings stay as stored too.
     *  - admin: unchanged. The revision history is written by the server only (on a return) and stays as stored.
     * The ratee's saves still clear the reviewers' scores as the page does (a re-rated form starts again).
     */
    private function applyEditRules(array $tabs, array $stored, string $mode, FolderStatus $status, string $docId): array
    {
        if ($mode === 'admin' || !$stored) {
            return $tabs;                                                        // admin, or nothing stored yet to protect
        }
        $changed = [];
        $isEmpty = fn($v) => $v === null || $v === '' || $v === [];
        $norm = fn($v) => $isEmpty($v) ? '' : (is_scalar($v) ? (string) $v : json_encode($v));
        // strict: $to gets the stored value (a field the stored row lacks becomes '' in a row, is removed from the form).
        // fill: as strict, unless the stored value is empty, in which case $to keeps what was sent.
        $keep = function (array &$to, array $from, array $fields, string $where, bool $isRow, bool $fill = false) use (&$changed, $norm, $isEmpty) {
            foreach ($fields as $f) {
                if ($fill && $isEmpty($from[$f] ?? null)) continue;
                if ($norm($to[$f] ?? null) !== $norm($from[$f] ?? null)) {
                    $changed[$where . $f] = true;
                }
                if (array_key_exists($f, $from)) {
                    $to[$f] = $from[$f];
                } elseif ($isRow) {
                    if (array_key_exists($f, $to)) $to[$f] = '';
                } else {
                    unset($to[$f]);
                }
            }
        };
        $tabKey = fn($t, $i) => (string) ($t['id'] ?? ('#' . $i));
        $rowsFixed   = $mode === 'reviewer' || $status !== FolderStatus::DRAFT_TARGET;
        $targetPhase = in_array($status, [FolderStatus::TARGET_SUBMITTED, FolderStatus::TARGET_UNAPPROVED, FolderStatus::TARGET_APPROVED], true);

        $storedByKey = [];
        foreach ($stored as $i => $t) $storedByKey[$tabKey($t, $i)] = $t;
        $sentByKey = [];
        foreach ($tabs as $i => $t) $sentByKey[$tabKey($t, $i)] = $t;

        $result = [];
        foreach (($rowsFixed ? $stored : $tabs) as $i => $base) {                // a fixed form keeps the stored tabs, in order
            $key = $tabKey($base, $i);
            if ($rowsFixed && !isset($sentByKey[$key])) { $result[] = $base; continue; }
            $tab = $rowsFixed ? $sentByKey[$key] : $base;
            $old = $storedByKey[$key] ?? [];
            $F = is_array($tab['formData'] ?? null) ? $tab['formData'] : [];
            $S = is_array($old['formData'] ?? null) ? $old['formData'] : [];

            $keep($F, $S, ['revisionHistory'], 'form.', false);
            if ($mode === 'owner') {
                $keep($F, $S, ['pmtRemarks'], 'form.', false);
                if ($rowsFixed) $keep($F, $S, self::TARGET_FORM_DEFAULTS, 'form.', false, true);
            } else {
                $keep($F, $S, self::RATEE_FORM_FIELDS, 'form.', false);
                $keep($F, $S, self::RATEE_FORM_DEFAULTS, 'form.', false, true);
            }

            $cats = [];
            foreach (['core', 'strategic', 'support'] as $cat) {
                $sentRows = is_array($F['categories'][$cat] ?? null) ? $F['categories'][$cat] : [];
                $oldRows  = is_array($S['categories'][$cat] ?? null) ? $S['categories'][$cat] : [];
                $oldById = $sentById = [];
                foreach ($oldRows as $r)  if (isset($r['row_id'])) $oldById[(string) $r['row_id']] = $r;
                foreach ($sentRows as $r) if (isset($r['row_id'])) $sentById[(string) $r['row_id']] = $r;

                // Fixed targets: every stored row in its stored place, then any new rows from this save as blanks.
                $order = $rowsFixed
                    ? array_merge($oldRows, array_values(array_diff_key($sentById, $oldById)))
                    : $sentRows;
                $out = [];
                foreach ($order as $r) {
                    $id  = (string) ($r['row_id'] ?? '');
                    $was = $oldById[$id] ?? [];                                  // a new row has no stored values yet
                    if ($rowsFixed && $was && !isset($sentById[$id])) {          // left out of this save: keep as stored
                        $changed["rows.$cat"] = true; $out[] = $r; continue;
                    }
                    $row = $rowsFixed ? ($sentById[$id] ?? $r) : $r;
                    if ($rowsFixed) {
                        $keep($row, $was, self::TARGET_ROW_FIELDS, 'row.', true);
                        $keep($row, $was, self::TARGET_ROW_DEFAULTS, 'row.', true, true);
                    }
                    if ($mode === 'owner') {
                        $keep($row, $was, self::REVIEWER_ROW_FIELDS, 'row.', true);
                    } else {
                        $keep($row, $was, self::RATEE_ROW_FIELDS, 'row.', true);
                        if ($targetPhase) $keep($row, $was, self::EVAL_ROW_FIELDS, 'row.', true);
                    }
                    $out[] = $row;
                }
                $cats[$cat] = $out;
            }
            if (isset($F['categories']) || isset($S['categories'])) {
                $F['categories'] = array_merge(is_array($F['categories'] ?? null) ? $F['categories'] : [], $cats);
            }
            $tab['formData'] = $F;
            $result[] = $tab;
        }

        if ($changed) {
            log_message('warning', 'Document {doc}: save by user {user} ({mode}, {status}) tried to change locked parts, kept as stored: {parts}', [
                'doc' => $docId, 'user' => session()->get('user_id'), 'mode' => $mode, 'status' => $status->value, 'parts' => implode(', ', array_keys($changed)),
            ]);
        }
        return $result;
    }

    /**
     * Tab ids, category keys and row ids end up inside HTML attributes and inline handlers on the editor
     * page (e.g. openMovModal('<row id>')), so the owner could otherwise plant script there for every
     * reviewer (security test, 2026-11-06). The editor only ever creates ids from letters, digits, _ and -.
     */
    private function hasSafeIdentifiers($tabs): bool
    {
        if (!is_array($tabs)) {
            return false;
        }
        $id = '/^[A-Za-z0-9_-]{1,100}$/';
        foreach ($tabs as $tab) {
            if (!is_array($tab) || (isset($tab['id']) && !preg_match($id, (string) $tab['id']))) {
                return false;
            }
            foreach (($tab['formData']['categories'] ?? []) as $cat => $rows) {
                if (!preg_match('/^[A-Za-z0-9_-]{1,40}$/', (string) $cat) || !is_array($rows)) {
                    return false;
                }
                foreach ($rows as $row) {
                    if (is_array($row) && isset($row['row_id']) && !preg_match($id, (string) $row['row_id'])) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /** Fingerprint of a document's stored content (raw tabs JSON + title), used by update() to detect conflicting saves. */
    private static function contentFingerprint(array $row): string
    {
        return sha1(($row['tabs'] ?? '') . "\x1f" . ($row['title'] ?? ''));
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