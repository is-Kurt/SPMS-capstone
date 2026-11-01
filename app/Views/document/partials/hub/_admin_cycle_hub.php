<?php
    $hubTargetStart = $activeFolder['ipcr_target_start'] ?? $activeFolder['opcr_target_start'] ?? $activeFolder['dpcr_target_start'] ?? $activeFolder['iperf_target_start'] ?? null;
    $hubTargetEnd   = $activeFolder['ipcr_target_end'] ?? $activeFolder['opcr_target_end'] ?? $activeFolder['dpcr_target_end'] ?? $activeFolder['iperf_target_end'] ?? null;
    $hubEvalStart   = $activeFolder['ipcr_eval_start'] ?? $activeFolder['opcr_eval_start'] ?? $activeFolder['dpcr_eval_start'] ?? $activeFolder['iperf_eval_start'] ?? null;
    $hubEvalEnd     = $activeFolder['ipcr_eval_end'] ?? $activeFolder['opcr_eval_end'] ?? $activeFolder['dpcr_eval_end'] ?? $activeFolder['iperf_eval_end'] ?? null;

    $isUnifiedSchedule = (!empty($hubTargetStart) &&
        ($activeFolder['opcr_target_start'] ?? $hubTargetStart) === $hubTargetStart &&
        ($activeFolder['dpcr_target_start'] ?? $hubTargetStart) === $hubTargetStart &&
        ($activeFolder['iperf_target_start'] ?? $hubTargetStart) === $hubTargetStart
    );

    $nowStr = date('Y-m-d H:i:s');
    $nowTs = time();

    $tStartDate = !empty($hubTargetStart) ? strtotime($hubTargetStart) : null;
    $tEndDate   = !empty($hubTargetEnd)   ? strtotime($hubTargetEnd)   : null;
    $eStartDate = !empty($hubEvalStart)   ? strtotime($hubEvalStart)   : null;
    $eEndDate   = !empty($hubEvalEnd)     ? strtotime($hubEvalEnd)     : null;

    // Dynamic Institutional Cycle Phase Determination
    if (!empty($isArchived)) {
        $cyclePhase = 'archived';
        $phaseBadgeLabel = 'ARCHIVED & FROZEN';
        $statusPillModifier = 'is-neutral';
        $adminStepIndex = 4;
        $bannerTitle = 'Evaluation Cycle is Closed & Archived';
        $bannerDesc = 'This evaluation cycle is archived and read-only. All ratings, targets, and cascading actions are frozen. To modify dates or restore this cycle, use the Folder Management options.';
        $bannerIconClass = 'bg-amber-500/20 text-amber-700 dark:text-amber-400 border border-amber-500/30';
    } elseif (!empty($hubEvalEnd) && $nowStr > $hubEvalEnd) {
        $cyclePhase = 'concluded';
        $phaseBadgeLabel = 'CYCLE CONCLUDED / CALIBRATION';
        $statusPillModifier = 'is-warning';
        $adminStepIndex = 4;
        $bannerTitle = 'Evaluation Window Concluded — Ratings Calibration';
        $bannerDesc = 'The formal evaluation window has concluded. Final ratings and performance scores are currently undergoing PMT calibration and review. Once finalized, this cycle can be archived for institutional record-keeping.';
        $bannerIconClass = 'bg-purple-500/20 text-purple-700 dark:text-purple-400 border border-purple-500/30';
    } elseif (!empty($hubEvalStart) && $nowStr >= $hubEvalStart) {
        $cyclePhase = 'evaluation';
        $phaseBadgeLabel = 'EVALUATION PHASE ACTIVE';
        $statusPillModifier = ''; // emerald active
        $adminStepIndex = 3;
        $bannerTitle = 'Institutional Evaluation Phase is Active';
        $bannerDesc = 'The formal performance evaluation window is open across Benguet State University. Supervisors, PMT/TWG, and Executive Reviewers are evaluating actual accomplishments against approved targets and verifying attached Means of Verification (MOVs).';
        $bannerIconClass = 'bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30';
    } elseif (!empty($hubTargetEnd) && $nowStr > $hubTargetEnd) {
        $cyclePhase = 'execution';
        $phaseBadgeLabel = 'PERFORMANCE PERIOD / MONITORING';
        $statusPillModifier = 'is-info';
        $adminStepIndex = 2;
        $bannerTitle = 'Performance Period & Execution Phase';
        $bannerDesc = 'The target-setting window has concluded and approved commitments are locked. Ratees across departments are executing deliverables and progressively compiling actual accomplishments and Means of Verification (MOVs) prior to the formal evaluation window.';
        $bannerIconClass = 'bg-sky-500/20 text-sky-700 dark:text-sky-400 border border-sky-500/30';
    } elseif (!empty($hubTargetStart) && $nowStr >= $hubTargetStart) {
        $cyclePhase = 'target';
        $phaseBadgeLabel = 'TARGET PHASE ACTIVE';
        $statusPillModifier = ''; // emerald active
        $adminStepIndex = 2;
        $bannerTitle = 'Target Formulation Window is Active';
        $bannerDesc = 'Institutional target formulation is currently underway. Vice Presidents, Deans, and Department Heads are formulating and submitting OPCR and DPCR performance commitments. Ensure all executive targets are reviewed and approved so subordinates have approved bases.';
        $bannerIconClass = 'bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30';
    } else {
        $cyclePhase = 'upcoming';
        $phaseBadgeLabel = 'CYCLE SETUP / UPCOMING';
        $statusPillModifier = 'is-neutral';
        $adminStepIndex = 1;
        $bannerTitle = 'Evaluation Cycle Initialized';
        $bannerDesc = 'This evaluation cycle has been created. Ensure deadline dates for OPCR, DPCR, IPCR, and IPERF are configured, then use the Cycle Release panel on the right to cascade the cycle to the Vice Presidents (Executive Team).';
        $bannerIconClass = 'bg-zinc-500/20 text-zinc-700 dark:text-zinc-400 border border-zinc-500/30';
    }

    $buildPhaseWindowPill = static function (?int $startTs, ?int $endTs, string $fallbackLabel) use ($nowTs, $isArchived): array {
        if ($isArchived) {
            return ['dot' => 'bg-amber-500', 'text' => 'ARCHIVED'];
        }
        if (!$startTs && !$endTs) {
            return ['dot' => 'bg-sky-600 dark:bg-sky-400', 'text' => $fallbackLabel];
        }
        $startDayTs = $startTs ? strtotime(date('Y-m-d', $startTs) . ' 00:00:00') : null;
        $endDayTs   = $endTs   ? strtotime(date('Y-m-d', $endTs) . ' 23:59:59')   : null;

        if ($startDayTs && $nowTs < $startDayTs) {
            $daysUntil = (int) ceil(($startDayTs - $nowTs) / 86400);
            $txt = $daysUntil <= 1 ? 'UPCOMING • Starts in 1 day' : "UPCOMING • Starts in {$daysUntil} days";
            return ['dot' => 'bg-sky-600 dark:bg-sky-400', 'text' => $txt];
        }
        if ($endDayTs && $nowTs <= $endDayTs) {
            $daysLeft = (int) floor(($endDayTs - $nowTs) / 86400);
            if ($daysLeft > 1) {
                $txt = "ACTIVE • {$daysLeft} days left";
            } elseif ($daysLeft === 1) {
                $txt = 'ACTIVE • 1 day left';
            } else {
                $txt = 'ACTIVE • Due Today';
            }
            return ['dot' => 'bg-emerald-600 dark:bg-emerald-400', 'text' => $txt];
        }
        return ['dot' => 'bg-zinc-400 dark:bg-zinc-500', 'text' => 'CLOSED'];
    };

    $targetPhasePill = $buildPhaseWindowPill($tStartDate, $tEndDate, 'TARGET PHASE');
    $evalPhasePill   = $buildPhaseWindowPill($eStartDate, $eEndDate, 'EVAL PHASE');

    $targetWindowDateStr = ($tStartDate && $tEndDate)
        ? date('M j', $tStartDate) . ' – ' . date('M j, Y', $tEndDate)
        : (($tStartDate || $tEndDate) ? date('M j, Y', $tStartDate ?: $tEndDate) : 'Dates Not Configured');

    $evalWindowDateStr = ($eStartDate && $eEndDate)
        ? date('M j', $eStartDate) . ' – ' . date('M j, Y', $eEndDate)
        : (($eStartDate || $eEndDate) ? date('M j, Y', $eStartDate ?: $eEndDate) : 'Scheduled Following Targets');
?>

<!-- ADMIN INSTITUTIONAL CYCLE MONITORING HUB CARD -->
<div class="spms-hub-card">
    <!-- HEADER -->
    <div class="flex items-center justify-between gap-3 mb-3 sm:mb-5">
        <h2 class="spms-hub-title min-w-0 truncate"><?= esc($activeFolder['title'] ?: 'Untitled Cycle') ?></h2>
        <div class="shrink-0">
            <?php if ($isArchived): ?>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500/15 text-amber-700 dark:text-amber-400 border border-amber-500/30">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    <span>Archived &amp; Frozen</span>
                </div>
            <?php else: ?>
                <div class="spms-hub-status-pill <?= $statusPillModifier ?>">
                    <span class="spms-hub-status-dot"></span>
                    <span><?= esc($phaseBadgeLabel) ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- INSTITUTIONAL CYCLE PROGRESS STEPPER -->
    <div>
        <div class="flex flex-wrap items-center justify-between gap-2 mt-[18px] mb-3 sm:mt-6 sm:mb-4">
            <span class="spms-hub-tracker-label" style="margin: 0;">INSTITUTIONAL CYCLE PROGRESS</span>
            <div class="inline-flex items-center gap-2 tabular-nums">
                <span class="text-[10.5px] sm:text-[11px] font-extrabold uppercase tracking-[0.08em] text-slate-500 dark:text-zinc-400">University Scope</span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200 dark:bg-zinc-800 dark:text-zinc-200 dark:border-zinc-700">
                    <span><?= (int)($cycleStats['total'] ?? 0) ?> Cascaded Units</span>
                </span>
            </div>
        </div>

        <div class="spms-hub-stepper-row">
            <!-- Step 1: Cycle Release -->
            <div style="display: flex; align-items: center; flex-shrink: 0;">
                <?php if ($adminStepIndex === 1): ?>
                    <div class="spms-hub-circle-active">
                        <div class="spms-hub-circle-active-dot"></div>
                    </div>
                    <span class="spms-hub-step-text-active">Cycle Release</span>
                <?php else: ?>
                    <div class="spms-hub-circle-completed">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="spms-hub-step-text-active">Cycle Release</span>
                <?php endif; ?>
            </div>

            <!-- Arrow 1 -> 2 -->
            <?php if ($adminStepIndex === 1): ?>
                <svg class="spms-hub-stepper-arrow" viewBox="0 0 60 12" fill="none">
                    <line x1="0" y1="6" x2="48" y2="6" stroke="#f59e0b" stroke-width="2" stroke-dasharray="3 3" />
                    <polygon points="46,2 56,6 46,10" fill="#f59e0b" />
                </svg>
            <?php else: ?>
                <svg class="spms-hub-stepper-arrow" viewBox="0 0 60 12" fill="none">
                    <line x1="0" y1="6" x2="48" y2="6" stroke="#10b981" stroke-width="2" />
                    <polygon points="46,2 56,6 46,10" fill="#10b981" />
                </svg>
            <?php endif; ?>

            <!-- Step 2: Target Formulation -->
            <div style="display: flex; align-items: center; flex-shrink: 0;">
                <?php if ($adminStepIndex === 2): ?>
                    <div class="spms-hub-circle-active">
                        <div class="spms-hub-circle-active-dot"></div>
                    </div>
                    <span class="spms-hub-step-text-active">Target Setting</span>
                <?php elseif ($adminStepIndex > 2): ?>
                    <div class="spms-hub-circle-completed">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="spms-hub-step-text-active">Target Setting</span>
                <?php else: ?>
                    <div class="spms-hub-circle-inactive"></div>
                    <span class="spms-hub-step-text-inactive">Target Setting</span>
                <?php endif; ?>
            </div>

            <!-- Arrow 2 -> 3 -->
            <?php if ($adminStepIndex > 2): ?>
                <svg class="spms-hub-stepper-arrow" viewBox="0 0 60 12" fill="none">
                    <line x1="0" y1="6" x2="48" y2="6" stroke="#10b981" stroke-width="2" />
                    <polygon points="46,2 56,6 46,10" fill="#10b981" />
                </svg>
            <?php elseif ($adminStepIndex === 2): ?>
                <svg class="spms-hub-stepper-arrow" viewBox="0 0 60 12" fill="none">
                    <line x1="0" y1="6" x2="48" y2="6" stroke="<?= $cyclePhase === 'execution' ? '#0ea5e9' : '#f59e0b' ?>" stroke-width="2" stroke-dasharray="3 3" />
                    <polygon points="46,2 56,6 46,10" fill="<?= $cyclePhase === 'execution' ? '#0ea5e9' : '#f59e0b' ?>" />
                </svg>
            <?php else: ?>
                <svg class="spms-hub-stepper-arrow text-zinc-300 dark:text-[#145235]" viewBox="0 0 60 12" fill="none">
                    <line x1="0" y1="6" x2="48" y2="6" stroke="currentColor" stroke-width="1.5" />
                    <polygon points="46,2 56,6 46,10" fill="currentColor" />
                </svg>
            <?php endif; ?>

            <!-- Step 3: Performance Evaluation -->
            <div style="display: flex; align-items: center; flex-shrink: 0;">
                <?php if ($adminStepIndex === 3): ?>
                    <div class="spms-hub-circle-active">
                        <div class="spms-hub-circle-active-dot"></div>
                    </div>
                    <span class="spms-hub-step-text-active">Evaluation</span>
                <?php elseif ($adminStepIndex > 3): ?>
                    <div class="spms-hub-circle-completed">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="spms-hub-step-text-active">Evaluation</span>
                <?php else: ?>
                    <div class="spms-hub-circle-inactive"></div>
                    <span class="spms-hub-step-text-inactive">Evaluation</span>
                <?php endif; ?>
            </div>

            <!-- Arrow 3 -> 4 -->
            <?php if ($adminStepIndex > 3): ?>
                <svg class="spms-hub-stepper-arrow" viewBox="0 0 60 12" fill="none">
                    <line x1="0" y1="6" x2="48" y2="6" stroke="#10b981" stroke-width="2" />
                    <polygon points="46,2 56,6 46,10" fill="#10b981" />
                </svg>
            <?php elseif ($adminStepIndex === 3): ?>
                <svg class="spms-hub-stepper-arrow" viewBox="0 0 60 12" fill="none">
                    <line x1="0" y1="6" x2="48" y2="6" stroke="#f59e0b" stroke-width="2" stroke-dasharray="3 3" />
                    <polygon points="46,2 56,6 46,10" fill="#f59e0b" />
                </svg>
            <?php else: ?>
                <svg class="spms-hub-stepper-arrow text-zinc-300 dark:text-[#145235]" viewBox="0 0 60 12" fill="none">
                    <line x1="0" y1="6" x2="48" y2="6" stroke="currentColor" stroke-width="1.5" />
                    <polygon points="46,2 56,6 46,10" fill="currentColor" />
                </svg>
            <?php endif; ?>

            <!-- Step 4: Final Ratings & Archival -->
            <div style="display: flex; align-items: center; flex-shrink: 0;">
                <?php if ($adminStepIndex === 4 && $isArchived): ?>
                    <div class="spms-hub-circle-completed">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="spms-hub-step-text-active">Final Ratings &amp; Archival</span>
                <?php elseif ($adminStepIndex === 4): ?>
                    <div class="spms-hub-circle-active">
                        <div class="spms-hub-circle-active-dot"></div>
                    </div>
                    <span class="spms-hub-step-text-active">Final Ratings &amp; Archival</span>
                <?php else: ?>
                    <div class="spms-hub-circle-inactive"></div>
                    <span class="spms-hub-step-text-inactive">Final Ratings &amp; Archival</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- CONTEXT-AWARE INFORMATIONAL BANNER -->
    <div class="spms-hub-banner p-2.5 sm:p-4 rounded-xl mt-3 sm:mt-5 mb-3 sm:mb-5 flex items-start gap-2.5 sm:gap-3.5 shadow-xs">
        <div class="spms-hub-banner-icon w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5 <?= $bannerIconClass ?>">
            <?php if ($cyclePhase === 'archived'): ?>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                </svg>
            <?php elseif ($cyclePhase === 'concluded'): ?>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            <?php elseif ($cyclePhase === 'evaluation'): ?>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            <?php else: ?>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            <?php endif; ?>
        </div>
        <div class="text-[10px] sm:text-xs leading-relaxed">
            <span class="spms-hub-banner-title font-extrabold block mb-0.5 text-[11px] sm:text-sm"><?= esc($bannerTitle) ?></span>
            <?= esc($bannerDesc) ?>
        </div>
    </div>

    <!-- 4 STAT TILES (INSTITUTIONAL METRICS) -->
    <div class="spms-hub-stat-grid spms-hub-stat-grid-4">
        <!-- 1. Target Window -->
        <div class="spms-hub-stat-tile">
            <div>
                <div class="spms-hub-tile-label">Target Setting Window</div>
                <div class="spms-hub-tile-value"><?= esc($targetWindowDateStr) ?></div>
            </div>
            <div>
                <div class="spms-hub-window-pill">
                    <span class="w-1.5 h-1.5 rounded-full <?= $targetPhasePill['dot'] ?> inline-block"></span>
                    <span><?= esc($targetPhasePill['text']) ?></span>
                </div>
            </div>
        </div>

        <!-- 2. Evaluation Window -->
        <div class="spms-hub-stat-tile">
            <div>
                <div class="spms-hub-tile-label">Evaluation Window</div>
                <div class="spms-hub-tile-value"><?= esc($evalWindowDateStr) ?></div>
            </div>
            <div>
                <div class="spms-hub-window-pill">
                    <span class="w-1.5 h-1.5 rounded-full <?= $evalPhasePill['dot'] ?> inline-block"></span>
                    <span><?= esc($evalPhasePill['text']) ?></span>
                </div>
            </div>
        </div>

        <!-- 3. Cascaded Units Coverage -->
        <div class="spms-hub-stat-tile">
            <div>
                <div class="spms-hub-tile-label">Institutional Units</div>
                <div class="spms-hub-tile-value">
                    <?= (int)($cycleStats['total'] ?? 0) ?> Cascaded Folders
                </div>
            </div>
            <div>
                <div class="spms-hub-window-pill">
                    <span class="w-1.5 h-1.5 rounded-full <?= ($cycleStats['target_pct'] ?? 0) >= 100 ? 'bg-emerald-500' : 'bg-amber-500' ?> inline-block"></span>
                    <span><?= (int)($cycleStats['target_pct'] ?? 0) ?>% TARGETS APPROVED</span>
                </div>
            </div>
        </div>

        <!-- 4. Evaluation Compliance -->
        <div class="spms-hub-stat-tile">
            <div>
                <div class="spms-hub-tile-label">Evaluation Submissions</div>
                <div class="spms-hub-tile-value">
                    <?= (int)(($cycleStats['eval_submitted'] ?? 0) + ($cycleStats['eval_approved'] ?? 0)) ?> / <?= (int)($cycleStats['total'] ?? 0) ?> Submitted
                </div>
            </div>
            <div>
                <div class="spms-hub-window-pill">
                    <span class="w-1.5 h-1.5 rounded-full <?= ($cycleStats['eval_pct'] ?? 0) >= 100 ? 'bg-emerald-500' : 'bg-sky-500' ?> inline-block"></span>
                    <span><?= (int)($cycleStats['eval_pct'] ?? 0) ?>% RATINGS COMPLETED</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ACTIONS -->
    <div class="spms-hub-actions-bar">
        <?php if ($isArchived): ?>
            <button type="button" disabled class="spms-hub-btn-primary opacity-40 cursor-not-allowed hover:bg-transparent" title="Archived evaluation cycles cannot be edited. Restore the cycle first.">
                Edit Cycle Dates
            </button>
        <?php else: ?>
            <button type="button" onclick='openEditFolderModal(<?= json_encode([
                "id"                           => $activeFolder["id"],
                "title"                        => $activeFolder["title"],
                "rating_period"                => $activeFolder["rating_period"] ?? "",
                "ipcr_target_start"            => $activeFolder["ipcr_target_start"] ?? "",
                "ipcr_target_end"              => $activeFolder["ipcr_target_end"] ?? "",
                "ipcr_eval_start"              => $activeFolder["ipcr_eval_start"] ?? "",
                "ipcr_eval_end"                => $activeFolder["ipcr_eval_end"] ?? "",
                "dpcr_target_start"            => $activeFolder["dpcr_target_start"] ?? "",
                "dpcr_target_end"              => $activeFolder["dpcr_target_end"] ?? "",
                "dpcr_eval_start"              => $activeFolder["dpcr_eval_start"] ?? "",
                "dpcr_eval_end"                => $activeFolder["dpcr_eval_end"] ?? "",
                "opcr_target_start"            => $activeFolder["opcr_target_start"] ?? "",
                "opcr_target_end"              => $activeFolder["opcr_target_end"] ?? "",
                "opcr_eval_start"              => $activeFolder["opcr_eval_start"] ?? "",
                "opcr_eval_end"                => $activeFolder["opcr_eval_end"] ?? "",
                "iperf_target_start"           => $activeFolder["iperf_target_start"] ?? "",
                "iperf_target_end"             => $activeFolder["iperf_target_end"] ?? "",
                "iperf_eval_start"             => $activeFolder["iperf_eval_start"] ?? "",
                "iperf_eval_end"               => $activeFolder["iperf_eval_end"] ?? "",
                "approving_authority_name"     => $activeFolder["approving_authority_name"] ?? "",
                "approving_authority_position" => $activeFolder["approving_authority_position"] ?? ""
            ]) ?>)' class="spms-hub-btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Edit Cycle Dates</span>
            </button>
        <?php endif; ?>

        <?php if ($isArchived): ?>
            <button type="button" disabled class="spms-hub-btn-secondary spms-hub-btn-secondary-full opacity-40 cursor-not-allowed hover:bg-transparent" title="Archived evaluation cycles are frozen.">
                SPMS Cascade Guide
            </button>
        <?php else: ?>
            <button type="button" onclick="openUserGuideModal()" class="spms-hub-btn-secondary spms-hub-btn-secondary-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500 dark:text-zinc-400 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>SPMS Cascade Guide</span>
            </button>
        <?php endif; ?>
    </div>
</div>
