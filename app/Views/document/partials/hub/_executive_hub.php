<?php
                            $primaryDoc = reset($myDocs);

                            $attachmentCount = 0;
                            $attachmentsList = [];
                            if (!empty($primaryDoc['id'])) {
                                $attModel = new \App\Models\DocumentAttachmentModel();
                                $attachmentsList = $attModel->where('document_id', $primaryDoc['id'])
                                                           ->where('deleted_at IS NULL')
                                                           ->findAll();
                                $attachmentCount = count($attachmentsList);
                            }

                            $pTitleUpper = strtoupper($primaryDoc['title'] ?? '');
                            if (str_contains($pTitleUpper, 'OPCR') || str_contains($pTitleUpper, 'OFFICE')) {
                                $effectiveDocType = 'OPCR';
                                $officialPaperName = 'Office Performance Commitment and Review (OPCR)';
                            } elseif (str_contains($pTitleUpper, 'DPCR') || str_contains($pTitleUpper, 'DIVISION') || str_contains($pTitleUpper, 'DEPARTMENT')) {
                                $effectiveDocType = 'DPCR';
                                $officialPaperName = !empty($isOwnerDean)
                                    ? 'DPCR (Collegiate / Division Performance Commitment and Review)'
                                    : 'DPCR (Departmental Performance Commitment and Review)';
                            } elseif (str_contains($pTitleUpper, 'IPERF') || str_contains($pTitleUpper, 'NON-TEACHING')) {
                                $effectiveDocType = 'IPERF';
                                $officialPaperName = 'Individual Performance Evaluation Review Form (IPERF)';
                            } elseif (str_contains($pTitleUpper, 'IPCR')) {
                                $effectiveDocType = 'IPCR';
                                $officialPaperName = 'Individual Performance Commitment and Review (IPCR)';
                            } else {
                                $effectiveDocType = strtoupper($ownerDocType ?? 'IPCR');
                                $officialPaperName = match($effectiveDocType) {
                                    'OPCR'  => 'Office Performance Commitment and Review (OPCR)',
                                    'DPCR'  => 'Department / Collegiate Performance Commitment and Review (DPCR)',
                                    'IPERF' => 'Individual Performance Evaluation Review Form (IPERF)',
                                    default => 'Individual Performance Commitment and Review (IPCR)'
                                };
                            }

                            $rawStatus = $activeFolder['status'] ?? 'draft_target';
                            $cFolderStatus = \App\Enums\FolderStatus::canonical($rawStatus);

                            $isTargetPhaseStatus = $cFolderStatus->isTargetPhase();
                            $activeSupsForCount = $isTargetPhaseStatus
                                ? ($targetSupervisors ?? $evaluatorSupervisors ?? [])
                                : ($evalSupervisors ?? $evaluatorSupervisors ?? []);

                            $totalSups = !empty($activeSupsForCount) ? count($activeSupsForCount) : 0;
                            $approvedSups = !empty($activeSupsForCount) ? count(array_filter($activeSupsForCount, fn($s) => $s['status'] === 'approved')) : 0;
                            $returnedSups = !empty($activeSupsForCount) ? count(array_filter($activeSupsForCount, fn($s) => $s['status'] === 'returned')) : 0;
                            $allSupervisorsApproved = ($totalSups > 0 && $approvedSups === $totalSups);

                            $statusBadgeText = $cFolderStatus->label();
                            $statusPillModifier = match ($cFolderStatus) {
                                \App\Enums\FolderStatus::TARGET_APPROVED,
                                \App\Enums\FolderStatus::EVAL_APPROVED => '',
                                \App\Enums\FolderStatus::TARGET_MISSED,
                                \App\Enums\FolderStatus::UNEVALUATED => 'is-danger',
                                \App\Enums\FolderStatus::TARGET_UNAPPROVED,
                                \App\Enums\FolderStatus::EVAL_UNRATED => 'is-warning',
                                \App\Enums\FolderStatus::TARGET_SUBMITTED,
                                \App\Enums\FolderStatus::EVAL_SUBMITTED => 'is-info',
                                default => 'is-neutral',
                            };

                            $docTypeKey = strtolower($effectiveDocType);
                            $rawTStart  = $activeFolder[$docTypeKey . '_target_start'] ?? $activeFolder['target_date_start'] ?? $activeFolder['ipcr_target_start'] ?? $activeFolder['opcr_target_start'] ?? $activeFolder['dpcr_target_start'] ?? $activeFolder['iperf_target_start'] ?? null;
                            $rawTEnd    = $activeFolder[$docTypeKey . '_target_end']   ?? $activeFolder['target_date_end']   ?? $activeFolder['ipcr_target_end']   ?? $activeFolder['opcr_target_end']   ?? $activeFolder['dpcr_target_end']   ?? $activeFolder['iperf_target_end']   ?? null;
                            $rawEStart  = $activeFolder[$docTypeKey . '_eval_start']   ?? $activeFolder['eval_date_start']   ?? $activeFolder['ipcr_eval_start']   ?? $activeFolder['opcr_eval_start']   ?? $activeFolder['dpcr_eval_start']   ?? $activeFolder['iperf_eval_start']   ?? null;
                            $rawEEnd    = $activeFolder[$docTypeKey . '_eval_end']     ?? $activeFolder['eval_date_end']     ?? $activeFolder['ipcr_eval_end']     ?? $activeFolder['opcr_eval_end']     ?? $activeFolder['dpcr_eval_end']     ?? $activeFolder['iperf_eval_end']     ?? null;

                            $isSubmitted = !empty($activeFolder['submitted_at'])
                                || !empty($activeFolder['target_submitted_at'])
                                || !empty($activeFolder['final_rating'])
                                || in_array($rawStatus, ['target_submitted', 'eval_submitted', 'eval_approved', 'target_unapproved', 'eval_unrated'], true);

                            // Stepper active index (1: Target Setting [dates/draft], 2: Target Approved [status], 3: Evaluation [dates/eval], 4: Final Rating [status])
                            $currentStepIndex = $cFolderStatus->stepperIndex($rawEStart, $rawTEnd, $isSubmitted);

                            $tStartDate = !empty($rawTStart) ? strtotime($rawTStart) : null;
                            $tEndDate   = !empty($rawTEnd)   ? strtotime($rawTEnd)   : null;
                            $eStartDate = !empty($rawEStart) ? strtotime($rawEStart) : null;
                            $eEndDate   = !empty($rawEEnd)   ? strtotime($rawEEnd)   : null;

                            $targetWindowDateStr = ($tStartDate && $tEndDate)
                                ? date('M j', $tStartDate) . ' – ' . date('M j, Y', $tEndDate)
                                : (($tStartDate || $tEndDate) ? date('M j, Y', $tStartDate ?: $tEndDate) : 'Dates Not Configured');

                            $evalWindowDateStr = ($eStartDate && $eEndDate)
                                ? date('M j', $eStartDate) . ' – ' . date('M j, Y', $eEndDate)
                                : (($eStartDate || $eEndDate) ? date('M j, Y', $eStartDate ?: $eEndDate) : 'Scheduled Following Targets');

                            $now = time();
                            $buildPhaseWindowPill = static function (?int $startTs, ?int $endTs, string $fallbackLabel) use ($now, $isArchived): array {
                                if ($isArchived) {
                                    return ['dot' => 'bg-amber-500', 'text' => 'ARCHIVED'];
                                }
                                if (!$startTs && !$endTs) {
                                    return ['dot' => 'bg-sky-600 dark:bg-sky-400', 'text' => $fallbackLabel];
                                }
                                $startDayTs = $startTs ? strtotime(date('Y-m-d', $startTs) . ' 00:00:00') : null;
                                $endDayTs   = $endTs   ? strtotime(date('Y-m-d', $endTs) . ' 23:59:59')   : null;

                                if ($startDayTs && $now < $startDayTs) {
                                    $daysUntil = (int) ceil(($startDayTs - $now) / 86400);
                                    $txt = $daysUntil <= 1 ? 'UPCOMING • Starts in 1 day' : "UPCOMING • Starts in {$daysUntil} days";
                                    return ['dot' => 'bg-sky-600 dark:bg-sky-400', 'text' => $txt];
                                }
                                if ($endDayTs && $now <= $endDayTs) {
                                    $daysLeft = (int) floor(($endDayTs - $now) / 86400);
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

                            $finalRatingVal = $activeFolder['final_rating'] ?? null;
                            $hasFinalRating = (!empty($finalRatingVal) && (float) $finalRatingVal > 0);
                            if ($hasFinalRating) {
                                $num = number_format((float) $finalRatingVal, 2);
                                $adjective = match(true) {
                                    $finalRatingVal >= 4.50 => 'Outstanding',
                                    $finalRatingVal >= 3.50 => 'Very Satisfactory',
                                    $finalRatingVal >= 2.50 => 'Satisfactory',
                                    $finalRatingVal >= 1.50 => 'Unsatisfactory',
                                    default => 'Poor'
                                };
                                $finalRatingDisplay = "{$num} · {$adjective}";
                            } else {
                                $finalRatingDisplay = 'Pending';
                            }

                            $folderTitleText = $activeFolder['title'] ?: 'Untitled Folder';
                        ?>

                        <!-- EXECUTIVE PERFORMANCE PAPER HUB CARD -->
                        <div class="spms-hub-card">
                            <!-- HEADER -->
                            <div class="flex items-center justify-between gap-3 mb-3 sm:mb-5">
                                <h2 class="spms-hub-title min-w-0 truncate"><?= esc($folderTitleText) ?></h2>
                                <div class="shrink-0">
                                    <div class="spms-hub-status-pill <?= $statusPillModifier ?>">
                                        <span class="spms-hub-status-dot"></span>
                                        <span><?= esc($statusBadgeText) ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- CYCLE PROGRESS TRACKER -->
                            <div>
                                <div class="flex flex-wrap items-center justify-between gap-2 mt-[18px] mb-3 sm:mt-7 sm:mb-4">
                                    <span class="spms-hub-tracker-label" style="margin: 0;">CYCLE PROGRESS TRACKER</span>
                                    <div class="inline-flex items-center gap-2 tabular-nums">
                                        <span class="text-[10.5px] sm:text-[11px] font-extrabold uppercase tracking-[0.08em] text-slate-500 dark:text-zinc-400">Final Score</span>
                                        <?php if ($hasFinalRating): ?>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-extrabold border <?= $currentStepIndex === 4 ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800' : 'bg-slate-100 text-slate-800 border-slate-200 dark:bg-zinc-800 dark:text-zinc-100 dark:border-zinc-700' ?>">
                                                <span><?= esc($num) ?></span>
                                                <span class="text-[10px] font-semibold opacity-60">/ 5.00</span>
                                                <span class="opacity-40">&middot;</span>
                                                <span><?= esc($adjective) ?></span>
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-bold bg-slate-100/80 text-slate-500 border border-slate-200/80 dark:bg-zinc-900 dark:text-zinc-400 dark:border-zinc-800">
                                                <span>--</span>
                                                <span class="text-[10px] font-semibold opacity-60">/ 5.00</span>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="spms-hub-stepper-row">
                                    
                                    <!-- Step 1: Target Setting -->
                                    <div style="display: flex; align-items: center; flex-shrink: 0;">
                                        <?php if ($currentStepIndex === 1): ?>
                                            <div class="spms-hub-circle-active">
                                                <div class="spms-hub-circle-active-dot"></div>
                                            </div>
                                            <span class="spms-hub-step-text-active">Target Setting</span>
                                        <?php elseif ($currentStepIndex > 1): ?>
                                            <div class="spms-hub-circle-completed">
                                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                            <span class="spms-hub-step-text-active">Target Setting</span>
                                        <?php else: ?>
                                            <div class="spms-hub-circle-inactive"></div>
                                            <span class="spms-hub-step-text-inactive">Target Setting</span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Arrow 1 -> 2 -->
                                    <?php if ($currentStepIndex === 1): ?>
                                        <svg class="spms-hub-stepper-arrow" viewBox="0 0 60 12" fill="none">
                                            <line x1="0" y1="6" x2="48" y2="6" stroke="#f59e0b" stroke-width="2" stroke-dasharray="3 3" />
                                            <polygon points="46,2 56,6 46,10" fill="#f59e0b" />
                                        </svg>
                                    <?php elseif ($currentStepIndex > 1): ?>
                                        <svg class="spms-hub-stepper-arrow" viewBox="0 0 60 12" fill="none">
                                            <line x1="0" y1="6" x2="48" y2="6" stroke="#10b981" stroke-width="2" />
                                            <polygon points="46,2 56,6 46,10" fill="#10b981" />
                                        </svg>
                                    <?php else: ?>
                                        <svg class="spms-hub-stepper-arrow text-zinc-300 dark:text-[#145235]" viewBox="0 0 60 12" fill="none">
                                            <line x1="0" y1="6" x2="48" y2="6" stroke="currentColor" stroke-width="1.5" />
                                            <polygon points="46,2 56,6 46,10" fill="currentColor" />
                                        </svg>
                                    <?php endif; ?>

                                    <!-- Step 2: Target Approved -->
                                    <div style="display: flex; align-items: center; flex-shrink: 0;">
                                        <?php if ($currentStepIndex === 2): ?>
                                            <div class="spms-hub-circle-active">
                                                <div class="spms-hub-circle-active-dot"></div>
                                            </div>
                                            <span class="spms-hub-step-text-active">Target Approved</span>
                                        <?php elseif ($currentStepIndex > 2): ?>
                                            <div class="spms-hub-circle-completed">
                                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                            <span class="spms-hub-step-text-active">Target Approved</span>
                                        <?php else: ?>
                                            <div class="spms-hub-circle-inactive"></div>
                                            <span class="spms-hub-step-text-inactive">Target Approved</span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Arrow 2 -> 3 -->
                                    <?php if ($currentStepIndex === 2): ?>
                                        <svg class="spms-hub-stepper-arrow" viewBox="0 0 60 12" fill="none">
                                            <line x1="0" y1="6" x2="48" y2="6" stroke="#f59e0b" stroke-width="2" stroke-dasharray="3 3" />
                                            <polygon points="46,2 56,6 46,10" fill="#f59e0b" />
                                        </svg>
                                    <?php elseif ($currentStepIndex > 2): ?>
                                        <svg class="spms-hub-stepper-arrow" viewBox="0 0 60 12" fill="none">
                                            <line x1="0" y1="6" x2="48" y2="6" stroke="#10b981" stroke-width="2" />
                                            <polygon points="46,2 56,6 46,10" fill="#10b981" />
                                        </svg>
                                    <?php else: ?>
                                        <svg class="spms-hub-stepper-arrow text-zinc-300 dark:text-[#145235]" viewBox="0 0 60 12" fill="none">
                                            <line x1="0" y1="6" x2="48" y2="6" stroke="currentColor" stroke-width="1.5" />
                                            <polygon points="46,2 56,6 46,10" fill="currentColor" />
                                        </svg>
                                    <?php endif; ?>

                                    <!-- Step 3: Evaluation -->
                                    <div style="display: flex; align-items: center; flex-shrink: 0;">
                                        <?php if ($currentStepIndex === 3): ?>
                                            <div class="spms-hub-circle-active">
                                                <div class="spms-hub-circle-active-dot"></div>
                                            </div>
                                            <span class="spms-hub-step-text-active">Evaluation</span>
                                        <?php elseif ($currentStepIndex > 3): ?>
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
                                    <?php if ($currentStepIndex === 3): ?>
                                        <svg class="spms-hub-stepper-arrow" viewBox="0 0 60 12" fill="none">
                                            <line x1="0" y1="6" x2="48" y2="6" stroke="#f59e0b" stroke-width="2" stroke-dasharray="3 3" />
                                            <polygon points="46,2 56,6 46,10" fill="#f59e0b" />
                                        </svg>
                                    <?php elseif ($currentStepIndex > 3): ?>
                                        <svg class="spms-hub-stepper-arrow" viewBox="0 0 60 12" fill="none">
                                            <line x1="0" y1="6" x2="48" y2="6" stroke="#10b981" stroke-width="2" />
                                            <polygon points="46,2 56,6 46,10" fill="#10b981" />
                                        </svg>
                                    <?php else: ?>
                                        <svg class="spms-hub-stepper-arrow text-zinc-300 dark:text-[#145235]" viewBox="0 0 60 12" fill="none">
                                            <line x1="0" y1="6" x2="48" y2="6" stroke="currentColor" stroke-width="1.5" />
                                            <polygon points="46,2 56,6 46,10" fill="currentColor" />
                                        </svg>
                                    <?php endif; ?>

                                    <!-- Step 4: Final Rating -->
                                    <div style="display: flex; align-items: center; flex-shrink: 0;">
                                        <?php if ($cFolderStatus === \App\Enums\FolderStatus::EVAL_APPROVED || $currentStepIndex > 4): ?>
                                            <div class="spms-hub-circle-completed">
                                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                            <span class="spms-hub-step-text-active">Final Rating</span>
                                        <?php elseif ($currentStepIndex === 4): ?>
                                            <div class="spms-hub-circle-active">
                                                <div class="spms-hub-circle-active-dot"></div>
                                            </div>
                                            <span class="spms-hub-step-text-active">Final Rating</span>
                                        <?php else: ?>
                                            <div class="spms-hub-circle-inactive"></div>
                                            <span class="spms-hub-step-text-inactive">Final Rating</span>
                                        <?php endif; ?>
                                    </div>

                                </div>
                            </div>

                            <!-- 3 STAT TILES -->
                            <div class="spms-hub-stat-grid">
                                <!-- 1. Target Setting Period -->
                                <div class="spms-hub-stat-tile">
                                    <div>
                                        <div class="spms-hub-tile-label">Target Setting Period</div>
                                        <div class="spms-hub-tile-value"><?= esc($targetWindowDateStr) ?></div>
                                    </div>
                                    <div>
                                        <div class="spms-hub-window-pill">
                                            <span class="w-1.5 h-1.5 rounded-full <?= $targetPhasePill['dot'] ?> inline-block"></span>
                                            <span><?= esc($targetPhasePill['text']) ?></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. Evaluation Period -->
                                <div class="spms-hub-stat-tile">
                                    <div>
                                        <div class="spms-hub-tile-label">Evaluation Period</div>
                                        <div class="spms-hub-tile-value"><?= esc($evalWindowDateStr) ?></div>
                                    </div>
                                    <div>
                                        <div class="spms-hub-window-pill">
                                            <span class="w-1.5 h-1.5 rounded-full <?= $evalPhasePill['dot'] ?> inline-block"></span>
                                            <span><?= esc($evalPhasePill['text']) ?></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. Evidence / MOVs -->
                                <div class="spms-hub-stat-tile">
                                    <div>
                                        <div class="spms-hub-tile-label">Evidence / MOVs</div>
                                        <div class="spms-hub-tile-value"><?= $attachmentCount ?> Files Attached</div>
                                    </div>
                                    <div>
                                        <button type="button" onclick="openAttachmentsModal()" class="spms-hub-tile-btn">
                                            View Attachments
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="spms-hub-actions-bar">
                                <a href="<?= site_url('document/' . $primaryDoc['id']) ?>" class="spms-hub-btn-primary">
                                    OPEN & EDIT PAPER
                                </a>
                                <a href="<?= site_url('document/' . $primaryDoc['id'] . '/export-excel') ?>" class="spms-hub-btn-secondary">
                                    Export Excel
                                </a>
                                <button type="button" onclick="openUserGuideModal()" class="spms-hub-btn-secondary">
                                    User Guide
                                </button>
                            </div>

                            <!-- Supervising Authorities & Approval Status Tracker with Subtabs -->
                            <?php 
                                $targetSupsList = $targetSupervisors ?? $evaluatorSupervisors ?? [];
                                $evalSupsList   = $evalSupervisors ?? $evaluatorSupervisors ?? [];
                                $hasSupervisors = !empty($targetSupsList) || !empty($evalSupsList);
                                $defaultSubtab  = $cFolderStatus->isEvaluationPhase() ? 'eval' : 'target';

                                // Split Target authorities into regular supervisors and assigned TWG
                                $tRegularSups = array_values(array_filter($targetSupsList, fn($s) => empty($s['is_twg'])));
                                $tTwgSups     = array_values(array_filter($targetSupsList, fn($s) => !empty($s['is_twg'])));

                                // Split Eval authorities into regular supervisors and assigned TWG
                                $eRegularSups = array_values(array_filter($evalSupsList, fn($s) => empty($s['is_twg'])));
                                $eTwgSups     = array_values(array_filter($evalSupsList, fn($s) => !empty($s['is_twg'])));
                            ?>
                            <?php if ($hasSupervisors): ?>
                                <div class="mt-6 pt-5 border-t border-slate-200 dark:border-zinc-800">
                                    <!-- TOP ROW: Subtabs on TOP LEFT -->
                                    <div class="mb-4">
                                        <div class="inline-flex items-center p-0.5 bg-slate-100 dark:bg-zinc-800/90 rounded-xl border border-slate-200/80 dark:border-zinc-700/80 shadow-2xs self-start">
                                            <button type="button" 
                                                    id="tab-btn-target" 
                                                    onclick="switchSupervisorTab('target')" 
                                                    class="px-3 py-1.5 text-[11px] transition-all rounded-lg cursor-pointer <?= $defaultSubtab === 'target' ? 'font-black bg-white dark:bg-zinc-700 text-emerald-700 dark:text-emerald-400 shadow-xs border border-slate-200/80 dark:border-zinc-600' : 'font-semibold text-slate-500 dark:text-zinc-400 hover:text-slate-800 dark:hover:text-zinc-200' ?>">
                                                Target Setting
                                            </button>
                                            <button type="button" 
                                                    id="tab-btn-eval" 
                                                    onclick="switchSupervisorTab('eval')" 
                                                    class="px-3 py-1.5 text-[11px] transition-all rounded-lg cursor-pointer <?= $defaultSubtab === 'eval' ? 'font-black bg-white dark:bg-zinc-700 text-emerald-700 dark:text-emerald-400 shadow-xs border border-slate-200/80 dark:border-zinc-600' : 'font-semibold text-slate-500 dark:text-zinc-400 hover:text-slate-800 dark:hover:text-zinc-200' ?>">
                                                Evaluation Phase
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Pane 1: Target Setting (2-Column Grid: 1st Col Supervisor, 2nd Col TWG) -->
                                    <div id="pane-supervisors-target" class="<?= $defaultSubtab === 'target' ? '' : 'hidden' ?>">
                                        <div class="spms-supervisors-grid grid grid-cols-1 md:grid-cols-2 gap-5 lg:gap-6">
                                            <!-- Col 1: Supervising Authorities -->
                                            <div class="flex flex-col">
                                                <div class="flex items-center gap-2 mb-3">
                                                    <div class="w-6 h-6 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/80 flex items-center justify-center shrink-0">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-zinc-200">
                                                            Supervising Authorities
                                                        </h4>
                                                        <p class="text-[11px] text-slate-500 dark:text-zinc-400">
                                                            Target setting approvals and commitment sign-offs.
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="space-y-3 flex-1">
                                                    <?php if (empty($tRegularSups)): ?>
                                                        <div class="p-4 rounded-xl border border-dashed border-slate-200 dark:border-zinc-800 text-center text-xs text-slate-400 dark:text-zinc-500">
                                                            No supervising authorities recorded for Target Setting.
                                                        </div>
                                                    <?php else: ?>
                                                        <?php foreach ($tRegularSups as $sup): ?>
                                                            <div class="flex items-center justify-between p-3 rounded-xl border bg-slate-50/70 dark:bg-zinc-900/60 border-slate-200/80 dark:border-zinc-800 transition-all hover:border-slate-300 dark:hover:border-zinc-700">
                                                                <div class="flex items-center gap-3 min-w-0 pr-2">
                                                                    <div class="w-8 h-8 rounded-full bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 flex items-center justify-center font-bold text-xs text-slate-700 dark:text-zinc-300 shrink-0 shadow-xs">
                                                                        <?= strtoupper(substr($sup['name'] ?: 'S', 0, 1)) ?>
                                                                    </div>
                                                                    <div class="min-w-0">
                                                                        <div class="text-xs font-bold text-slate-900 dark:text-zinc-100 truncate">
                                                                            <?= esc($sup['name']) ?>
                                                                        </div>
                                                                        <div class="text-[10px] text-slate-500 dark:text-zinc-400 truncate">
                                                                            <?= esc($sup['position']) ?>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="shrink-0">
                                                                    <?php if ($sup['status'] === 'approved'): ?>
                                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-emerald-600 dark:text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                                            </svg>
                                                                            <?= esc($sup['status_label'] ?: 'Approved') ?>
                                                                        </span>
                                                                    <?php elseif ($sup['status'] === 'returned'): ?>
                                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-300 dark:border-rose-800">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-rose-600 dark:text-rose-400" viewBox="0 0 20 20" fill="currentColor">
                                                                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1z" clip-rule="evenodd" />
                                                                            </svg>
                                                                            <?= esc($sup['status_label'] ?: 'Returned') ?>
                                                                        </span>
                                                                    <?php elseif ($sup['status'] === 'draft'): ?>
                                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                                                            <?= esc($sup['status_label'] ?: 'Awaiting Submission') ?>
                                                                        </span>
                                                                    <?php else: ?>
                                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                                            </svg>
                                                                            <?= esc($sup['status_label'] ?: 'Pending Review') ?>
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Col 2: TWG / PMT Verification -->
                                            <div class="flex flex-col">
                                                <div class="flex items-center gap-2 mb-3">
                                                    <div class="w-6 h-6 rounded-md bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800/80 flex items-center justify-center shrink-0">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-zinc-200">
                                                            Technical Working Group (TWG) / PMT
                                                        </h4>
                                                        <p class="text-[11px] text-slate-500 dark:text-zinc-400">
                                                            Target setting verification and standards compliance review.
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="space-y-3 flex-1">
                                                    <?php if (empty($tTwgSups)): ?>
                                                        <div class="p-4 rounded-xl border border-dashed border-slate-200 dark:border-zinc-800 text-center text-xs text-slate-400 dark:text-zinc-500">
                                                            No TWG / PMT reviewer assigned.
                                                        </div>
                                                    <?php else: ?>
                                                        <?php foreach ($tTwgSups as $twg): ?>
                                                            <div class="flex items-center justify-between p-3 rounded-xl border bg-slate-50/70 dark:bg-zinc-900/60 border-slate-200/80 dark:border-zinc-800 transition-all hover:border-slate-300 dark:hover:border-zinc-700">
                                                                <div class="flex items-center gap-3 min-w-0 pr-2">
                                                                    <div class="w-8 h-8 rounded-full bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 flex items-center justify-center font-bold text-xs text-slate-700 dark:text-zinc-300 shrink-0 shadow-xs">
                                                                        <?= strtoupper(substr($twg['name'] ?: 'T', 0, 1)) ?>
                                                                    </div>
                                                                    <div class="min-w-0">
                                                                        <div class="text-xs font-bold text-slate-900 dark:text-zinc-100 truncate">
                                                                            <?= esc($twg['name']) ?>
                                                                        </div>
                                                                        <div class="text-[10px] text-slate-500 dark:text-zinc-400 truncate">
                                                                            <?= esc($twg['position']) ?>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="shrink-0">
                                                                    <?php if ($twg['status'] === 'approved'): ?>
                                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-emerald-600 dark:text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                                            </svg>
                                                                            <?= esc($twg['status_label'] ?: 'Approved') ?>
                                                                        </span>
                                                                    <?php elseif ($twg['status'] === 'returned'): ?>
                                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-300 dark:border-rose-800">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-rose-600 dark:text-rose-400" viewBox="0 0 20 20" fill="currentColor">
                                                                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1z" clip-rule="evenodd" />
                                                                            </svg>
                                                                            <?= esc($twg['status_label'] ?: 'Returned') ?>
                                                                        </span>
                                                                    <?php elseif ($twg['status'] === 'queued'): ?>
                                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-300 dark:border-indigo-800" title="Waiting for all supervising authorities to approve first">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                            </svg>
                                                                            <?= esc($twg['status_label'] ?: 'Queued for Verification') ?>
                                                                        </span>
                                                                    <?php elseif ($twg['status'] === 'draft'): ?>
                                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                                                            <?= esc($twg['status_label'] ?: 'Awaiting Submission') ?>
                                                                        </span>
                                                                    <?php else: ?>
                                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                                            </svg>
                                                                            <?= esc($twg['status_label'] ?: 'Pending Verification') ?>
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pane 2: Evaluation Phase (2-Column Grid: 1st Col Supervisor, 2nd Col TWG) -->
                                    <div id="pane-supervisors-eval" class="<?= $defaultSubtab === 'eval' ? '' : 'hidden' ?>">
                                        <div class="spms-supervisors-grid grid grid-cols-1 md:grid-cols-2 gap-5 lg:gap-6">
                                            <!-- Col 1: Supervising Authorities -->
                                            <div class="flex flex-col">
                                                <div class="flex items-center gap-2 mb-3">
                                                    <div class="w-6 h-6 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/80 flex items-center justify-center shrink-0">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-zinc-200">
                                                            Supervising Authorities
                                                        </h4>
                                                        <p class="text-[11px] text-slate-500 dark:text-zinc-400">
                                                            Performance evaluation rating approvals.
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="space-y-3 flex-1">
                                                    <?php if (empty($eRegularSups)): ?>
                                                        <div class="p-4 rounded-xl border border-dashed border-slate-200 dark:border-zinc-800 text-center text-xs text-slate-400 dark:text-zinc-500">
                                                            No supervising authorities recorded for Evaluation Phase.
                                                        </div>
                                                    <?php else: ?>
                                                        <?php foreach ($eRegularSups as $sup): ?>
                                                            <div class="flex items-center justify-between p-3 rounded-xl border bg-slate-50/70 dark:bg-zinc-900/60 border-slate-200/80 dark:border-zinc-800 transition-all hover:border-slate-300 dark:hover:border-zinc-700">
                                                                <div class="flex items-center gap-3 min-w-0 pr-2">
                                                                    <div class="w-8 h-8 rounded-full bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 flex items-center justify-center font-bold text-xs text-slate-700 dark:text-zinc-300 shrink-0 shadow-xs">
                                                                        <?= strtoupper(substr($sup['name'] ?: 'S', 0, 1)) ?>
                                                                    </div>
                                                                    <div class="min-w-0">
                                                                        <div class="text-xs font-bold text-slate-900 dark:text-zinc-100 truncate">
                                                                            <?= esc($sup['name']) ?>
                                                                        </div>
                                                                        <div class="text-[10px] text-slate-500 dark:text-zinc-400 truncate">
                                                                            <?= esc($sup['position']) ?>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="shrink-0">
                                                                    <?php if ($sup['status'] === 'approved'): ?>
                                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-emerald-600 dark:text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                                            </svg>
                                                                            <?= esc($sup['status_label'] ?: 'Approved') ?>
                                                                        </span>
                                                                    <?php elseif ($sup['status'] === 'returned'): ?>
                                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-300 dark:border-rose-800">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-rose-600 dark:text-rose-400" viewBox="0 0 20 20" fill="currentColor">
                                                                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1z" clip-rule="evenodd" />
                                                                            </svg>
                                                                            <?= esc($sup['status_label'] ?: 'Returned') ?>
                                                                        </span>
                                                                    <?php elseif ($sup['status_label'] === 'Awaiting Eval Window' || $sup['status'] === 'draft'): ?>
                                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                                                            <?= esc($sup['status_label'] ?: ($sup['status'] === 'draft' ? 'Awaiting Submission' : 'Awaiting Eval Window')) ?>
                                                                        </span>
                                                                    <?php else: ?>
                                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                                            </svg>
                                                                            <?= esc($sup['status_label'] ?: 'Pending Review') ?>
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Col 2: TWG / PMT Calibration -->
                                            <div class="flex flex-col">
                                                <div class="flex items-center gap-2 mb-3">
                                                    <div class="w-6 h-6 rounded-md bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800/80 flex items-center justify-center shrink-0">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-zinc-200">
                                                            Technical Working Group (TWG) / PMT
                                                        </h4>
                                                        <p class="text-[11px] text-slate-500 dark:text-zinc-400">
                                                            Performance rating calibration and MOV compliance review.
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="space-y-3 flex-1">
                                                    <?php if (empty($eTwgSups)): ?>
                                                        <div class="p-4 rounded-xl border border-dashed border-slate-200 dark:border-zinc-800 text-center text-xs text-slate-400 dark:text-zinc-500">
                                                            No TWG / PMT reviewer assigned.
                                                        </div>
                                                    <?php else: ?>
                                                        <?php foreach ($eTwgSups as $twg): ?>
                                                            <div class="flex items-center justify-between p-3 rounded-xl border bg-slate-50/70 dark:bg-zinc-900/60 border-slate-200/80 dark:border-zinc-800 transition-all hover:border-slate-300 dark:hover:border-zinc-700">
                                                                <div class="flex items-center gap-3 min-w-0 pr-2">
                                                                    <div class="w-8 h-8 rounded-full bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 flex items-center justify-center font-bold text-xs text-slate-700 dark:text-zinc-300 shrink-0 shadow-xs">
                                                                        <?= strtoupper(substr($twg['name'] ?: 'T', 0, 1)) ?>
                                                                    </div>
                                                                    <div class="min-w-0">
                                                                        <div class="text-xs font-bold text-slate-900 dark:text-zinc-100 truncate">
                                                                            <?= esc($twg['name']) ?>
                                                                        </div>
                                                                        <div class="text-[10px] text-slate-500 dark:text-zinc-400 truncate">
                                                                            <?= esc($twg['position']) ?>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="shrink-0">
                                                                    <?php if ($twg['status'] === 'approved'): ?>
                                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-emerald-600 dark:text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                                            </svg>
                                                                            <?= esc($twg['status_label'] ?: 'Approved') ?>
                                                                        </span>
                                                                    <?php elseif ($twg['status'] === 'returned'): ?>
                                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-300 dark:border-rose-800">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-rose-600 dark:text-rose-400" viewBox="0 0 20 20" fill="currentColor">
                                                                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1z" clip-rule="evenodd" />
                                                                            </svg>
                                                                            <?= esc($twg['status_label'] ?: 'Returned') ?>
                                                                        </span>
                                                                    <?php elseif ($twg['status'] === 'queued'): ?>
                                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-300 dark:border-indigo-800" title="Waiting for all supervising authorities to approve first">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                            </svg>
                                                                            <?= esc($twg['status_label'] ?: 'Queued for Verification') ?>
                                                                        </span>
                                                                    <?php elseif ($twg['status_label'] === 'Awaiting Eval Window' || $twg['status'] === 'draft'): ?>
                                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                                                            <?= esc($twg['status_label'] ?: ($twg['status'] === 'draft' ? 'Awaiting Submission' : 'Awaiting Eval Window')) ?>
                                                                        </span>
                                                                    <?php else: ?>
                                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                                            </svg>
                                                                            <?= esc($twg['status_label'] ?: 'Pending Verification') ?>
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <script>
                                    function switchSupervisorTab(phase) {
                                        var paneTarget = document.getElementById('pane-supervisors-target');
                                        var paneEval   = document.getElementById('pane-supervisors-eval');
                                        var btnTarget  = document.getElementById('tab-btn-target');
                                        var btnEval    = document.getElementById('tab-btn-eval');

                                        if (!paneTarget || !paneEval || !btnTarget || !btnEval) return;

                                        var baseBtn = 'px-3 py-1.5 text-[11px] transition-all rounded-lg cursor-pointer ';
                                        var activeBtn = baseBtn + 'font-black bg-white dark:bg-zinc-700 text-emerald-700 dark:text-emerald-400 shadow-xs border border-slate-200/80 dark:border-zinc-600';
                                        var inactiveBtn = baseBtn + 'font-semibold text-slate-500 dark:text-zinc-400 hover:text-slate-800 dark:hover:text-zinc-200';

                                        if (phase === 'target') {
                                            paneTarget.classList.remove('hidden');
                                            paneEval.classList.add('hidden');

                                            btnTarget.className = activeBtn;
                                            btnEval.className   = inactiveBtn;
                                        } else {
                                            paneEval.classList.remove('hidden');
                                            paneTarget.classList.add('hidden');

                                            btnEval.className   = activeBtn;
                                            btnTarget.className = inactiveBtn;
                                        }
                                    }
                                </script>
                            <?php endif; ?>
                        </div>

