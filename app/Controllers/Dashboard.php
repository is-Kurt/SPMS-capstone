<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DocumentFolderModel;
use App\Models\UnitModel;
use App\Models\UserModel;
use App\Models\PlantillaModel;
use App\Models\EvaluationRoutingModel;
use App\Enums\FolderStatus;
use App\Enums\StaffType;
use App\Models\EmploymentStatusModel;

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
     * GET /dashboard/archived or /dashboard/archived/{cycleId}
     * Displays archived evaluation cycles and analytics for Admin.
     */
    public function archived(?string $cycleId = null)
    {
        if (session()->get('role') !== 'Admin') {
            return redirect()->to(site_url('dashboard'));
        }

        return $this->index($cycleId, true);
    }

    /**
     * GET /dashboard or GET /dashboard/{cycleId}
     * Computes performance metrics, CSC distribution charts, and college leaderboards.
     */
    public function index(?string $cycleId = null, bool $isArchivedView = false)
    {
        $db = $this->db ?? \Config\Database::connect();
        $userId  = session()->get('user_id');
        $sysRole = session()->get('role');

        if ($isArchivedView && $sysRole !== 'Admin') {
            return redirect()->to(site_url('dashboard'));
        }

        $folderModel = new DocumentFolderModel();
        $unitModel   = new UnitModel();
        $userModel   = new UserModel();

        // 1. Identify all accessible top-level evaluation cycles (root folders)
        $rootFolders = $this->getAccessibleRootFolders($folderModel, (int)$userId, (string)$sysRole, $isArchivedView);

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
            if ($cycleId) {
                $base = $isArchivedView ? 'dashboard/archived/' : 'dashboard/';
                return redirect()->to(site_url($base . $activeCycle['id']));
            }
        }

        if ($cycleId && empty($rootFolders)) {
            $base = $isArchivedView ? 'dashboard/archived' : 'dashboard';
            return redirect()->to(site_url($base));
        }

        // 1.1 Load deadlines & schedule for active cycle
        $phaseSchedule = [
            'phase1' => [
                'timeframe'  => 'Schedule Not Set',
                'status'     => 'upcoming',
                'is_ongoing' => false,
                'label'      => 'Upcoming',
            ],
            'phase_mid' => [
                'timeframe'  => 'Ongoing Execution',
                'status'     => 'upcoming',
                'is_ongoing' => false,
                'label'      => 'Upcoming',
            ],
            'phase2' => [
                'timeframe'  => 'Schedule Not Set',
                'status'     => 'upcoming',
                'is_ongoing' => false,
                'label'      => 'Upcoming',
            ],
        ];

        if ($activeCycle && !empty($activeCycle['id'])) {
            $deadlineModel = new \App\Models\CycleDeadlineModel();
            $cycleDates = $deadlineModel->getCycleDatesFlat((string)$activeCycle['id']);
            $activeCycle = array_merge($activeCycle, $cycleDates);

            $tStartDate = $activeCycle['ipcr_target_start'] ?? $activeCycle['opcr_target_start'] ?? $activeCycle['dpcr_target_start'] ?? $activeCycle['iperf_target_start'] ?? null;
            $tEndDate   = $activeCycle['ipcr_target_end']   ?? $activeCycle['opcr_target_end']   ?? $activeCycle['dpcr_target_end']   ?? $activeCycle['iperf_target_end']   ?? null;
            $eStartDate = $activeCycle['ipcr_eval_start']   ?? $activeCycle['opcr_eval_start']   ?? $activeCycle['dpcr_eval_start']   ?? $activeCycle['iperf_eval_start']   ?? null;
            $eEndDate   = $activeCycle['ipcr_eval_end']     ?? $activeCycle['opcr_eval_end']     ?? $activeCycle['dpcr_eval_end']     ?? $activeCycle['iperf_eval_end']     ?? null;

            $formatTimeframe = static function (?string $start, ?string $end): string {
                if (empty($start) && empty($end)) {
                    return 'Schedule Not Set';
                }
                if (!empty($start) && !empty($end)) {
                    $tsStart = strtotime($start);
                    $tsEnd   = strtotime($end);
                    if ($tsStart && $tsEnd) {
                        if (date('Y-m', $tsStart) === date('Y-m', $tsEnd)) {
                            return date('M d', $tsStart) . ' – ' . date('d, Y', $tsEnd);
                        }
                        if (date('Y', $tsStart) === date('Y', $tsEnd)) {
                            return date('M d', $tsStart) . ' – ' . date('M d, Y', $tsEnd);
                        }
                        return date('M d, Y', $tsStart) . ' – ' . date('M d, Y', $tsEnd);
                    }
                }
                if (!empty($end)) {
                    return 'Until ' . date('M d, Y', strtotime($end));
                }
                return 'From ' . date('M d, Y', strtotime($start));
            };

            $nowStr = date('Y-m-d H:i:s');
            $isArchived = !empty($activeCycle['deleted_at']) || ($activeCycle['status'] ?? '') === 'archived';

            // Phase 1 (Target Commitment Phase) Status
            $p1Timeframe = $formatTimeframe($tStartDate, $tEndDate);
            $p1Status    = 'upcoming';
            $p1IsOngoing = false;
            $p1Label     = 'Upcoming';

            if ($isArchived) {
                $p1Status = 'closed';
                $p1Label  = 'Closed';
            } elseif (!empty($tEndDate) && $nowStr > $tEndDate) {
                $p1Status = 'closed';
                $p1Label  = 'Closed';
            } elseif (!empty($tStartDate) && $nowStr >= $tStartDate) {
                $p1Status    = 'ongoing';
                $p1IsOngoing = true;
                $p1Label     = 'Ongoing';
            } elseif (empty($tStartDate) && !empty($tEndDate) && $nowStr <= $tEndDate) {
                $p1Status    = 'ongoing';
                $p1IsOngoing = true;
                $p1Label     = 'Ongoing';
            }

            // Phase 2 (Performance Monitoring & Coaching Phase - Stage 2) Status
            $pMidTimeframe = ($tEndDate && $eStartDate) ? $formatTimeframe($tEndDate, $eStartDate) : 'Ongoing Execution';
            $pMidStatus    = 'upcoming';
            $pMidIsOngoing = false;
            $pMidLabel     = 'Upcoming';

            if ($isArchived) {
                $pMidStatus = 'closed';
                $pMidLabel  = 'Closed';
            } elseif (!empty($eEndDate) && $nowStr > $eEndDate) {
                $pMidStatus = 'completed';
                $pMidLabel  = 'Completed';
            } elseif (!empty($eStartDate) && $nowStr >= $eStartDate) {
                $pMidStatus = 'completed';
                $pMidLabel  = 'Completed';
            } elseif (!empty($tEndDate) && $nowStr > $tEndDate) {
                $pMidStatus    = 'ongoing';
                $pMidIsOngoing = true;
                $pMidLabel     = 'Active Execution';
            } elseif (!empty($tStartDate) && $nowStr >= $tStartDate) {
                $pMidStatus    = 'ongoing';
                $pMidIsOngoing = true;
                $pMidLabel     = 'Active Execution';
            }

            // Phase 3 (Accomplishment Report Phase - Stage 3) Status
            $p2Timeframe = $formatTimeframe($eStartDate, $eEndDate);
            $p2Status    = 'upcoming';
            $p2IsOngoing = false;
            $p2Label     = 'Upcoming';

            if ($isArchived) {
                $p2Status = 'closed';
                $p2Label  = 'Closed';
            } elseif (!empty($eEndDate) && $nowStr > $eEndDate) {
                $p2Status    = 'calibration';
                $p2IsOngoing = true; // Actively calibrating final ratings
                $p2Label     = 'Calibration';
            } elseif (!empty($eStartDate) && $nowStr >= $eStartDate) {
                $p2Status    = 'ongoing';
                $p2IsOngoing = true;
                $p2Label     = 'Ongoing';
            } elseif (!empty($tEndDate) && $nowStr > $tEndDate) {
                $p2Status    = 'monitoring';
                $p2IsOngoing = false;
                $p2Label     = 'Monitoring';
            }

            $phaseSchedule = [
                'phase1' => [
                    'timeframe'  => $p1Timeframe,
                    'status'     => $p1Status,
                    'is_ongoing' => $p1IsOngoing,
                    'label'      => $p1Label,
                    'start'      => $tStartDate,
                    'end'        => $tEndDate,
                ],
                'phase_mid' => [
                    'timeframe'  => $pMidTimeframe,
                    'status'     => $pMidStatus,
                    'is_ongoing' => $pMidIsOngoing,
                    'label'      => $pMidLabel,
                ],
                'phase2' => [
                    'timeframe'  => $p2Timeframe,
                    'status'     => $p2Status,
                    'is_ongoing' => $p2IsOngoing,
                    'label'      => $p2Label,
                    'start'      => $eStartDate,
                    'end'        => $eEndDate,
                ],
            ];
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
        $isPresidentScope = false;
        if ($sysRole === 'Supervisor') {
            $ownPlantilla  = $userModel->getActivePlantillaDetails($userId);
            if ($ownPlantilla) {
                $posTitle = strtolower($ownPlantilla['position'] ?? '');
                $userEmail = strtolower(session()->get('email') ?? '');
                $isPresident = session()->get('is_president') 
                            || (str_contains($posTitle, 'president') && !str_contains($posTitle, 'vice')) 
                            || str_contains($userEmail, 'president');

                if ($isPresident) {
                    $isPresidentScope = true;
                    $scopedUnitIds = []; // President has university-wide executive oversight
                    $supervisorCollegeName = 'Executive Oversight';
                } else {
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
            }
            $scopedUnitIds = array_unique(array_filter($scopedUnitIds));
        }

        $targetFolderIds = [];
        if ($activeCycle) {
            $isCycleArchived = !empty($activeCycle['deleted_at']) || ($activeCycle['status'] ?? '') === 'archived';
            $descendantIds = $folderModel->getAllDescendantFolderIds($activeCycle['id'], $isCycleArchived);
            $targetFolderIds = !empty($descendantIds) ? $descendantIds : [];
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
        $targetUnstartedCount  = 0;

        $evalCompletedCount    = 0;
        $evalActionCount       = 0; // to evaluate, evaluated
        $evalSubmittedCount    = 0;
        $evalDraftCount        = 0; // draft, pending target phase
        $evalReturnedCount     = 0; // reevaluate, twg_disapproved
        $evalUnstartedCount    = 0;

        foreach ($cycleFolders as $f) {
            if (!empty($f['department'])) {
                $collegeDepartments[$f['department']] = $f['department'];
            }

            $isUnstarted = ($f['folder_status'] ?? '') === 'unstarted' || empty($f['folder_id']);
            if ($isUnstarted) {
                $targetUnstartedCount++;
                $evalUnstartedCount++;
            }

            if ($f['target_state'] === 'approved') {
                $targetApprovedCount++;
            } elseif ($f['target_state'] === 'submitted' || $f['target_state'] === 'unapproved') {
                $targetPendingCount++;
            } elseif ($f['target_state'] === 'returned') {
                $targetReturnedCount++;
                $targetDraftCount++;
            } else {
                $targetDraftCount++;
            }

            if ($f['eval_state'] === 'approved' || $f['eval_state'] === 'completed') {
                $evalCompletedCount++;
            } elseif ($f['eval_state'] === 'evaluating') {
                $evalActionCount++;
                $evalSubmittedCount++;
            } elseif ($f['eval_state'] === 'submitted' || $f['eval_state'] === 'unrated') {
                $evalSubmittedCount++;
            } elseif ($f['eval_state'] === 'returned') {
                $evalReturnedCount++;
                $evalDraftCount++;
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

        // 5. College & Department Leaderboard and Hierarchical Unit Structure
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

            $cStatus = FolderStatus::canonical($f['folder_status'] ?? '');

            if ($cStatus->isEvaluationPhase() || $cStatus === FolderStatus::TARGET_APPROVED) {
                $deptLeaderboard[$dept]['target_approved']++;
            }

            if ($cStatus === FolderStatus::EVAL_APPROVED) {
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

        // 5.1 Build database-driven hierarchical organizational units & recursive compliance ratios
        $allUnitsList = $db->table('units')
            ->select('id, name, parent_id')
            ->orderBy('id', 'ASC')
            ->get()->getResultArray();

        $unitsById = [];
        $childrenByParent = [];
        foreach ($allUnitsList as $u) {
            $uId = (int)$u['id'];
            $pId = !empty($u['parent_id']) ? (int)$u['parent_id'] : 0;
            $unitsById[$uId] = [
                'id'        => $uId,
                'name'      => $u['name'],
                'parent_id' => $pId,
            ];
            $childrenByParent[$pId][] = $uId;
        }

        // Descendant IDs helper (returns self and all subtree unit IDs)
        $getDescendantIds = function (int $uId) use (&$childrenByParent, &$getDescendantIds): array {
            $res = [$uId];
            if (!empty($childrenByParent[$uId])) {
                foreach ($childrenByParent[$uId] as $cId) {
                    $res = array_merge($res, $getDescendantIds($cId));
                }
            }
            return array_unique($res);
        };

        // Ancestor IDs helper (returns parent, grandparent, etc. up to root)
        $getAncestorIds = function (int $uId) use (&$unitsById): array {
            $ancestors = [];
            $curr = $unitsById[$uId] ?? null;
            while ($curr && !empty($curr['parent_id']) && isset($unitsById[$curr['parent_id']])) {
                $ancestors[] = (int)$curr['parent_id'];
                $curr = $unitsById[$curr['parent_id']];
            }
            return $ancestors;
        };

        // Enrich cycle folders with unit ancestors for hierarchical masterlist filtering
        $foldersByUnit = [];
        foreach ($cycleFolders as &$cf) {
            $uId = !empty($cf['unit_id']) ? (int)$cf['unit_id'] : 0;
            $ancIds = $uId > 0 ? $getAncestorIds($uId) : [];
            $ancNames = [];
            foreach ($ancIds as $aId) {
                if (isset($unitsById[$aId])) {
                    $ancNames[] = $unitsById[$aId]['name'];
                }
            }
            $cf['ancestor_unit_ids']   = $ancIds;
            $cf['ancestor_unit_names'] = $ancNames;
            $foldersByUnit[$uId][] = $cf;
        }
        unset($cf);

        // Preorder DFS traversal to construct hierarchical units tree with accurate recursive metrics
        $hierarchicalUnits = [];
        $traverseTree = function (int $parentId, int $level) use (
            &$traverseTree, &$childrenByParent, &$unitsById, &$hierarchicalUnits,
            &$foldersByUnit, &$getDescendantIds, &$getAncestorIds
        ) {
            if (empty($childrenByParent[$parentId])) {
                return;
            }

            $childIds = $childrenByParent[$parentId];
            usort($childIds, function ($a, $b) use (&$unitsById) {
                return strcmp($unitsById[$a]['name'] ?? '', $unitsById[$b]['name'] ?? '');
            });

            foreach ($childIds as $cId) {
                $unit = $unitsById[$cId];
                $subtreeIds = $getDescendantIds($cId);
                $ancestorIds = $getAncestorIds($cId);

                $subtreeFolders = [];
                foreach ($subtreeIds as $subId) {
                    if (!empty($foldersByUnit[$subId])) {
                        foreach ($foldersByUnit[$subId] as $sf) {
                            $subtreeFolders[] = $sf;
                        }
                    }
                }

                $directFolders   = $foldersByUnit[$cId] ?? [];
                $headcount       = count($subtreeFolders);
                $directHeadcount = count($directFolders);

                // Only include units that have personnel either directly or in their descendants
                if ($headcount === 0) {
                    continue;
                }

                $targetApproved = 0;
                $evalCompleted  = 0;
                $ratings        = [];

                foreach ($subtreeFolders as $sf) {
                    $cStatus = FolderStatus::canonical($sf['folder_status'] ?? '');
                    if ($cStatus->isEvaluationPhase() || $cStatus === FolderStatus::TARGET_APPROVED) {
                        $targetApproved++;
                    }
                    if ($cStatus === FolderStatus::EVAL_APPROVED) {
                        $evalCompleted++;
                    }
                    if ($sf['rating_num'] !== null && (float)$sf['rating_num'] > 0) {
                        $ratings[] = (float)$sf['rating_num'];
                    }
                }

                $directTargetApproved = 0;
                $directEvalCompleted  = 0;
                $directRatings        = [];
                foreach ($directFolders as $df) {
                    $cStatus = FolderStatus::canonical($df['folder_status'] ?? '');
                    if ($cStatus->isEvaluationPhase() || $cStatus === FolderStatus::TARGET_APPROVED) {
                        $directTargetApproved++;
                    }
                    if ($cStatus === FolderStatus::EVAL_APPROVED) {
                        $directEvalCompleted++;
                    }
                    if ($df['rating_num'] !== null && (float)$df['rating_num'] > 0) {
                        $directRatings[] = (float)$df['rating_num'];
                    }
                }
                $directAverageRating = !empty($directRatings) ? round(array_sum($directRatings) / count($directRatings), 2) : 0;

                $compliancePct = $headcount > 0 ? round(($evalCompleted / $headcount) * 100) : 0;
                $averageRating = !empty($ratings) ? round(array_sum($ratings) / count($ratings), 2) : 0;

                if ($compliancePct >= 100) {
                    $statusBadge = '100% Compliant';
                    $badgeClass  = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-[#102a1e] dark:text-emerald-400 dark:border-[#1b4330]';
                } elseif ($compliancePct >= 50) {
                    $statusBadge = 'On Track';
                    $badgeClass  = 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-info-500/10 dark:text-blue-400 dark:border-info-500/20';
                } else {
                    $statusBadge = 'Action Needed';
                    $badgeClass  = 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-danger-500/10 dark:text-rose-400 dark:border-danger-500/20';
                }

                $hierarchicalUnits[] = [
                    'id'                     => $cId,
                    'name'                   => $unit['name'],
                    'parent_id'              => $unit['parent_id'],
                    'level'                  => $level,
                    'ancestor_ids'           => $ancestorIds,
                    'descendant_ids'         => $subtreeIds,
                    'headcount'              => $headcount,
                    'direct_headcount'       => $directHeadcount,
                    'target_approved'        => $targetApproved,
                    'revisions_needed'       => 0,
                    'eval_completed'         => $evalCompleted,
                    'compliance_pct'         => $compliancePct,
                    'average_rating'         => $averageRating,
                    'status_badge'           => $statusBadge,
                    'badge_class'            => $badgeClass,
                    'ratio'                  => $evalCompleted . '/' . $headcount,
                    'target_ratio'           => $targetApproved . '/' . $headcount,
                    'direct_target_approved' => $directTargetApproved,
                    'direct_eval_completed'  => $directEvalCompleted,
                    'direct_average_rating'  => $directAverageRating,
                    'children_ids'           => $childrenByParent[$cId] ?? [],
                    'has_children'           => !empty($childrenByParent[$cId]),
                ];

                // Recurse down children
                $traverseTree($cId, $level + 1);
            }
        };

        $traverseTree(0, 0);

        // Safety pass: Catch any unit with personnel not under tree root 0
        $visitedIds = array_column($hierarchicalUnits, 'id');
        foreach ($foldersByUnit as $uId => $uFolders) {
            if ($uId > 0 && !in_array($uId, $visitedIds, true)) {
                $uName = $unitsById[$uId]['name'] ?? ($uFolders[0]['department'] ?? 'Unit #' . $uId);
                $headcount = count($uFolders);
                $targetApproved = 0;
                $evalCompleted  = 0;
                $ratings        = [];
                foreach ($uFolders as $sf) {
                    $cStatus = FolderStatus::canonical($sf['folder_status'] ?? '');
                    if ($cStatus->isEvaluationPhase() || $cStatus === FolderStatus::TARGET_APPROVED) {
                        $targetApproved++;
                    }
                    if ($cStatus === FolderStatus::EVAL_APPROVED) {
                        $evalCompleted++;
                    }
                    if ($sf['rating_num'] !== null && (float)$sf['rating_num'] > 0) {
                        $ratings[] = (float)$sf['rating_num'];
                    }
                }
                $compliancePct = $headcount > 0 ? round(($evalCompleted / $headcount) * 100) : 0;
                $averageRating = !empty($ratings) ? round(array_sum($ratings) / count($ratings), 2) : 0;
                $hierarchicalUnits[] = [
                    'id'                     => $uId,
                    'name'                   => $uName,
                    'parent_id'              => 0,
                    'level'                  => 0,
                    'ancestor_ids'           => [],
                    'descendant_ids'         => [$uId],
                    'headcount'              => $headcount,
                    'direct_headcount'       => $headcount,
                    'target_approved'        => $targetApproved,
                    'revisions_needed'       => 0,
                    'eval_completed'         => $evalCompleted,
                    'compliance_pct'         => $compliancePct,
                    'average_rating'         => $averageRating,
                    'status_badge'           => ($compliancePct >= 100) ? '100% Compliant' : (($compliancePct >= 50) ? 'On Track' : 'Action Needed'),
                    'badge_class'            => ($compliancePct >= 100) ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-[#102a1e] dark:text-emerald-400 dark:border-[#1b4330]' : 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-danger-500/10 dark:text-rose-400 dark:border-danger-500/20',
                    'ratio'                  => $evalCompleted . '/' . $headcount,
                    'target_ratio'           => $targetApproved . '/' . $headcount,
                    'direct_target_approved' => $targetApproved,
                    'direct_eval_completed'  => $evalCompleted,
                    'direct_average_rating'  => $averageRating,
                    'children_ids'           => [],
                    'has_children'           => false,
                ];
            }
        }

        // Total MOV Attachments in this cycle (Stage 2: Performance Monitoring & Coaching evidence)
        $totalMovCount = 0;
        if (!empty($targetFolderIds)) {
            $totalMovCount = $db->table('document_attachments da')
                ->groupStart()
                    ->whereIn('da.folder_id', $targetFolderIds)
                    ->orWhereIn('da.document_id', $targetFolderIds)
                ->groupEnd()
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

        // 6. Top Performing Employees (Leaderboard sorted by numerical rating DESC)
        $ratedPersonnel = array_filter($cycleFolders, function ($f) {
            return !empty($f['rating_num']) && (float)$f['rating_num'] > 0;
        });
        usort($ratedPersonnel, function ($a, $b) {
            if ((float)$b['rating_num'] == (float)$a['rating_num']) {
                return strcmp($a['full_name'] ?? '', $b['full_name'] ?? '');
            }
            return ((float)$b['rating_num'] > (float)$a['rating_num']) ? 1 : -1;
        });
        $topPerformers = array_values($ratedPersonnel);

        // Mutually-exclusive lifecycle counts
        $stageCounts = [
            'all'          => count($cycleFolders),
            'target_phase' => 0,
            'eval_phase'   => 0,
            'completed'    => 0,
        ];
        $filterPositions = [];
        foreach ($cycleFolders as $cf) {
            $stage = $cf['lifecycle_stage'] ?? 'target_phase';
            if (isset($stageCounts[$stage])) {
                $stageCounts[$stage]++;
            }
            if (!empty($cf['position'])) {
                $filterPositions[$cf['position']] = $cf['position'];
            }
        }
        ksort($filterPositions);
        $employmentStatuses = (new EmploymentStatusModel())->orderBy('name', 'ASC')->findAll();
        $cascadedPersonnelCount = max(0, $totalPersonnel - $targetUnstartedCount);

        $mainData = [
            'sysRole'              => $sysRole,
            'isArchivedView'       => $isArchivedView,
            'rootFolders'          => $rootFolders,
            'activeCycle'          => $activeCycle,
            'phaseSchedule'        => $phaseSchedule,
            'selectedUnitId'       => $selectedUnitId,
            'selectedUnitName'     => $selectedUnitName,
            'allUnits'             => $allUnits,
            'allUnitsList'         => $allUnitsList,
            'scopedUnitIds'        => $scopedUnitIds,
            'totalPersonnel'       => $totalPersonnel,
            'totalRated'           => $totalRated,
            'totalMovCount'        => $totalMovCount,
            'overallAverage'       => $overallAverage,
            'adjectivalLabel'      => $adjectivalLabel,
            'adjectivalBadgeClass' => $adjectivalBadgeClass,
            'targetComplianceRate' => $targetComplianceRate,
            'evalCompletionRate'   => $evalCompletionRate,
            'cscDistribution'      => $cscDistribution,
            'deptLeaderboard'      => $deptLeaderboard,
            'hierarchicalUnits'    => $hierarchicalUnits,
            'topPerformers'        => $topPerformers,
            'empStatusCounts'      => $empStatusCounts,
            'stageCounts'          => $stageCounts,
            'filterPositions'      => array_values($filterPositions),
            'employmentStatuses'   => $employmentStatuses,
            'isChairScope'          => $isChairScope,
            'isPresidentScope'      => $isPresidentScope,
            'supervisorCollegeName' => $supervisorCollegeName,
            'collegeDepartments'    => $collegeDepartments,
            'cycleFolders'          => $cycleFolders,
            'pipeline'             => [
                'target' => [
                    'approved'  => $targetApprovedCount,
                    'pending'   => $targetPendingCount,
                    'submitted' => $targetPendingCount,
                    'draft'     => $targetDraftCount,
                    'unstarted' => $targetUnstartedCount,
                    'cascaded'  => $cascadedPersonnelCount,
                    'returned'  => $targetReturnedCount
                ],
                'monitoring' => [
                    'mov_count'     => $totalMovCount,
                    'active_folders'=> $cascadedPersonnelCount,
                    'cascaded'      => $cascadedPersonnelCount,
                    'unstarted'     => $targetUnstartedCount,
                ],
                'evaluation' => [
                    'completed' => $evalCompletedCount,
                    'approved'  => $evalCompletedCount,
                    'action'    => $evalActionCount,
                    'submitted' => $evalSubmittedCount,
                    'draft'     => $evalDraftCount,
                    'unstarted' => $evalUnstartedCount,
                    'cascaded'  => $cascadedPersonnelCount,
                    'returned'  => $evalReturnedCount,
                    'pending'   => $evalDraftCount
                ],
                // Civil Service Commission (CSC MC No. 6, s. 2012) 4-Stage SPMS Lifecycle
                'stage1' => [
                    'name'      => 'Performance Planning & Commitment',
                    'sub'       => 'Target Setting Phase',
                    'approved'  => $targetApprovedCount,
                    'pending'   => $targetPendingCount,
                    'draft'     => $targetDraftCount,
                    'unstarted' => $targetUnstartedCount,
                    'cascaded'  => $cascadedPersonnelCount,
                    'returned'  => $targetReturnedCount,
                ],
                'stage2' => [
                    'name'             => 'Performance Monitoring & Coaching',
                    'sub'              => 'Execution & Evidence Phase',
                    'active_execution' => $targetApprovedCount,
                    'active_folders'   => $cascadedPersonnelCount,
                    'mov_count'        => $totalMovCount,
                    'unstarted'        => $targetUnstartedCount,
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
                    'unstarted'  => $evalUnstartedCount,
                    'cascaded'   => $cascadedPersonnelCount,
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
        ];

        if (method_exists($this->request, 'isAJAX') && $this->request->isAJAX()) {
            if ($this->request->getHeaderLine('X-SPMS-Scope') === 'tab') {
                return $this->response->setJSON([
                    'sidebar'          => view('document/_folder_rows', [
                        'folders'          => $rootFolders,
                        'selectedFolderId' => $activeCycle['id'] ?? null,
                    ]),
                    'main'             => view('dashboard/index', $mainData),
                    'selectedFolderId' => $activeCycle['id'] ?? null,
                    'url'              => site_url(($isArchivedView ? 'dashboard/archived/' : 'dashboard/') . ($activeCycle['id'] ?? ''))
                ]);
            }

            return view('dashboard/index', $mainData);
        }

        return view('components/app_shell', [
            'context'          => 'dashboard',
            'maxWidthClass'    => 'max-w-[125rem] 2xl:max-w-[140rem]',
            'sidebarFolders'   => $rootFolders,
            'selectedFolderId' => $activeCycle['id'] ?? null,
            'sidebarTitle'     => $isArchivedView ? 'Archived Folders' : 'Evaluation Folders',
            'mainView'         => 'dashboard/index',
            'mainData'         => $mainData
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
            $foundCycle = $folderModel->find($cycleId);
            if ($foundCycle) {
                if (!empty($foundCycle['deleted_at'])) {
                    if ($sysRole === 'Admin') {
                        $activeCycle = $foundCycle;
                    }
                } else {
                    $rootFolders = $this->getAccessibleRootFolders($folderModel, (int)$userId, (string)$sysRole, false);
                    foreach ($rootFolders as $rf) {
                        if ((string)$rf['id'] === (string)$cycleId) {
                            $activeCycle = $rf;
                            break;
                        }
                    }
                }
            }
        }
        if (!$activeCycle) {
            $rootFolders = $this->getAccessibleRootFolders($folderModel, (int)$userId, (string)$sysRole, false);
            if (!empty($rootFolders)) {
                $activeCycle = $rootFolders[0];
            }
        }

        if (!$activeCycle) {
            return redirect()->to(site_url('dashboard'))->with('error', 'No evaluation cycle available.');
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
     * Resolves accessible evaluation cycle root folders for the current user session.
     * Admin, PMT, and University President have institutional oversight across all root cycles.
     * Supervisors only see evaluation cycles that have been cascaded to them (matching Folders tab).
     */
    private function getAccessibleRootFolders(DocumentFolderModel $folderModel, int $userId, string $sysRole, bool $isArchived = false): array
    {
        if ($isArchived) {
            if ($sysRole !== 'Admin') {
                return [];
            }
            return $folderModel->groupStart()
                ->where('parent_folder_id IS NULL')
                ->orWhere('parent_folder_id', '')
                ->groupEnd()
                ->where('deleted_at IS NOT NULL')
                ->orderBy('deleted_at', 'DESC')
                ->findAll();
        }

        $userModel = new UserModel();
        $userRow = $userModel->find($userId);
        $userEmail = strtolower($userRow['email'] ?? '');

        $isPresidentSession = false;
        try {
            if (session_status() === PHP_SESSION_ACTIVE || !headers_sent()) {
                $isPresidentSession = (bool)session()->get('is_president');
            }
        } catch (\Throwable $e) {
            $isPresidentSession = false;
        }

        $ownPlantilla = $userModel->getActivePlantillaDetails($userId);
        $posTitle = strtolower($ownPlantilla['position'] ?? '');
        $isPresident = $isPresidentSession 
                    || (str_contains($posTitle, 'president') && !str_contains($posTitle, 'vice')) 
                    || str_contains($userEmail, 'president');

        if (in_array($sysRole, ['Admin', 'PMT']) || $isPresident) {
            return $folderModel->groupStart()
                ->where('parent_folder_id IS NULL')
                ->orWhere('parent_folder_id', '')
                ->groupEnd()
                ->where('deleted_at IS NULL')
                ->orderBy('created_at', 'DESC')
                ->findAll();
        }

        // For Supervisors: Only cycles where this user owns an active folder (i.e. cascaded to them)
        $myFolders = $folderModel->where('user_id', $userId)
            ->where('deleted_at IS NULL')
            ->findAll();

        if (empty($myFolders)) {
            return [];
        }

        $cascadedCycleIds = [];
        foreach ($myFolders as $mf) {
            $cId = !empty($mf['cycle_folder_id'])
                ? (string)$mf['cycle_folder_id']
                : ((empty($mf['parent_folder_id'])) ? (string)$mf['id'] : (string)$folderModel->getRootCycleFolderId($mf['id']));
            if ($cId !== '') {
                $cascadedCycleIds[] = $cId;
            }
        }
        $cascadedCycleIds = array_values(array_unique(array_filter($cascadedCycleIds)));

        if (empty($cascadedCycleIds)) {
            return [];
        }

        return $folderModel->groupStart()
            ->where('parent_folder_id IS NULL')
            ->orWhere('parent_folder_id', '')
            ->groupEnd()
            ->whereIn('id', $cascadedCycleIds)
            ->where('deleted_at IS NULL')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Gathers all personnel records and folder states for the specified cycle and scope.
     */
    private function gatherCycleFolders($activeCycle, ?int $selectedUnitId, string $sysRole, int $userId, array $allUnits = [], ?array $scopedUnitIds = null, ?string $supervisorCollegeName = null): array
    {
        if (!$activeCycle || empty($activeCycle['id'])) {
            return [];
        }

        $db = $this->db ?? \Config\Database::connect();
        $folderModel = new DocumentFolderModel();
        $unitModel   = new UnitModel();

        $cycleFolders = [];

        if ($activeCycle) {
            $isCycleArchived = !empty($activeCycle['deleted_at']) || ($activeCycle['status'] ?? '') === 'archived';
            $descendantIds = $folderModel->getAllDescendantFolderIds($activeCycle['id'], $isCycleArchived);
            $targetFolderIds = !empty($descendantIds) ? $descendantIds : [];

            if (!empty($targetFolderIds)) {
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
                    ->whereIn('df.id', $targetFolderIds);

                if (!$isCycleArchived) {
                    $builder->where('df.deleted_at IS NULL');
                }

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
                } elseif (!in_array($sysRole, ['Admin', 'HR', 'TWG', 'PMT'])) {
                    $builder->where('df.user_id', $userId);
                }

                $builder->groupBy('df.id');
                $cycleFolders = $builder->get()->getResultArray();
            }
        }

        // Discover active employees who haven't created a folder yet
        $unstartedScopedUnitIds = [];
        if ($sysRole === 'Supervisor' && !empty($scopedUnitIds)) {
            $unstartedScopedUnitIds = $scopedUnitIds;
        } elseif (in_array($sysRole, ['Admin', 'PMT'])) {
            if ($selectedUnitId) {
                $filteredUnitIds = $unitModel->getDescendantIds([$selectedUnitId]);
                $filteredUnitIds[] = $selectedUnitId;
                $unstartedScopedUnitIds = array_unique($filteredUnitIds);
            } elseif (!empty($allUnits)) {
                $unstartedScopedUnitIds = array_column($allUnits, 'id');
            }
        }

        if (!$isCycleArchived && !empty($unstartedScopedUnitIds)) {
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
                ->groupBy('u.id')
                ->get()->getResultArray();

            foreach ($activeEmployees as $ce) {
                if (!in_array($ce['user_id'], $existingUserIds)) {
                    $existingUserIds[] = $ce['user_id'];
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

            $cStatus = FolderStatus::canonical($f['folder_status'] ?? '');

            // Target stage tracking & tagging
            if ($cStatus->isEvaluationPhase() || $cStatus === FolderStatus::TARGET_APPROVED) {
                $f['target_state'] = 'approved';
                $f['target_label'] = 'Approved';
                $f['target_badge'] = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-[#102a1e] dark:text-emerald-400 dark:border-[#1b4330]';
            } elseif ($cStatus === FolderStatus::TARGET_SUBMITTED) {
                $f['target_state'] = 'submitted';
                $f['target_label'] = 'Submitted (Pending Review)';
                $f['target_badge'] = 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-info-500/10 dark:text-blue-400 dark:border-info-500/20';
            } elseif ($cStatus === FolderStatus::TARGET_UNAPPROVED) {
                $f['target_state'] = 'unapproved';
                $f['target_label'] = 'Submitted (Unapproved)';
                $f['target_badge'] = 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20';
            } elseif ($cStatus === FolderStatus::TARGET_MISSED) {
                $f['target_state'] = 'missed';
                $f['target_label'] = 'Missed Deadline';
                $f['target_badge'] = 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-danger-500/10 dark:text-rose-400 dark:border-danger-500/20';
            } else {
                $f['target_state'] = 'draft';
                $f['target_label'] = 'Not Submitted (Draft)';
                $f['target_badge'] = 'bg-zinc-100 text-zinc-600 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400';
            }

            // Evaluation stage tracking & tagging
            if ($cStatus === FolderStatus::EVAL_APPROVED) {
                $f['eval_state'] = 'approved';
                $f['eval_label'] = 'Approved';
                $f['eval_badge'] = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-[#102a1e] dark:text-emerald-400 dark:border-[#1b4330]';
            } elseif ($cStatus === FolderStatus::EVAL_SUBMITTED) {
                $f['eval_state'] = 'submitted';
                $f['eval_label'] = 'Submitted (Pending Review)';
                $f['eval_badge'] = 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-info-500/10 dark:text-blue-400 dark:border-info-500/20';
            } elseif ($cStatus === FolderStatus::EVAL_UNRATED) {
                $f['eval_state'] = 'unrated';
                $f['eval_label'] = 'Submitted (Unrated)';
                $f['eval_badge'] = 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20';
            } elseif ($cStatus === FolderStatus::UNEVALUATED) {
                $f['eval_state'] = 'missed';
                $f['eval_label'] = 'Missed Deadline';
                $f['eval_badge'] = 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-danger-500/10 dark:text-rose-400 dark:border-danger-500/20';
            } elseif ($cStatus === FolderStatus::DRAFT_EVALUATION) {
                $f['eval_state'] = 'draft';
                $f['eval_label'] = 'Not Submitted (Draft)';
                $f['eval_badge'] = 'bg-zinc-100 text-zinc-600 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400';
            } else {
                $f['eval_state'] = 'draft';
                $f['eval_label'] = 'Pending Target Phase';
                $f['eval_badge'] = 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-zinc-800/50 dark:text-zinc-400 dark:border-zinc-700';
            }

            // Mutually-exclusive lifecycle stage (Alternative Fix)
            if ($cStatus === FolderStatus::EVAL_APPROVED) {
                $f['lifecycle_stage'] = 'completed';
            } elseif ($cStatus->isEvaluationPhase() || $cStatus === FolderStatus::TARGET_APPROVED) {
                $f['lifecycle_stage'] = 'eval_phase';
            } else {
                $f['lifecycle_stage'] = 'target_phase';
            }

            // Staff type enum
            $f['staff_type'] = StaffType::fromBoolean($f['is_teaching'] ?? 0);

            // High-level filter tag for Dean's quick buttons:
            if ($f['target_state'] === 'draft' || ($f['target_state'] === 'approved' && $f['eval_state'] === 'draft')) {
                $f['submission_filter'] = 'missing';
            } elseif (in_array($f['target_state'], ['submitted', 'unapproved'], true) || in_array($f['eval_state'], ['submitted', 'unrated', 'evaluating'], true)) {
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
                    $f['adjectival_code']  = 'O';
                    $f['adjectival_badge'] = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-400 dark:border-emerald-500/30';
                    $f['pbb_status']       = 'Eligible (O)';
                    $f['pbb_badge']        = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-400 dark:border-emerald-500/30';
                } elseif ($score >= 3.50) {
                    $f['adjectival_label'] = 'Very Satisfactory';
                    $f['adjectival_code']  = 'VS';
                    $f['adjectival_badge'] = 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-info-500/15 dark:text-blue-400 dark:border-info-500/30';
                    $f['pbb_status']       = 'Eligible (VS)';
                    $f['pbb_badge']        = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-400 dark:border-emerald-500/30';
                } elseif ($score >= 2.50) {
                    $f['adjectival_label'] = 'Satisfactory';
                    $f['adjectival_code']  = 'S';
                    $f['adjectival_badge'] = 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/15 dark:text-amber-400 dark:border-amber-500/30';
                    $f['pbb_status']       = 'Ineligible';
                    $f['pbb_badge']        = 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700';
                } elseif ($score >= 1.50) {
                    $f['adjectival_label'] = 'Unsatisfactory';
                    $f['adjectival_code']  = 'US';
                    $f['adjectival_badge'] = 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-500/15 dark:text-orange-400 dark:border-orange-500/30';
                    $f['pbb_status']       = 'Ineligible';
                    $f['pbb_badge']        = 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-danger-500/15 dark:text-rose-400 dark:border-danger-500/30';
                } else {
                    $f['adjectival_label'] = 'Poor';
                    $f['adjectival_code']  = 'P';
                    $f['adjectival_badge'] = 'bg-red-50 text-red-700 border-red-200 dark:bg-danger-500/15 dark:text-danger-400 dark:border-danger-500/30';
                    $f['pbb_status']       = 'Ineligible';
                    $f['pbb_badge']        = 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-danger-500/15 dark:text-rose-400 dark:border-danger-500/30';
                }
                $f['adjectival_display'] = "{$f['adjectival_label']} ({$f['adjectival_code']})";
            } else {
                $f['rating_num']          = null;
                $f['adjectival_label']    = 'Not Yet Rated';
                $f['adjectival_code']     = 'NR';
                $f['adjectival_badge']    = 'bg-zinc-100 text-zinc-500 border-zinc-200 dark:bg-zinc-800/60 dark:text-zinc-400 dark:border-zinc-700/60';
                $f['pbb_status']          = 'Pending';
                $f['pbb_badge']           = 'bg-zinc-100 text-zinc-500 border-zinc-200 dark:bg-zinc-800/60 dark:text-zinc-400 dark:border-zinc-700/60';
                $f['adjectival_display']  = 'Not Yet Rated';
            }
        }
        unset($f);

        return $cycleFolders;
    }
}

