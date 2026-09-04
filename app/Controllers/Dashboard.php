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

        // 2. Fetch all ratee folders in this cycle
        $cycleFolders = [];
        if ($activeCycle) {
            $descendantIds = $folderModel->getAllDescendantFolderIds($activeCycle['id']);
            $targetFolderIds = !empty($descendantIds) ? $descendantIds : [$activeCycle['id']];

            $builder = $db->table('document_folders df')
                ->select("df.id as folder_id, df.user_id, df.title as folder_title, df.final_rating, df.status as folder_status, df.updated_at, df.created_at,
                          u.first_name, u.last_name, u.email, u.doc_type,
                          pos.title as position, pos.is_teaching,
                          un.id as unit_id, un.name as department")
                ->join('users u', 'u.id = df.user_id')
                ->join('plantillas p', 'p.user_id = u.id AND p.ended_at IS NULL', 'left')
                ->join('positions pos', 'pos.id = p.position_id', 'left')
                ->join('units un', 'un.id = p.unit_id', 'left')
                ->whereIn('df.id', $targetFolderIds)
                ->where('df.deleted_at IS NULL');

            // Role Scoping:
            if ($sysRole === 'Supervisor') {
                $ownPlantilla  = $userModel->getActivePlantillaDetails($userId);
                $scopedUnitIds = $ownPlantilla ? $unitModel->getDescendantIds([$ownPlantilla['unit_id']]) : [];
                if (!empty($scopedUnitIds)) {
                    $builder->whereIn('un.id', $scopedUnitIds);
                } else {
                    $builder->join('evaluation_routings er_sc', 'er_sc.folder_id = df.id')
                            ->where('er_sc.evaluator_id', $userId);
                }
            } elseif (!in_array($sysRole, ['Admin', 'HR', 'TWG'])) {
                // Standard employee / faculty sees personal overview
                $builder->where('df.user_id', $userId);
            }

            $builder->groupBy('df.id');
            $cycleFolders = $builder->get()->getResultArray();
        }

        // 3. Compute Metrics
        $totalPersonnel = count($cycleFolders);
        $ratingsList = [];

        $targetApprovedCount   = 0;
        $targetPendingCount    = 0;
        $targetDraftCount      = 0;
        $targetReturnedCount   = 0;

        $evalCompletedCount    = 0;
        $evalActionCount       = 0; // to evaluate, evaluated
        $evalSubmittedCount    = 0;
        $evalPendingCount      = 0; // draft, reevaluate

        foreach ($cycleFolders as &$f) {
            $f['full_name'] = trim(($f['first_name'] ?? '') . ' ' . ($f['last_name'] ?? '')) ?: 'User #' . $f['user_id'];
            $f['department'] = !empty($f['department']) ? $f['department'] : 'General Administration / Unassigned';
            $f['position']   = !empty($f['position']) ? $f['position'] : 'Faculty / Staff';

            $status = $f['folder_status'];

            // Target stage tracking
            if (in_array($status, [FolderStatus::TARGET_APPROVED->value, FolderStatus::SUBMITTED->value, FolderStatus::TO_EVALUATE->value, FolderStatus::EVALUATED->value, FolderStatus::APPROVED->value, FolderStatus::TWG_APPROVED->value])) {
                $targetApprovedCount++;
            } elseif ($status === FolderStatus::PENDING_TARGET_APPROVAL->value) {
                $targetPendingCount++;
            } elseif ($status === FolderStatus::DRAFT_TARGET->value) {
                $targetDraftCount++;
            } elseif (in_array($status, [FolderStatus::TARGET_RETURNED->value, FolderStatus::TARGET_UNAPPROVED->value])) {
                $targetReturnedCount++;
            }

            // Evaluation stage tracking
            if (in_array($status, [FolderStatus::APPROVED->value, FolderStatus::TWG_APPROVED->value])) {
                $evalCompletedCount++;
            } elseif (in_array($status, [FolderStatus::TO_EVALUATE->value, FolderStatus::EVALUATED->value])) {
                $evalActionCount++;
            } elseif ($status === FolderStatus::SUBMITTED->value) {
                $evalSubmittedCount++;
            } elseif (in_array($status, [FolderStatus::DRAFT->value, FolderStatus::REEVALUATE->value])) {
                $evalPendingCount++;
            }

            // Rating capture
            if ($f['final_rating'] !== null && (float)$f['final_rating'] > 0) {
                $score = round((float)$f['final_rating'], 2);
                $ratingsList[] = $score;
                $f['rating_num'] = $score;
            } else {
                $f['rating_num'] = null;
            }
        }
        unset($f);

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
                    'name'            => $dept,
                    'headcount'       => 0,
                    'target_approved' => 0,
                    'eval_completed'  => 0,
                    'ratings'         => [],
                    'average_rating'  => 0,
                    'compliance_pct'  => 0,
                    'status_badge'    => 'In Progress'
                ];
            }
            $deptLeaderboard[$dept]['headcount']++;

            if (in_array($f['folder_status'], [
                FolderStatus::TARGET_APPROVED->value,
                FolderStatus::SUBMITTED->value,
                FolderStatus::TO_EVALUATE->value,
                FolderStatus::EVALUATED->value,
                FolderStatus::APPROVED->value,
                FolderStatus::TWG_APPROVED->value
            ])) {
                $deptLeaderboard[$dept]['target_approved']++;
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

            if ($d['compliance_pct'] >= 100) {
                $d['status_badge'] = '100% Compliant';
            } elseif ($d['compliance_pct'] >= 50) {
                $d['status_badge'] = 'On Track';
            } else {
                $d['status_badge'] = 'Action Needed';
            }
        }
        unset($d);

        usort($deptLeaderboard, function ($a, $b) {
            if ($b['compliance_pct'] === $a['compliance_pct']) {
                return $b['average_rating'] <=> $a['average_rating'];
            }
            return $b['compliance_pct'] <=> $a['compliance_pct'];
        });

        // 6. Recent Reviews Feed
        $recentFolders = $cycleFolders;
        usort($recentFolders, function ($a, $b) {
            return strtotime($b['updated_at'] ?? $b['created_at']) <=> strtotime($a['updated_at'] ?? $a['created_at']);
        });
        $recentFolders = array_slice($recentFolders, 0, 7);

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
                'totalPersonnel'       => $totalPersonnel,
                'totalRated'           => $totalRated,
                'overallAverage'       => $overallAverage,
                'adjectivalLabel'      => $adjectivalLabel,
                'adjectivalBadgeClass' => $adjectivalBadgeClass,
                'targetComplianceRate' => $targetComplianceRate,
                'evalCompletionRate'   => $evalCompletionRate,
                'cscDistribution'      => $cscDistribution,
                'deptLeaderboard'      => $deptLeaderboard,
                'recentFolders'        => $recentFolders,
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
                        'pending'   => $evalPendingCount
                    ]
                ]
            ]
        ]);
    }
}
