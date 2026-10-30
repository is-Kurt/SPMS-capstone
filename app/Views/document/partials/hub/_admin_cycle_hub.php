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
                                        <div class="spms-hub-status-pill">
                                            <span class="spms-hub-status-dot"></span>
                                            <span>TARGET PHASE ACTIVE</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- TIMELINE INFORMATION BANNER -->
                            <?php if ($isArchived): ?>
                                <div class="p-2.5 sm:p-4 rounded-xl mb-3 sm:mb-5 flex items-start gap-2.5 sm:gap-3.5 shadow-xs border border-amber-500/30 bg-amber-500/10 text-amber-800 dark:text-amber-300">
                                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5 bg-amber-500/20 text-amber-700 dark:text-amber-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                        </svg>
                                    </div>
                                    <div class="text-[10px] sm:text-xs leading-relaxed">
                                        <span class="font-extrabold block mb-0.5 text-[11px] sm:text-sm">Evaluation Cycle is Closed &amp; Archived</span>
                                        This evaluation cycle is archived and read-only. All ratings, targets, and cascading actions are frozen. To modify dates or make changes, restore the folder using the <strong>Restore Folder</strong> button in Folder Management.
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="spms-hub-banner p-2.5 sm:p-4 rounded-xl mb-3 sm:mb-5 flex items-start gap-2.5 sm:gap-3.5 shadow-xs">
                                    <div class="spms-hub-banner-icon w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="text-[10px] sm:text-xs leading-relaxed">
                                        <span class="spms-hub-banner-title font-extrabold block mb-0.5 text-[11px] sm:text-sm">Target Setting Window is Active</span>
                                        As Administrator, you oversee the evaluation schedule and release cycles to executive leadership. You have <strong>no personal evaluation paper to fill</strong>. Use the <strong>Cycle Release</strong> panel on the right to release this cycle to the <strong>Vice Presidents (Executive Team)</strong> so OPCR commitments can be formulated.
                                    </div>
                                </div>
                            <?php endif; ?>

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
                            ?>
                            <!-- 2 STAT TILES -->
                            <div class="spms-hub-stat-grid spms-hub-stat-grid-2">
                                <!-- 1. Target Period -->
                                <div class="spms-hub-stat-tile">
                                    <div>
                                        <div class="spms-hub-tile-label">Target Setting Period</div>
                                        <div class="spms-hub-tile-value">
                                            <?= !empty($hubTargetStart) && !empty($hubTargetEnd) ? date('M j', strtotime($hubTargetStart)) . ' – ' . date('M j, Y', strtotime($hubTargetEnd)) : 'Dates Not Configured' ?>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="spms-hub-window-pill">
                                            <span class="w-1.5 h-1.5 rounded-full <?= $isUnifiedSchedule ? 'bg-emerald-600 dark:bg-emerald-400' : 'bg-amber-500' ?> inline-block"></span>
                                            <span><?= $isUnifiedSchedule ? 'UNIFIED RANGE' : 'STAGGERED' ?></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. Evaluation Period -->
                                <div class="spms-hub-stat-tile">
                                    <div>
                                        <div class="spms-hub-tile-label">Evaluation Period</div>
                                        <div class="spms-hub-tile-value">
                                            <?= !empty($hubEvalStart) && !empty($hubEvalEnd) ? date('M j', strtotime($hubEvalStart)) . ' – ' . date('M j, Y', strtotime($hubEvalEnd)) : 'Scheduled Following Targets' ?>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="spms-hub-window-pill">
                                            <span class="w-1.5 h-1.5 rounded-full bg-sky-600 dark:bg-sky-400 inline-block"></span>
                                            <span>EVAL PHASE</span>
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
                                            Edit Cycle Dates
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($isArchived): ?>
                                        <button type="button" disabled class="spms-hub-btn-secondary spms-hub-btn-secondary-full opacity-40 cursor-not-allowed hover:bg-transparent" title="Archived evaluation cycles are frozen.">
                                            SPMS Cascade Guide
                                        </button>
                                    <?php else: ?>
                                        <button type="button" onclick="openUserGuideModal()" class="spms-hub-btn-secondary spms-hub-btn-secondary-full">
                                            SPMS Cascade Guide
                                        </button>
                                    <?php endif; ?>
                                </div>
                        </div>

