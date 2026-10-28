<?php use App\Enums\FolderStatus; ?>
    <div class="flex-none flex items-center justify-between py-2 px-3 sm:px-6 bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800 gap-2 sm:gap-4 print-hide">
        
        <?php if (!($isEmbed ?? false)): ?>
        <?php 
            $sysRole = session()->get('role');
            $homeUrl = in_array($sysRole, ['TWG', 'PMT']) ? site_url('ratings') : site_url('folders');
        ?>
        <div class="flex items-center gap-1.5 sm:gap-3 min-w-0 flex-1">
            <!-- Brand mark -->
            <a href="<?= $homeUrl ?>" 
               onclick="return typeof handleInAppExit === 'function' ? handleInAppExit(event, this.href, false) : true;"
               class="cursor-pointer shrink-0 block"
               title="Return to SPMS Home">
                <div class="flex-shrink-0 flex items-center gap-1 mr-2 sm:mr-4 text-text hover:text-accent transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 sm:h-7 sm:w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="hidden sm:block font-black tracking-tighter text-xl uppercase">SPMS</span>
                </div>
            </a>

            <?php if (!empty($rateeNav)): ?>
                <!-- Quick Ratee Navigator Widget (Desktop view - hidden on mobile, placed in toolstrip below) -->
                <div class="hidden md:flex items-center bg-surface-border/25 border border-surface-border rounded-lg p-0.5 text-xs font-bold shadow-2xs shrink-0 mr-1 sm:mr-3">
                    <?php if (!empty($rateeNav['prev'])): ?>
                        <a href="<?= site_url('ratings/show/' . $rateeNav['prev']['folder_id']) ?>" 
                           class="flex items-center gap-1 px-2 py-1 hover:bg-surface-border/50 text-text rounded-md transition-colors cursor-pointer"
                           title="Previous Ratee: <?= esc($rateeNav['prev']['name']) ?><?= !empty($rateeNav['prev']['position']) ? ' (' . esc($rateeNav['prev']['position']) . ')' : '' ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                            <span class="text-[11px]">Prev</span>
                        </a>
                    <?php else: ?>
                        <span class="flex items-center gap-1 px-2 py-1 text-text-muted/30 rounded-md cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                            <span class="text-[11px]">Prev</span>
                        </span>
                    <?php endif; ?>

                    <span class="px-2 py-0.5 text-[11px] font-black text-text border-x border-surface-border/50 whitespace-nowrap"
                          title="Reviewing Ratee <?= $rateeNav['currentIndex'] ?> of <?= $rateeNav['totalRatees'] ?>">
                        <span class="text-accent font-extrabold"><?= $rateeNav['currentIndex'] ?></span>
                        <span class="text-text-muted font-normal">/</span>
                        <span><?= $rateeNav['totalRatees'] ?></span>
                    </span>

                    <?php if (!empty($rateeNav['next'])): ?>
                        <a href="<?= site_url('ratings/show/' . $rateeNav['next']['folder_id']) ?>" 
                           class="flex items-center gap-1 px-2 py-1 hover:bg-surface-border/50 text-text rounded-md transition-colors cursor-pointer"
                           title="Next Ratee: <?= esc($rateeNav['next']['name']) ?><?= !empty($rateeNav['next']['position']) ? ' (' . esc($rateeNav['next']['position']) . ')' : '' ?>">
                            <span class="text-[11px]">Next</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    <?php else: ?>
                        <span class="flex items-center gap-1 px-2 py-1 text-text-muted/30 rounded-md cursor-not-allowed">
                            <span class="text-[11px]">Next</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <span class="font-bold text-sm text-text px-1 sm:px-2 py-1 min-w-[50px] max-w-[140px] xs:max-w-[200px] sm:max-w-md md:max-w-lg truncate select-none cursor-default" title="<?= esc($doc['title']) ?>">
                <?= esc($doc['title']) ?>
            </span>
            <input type="hidden" id="doc-title" value="<?= esc($doc['title']) ?>">

            <?php if (empty($isGuide)): ?>
                <?php
                    $cVer = (int)($currentVersion ?? 1);
                    $vVer = (int)($viewingVersion ?? $cVer);
                    $isHist = !empty($isHistoricalVersion);
                    $versionsList = $folderVersions ?? [];
                ?>
                <div class="relative inline-block text-left shrink-0 ml-1" id="version-dropdown-container">
                    <button type="button"
                            onclick="const m = document.getElementById('version-dropdown-menu'); if (m) m.classList.toggle('hidden');"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-extrabold border transition-all cursor-pointer shadow-2xs <?= $isHist ? 'bg-amber-500/15 text-amber-700 dark:text-amber-300 border-amber-500/30 hover:bg-amber-500/25' : 'bg-surface-border/25 hover:bg-surface-border/45 text-text-muted hover:text-text border-surface-border' ?>"
                            title="Document Version History (New version created on every submission)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 <?= $isHist ? 'text-amber-500' : 'text-text-muted' ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>v<?= $vVer ?><?= $isHist ? ' (Read-Only)' : '' ?></span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div id="version-dropdown-menu" class="hidden absolute left-0 mt-2 w-72 sm:w-80 rounded-xl bg-surface border border-surface-border shadow-xl z-50 overflow-hidden">
                        <div class="px-3.5 py-2.5 bg-surface-border/20 border-b border-surface-border flex items-center justify-between">
                            <span class="text-[11px] font-black uppercase tracking-wider text-text">Submission Version History</span>
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-surface-border/40 text-text-muted">Latest: v<?= $cVer ?></span>
                        </div>
                        <div class="max-h-72 overflow-y-auto divide-y divide-surface-border/60">
                            <?php
                                // Build indexed map of recorded snapshots by version number
                                $snapshotsByVer = [];
                                foreach ($versionsList as $vl) {
                                    $verKey = (int)($vl['version_number'] ?? $vl['version'] ?? 1);
                                    $snapshotsByVer[$verKey] = $vl;
                                }
                                // Ensure current version is always listed at the top
                                $allVerNums = array_unique(array_merge([$cVer], array_keys($snapshotsByVer)));
                                rsort($allVerNums);
                            ?>
                            <?php foreach ($allVerNums as $verNum): ?>
                                <?php
                                    $snap = $snapshotsByVer[$verNum] ?? null;
                                    $isSelectedVer = ($verNum === $vVer);
                                    $isCurrentVer  = ($verNum === $cVer);
                                    $verUrl = $isCurrentVer
                                        ? site_url('document/' . ($doc['id'] ?? ''))
                                        : site_url('document/' . ($doc['id'] ?? '') . '?version=' . $verNum);
                                ?>
                                <a href="<?= esc($verUrl) ?>"
                                   class="block px-3.5 py-2.5 hover:bg-surface-border/40 transition-colors <?= $isSelectedVer ? 'bg-surface-border/25' : '' ?>">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-extrabold <?= $isCurrentVer ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30' : 'bg-surface-border/40 text-text' ?>">
                                                v<?= $verNum ?>
                                            </span>
                                            <span class="text-xs font-bold text-text">
                                                <?= $isCurrentVer ? 'Current Version' : 'Historical Snapshot' ?>
                                            </span>
                                        </div>
                                        <?php if (!empty($snap['returned_at']) || !empty($snap['returned_by_name'])): ?>
                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-rose-500/15 text-rose-600 dark:text-rose-400 border border-rose-500/25">
                                                Returned
                                            </span>
                                        <?php elseif (!empty($snap['submitted_at'])): ?>
                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-500/25">
                                                Submitted
                                            </span>
                                        <?php else: ?>
                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-surface-border/30 text-text-muted border border-surface-border">
                                                Working Draft
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($snap['submitted_at'])): ?>
                                        <div class="text-[10px] text-text-muted mt-1">
                                            Submitted: <?= esc(date('M j, Y g:i A', strtotime($snap['submitted_at']))) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($snap['returned_by_name'])): ?>
                                        <?php $snapRemarks = $snap['return_remarks'] ?? $snap['return_reason'] ?? ''; ?>
                                        <div class="text-[10px] text-rose-600 dark:text-rose-400 mt-0.5 truncate" title="Returned by <?= esc($snap['returned_by_name']) ?><?= !empty($snapRemarks) ? ': ' . esc($snapRemarks) : '' ?>">
                                            Returned by <?= esc($snap['returned_by_name']) ?><?= !empty($snapRemarks) ? ' — "' . esc($snapRemarks) . '"' : '' ?>
                                        </div>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <script>
                    document.addEventListener('click', function(ev) {
                        const container = document.getElementById('version-dropdown-container');
                        const menu = document.getElementById('version-dropdown-menu');
                        if (container && menu && !container.contains(ev.target)) {
                            menu.classList.add('hidden');
                        }
                    });
                </script>
            <?php endif; ?>

            <span id="save-status" class="ml-1 sm:ml-3 shrink-0 text-[10px] uppercase tracking-widest font-bold transition-all"></span>
        </div>
        <?php else: ?>
        <div class="flex items-center gap-2 min-w-0 flex-1">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-sky-100 text-sky-800 dark:bg-sky-950/80 dark:text-sky-300 text-[11px] font-bold border border-sky-200 dark:border-sky-800">
                Reference Guide (Read-Only)
            </span>
            <span class="font-bold text-xs text-text truncate"><?= esc($doc['title']) ?></span>
        </div>
        <?php endif; ?>
        
        <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
            <?php if ($canEditTargets || $canEditEvaluation): ?>
            <!-- Save Button with hover (Ctrl + S) -->
            <div class="relative group inline-flex items-center print-hide">
                <button type="button" onclick="saveDocument(true)" title="Save (Ctrl + S)"
                        class="inline-flex items-center gap-1.5 px-2.5 sm:px-3.5 py-2 sm:py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] sm:text-xs font-bold rounded-lg shadow-sm transition-all active:scale-[0.98] cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    <span class="hidden sm:inline">Save</span>
                </button>
                <div class="absolute top-full left-1/2 -translate-x-1/2 mt-2 hidden group-hover:flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-zinc-900 text-white shadow-xl pointer-events-none whitespace-nowrap z-50">
                    <span>Save</span>
                    <span class="text-zinc-400 font-mono text-[9px] bg-zinc-800 px-1 py-0.5 rounded border border-zinc-700">(Ctrl + S)</span>
                    <div class="absolute -top-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-zinc-900 rotate-45"></div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($canEditTargets): ?>
            <!-- Dynamic Reset Doc / Append from Basis Action Button -->
            <button id="btn-dynamic-doc-action" type="button" 
                    onclick="handleDynamicDocAction()" 
                    class="inline-flex items-center gap-1.5 px-2.5 sm:px-3.5 py-2 sm:py-2.5 rounded-lg text-[10px] sm:text-xs font-bold border transition-all shadow-sm active:scale-[0.98] cursor-pointer bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-200 border-slate-300 dark:border-zinc-700 print-hide"
                    title="Reset document targets and rubrics to clean template">
                <svg id="dynamic-doc-action-icon" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span id="dynamic-doc-action-label">Reset Doc</span>
            </button>
            <?php endif; ?>

            <?php if ($isOwner || session()->get('role') === 'Admin'): ?>
            <!-- Export to Excel Button (Desktop - hidden on mobile) -->
            <button type="button" onclick="exportToExcel()" 
                    class="hidden md:inline-flex items-center gap-1.5 px-2.5 sm:px-3.5 py-2 sm:py-2.5 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-[10px] sm:text-xs font-bold rounded-lg border border-emerald-500/30 hover:border-emerald-500/50 transition-all cursor-pointer shadow-sm active:scale-[0.98] print-hide"
                    title="Export document to official Civil Service Commission Excel spreadsheet (.xlsx)">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Export Excel</span>
            </button>
            <?php endif; ?>


            <?php if (!$isGuide): ?>
                <?php
                    $canonStatus = FolderStatus::canonical($status ?? '');
                ?>

                <?php if (!empty($isHistoricalVersion)): ?>
                    <!-- Historical version is read-only; indicator is shown in the version dropdown -->

                <?php elseif ($canonStatus === FolderStatus::TARGET_MISSED || $canonStatus === FolderStatus::UNEVALUATED): ?>
                    <span class="inline-flex items-center px-3 py-2 rounded-lg text-xs font-black uppercase tracking-wider bg-rose-500/15 text-rose-700 dark:text-rose-400 border border-rose-500/30">
                        Missed Deadline
                    </span>

                <?php elseif ($isOwner): ?>
                    <!-- RATEE ACTIONS (Ultra-minimal: Submit / Unsubmit / Cascade) -->
                    <?php if ($canonStatus->isTargetPhase()): ?>
                        <?php if ($canonStatus === FolderStatus::DRAFT_TARGET): ?>
                            <?php if (session()->get('role') === 'Admin'): ?>
                                <div class="flex items-center gap-1.5 bg-emerald-50 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg shadow-xs">
                                    <span>Master Cycle Template</span>
                                </div>
                            <?php elseif (isset($isParentTargetApproved) && !$isParentTargetApproved): ?>
                                <button type="button" disabled 
                                        class="bg-amber-500/70 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-5 rounded-lg shadow-sm cursor-not-allowed" 
                                        title="Waiting for Superior Target Approval. Under SPMS rules, individual commitments require approved superior targets as a basis.">
                                    Submit
                                </button>
                            <?php else: ?>
                                <button id="btn-submit-target" type="button" 
                                        onclick="saveWith({ after: () => lockFolderTarget() })" 
                                        class="bg-info-500 hover:bg-info-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg shadow-info-500/20 transition-all active:scale-[0.98] cursor-pointer">
                                    Submit
                                </button>
                            <?php endif; ?>

                        <?php elseif ($canonStatus === FolderStatus::TARGET_SUBMITTED): ?>
                            <?php
                                $routingModel = new \App\Models\EvaluationRoutingModel();
                                $hasPartialTargetApproval = $routingModel->where('folder_id', $doc['document_folder_id'])
                                                                         ->whereIn('status', ['approved', 'target_approved'])
                                                                         ->countAllResults() > 0;
                            ?>
                            <?php if (!$hasPartialTargetApproval): ?>
                                <button id="btn-unsubmit-target" type="button" 
                                        onclick="unsubmitTargetDocument()" 
                                        class="bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:hover:bg-rose-900/60 dark:text-rose-300 border border-rose-300 dark:border-rose-800 text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-5 rounded-lg shadow-sm transition-all active:scale-[0.98] cursor-pointer">
                                    Unsubmit
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- Evaluation Phase Ratee Actions -->
                        <?php if ($canonStatus === FolderStatus::DRAFT_EVALUATION): ?>
                            <button id="btn-submit" type="button" 
                                    onclick="saveWith({ after: () => lockFolderEvaluation() })" 
                                    class="bg-info-500 hover:bg-info-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg shadow-info-500/20 transition-all active:scale-[0.98] cursor-pointer">
                                Submit
                            </button>

                        <?php elseif ($canonStatus === FolderStatus::EVAL_SUBMITTED): ?>
                            <?php 
                                $ownerDocType = strtolower($doc['doc_type'] ?? 'ipcr');
                                $evalEndCol = $ownerDocType . '_eval_end';
                                $isEvalPeriodEnded = !empty($doc[$evalEndCol]) && date('Y-m-d H:i:s') > $doc[$evalEndCol]; 
                                $routingModel = new \App\Models\EvaluationRoutingModel();
                                $hasPartialApproval = $routingModel->where('folder_id', $doc['document_folder_id'])
                                                                   ->whereIn('status', ['approved', 'eval_approved'])
                                                                   ->countAllResults() > 0;
                                $cannotUnsubmit = $isEvalPeriodEnded || $hasPartialApproval;
                            ?>
                            <?php if (!$cannotUnsubmit): ?>
                                <button id="btn-unsubmit-eval" type="button" 
                                        onclick="unsubmitEvaluationDocument()"
                                        class="bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:hover:bg-rose-900/60 dark:text-rose-300 border border-rose-300 dark:border-rose-800 shadow-sm active:scale-[0.98] cursor-pointer text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-5 rounded-lg transition-all"
                                        title="Unsubmit to edit your ratings">
                                    Unsubmit
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- REVIEWER ACTIONS (Ultra-minimal: Approve / Return / Unapprove) -->
                    <?php if (in_array(session()->get('role'), ['TWG', 'PMT'])): ?>
                        <?php if ($canonStatus->isTargetPhase()): ?>
                            <?php if ($canonStatus === FolderStatus::TARGET_APPROVED && in_array($doc['target_approver_role'] ?? '', ['TWG', 'PMT'])): ?>
                                <button id="btn-unapprove-target" type="button" 
                                        onclick="unapproveFolderTarget()" 
                                        class="bg-warning-500 hover:bg-warning-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-5 rounded-lg shadow-lg transition-all active:scale-[0.98] cursor-pointer">
                                    Unapprove
                                </button>
                            <?php elseif ($canonStatus === FolderStatus::TARGET_SUBMITTED): ?>
                                <?php if (!empty($allSupervisorsApproved)): ?>
                                    <div class="flex items-center gap-1.5 sm:gap-2">
                                        <button id="btn-return-target" type="button" 
                                                onclick="returnFolderRevision()" 
                                                class="bg-revision-500 hover:bg-revision-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-5 rounded-lg shadow-lg transition-all active:scale-[0.98] cursor-pointer">
                                            Return
                                        </button>
                                        <button id="btn-approve-target" type="button" 
                                                onclick="approveFolderTarget(null)" 
                                                class="bg-success-500 hover:bg-success-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-5 rounded-lg shadow-lg transition-all active:scale-[0.98] cursor-pointer">
                                            Approve
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border border-indigo-500/25 shadow-xs" title="All supervising authorities must approve first before TWG/PMT verification.">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Queued for Verification
                                    </span>
                                <?php endif; ?>
                            <?php elseif ($canonStatus === FolderStatus::DRAFT_TARGET): ?>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-zinc-500/10 text-zinc-600 dark:text-zinc-300 border border-zinc-500/20 shadow-xs" title="Employee is currently drafting targets. You can view and leave comments on cells.">
                                    <span>View & Comment Mode</span>
                                </span>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- TWG in Evaluation Phase -->
                            <?php if ($canonStatus === FolderStatus::EVAL_APPROVED): ?>
                                <button id="btn-unapprove-evaluation" type="button" 
                                        onclick="unapproveFolderEvaluation()" 
                                        class="bg-warning-500 hover:bg-warning-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-5 rounded-lg shadow-lg shadow-warning-500/20 transition-all active:scale-[0.98] cursor-pointer">
                                    Unapprove
                                </button>
                            <?php elseif ($canonStatus === FolderStatus::EVAL_SUBMITTED): ?>
                                <?php if (!empty($allSupervisorsApproved)): ?>
                                    <div class="flex items-center gap-1.5 sm:gap-2">
                                        <button id="btn-return" type="button" 
                                                onclick="returnFolderRevision()" 
                                                class="bg-revision-500 hover:bg-revision-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-5 rounded-lg shadow-lg shadow-revision-500/20 transition-all active:scale-[0.98] cursor-pointer">
                                            Return
                                        </button>
                                        <button id="btn-approve" type="button" 
                                                onclick="approveFolderEvaluation()" 
                                                class="bg-success-500 hover:bg-success-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-5 rounded-lg shadow-lg shadow-success-500/20 transition-all active:scale-[0.98] cursor-pointer">
                                            Approve
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border border-indigo-500/25 shadow-xs" title="All supervising authorities must approve first before TWG/PMT verification.">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Queued for Verification
                                    </span>
                                <?php endif; ?>
                            <?php elseif ($canonStatus === FolderStatus::DRAFT_EVALUATION): ?>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-zinc-500/10 text-zinc-600 dark:text-zinc-300 border border-zinc-500/20 shadow-xs" title="Employee is currently drafting accomplishments. You can view and leave comments on cells.">
                                    <span>View & Comment Mode</span>
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- Supervisor / Admin Reviewer Actions -->
                        <?php if ($canonStatus->isTargetPhase()): ?>
                            <?php if ($canonStatus === FolderStatus::TARGET_SUBMITTED || $canonStatus === FolderStatus::TARGET_APPROVED): ?>
                                <?php 
                                    $isCurrentSupervisorApproved = (!empty($currentEvaluatorRoute) && in_array($currentEvaluatorRoute['status'], ['target_approved', 'approved']));
                                    $isTwgVerifiedTarget = ($canonStatus === FolderStatus::TARGET_APPROVED && in_array($doc['target_approver_role'] ?? '', ['TWG', 'PMT'], true));
                                ?>
                                <?php if ($isTwgVerifiedTarget): ?>
                                    <!-- Locked by TWG/PMT verification: Supervisor cannot unapprove unless TWG unapproves first -->
                                <?php elseif ($isCurrentSupervisorApproved): ?>
                                    <button id="btn-unapprove-target" type="button" 
                                            onclick="unapproveFolderTarget()" 
                                            class="bg-warning-500 hover:bg-warning-600 shadow-warning-500/20 active:scale-[0.98] cursor-pointer text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-5 rounded-lg shadow-lg transition-all">
                                        Unapprove
                                    </button>
                                <?php else: ?>
                                    <div class="flex items-center gap-1.5 sm:gap-2">
                                        <button id="btn-return-target" type="button" 
                                                onclick="returnFolderRevision()" 
                                                class="bg-revision-500 hover:bg-revision-600 shadow-revision-500/20 active:scale-[0.98] cursor-pointer text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-5 rounded-lg shadow-lg transition-all">
                                            Return
                                        </button>
                                        <button id="btn-approve-target" type="button" 
                                                onclick="approveFolderTarget(null)" 
                                                class="bg-success-500 hover:bg-success-600 shadow-success-500/20 active:scale-[0.98] cursor-pointer text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-5 rounded-lg shadow-lg transition-all">
                                            Approve
                                        </button>
                                    </div>
                                <?php endif; ?>
                            <?php elseif ($canonStatus === FolderStatus::DRAFT_TARGET): ?>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-zinc-500/10 text-zinc-600 dark:text-zinc-300 border border-zinc-500/20 shadow-xs" title="Employee is currently drafting targets. You can view and leave comments on cells.">
                                    <span>View & Comment Mode</span>
                                </span>
                            <?php endif; ?>

                        <?php else: ?>
                            <!-- Evaluation Phase Supervisor Actions -->
                            <?php if ($canonStatus === FolderStatus::EVAL_SUBMITTED || $canonStatus === FolderStatus::EVAL_APPROVED): ?>
                                <?php 
                                    $isCurrentEvalApproved = (isset($routingStatus) && in_array($routingStatus, ['approved', 'eval_approved'])) || ($canonStatus === FolderStatus::EVAL_APPROVED && session()->get('role') === 'Admin');
                                    $folderRecForTwg = !empty($doc['document_folder_id']) ? (new \App\Models\DocumentFolderModel())->find($doc['document_folder_id']) : null;
                                    $hasAssignedTwgEval = $folderRecForTwg ? !empty((new \App\Models\TwgUnitAssignmentModel())->resolveAssignedTwgForFolder($folderRecForTwg)) : false;
                                    $isTwgVerifiedEval = ($canonStatus === FolderStatus::EVAL_APPROVED && $hasAssignedTwgEval);
                                ?>
                                <?php if ($isTwgVerifiedEval): ?>
                                    <!-- Locked by TWG/PMT verification: Supervisor cannot unapprove unless TWG unapproves first -->
                                <?php elseif ($isCurrentEvalApproved): ?>
                                    <button id="btn-unapprove-evaluation" type="button" 
                                            onclick="unapproveFolderEvaluation()" 
                                            class="bg-warning-500 hover:bg-warning-600 shadow-warning-500/20 active:scale-[0.98] cursor-pointer text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-5 rounded-lg shadow-lg transition-all">
                                        Unapprove
                                    </button>
                                <?php else: ?>
                                    <div class="flex items-center gap-1.5 sm:gap-2">
                                        <button id="btn-return" type="button" 
                                                onclick="returnFolderRevision()" 
                                                class="bg-revision-500 hover:bg-revision-600 shadow-revision-500/20 active:scale-[0.98] cursor-pointer text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-5 rounded-lg shadow-lg transition-all">
                                            Return
                                        </button>
                                        <button id="btn-approve" type="button" 
                                                onclick="approveFolderEvaluation()" 
                                                class="bg-success-500 hover:bg-success-600 shadow-success-500/20 active:scale-[0.98] cursor-pointer text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-5 rounded-lg shadow-lg transition-all">
                                            Approve
                                        </button>
                                    </div>
                                <?php endif; ?>
                            <?php elseif ($canonStatus === FolderStatus::DRAFT_EVALUATION): ?>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-zinc-500/10 text-zinc-600 dark:text-zinc-300 border border-zinc-500/20 shadow-xs" title="Employee is currently drafting accomplishments. You can view and leave comments on cells.">
                                    <span>View & Comment Mode</span>
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="bg-info-50 dark:bg-info-500/10 border border-info-200 dark:border-info-500/20 text-info-600 dark:text-info-400 text-[10px] uppercase tracking-widest font-black py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg shadow-sm cursor-default flex items-center gap-1.5">
                    Guide<span class="hidden sm:inline"> Template</span>
                </div>
            <?php endif; ?>

            <!-- Theme Toggle Divider & Button -->
            <div class="h-6 w-px bg-surface-border hidden sm:block shrink-0 mx-0.5"></div>
            <button type="button" onclick="window.handleThemeToggle(this)"
                    class="inline-flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-surface-border/20 hover:bg-surface-border/40 text-text border border-surface-border transition-all cursor-pointer shadow-sm active:scale-[0.98] shrink-0 print-hide"
                    title="Toggle Light / Dark Mode"
                    aria-label="Toggle Light / Dark Mode">
                <!-- Sun icon: visible in dark mode, click to switch to light -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 hidden dark:block text-amber-400 hover:rotate-45 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <!-- Moon icon: visible in light mode, click to switch to dark -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 block dark:hidden text-zinc-700 hover:-rotate-12 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Secondary Action Toolstrip (Prev/Next Ratee, Print, Excel, Rubrics, Theme) -->
    <div class="md:hidden flex-none flex items-center gap-1.5 px-3 py-1.5 bg-surface/50 border-b border-surface-border overflow-x-auto custom-scrollbar print-hide">
        <?php if (!empty($rateeNav)): ?>
            <div class="flex items-center bg-surface-border/30 border border-surface-border rounded-lg p-0.5 text-xs font-bold shadow-2xs shrink-0">
                <?php if (!empty($rateeNav['prev'])): ?>
                    <a href="<?= site_url('ratings/show/' . $rateeNav['prev']['folder_id']) ?>" 
                       class="flex items-center gap-0.5 px-2 py-1 hover:bg-surface-border/50 text-text rounded-md transition-colors cursor-pointer"
                       title="Previous Ratee: <?= esc($rateeNav['prev']['name']) ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                        <span class="text-[10px]">Prev</span>
                    </a>
                <?php else: ?>
                    <span class="flex items-center gap-0.5 px-2 py-1 text-text-muted/30 rounded-md cursor-not-allowed">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                        <span class="text-[10px]">Prev</span>
                    </span>
                <?php endif; ?>

                <span class="px-2 py-0.5 text-[10px] font-black text-text border-x border-surface-border/50 whitespace-nowrap">
                    <span class="text-accent font-extrabold"><?= $rateeNav['currentIndex'] ?></span>
                    <span class="text-text-muted font-normal">/</span>
                    <span><?= $rateeNav['totalRatees'] ?></span>
                </span>

                <?php if (!empty($rateeNav['next'])): ?>
                    <a href="<?= site_url('ratings/show/' . $rateeNav['next']['folder_id']) ?>" 
                       class="flex items-center gap-0.5 px-2 py-1 hover:bg-surface-border/50 text-text rounded-md transition-colors cursor-pointer"
                       title="Next Ratee: <?= esc($rateeNav['next']['name']) ?>">
                        <span class="text-[10px]">Next</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </a>
                <?php else: ?>
                    <span class="flex items-center gap-0.5 px-2 py-1 text-text-muted/30 rounded-md cursor-not-allowed">
                        <span class="text-[10px]">Next</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($isOwner || session()->get('role') === 'Admin'): ?>
            <button type="button" onclick="exportToExcel()" 
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-[10px] font-bold rounded-lg border border-emerald-500/30 transition-all cursor-pointer shadow-xs shrink-0"
                    title="Export document to Excel (.xlsx)">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Excel</span>
            </button>
        <?php endif; ?>


        <!-- Mobile Theme Toggle Button -->
        <button type="button" onclick="window.handleThemeToggle(this)" 
                class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-surface-border/20 hover:bg-surface-border/40 text-text text-[10px] font-bold rounded-lg border border-surface-border transition-all cursor-pointer shadow-xs shrink-0"
                title="Toggle Light / Dark Mode"
                aria-label="Toggle Light / Dark Mode">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 hidden dark:block text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 block dark:hidden text-zinc-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
            <span>Theme</span>
        </button>
    </div>


    <div class="flex-none flex bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-surface-border px-3 sm:px-6 <?= $isEditable ? 'gap-2' : 'gap-4' ?> text-sm font-bold pt-2 overflow-x-auto whitespace-nowrap scrollbar-hide print-hide" id="tab-bar">
        <!-- Tabs injected here via JS -->
    </div>
