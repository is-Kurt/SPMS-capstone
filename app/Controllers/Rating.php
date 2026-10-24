<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\DocumentFolderModel;
use App\Models\EvaluationRoutingModel;
use App\Models\DocumentModel;
use App\Models\UnitModel;

/**
 * The "Ratings" dashboard: lets evaluators (Admins, Supervisors, HR) see every
 * subordinate folder they're responsible for, grouped into Action
 * Required / Pending Subordinate / Completed queues, and drill into one folder
 * to actually review and rate it.
 */
class Rating extends BaseController
{
    /**
     * GET /ratings/{folderId} - Builds the evaluator's queue: fetches every folder
     * routed to this evaluator and buckets each into a tab based on its status
     * (still with the employee vs. awaiting this evaluator's action vs. approved).
     */
    public function index($folderId = null) {
        $userId  = session()->get('user_id');
        $sysRole = session()->get('role');

        $folderModel = new DocumentFolderModel();

        // For TWG/PMT (who do not author personal folders), Admins, or Supervisors without personal folders
        // (such as the University President), display institutional root evaluation cycle folders in the sidebar.
        $userPersonalFolders = $folderModel->where('user_id', $userId)->where('deleted_at IS NULL')->orderBy('created_at', 'DESC')->findAll();
        if (in_array($sysRole, ['TWG', 'PMT', 'Admin']) || empty($userPersonalFolders)) {
            $folders = $folderModel->where('parent_folder_id IS NULL')
                                   ->where('deleted_at IS NULL')
                                   ->orderBy('created_at', 'DESC')
                                   ->findAll();
        } else {
            $folders = $userPersonalFolders;
        }

        if (!$folderId) {
            $lastId = session()->get('active_folder_id');
            if ($lastId && array_search($lastId, array_column($folders, 'id')) !== false) {
                return redirect()->to('ratings/' . $lastId);
            } elseif (!empty($folders)) {
                return redirect()->to('ratings/' . $folders[0]['id']);
            }
        } else {
            // Strict Authorization: The requested folderId MUST be an authorized folder in the user's sidebar
            // (e.g. root evaluation cycle for TWG/PMT/Admin, or personal folder for Supervisor).
            // Prevent users from accessing other users' personal folders via /ratings/{id}.
            if (array_search($folderId, array_column($folders, 'id')) === false) {
                session()->remove('active_folder_id');
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }
            session()->set('active_folder_id', $folderId);
        }

        if ($folderId) {
            $activeFolder = $folderModel->find($folderId);
            if (!$activeFolder || !empty($activeFolder['deleted_at'])) {
                session()->remove('active_folder_id');
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }
            $rootFolderId = $activeFolder['parent_folder_id'] ?? $activeFolder['id'];
        } elseif (!empty($folders)) {
            $activeFolder = $folders[0];
            $rootFolderId = $activeFolder['parent_folder_id'] ?? $activeFolder['id'];
        } else {
            $activeFolder = null;
            $rootFolderId = null;
        }

        if ($sysRole === 'Admin' && !empty($folders)) {
            $userModel = new \App\Models\UserModel();
            $presidentUser = $userModel->where('email', 'president@test.com')->first();
            $presId = $presidentUser ? (int)$presidentUser['id'] : null;

            $routingModel = new EvaluationRoutingModel();
            $parentIds = array_column($folders, 'id');
            if (!empty($parentIds)) {
                $childFolders = $folderModel->select('id, parent_folder_id, status')
                    ->where('parent_folder_id IS NOT NULL')
                    ->where('deleted_at IS NULL')
                    ->whereIn('parent_folder_id', $parentIds)
                    ->findAll();

                foreach ($childFolders as $cf) {
                    if ($presId && $presId !== (int)$userId) {
                        $routingModel->where('folder_id', $cf['id'])->where('evaluator_id', $presId)->delete();
                    }
                    $rExists = $routingModel->where('folder_id', $cf['id'])->where('evaluator_id', $userId)->first();
                    if (!$rExists) {
                        $routingModel->insert([
                            'folder_id'           => $cf['id'],
                            'evaluator_id'        => $userId,
                            'evaluator_folder_id' => $cf['parent_folder_id'],
                            'status'              => $cf['status'] ?? \App\Enums\FolderStatus::DRAFT->value,
                            'created_at'          => date('Y-m-d H:i:s'),
                            'updated_at'          => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }

            // Self-healing: Clean up any stale Admin routings on subordinate folders (where parent is not a root cycle)
            $folderModel->db->query("
                DELETE FROM evaluation_routings 
                WHERE id IN (
                    SELECT er.id 
                    FROM evaluation_routings er
                    JOIN document_folders df ON df.id = er.folder_id
                    JOIN document_folders pf ON pf.id = df.parent_folder_id
                    JOIN user_roles ur ON ur.user_id = er.evaluator_id
                    JOIN roles r ON r.id = ur.role_id AND r.name = 'Admin'
                    WHERE pf.parent_folder_id IS NOT NULL
                )
            ");
        }

        $isPresident = (bool) (session()->get('is_president') ?? false);
        $rawFolders = $folderModel->getRatingDashboardFolders($userId, $sysRole, $rootFolderId, $isPresident);

        foreach ($rawFolders as &$f) {
            if ($f['position'])   $f['position']   = str_replace(',', ', ', $f['position']);
            if ($f['department']) $f['department'] = str_replace(',', ', ', $f['department']);
        }

        unset($f);

        $periods = [
            'target' => [
                'label' => 'Target Approval Period',
                'tabs' => [
                    'target_approval' => ['label' => 'Pending Approval', 'folders' => []],
                    'target_returned' => ['label' => 'Returned', 'folders' => []],
                    'target_approved' => ['label' => 'Approved', 'folders' => []]
                ]
            ],
            'evaluation' => [
                'label' => 'Evaluation Period',
                'tabs' => [
                    'action'    => ['label' => 'Action Required', 'folders' => []],
                    'pending'   => ['label' => 'Returned for Revision', 'folders' => []],
                    'completed' => ['label' => 'Completed', 'folders' => []]
                ]
            ]
        ];

        $adminCascadedTotalCount = 0;
        $overallTotalCount = 0;
        $targetCascadedCount = 0;
        $targetOverallCount = 0;
        $evalCascadedCount = 0;
        $evalOverallCount = 0;

        foreach ($rawFolders as $f) {
            $status = $f['folder_status'];

            // Skip unsubmitted drafts for privacy - they are monitored on the Dashboard roster, not the review queue
            if (in_array($status, [\App\Enums\FolderStatus::DRAFT_TARGET->value, \App\Enums\FolderStatus::DRAFT->value])) {
                continue;
            }

            $overallTotalCount++;
            $isCascaded = !empty($f['is_cascaded_by_me']);
            if ($isCascaded) {
                $adminCascadedTotalCount++;
            }

            if (in_array($status, [\App\Enums\FolderStatus::APPROVED->value, \App\Enums\FolderStatus::TWG_APPROVED->value, \App\Enums\FolderStatus::TWG_DISAPPROVED->value, \App\Enums\FolderStatus::UNEVALUATED->value])) {
                $periods['evaluation']['tabs']['completed']['folders'][] = $f;
                $evalOverallCount++;
                if ($isCascaded) $evalCascadedCount++;
            } elseif ($status === \App\Enums\FolderStatus::PENDING_TARGET_APPROVAL->value) {
                $periods['target']['tabs']['target_approval']['folders'][] = $f;
                $targetOverallCount++;
                if ($isCascaded) $targetCascadedCount++;
            } elseif ($status === \App\Enums\FolderStatus::TARGET_RETURNED->value || $status === \App\Enums\FolderStatus::TARGET_UNAPPROVED->value) {
                $periods['target']['tabs']['target_returned']['folders'][] = $f;
                $targetOverallCount++;
                if ($isCascaded) $targetCascadedCount++;
            } elseif ($status === \App\Enums\FolderStatus::TARGET_APPROVED->value) {
                $periods['target']['tabs']['target_approved']['folders'][] = $f;
                $targetOverallCount++;
                if ($isCascaded) $targetCascadedCount++;
            } elseif ($status === \App\Enums\FolderStatus::REEVALUATE->value) {
                $periods['evaluation']['tabs']['pending']['folders'][] = $f;
                $evalOverallCount++;
                if ($isCascaded) $evalCascadedCount++;
            } else {
                $periods['evaluation']['tabs']['action']['folders'][] = $f;
                $evalOverallCount++;
                if ($isCascaded) $evalCascadedCount++;
            }
        }

        foreach ($periods as $pKey => &$period) {
            foreach ($period['tabs'] as $tKey => &$tab) {
                $tab['cascaded_count'] = count(array_filter($tab['folders'], fn($row) => !empty($row['is_cascaded_by_me'])));
                $tab['overall_count']  = count($tab['folders']);
            }
        }
        unset($period, $tab);

        $filterUnits = [];
        $filterPositions = [];
        foreach ($rawFolders as $f) {
            if (!empty($f['department'])) {
                foreach (explode(', ', $f['department']) as $d) $filterUnits[trim($d)] = trim($d);
            }
            if (!empty($f['position'])) {
                foreach (explode(', ', $f['position']) as $p) $filterPositions[trim($p)] = trim($p);
            }
        }
        sort($filterUnits);
        sort($filterPositions);

        $unitModel = new UnitModel();
        $unitModel->unnestCollegesFromOvpaa();
        $allUnits = $unitModel->orderBy('name', 'ASC')->findAll();

        $twgAssignmentModel = new \App\Models\TwgUnitAssignmentModel();
        $assignedUnits = ($sysRole === 'TWG') ? $twgAssignmentModel->getAssignmentsByUser($userId) : [];

        return view('components/app_shell', [
            'sidebarFolders'   => $folders,
            'selectedFolderId' => $folderId, 
            'mainView'         => 'ratings/index',
            'mainData'         => [
                'activeFolder'            => $activeFolder ?? null,
                'periods'                 => $periods,
                'sysRole'                 => $sysRole,
                'filterUnits'             => $filterUnits,
                'filterPositions'         => $filterPositions,
                'allUnits'                => $allUnits,
                'assignedUnits'           => $assignedUnits,
                'adminCascadedTotalCount' => $adminCascadedTotalCount,
                'overallTotalCount'       => $overallTotalCount,
                'targetCascadedCount'     => $targetCascadedCount,
                'targetOverallCount'      => $targetOverallCount,
                'evalCascadedCount'       => $evalCascadedCount,
                'evalOverallCount'        => $evalOverallCount
            ]
        ]);
    }

    /**
     * GET /ratings/show/{subFolderId} - Opens one subordinate's folder for review,
     * after verifying the viewer is an Admin or a routed evaluator. Deliberately
     * does NOT grant access to the folder's own owner: this route only exists to
     * review someone else's folder, there's no in-app link that ever points an
     * owner at their own folder here (Rating::index()'s dashboard only lists
     * folders you're routed to evaluate), and Folder::index() already gives owners
     * a strictly better - editable, not artificially read-only - view of their
     * own folder. So an owner landing here only ever means they clicked a
     * "Pending Review" email link addressed to their evaluator, not to them.
     * Also gathers "guide" documents from that subordinate's own superiors so the
     * evaluator has the same reference material the employee saw while drafting.
     */
    public function show($subFolderId) {
        $userId  = session()->get('user_id');
        $sysRole = session()->get('role');

        $folderModel = new DocumentFolderModel();
        $routingModel = new EvaluationRoutingModel();
        $documentModel = new DocumentModel();

        $subFolder = $folderModel->find($subFolderId);
        if (!$subFolder || !empty($subFolder['deleted_at'])) {
            // Delete any stale notifications referencing this non-existent or deleted folder
            (new \App\Models\NotificationModel())
                ->groupStart()
                    ->like('link', 'ratings/show/' . $subFolderId)
                    ->orLike('link', 'folders/' . $subFolderId)
                ->groupEnd()
                ->delete();

            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Privacy guard: Unsubmitted work-in-progress drafts cannot be viewed for evaluation
        if (in_array($subFolder['status'], [\App\Enums\FolderStatus::DRAFT->value, \App\Enums\FolderStatus::DRAFT_TARGET->value])) {
            session()->setFlashdata('error', 'This folder is currently being drafted by the employee and has not yet been submitted for evaluation.');
            return redirect()->to(site_url('ratings'));
        }

        $isAuthorized = false;

        if (in_array($sysRole, ['Admin', 'PMT'])) {
            $isAuthorized = true;
        } elseif ($sysRole === 'TWG') {
            $twgAssignmentModel = new \App\Models\TwgUnitAssignmentModel();
            $assignedUnits = $twgAssignmentModel->getAssignedUnitIds($userId);
            // If TWG reviewer has specific unit assignments, verify folder owner belongs to their assigned units.
            // If no assignments are configured, fallback to institutional scope.
            if (empty($assignedUnits) || $twgAssignmentModel->isTwgAssignedToFolder($userId, $subFolderId)) {
                $isAuthorized = true;
            }
        } else {
            $routingCount = $routingModel->where('folder_id', $subFolderId)
                                         ->where('evaluator_id', $userId)
                                         ->countAllResults();
            if ($routingCount > 0) $isAuthorized = true;
        }

        if (!$isAuthorized) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $userPersonalFolders = $folderModel->where('user_id', $userId)->where('deleted_at IS NULL')->orderBy('created_at', 'DESC')->findAll();
        if (in_array($sysRole, ['TWG', 'PMT', 'Admin']) || empty($userPersonalFolders)) {
            $folders = $folderModel->where('parent_folder_id IS NULL')
                                   ->where('deleted_at IS NULL')
                                   ->orderBy('created_at', 'DESC')
                                   ->findAll();
        } else {
            $folders = $userPersonalFolders;
        }

        $groupedGuides = [];
        $cascadedRoutes = $routingModel->getEvaluatorsForFolder($subFolderId);

        foreach ($cascadedRoutes as $route) {
            $guideFolder = $folderModel->find($route['evaluator_folder_id']);
            if ($guideFolder && empty($guideFolder['deleted_at'])) {
                $docs = $documentModel->where('document_folder_id', $guideFolder['id'])->findAll();
                $groupedGuides[] = [
                    'superior' => [
                        'id'   => $route['evaluator_id'],
                        'name' => $route['first_name'] . ' ' . $route['last_name'],
                        'role' => $route['evaluator_position'] ?? 'Evaluator' 
                    ], 
                    'docs' => !empty($docs) ? $docs : [['document_folder_id' => $route['evaluator_folder_id']]] 
                ];
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

        $userModel = new \App\Models\UserModel();
        $subFolderOwner = $userModel->find($subFolder['user_id']);
        if ($subFolderOwner) {
            $plantilla = $userModel->getActivePlantillaDetails($subFolder['user_id']);
            if ($plantilla) {
                $subFolderOwner['position'] = $plantilla['position'];
                $subFolderOwner['department'] = $plantilla['department'];
            }
        }

        $parentFolder = null;
        $isParentTargetApproved = true;
        if (!empty($subFolder['parent_folder_id'])) {
            $parentFolder = $folderModel->find($subFolder['parent_folder_id']);
            if ($parentFolder) {
                $candidateBasis = $documentModel->where('document_folder_id', $parentFolder['id'])->where('is_target', 1)->first()
                               ?? $documentModel->where('document_folder_id', $parentFolder['id'])->first();
                $candidateTitleUpper = strtoupper($candidateBasis['title'] ?? '');
                $isParentDocOpcr = str_contains($candidateTitleUpper, 'OPCR') || str_contains($candidateTitleUpper, 'OFFICE') || (strtoupper($candidateBasis['doc_type'] ?? '') === 'OPCR');

                // Under CSC SPMS guidelines, subordinates require their superior's approved targets as a basis
                $isParentTargetApproved = ($parentFolder['status'] === \App\Enums\FolderStatus::TARGET_APPROVED->value);
            }
        }

        $myDocs = $documentModel->where('document_folder_id', $subFolderId)->findAll();

        // Open the employee's document directly
        if (!empty($myDocs)) {
            $targetDoc = null;
            foreach ($myDocs as $d) {
                if (!empty($d['is_target'])) {
                    $targetDoc = $d;
                    break;
                }
            }
            if (!$targetDoc) {
                $targetDoc = reset($myDocs);
            }

            if ($targetDoc && !empty($targetDoc['id'])) {
                return redirect()->to(site_url('document/' . $targetDoc['id']));
            }
        }

        return view('components/app_shell', [
            'sidebarFolders'   => $folders, 
            'selectedFolderId' => session()->get('active_folder_id'), 
            'mainView'         => 'document/_doc_rows', 
            'mainData'         => [
                'activeFolder'           => $subFolder,
                'subFolderOwner'         => $subFolderOwner,
                'ownerDocType'           => $subFolderOwner['doc_type'] ?? null,
                'parentFolder'           => $parentFolder,
                'isParentTargetApproved' => $isParentTargetApproved,
                'myDocs'                 => $myDocs,
                'isReadOnly'             => true, 
                'presets'                => [],
                'groupedGuides'          => $groupedGuides,
                'cascadedChildren'       => []
            ]
        ]);
    }
}