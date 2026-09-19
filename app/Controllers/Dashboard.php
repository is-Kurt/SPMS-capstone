<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DocumentFolderModel;
use App\Models\UnitModel;
use App\Models\UserModel;
use App\Models\PlantillaModel;
use App\Models\EvaluationRoutingModel;
use App\Enums\FolderStatus;

/**
 * Executive Analytics Dashboard: Provides university-wide and department-level
 * performance monitoring, submission compliance tracking, and CSC adjectival
 * rating visual analytics for BSU-SPMS.
 */
class Dashboard extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * GET /dashboard or GET /dashboard/{cycleId}
     * Computes performance metrics, CSC distribution charts, and college leaderboards.
     */
    public function index(?string $cycleId = null)
    {
        $db = $this->db ?? \Config\Database::connect();
        $userId  = session()->get('user_id');
        $sysRole = session()->get('role');

        $folderModel = new DocumentFolderModel();
        $unitModel   = new UnitModel();
        $userModel   = new UserModel();

        // 1. Identify all top-level evaluation cycles (root folders)
        $rootFolders = $folderModel->where('parent_folder_id IS NULL')
            ->where('deleted_at IS NULL')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        if (empty($rootFolders)) {
            $rootFolders = $folderModel->where('deleted_at IS NULL')
                ->orderBy('created_at', 'DESC')
                ->findAll();
        }

        $activeCycle = null;
        if ($cycleId) {
            foreach ($rootFolders as $rf) {
                if ((string)$rf['id'] === (string)$cycleId) {
                    $activeCycle = $rf;
                    break;
                }
            }
        }

        if (!$activeCycle && !empty($rootFolders)) {
            $activeCycle = $rootFolders[0];
        }

        // College / Department Filtering for Oversight Roles
        $selectedUnitId = $this->request->getGet('unit_id');
        $selectedUnitId = (!empty($selectedUnitId) && is_numeric($selectedUnitId)) ? (int)$selectedUnitId : null;
        $selectedUnitName = null;

        $allUnits = $db->table('units u')
            ->select('u.id, u.name, u.parent_id, COUNT(p.id) as headcount')
            ->join('plantillas p', 'p.unit_id = u.id AND p.ended_at IS NULL', 'inner')
            ->groupBy('u.id')
            ->orderBy('u.name', 'ASC')
            ->get()->getResultArray();

        if ($selectedUnitId) {
            $unitRow = $db->table('units')->where('id', $selectedUnitId)->get()->getRowArray();
            $selectedUnitName = $unitRow['name'] ?? null;
        }

        // 2. Fetch all ratee folders in this cycle
        $cycleFolders = [];
        $supervisorCollegeName = null;
        $scopedUnitIds = [];
        $isChairScope = false;

        // Role Scoping for Supervisors:
        if ($sysRole === 'Supervisor') {
            $ownPlantilla  = $userModel->getActivePlantillaDetails($userId);
            if ($ownPlantilla) {
                $posTitle = strtolower($ownPlantilla['position'] ?? '');
                $isChairScope = str_contains($posTitle, 'chair') || str_contains($posTitle, 'head');
                
                if ($isChairScope) {
                    // Department Chair: Scope strictly to own department
                    $scopedUnitIds = [(int)$ownPlantilla['unit_id']];
                    $supervisorCollegeName = $ownPlantilla['department'] ?? 'Department';
                } else {
                    // College Dean: Scope to college and all sub-departments
                    $scopedUnitIds = $unitModel->getDescendantIds([$ownPlantilla['unit_id']]);
                    $scopedUnitIds[] = (int)$ownPlantilla['unit_id'];
                    $supervisorCollegeName = $ownPlantilla['department'] ?? 'College';
                }
            }
            $scopedUnitIds = array_unique(array_filter($scopedUnitIds));
        }

        $targetFolderIds = [];
        if ($activeCycle) {
            $descendantIds = $folderModel->getAllDescendantFolderIds($activeCycle['id']);
            $targetFolderIds = !empty($descendantIds) ? $descendantIds : [$activeCycle['id']];
        }

        $cycleFolders = $this->gatherCycleFolders($activeCycle, $selectedUnitId, $sysRole, $userId, $allUnits, $scopedUnitIds, $supervisorCollegeName);

        // 3. Compute Metrics
        $totalPersonnel = count($cycleFolders);
        $ratingsList = [];
        $collegeDepartments = [];

        $targetApprovedCount   = 0;
        $targetPendingCount    = 0;
        $targetDraftCount      = 0;
        $targetReturnedCount   = 0;

        $evalCompletedCount    = 0;
        $evalActionCount       = 0; // to evaluate, evaluated
        $evalSubmittedCount    = 0;
        $evalDraftCount        = 0; // draft, pending target phase
        $evalReturnedCount     = 0; // reevaluate, twg_disapproved

        foreach ($cycleFolders as $f) {
            if (!empty($f['department'])) {
                $collegeDepartments[$f['department']] = $f['department'];
            }

            if ($f['target_state'] === 'approved') {
                $targetApprovedCount++;
            } elseif ($f['target_state'] === 'submitted') {
                $targetPendingCount++;
            } elseif ($f['target_state'] === 'returned') {
                $targetReturnedCount++;
            } else {
                $targetDraftCount++;
            }

            if ($f['eval_state'] === 'approved' || $f['eval_state'] === 'completed') {
                $evalCompletedCount++;
            } elseif ($f['eval_state'] === 'evaluating') {
                $evalActionCount++;
            } elseif ($f['eval_state'] === 'submitted') {
                $evalSubmittedCount++;
            } elseif ($f['eval_state'] === 'returned') {
                $evalReturnedCount++;
            } else {
                $evalDraftCount++;
            }

            if ($f['rating_num'] !== null) {
                $ratingsList[] = $f['rating_num'];
            }
        }
        ksort($collegeDepartments);

        // 4. CSC Adjectival Rating Breakdown (CSC MC No. 6, s. 2012)
        $cscDistribution = [
            'outstanding' => [
                'label' => 'Outstanding',
                'range' => '4.50 – 5.00',
                'hex'   => '#10b981', // Emerald
                'count' => 0,
                'pct'   => 0
            ],
            'very_satisfactory' => [
                'label' => 'Very Satisfactory',
                'range' => '3.50 – 4.49',
                'hex'   => '#3b82f6', // Blue
                'count' => 0,
                'pct'   => 0
            ],
            'satisfactory' => [
                'label' => 'Satisfactory',
                'range' => '2.50 – 3.49',
                'hex'   => '#f59e0b', // Amber
                'count' => 0,
                'pct'   => 0
            ],
            'unsatisfactory' => [
                'label' => 'Unsatisfactory',
                'range' => '1.50 – 2.49',
                'hex'   => '#f97316', // Orange
                'count' => 0,
                'pct'   => 0
            ],
            'poor' => [
                'label' => 'Poor',
                'range' => 'Below 1.50',
                'hex'   => '#ef4444', // Red
                'count' => 0,
                'pct'   => 0
            ]
        ];

        foreach ($ratingsList as $score) {
            if ($score >= 4.50) {
                $cscDistribution['outstanding']['count']++;
            } elseif ($score >= 3.50) {
                $cscDistribution['very_satisfactory']['count']++;
            } elseif ($score >= 2.50) {
                $cscDistribution['satisfactory']['count']++;
            } elseif ($score >= 1.50) {
                $cscDistribution['unsatisfactory']['count']++;
            } else {
                $cscDistribution['poor']['count']++;
            }
        }

        $totalRated = count($ratingsList);
        foreach ($cscDistribution as &$tier) {
            $tier['pct'] = $totalRated > 0 ? round(($tier['count'] / $totalRated) * 100, 1) : 0;
        }
        unset($tier);

        // Overall Average Rating
        $overallAverage = $totalRated > 0 ? round(array_sum($ratingsList) / $totalRated, 2) : 0;
        $adjectivalLabel = 'Not Yet Rated';
        $adjectivalBadgeClass = 'bg-zinc-500/15 text-zinc-600 dark:text-zinc-400 border-zinc-500/30';
        if ($overallAverage >= 4.50) {
            $adjectivalLabel = 'Outstanding';
            $adjectivalBadgeClass = 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border-emerald-500/30';
        } elseif ($overallAverage >= 3.50) {
            $adjectivalLabel = 'Very Satisfactory';
            $adjectivalBadgeClass = 'bg-blue-500/15 text-blue-600 dark:text-blue-400 border-blue-500/30';
        } elseif ($overallAverage >= 2.50) {
            $adjectivalLabel = 'Satisfactory';
            $adjectivalBadgeClass = 'bg-amber-500/15 text-amber-600 dark:text-amber-400 border-amber-500/30';
        } elseif ($overallAverage >= 1.50) {
            $adjectivalLabel = 'Unsatisfactory';
            $adjectivalBadgeClass = 'bg-orange-500/15 text-orange-600 dark:text-orange-400 border-orange-500/30';
        } elseif ($overallAverage > 0) {
            $adjectivalLabel = 'Poor';
            $adjectivalBadgeClass = 'bg-red-500/15 text-red-600 dark:text-red-400 border-red-500/30';
        }

        // Compliance Percentages
        $targetComplianceRate = $totalPersonnel > 0 ? round(($targetApprovedCount / $totalPersonnel) * 100, 1) : 0;
        $evalCompletionRate   = $totalPersonnel > 0 ? round(($evalCompletedCount / $totalPersonnel) * 100, 1) : 0;

        // 5. College & Department Leaderboard
        $deptLeaderboard = [];
        foreach ($cycleFolders as $f) {
            $dept = $f['department'];
            if (!isset($deptLeaderboard[$dept])) {
                $deptLeaderboard[$dept] = [
                    'name'             => $dept,
                    'headcount'        => 0,
                    'target_approved'  => 0,
                    'revisions_needed' => 0,
                    'eval_completed'   => 0,
                    'ratings'          => [],
                    'average_rating'   => 0,
                    'compliance_pct'   => 0,
                    'status_badge'     => 'In Progress',
                    'badge_class'      => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-info-500/10 dark:text-blue-400 dark:border-info-500/20'
                ];
            }
            $deptLeaderboard[$dept]['headcount']++;

            if (in_array($f['folder_status'], [
                FolderStatus::TARGET_APPROVED->value,
                FolderStatus::SUBMITTED->value,
                FolderStatus::TO_EVALUATE->value,
                FolderStatus::EVALUATED->value,
                FolderStatus::APPROVED->value,
                FolderStatus::TWG_APPROVED->value,
                FolderStatus::TWG_DISAPPROVED->value
            ])) {
                $deptLeaderboard[$dept]['target_approved']++;
            }

            if (in_array($f['folder_status'], [
                FolderStatus::TARGET_RETURNED->value,
                FolderStatus::TARGET_UNAPPROVED->value,
                FolderStatus::REEVALUATE->value,
                FolderStatus::TWG_DISAPPROVED->value
            ])) {
                $deptLeaderboard[$dept]['revisions_needed']++;
            }

            if (in_array($f['folder_status'], [FolderStatus::APPROVED->value, FolderStatus::TWG_APPROVED->value])) {
                $deptLeaderboard[$dept]['eval_completed']++;
            }

            if ($f['rating_num'] !== null) {
                $deptLeaderboard[$dept]['ratings'][] = $f['rating_num'];
            }
        }

        foreach ($deptLeaderboard as &$d) {
            $d['compliance_pct'] = $d['headcount'] > 0 ? round(($d['eval_completed'] / $d['headcount']) * 100) : 0;
            $d['average_rating'] = !empty($d['ratings']) ? round(array_sum($d['ratings']) / count($d['ratings']), 2) : 0;

            if ($d['revisions_needed'] > 0) {
                $d['status_badge'] = $d['revisions_needed'] . ' Revision' . ($d['revisions_needed'] > 1 ? 's' : '');
                $d['badge_class']  = 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-400 dark:border-amber-800/80';
            } elseif ($d['compliance_pct'] >= 100) {
                $d['status_badge'] = '100% Compliant';
                $d['badge_class']  = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-[#102a1e] dark:text-emerald-400 dark:border-[#1b4330]';
            } elseif ($d['compliance_pct'] >= 50) {
                $d['status_badge'] = 'On Track';
                $d['badge_class']  = 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-info-500/10 dark:text-blue-400 dark:border-info-500/20';
            } else {
                $d['status_badge'] = 'Action Needed';
                $d['badge_class']  = 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-danger-500/10 dark:text-rose-400 dark:border-danger-500/20';
            }
        }
        unset($d);

        usort($deptLeaderboard, function ($a, $b) {
            if ($b['compliance_pct'] === $a['compliance_pct']) {
                if ($b['target_approved'] === $a['target_approved']) {
                    return strcmp($a['name'], $b['name']);
                }
                return $b['target_approved'] <=> $a['target_approved'];
            }
            return $b['compliance_pct'] <=> $a['compliance_pct'];
        });

        // Total MOV Attachments in this cycle (Stage 2: Performance Monitoring & Coaching evidence)
        $totalMovCount = 0;
        if (!empty($targetFolderIds)) {
            $totalMovCount = $db->table('document_attachments da')
                ->join('documents d', 'd.id = da.document_id')
                ->whereIn('d.document_folder_id', $targetFolderIds)
                ->countAllResults();
        }

        // Stage 4 metrics: PBB / Incentive Eligible (Outstanding & Very Satisfactory) and Development Needed
        $pbbEligibleCount = ($cscDistribution['outstanding']['count'] ?? 0) + ($cscDistribution['very_satisfactory']['count'] ?? 0);
        $devNeededCount   = ($cscDistribution['satisfactory']['count'] ?? 0) + ($cscDistribution['unsatisfactory']['count'] ?? 0) + ($cscDistribution['poor']['count'] ?? 0);

        // Employment Status breakdown
        $empStatusCounts = [
            'permanent'   => 0,
            'temporary'   => 0,
            'casual'      => 0,
            'contractual' => 0,
        ];
        foreach ($cycleFolders as $cf) {
            $st = strtolower($cf['employment_status'] ?? 'permanent');
            if (isset($empStatusCounts[$st])) {
                $empStatusCounts[$st]++;
            } else {
                $empStatusCounts['permanent']++;
            }
        }

        return view('components/app_shell', [
            'context'          => 'dashboard',
            'sidebarFolders'   => $rootFolders,
            'selectedFolderId' => $activeCycle['id'] ?? null,
            'sidebarTitle'     => 'Evaluation Folders',
            'mainView'         => 'dashboard/index',
            'mainData'         => [
                'sysRole'              => $sysRole,
                'rootFolders'          => $rootFolders,
                'activeCycle'          => $activeCycle,
                'selectedUnitId'       => $selectedUnitId,
                'selectedUnitName'     => $selectedUnitName,
                'allUnits'             => $allUnits,
                'totalPersonnel'       => $totalPersonnel,
                'totalRated'           => $totalRated,
                'overallAverage'       => $overallAverage,
                'adjectivalLabel'      => $adjectivalLabel,
                'adjectivalBadgeClass' => $adjectivalBadgeClass,
                'targetComplianceRate' => $targetComplianceRate,
                'evalCompletionRate'   => $evalCompletionRate,
                'cscDistribution'      => $cscDistribution,
                'empStatusCounts'      => $empStatusCounts,
                'isChairScope'          => $isChairScope,
                'supervisorCollegeName' => $supervisorCollegeName,
                'collegeDepartments'    => $collegeDepartments,
                'cycleFolders'          => $cycleFolders,
                'pipeline'             => [
                    'target' => [
                        'approved' => $targetApprovedCount,
                        'pending'  => $targetPendingCount,
                        'draft'    => $targetDraftCount,
                        'returned' => $targetReturnedCount
                    ],
                    'evaluation' => [
                        'completed' => $evalCompletedCount,
                        'action'    => $evalActionCount,
                        'submitted' => $evalSubmittedCount,
                        'draft'     => $evalDraftCount,
                        'returned'  => $evalReturnedCount,
                        'pending'   => $evalDraftCount
                    ],
                    // Civil Service Commission (CSC MC No. 6, s. 2012) 4-Stage SPMS Lifecycle
                    'stage1' => [
                        'name'     => 'Performance Planning & Commitment',
                        'sub'      => 'Target Setting Phase',
                        'approved' => $targetApprovedCount,
                        'pending'  => $targetPendingCount,
                        'draft'    => $targetDraftCount,
                        'returned' => $targetReturnedCount,
                    ],
                    'stage2' => [
                        'name'             => 'Performance Monitoring & Coaching',
                        'sub'              => 'Execution & Evidence Phase',
                        'active_execution' => $targetApprovedCount,
                        'mov_count'        => $totalMovCount,
                        'coaching_notes'   => $targetReturnedCount + $evalReturnedCount,
                        'execution_rate'   => $totalPersonnel > 0 ? round(($targetApprovedCount / $totalPersonnel) * 100) : 0,
                    ],
                    'stage3' => [
                        'name'       => 'Performance Review & Evaluation',
                        'sub'        => 'Accomplishment Phase',
                        'completed'  => $evalCompletedCount,
                        'evaluating' => $evalActionCount,
                        'submitted'  => $evalSubmittedCount,
                        'draft'      => $evalDraftCount,
                        'returned'   => $evalReturnedCount,
                    ],
                    'stage4' => [
                        'name'         => 'Performance Rewarding & Development',
                        'sub'          => 'Incentives & Finalization Phase',
                        'pbb_eligible' => $pbbEligibleCount,
                        'certified'    => $evalCompletedCount,
                        'export_ready' => $evalCompletedCount,
                        'dev_needed'   => $devNeededCount,
                    ]
                ]
            ]
        ]);
    }

    /**
     * GET /dashboard/export-masterlist or GET /dashboard/export-masterlist/{cycleId}
     * Generates and downloads the official CSC Performance Evaluation Masterlist spreadsheet.
     */
    public function exportMasterlist(?string $cycleId = null)
    {
        $db = $this->db ?? \Config\Database::connect();
        $userId  = session()->get('user_id');
        $sysRole = session()->get('role');

        $folderModel = new DocumentFolderModel();
        $unitModel   = new UnitModel();
        $userModel   = new UserModel();

        $activeCycle = null;
        if ($cycleId) {
            $activeCycle = $folderModel->find($cycleId);
        }
        if (!$activeCycle) {
            $activeCycle = $folderModel->where('parent_folder_id IS NULL')
                ->where('deleted_at IS NULL')
                ->orderBy('created_at', 'DESC')
                ->first();
        }

        $selectedUnitId = $this->request->getGet('unit_id');
        $selectedUnitId = (!empty($selectedUnitId) && is_numeric($selectedUnitId)) ? (int)$selectedUnitId : null;
        $selectedUnitName = null;
        if ($selectedUnitId) {
            $unitRow = $db->table('units')->where('id', $selectedUnitId)->get()->getRowArray();
            $selectedUnitName = $unitRow['name'] ?? null;
        }

        $allUnits = $db->table('units u')
            ->select('u.id, u.name, u.parent_id, COUNT(p.id) as headcount')
            ->join('plantillas p', 'p.unit_id = u.id AND p.ended_at IS NULL', 'inner')
            ->groupBy('u.id')
            ->orderBy('u.name', 'ASC')
            ->get()->getResultArray();

        $scopedUnitIds = null;
        $supervisorCollegeName = null;
        if ($sysRole === 'Supervisor') {
            $ownPlantilla = $userModel->getActivePlantillaDetails($userId);
            if ($ownPlantilla) {
                $posTitle = strtolower($ownPlantilla['position'] ?? '');
                $isChairScope = str_contains($posTitle, 'chair') || str_contains($posTitle, 'head');
                if ($isChairScope) {
                    $scopedUnitIds = [(int)$ownPlantilla['unit_id']];
                    $supervisorCollegeName = $ownPlantilla['department'] ?? 'Department';
                } else {
                    $scopedUnitIds = $unitModel->getDescendantIds([$ownPlantilla['unit_id']]);
                    $scopedUnitIds[] = (int)$ownPlantilla['unit_id'];
                    $supervisorCollegeName = $ownPlantilla['department'] ?? 'College';
                }
            }
            $scopedUnitIds = array_unique(array_filter($scopedUnitIds ?? []));
        }

        $cycleFolders = $this->gatherCycleFolders($activeCycle, $selectedUnitId, $sysRole, $userId, $allUnits, $scopedUnitIds, $supervisorCollegeName);

        $cycleTitle = $activeCycle['title'] ?? 'Active Evaluation Cycle';
        \App\Libraries\CscExcelExporter::exportMasterlist($cycleTitle, $cycleFolders, [], $selectedUnitName);
    }

    /**
     * Gathers all personnel records and folder states for the specified cycle and scope.
     */
    private function gatherCycleFolders($activeCycle, ?int $selectedUnitId, string $sysRole, int $userId, array $allUnits = [], ?array $scopedUnitIds = null, ?string $supervisorCollegeName = null): array
    {
        $db = $this->db ?? \Config\Database::connect();
        $folderModel = new DocumentFolderModel();
        $unitModel   = new UnitModel();

        $cycleFolders = [];

        if ($activeCycle) {
            $descendantIds = $folderModel->getAllDescendantFolderIds($activeCycle['id']);
            $targetFolderIds = !empty($descendantIds) ? $descendantIds : [$activeCycle['id']];

            $builder = $db->table('document_folders df')
                ->select("df.id as folder_id, df.user_id, df.title as folder_title, df.final_rating, df.status as folder_status, df.updated_at, df.created_at,
                          u.first_name, u.last_name, u.email, u.doc_type,
                          p.employment_status,
                          pos.title as position, pos.is_teaching,
                          un.id as unit_id, un.name as department")
                ->join('users u', 'u.id = df.user_id')
                ->join('plantillas p', 'p.user_id = u.id AND p.ended_at IS NULL', 'left')
                ->join('positions pos', 'pos.id = p.position_id', 'left')
                ->join('units un', 'un.id = p.unit_id', 'left')
                ->whereIn('df.id', $targetFolderIds)
                ->where('df.deleted_at IS NULL');

            if ($selectedUnitId) {
                $filteredUnitIds = $unitModel->getDescendantIds([$selectedUnitId]);
                $filteredUnitIds[] = $selectedUnitId;
                $builder->whereIn('un.id', array_unique($filteredUnitIds));
            }

            if ($sysRole === 'Supervisor') {
                if (!empty($scopedUnitIds)) {
                    $builder->whereIn('un.id', $scopedUnitIds);
                } else {
                    $builder->join('evaluation_routings er_sc', 'er_sc.folder_id = df.id')
                            ->where('er_sc.evaluator_id', $userId);
                }
            } elseif (!in_array($sysRole, ['Admin', 'HR', 'TWG'])) {
                $builder->where('df.user_id', $userId);
            }

            $builder->groupBy('df.id');
            $cycleFolders = $builder->get()->getResultArray();
        }

        // Discover active employees who haven't created a folder yet
        $unstartedScopedUnitIds = [];
        if ($sysRole === 'Supervisor' && !empty($scopedUnitIds)) {
            $unstartedScopedUnitIds = $scopedUnitIds;
        } elseif ($sysRole === 'Admin') {
            if ($selectedUnitId) {
                $filteredUnitIds = $unitModel->getDescendantIds([$selectedUnitId]);
                $filteredUnitIds[] = $selectedUnitId;
                $unstartedScopedUnitIds = array_unique($filteredUnitIds);
            } elseif (!empty($allUnits)) {
                $unstartedScopedUnitIds = array_column($allUnits, 'id');
            }
        }

        if (!empty($unstartedScopedUnitIds)) {
            $existingUserIds = array_column($cycleFolders, 'user_id');
            $activeEmployees = $db->table('users u')
                ->select("u.id as user_id, u.first_name, u.last_name, u.email,
                          p.employment_status,
                          pos.title as position, pos.is_teaching,
                          un.id as unit_id, un.name as department")
                ->join('plantillas p', 'p.user_id = u.id AND p.ended_at IS NULL', 'inner')
                ->join('positions pos', 'pos.id = p.position_id', 'left')
                ->join('units un', 'un.id = p.unit_id', 'left')
                ->whereIn('un.id', $unstartedScopedUnitIds)
                ->where('u.is_active', 1)
                ->get()->getResultArray();

            foreach ($activeEmployees as $ce) {
                if (!in_array($ce['user_id'], $existingUserIds)) {
                    $cycleFolders[] = [
                        'folder_id'         => null,
                        'user_id'           => $ce['user_id'],
                        'folder_title'      => 'No Folder Created',
                        'final_rating'      => null,
                        'folder_status'     => 'unstarted',
                        'updated_at'        => null,
                        'created_at'        => null,
                        'first_name'        => $ce['first_name'],
                        'last_name'         => $ce['last_name'],
                        'email'             => $ce['email'],
                        'employment_status' => $ce['employment_status'] ?? 'Permanent',
                        'doc_type'          => (($ce['is_teaching'] ?? 0) == 1 ? 'IPCR' : 'IPERF'),
                        'position'          => $ce['position'] ?? 'Faculty / Staff',
                        'is_teaching'       => $ce['is_teaching'] ?? 0,
                        'unit_id'           => $ce['unit_id'],
                        'department'        => $ce['department'] ?? ($supervisorCollegeName ?? 'General Administration')
                    ];
                }
            }
        }

        // Tag every record with labels and badges
        foreach ($cycleFolders as &$f) {
            $f['id']           = $f['folder_id'] ?? null;
            $f['full_name']    = trim(($f['first_name'] ?? '') . ' ' . ($f['last_name'] ?? '')) ?: 'User #' . ($f['user_id'] ?? 0);
            $f['ratee_name']   = $f['full_name'];
            $f['ratee_email']  = $f['email'] ?? '';
            $f['is_unstarted'] = empty($f['folder_id']) || ($f['folder_status'] ?? '') === 'unstarted';
            $f['department']   = !empty($f['department']) ? $f['department'] : 'General Administration / Unassigned';
            $f['position']     = !empty($f['position']) ? $f['position'] : 'Faculty / Staff';
            $f['doc_type']     = !empty($f['doc_type']) ? strtoupper($f['doc_type']) : (($f['is_teaching'] ?? 0) == 1 ? 'IPCR' : 'IPERF');

            $empStatus = !empty($f['employment_status']) ? trim($f['employment_status']) : 'Permanent';
            $f['employment_status'] = $empStatus;

            // Badges for Employment Status
            switch (strtolower($empStatus)) {
                case 'temporary':
                    $f['emp_status_badge'] = 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-400 dark:border-amber-800/80';
                    break;
                case 'casual':
                    $f['emp_status_badge'] = 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/60 dark:text-sky-400 dark:border-sky-800/80';
                    break;
                case 'contractual':
                    $f['emp_status_badge'] = 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/60 dark:text-purple-400 dark:border-purple-800/80';
                    break;
                case 'coterminous':
                    $f['emp_status_badge'] = 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-400 dark:border-indigo-800/80';
                    break;
                case 'permanent':
                default:
                    $f['emp_status_badge'] = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-[#102a1e] dark:text-emerald-400 dark:border-[#1b4330]';
                    break;
            }

            $status = $f['folder_status'];

            // Target stage tracking & tagging
            if (in_array($status, [FolderStatus::TARGET_APPROVED->value, FolderStatus::SUBMITTED->value, FolderStatus::TO_EVALUATE->value, FolderStatus::EVALUATED->value, FolderStatus::APPROVED->value, FolderStatus::TWG_APPROVED->value, FolderStatus::TWG_DISAPPROVED->value, FolderStatus::UNEVALUATED->value])) {
                $f['target_state'] = 'approved';
                $f['target_label'] = 'Approved';
                $f['target_badge'] = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-[#102a1e] dark:text-emerald-400 dark:border-[#1b4330]';
            } elseif ($status === FolderStatus::PENDING_TARGET_APPROVAL->value) {
                $f['target_state'] = 'submitted';
                $f['target_label'] = 'Submitted (Pending Review)';
                $f['target_badge'] = 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-info-500/10 dark:text-blue-400 dark:border-info-500/20';
            } elseif ($status === FolderStatus::DRAFT_TARGET->value) {
                $f['target_state'] = 'draft';
                $f['target_label'] = 'Not Submitted (Draft)';
                $f['target_badge'] = 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-danger-500/10 dark:text-rose-400 dark:border-danger-500/20';
            } elseif (in_array($status, [FolderStatus::TARGET_RETURNED->value, FolderStatus::TARGET_UNAPPROVED->value])) {
                $f['target_state'] = 'returned';
                $f['target_label'] = 'Needs Revision';
                $f['target_badge'] = 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-400 dark:border-amber-800/80';
            } else {
                $f['target_state'] = 'draft';
                $f['target_label'] = 'Not Started (No Folder)';
                $f['target_badge'] = 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-danger-500/10 dark:text-rose-400 dark:border-danger-500/20';
            }

            // Evaluation stage tracking & tagging
            if (in_array($status, [FolderStatus::APPROVED->value, FolderStatus::TWG_APPROVED->value])) {
                $f['eval_state'] = 'approved';
                $f['eval_label'] = 'Completed & Approved';
                $f['eval_badge'] = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-[#102a1e] dark:text-emerald-400 dark:border-[#1b4330]';
            } elseif ($status === FolderStatus::UNEVALUATED->value) {
                $f['eval_state'] = 'completed';
                $f['eval_label'] = 'Unevaluated';
                $f['eval_badge'] = 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-zinc-800/50 dark:text-zinc-400 dark:border-zinc-700';
            } elseif ($status === FolderStatus::TWG_DISAPPROVED->value) {
                $f['eval_state'] = 'returned';
                $f['eval_label'] = 'Disapproved by TWG';
                $f['eval_badge'] = 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-danger-500/10 dark:text-rose-400 dark:border-danger-500/20';
            } elseif (in_array($status, [FolderStatus::TO_EVALUATE->value, FolderStatus::EVALUATED->value])) {
                $f['eval_state'] = 'evaluating';
                $f['eval_label'] = 'Submitted (Evaluating)';
                $f['eval_badge'] = 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-highlight-500/20 dark:text-highlight-400 dark:border-highlight-500/20';
            } elseif ($status === FolderStatus::SUBMITTED->value) {
                $f['eval_state'] = 'submitted';
                $f['eval_label'] = 'Submitted';
                $f['eval_badge'] = 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-info-500/10 dark:text-blue-400 dark:border-info-500/20';
            } elseif ($status === FolderStatus::REEVALUATE->value) {
                $f['eval_state'] = 'returned';
                $f['eval_label'] = 'Needs Revision';
                $f['eval_badge'] = 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-400 dark:border-amber-800/80';
            } elseif ($status === FolderStatus::DRAFT->value) {
                $f['eval_state'] = 'draft';
                $f['eval_label'] = 'Not Submitted (Draft)';
                $f['eval_badge'] = 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-danger-500/10 dark:text-rose-400 dark:border-danger-500/20';
            } else {
                $f['eval_state'] = 'draft';
                $f['eval_label'] = 'Pending Target Phase';
                $f['eval_badge'] = 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-zinc-800/50 dark:text-zinc-400 dark:border-zinc-700';
            }

            // High-level filter tag for Dean's quick buttons:
            if ($f['target_state'] === 'draft' || ($f['target_state'] === 'approved' && $f['eval_state'] === 'draft')) {
                $f['submission_filter'] = 'missing';
            } elseif ($f['target_state'] === 'submitted' || $f['eval_state'] === 'submitted' || $f['eval_state'] === 'evaluating') {
                $f['submission_filter'] = 'review';
            } elseif ($f['target_state'] === 'returned' || $f['eval_state'] === 'returned') {
                $f['submission_filter'] = 'revision';
            } elseif ($f['eval_state'] === 'approved' || $f['eval_state'] === 'completed') {
                $f['submission_filter'] = 'completed';
            } else {
                $f['submission_filter'] = 'all';
            }

            // Rating capture & Adjectival categorization
            if ($f['final_rating'] !== null && (float)$f['final_rating'] > 0) {
                $score = round((float)$f['final_rating'], 2);
                $f['rating_num'] = $score;

                if ($score >= 4.50) {
                    $f['adjectival_label'] = 'Outstanding';
                    $f['adjectival_badge'] = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-400 dark:border-emerald-500/30';
                    $f['pbb_status']       = 'Eligible (O)';
                    $f['pbb_badge']        = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-400 dark:border-emerald-500/30';
                } elseif ($score >= 3.50) {
                    $f['adjectival_label'] = 'Very Satisfactory';
                    $f['adjectival_badge'] = 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-info-500/15 dark:text-blue-400 dark:border-info-500/30';
                    $f['pbb_status']       = 'Eligible (VS)';
                    $f['pbb_badge']        = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-400 dark:border-emerald-500/30';
                } elseif ($score >= 2.50) {
                    $f['adjectival_label'] = 'Satisfactory';
                    $f['adjectival_badge'] = 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/15 dark:text-amber-400 dark:border-amber-500/30';
                    $f['pbb_status']       = 'Ineligible';
                    $f['pbb_badge']        = 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700';
                } elseif ($score >= 1.50) {
                    $f['adjectival_label'] = 'Unsatisfactory';
                    $f['adjectival_badge'] = 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-500/15 dark:text-orange-400 dark:border-orange-500/30';
                    $f['pbb_status']       = 'Ineligible';
                    $f['pbb_badge']        = 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-danger-500/15 dark:text-rose-400 dark:border-danger-500/30';
                } else {
                    $f['adjectival_label'] = 'Poor';
                    $f['adjectival_badge'] = 'bg-red-50 text-red-700 border-red-200 dark:bg-danger-500/15 dark:text-danger-400 dark:border-danger-500/30';
                    $f['pbb_status']       = 'Ineligible';
                    $f['pbb_badge']        = 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-danger-500/15 dark:text-rose-400 dark:border-danger-500/30';
                }
            } else {
                $f['rating_num']       = null;
                $f['adjectival_label'] = 'Not Yet Rated';
                $f['adjectival_badge'] = 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-zinc-800/60 dark:text-zinc-400 dark:border-zinc-700/60';
                $f['pbb_status']       = 'Pending';
                $f['pbb_badge']        = 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-zinc-800/60 dark:text-zinc-400 dark:border-zinc-700/60';
            }
        }
        unset($f);

        return $cycleFolders;
    }
}

