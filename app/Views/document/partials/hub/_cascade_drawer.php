        <?php 
            $isUserAdmin = (session()->get('role') === 'Admin');
            $userPos = strtolower(session()->get('position') ?? '');
            $userDocType = strtolower(session()->get('doc_type') ?? '');
            $docType = strtolower($ownerDocType ?? 'ipcr');
            $isOpcrFolder = ($docType === 'opcr') 
                         || str_contains(strtoupper($activeFolder['title'] ?? ''), 'OPCR')
                         || str_contains($userPos, 'vice president')
                         || str_contains($userPos, 'vpaa')
                         || str_contains($userPos, 'president')
                         || ($userDocType === 'opcr');

            if (!$isOpcrFolder && !empty($documents)) {
                foreach ($documents as $d) {
                    if (stripos($d['title'] ?? '', 'opcr') !== false) {
                        $isOpcrFolder = true;
                        break;
                    }
                }
            }

            $isSupervisorOrChair = in_array(session()->get('role'), ['Supervisor', 'Admin']) 
                                || !empty($isOwnerChair) 
                                || str_contains($userPos, 'chair') 
                                || str_contains($userPos, 'head')
                                || str_contains($userPos, 'dean')
                                || $isOpcrFolder;
            $canCascade = $isUserAdmin || ($isSupervisorOrChair && ($activeFolder['user_id'] == session()->get('user_id'))); 
            $tStartDate = !empty($activeFolder[$docType . '_target_start']) ? date('M d, Y', strtotime($activeFolder[$docType . '_target_start'])) : null;
            $tEndDate   = !empty($activeFolder[$docType . '_target_end'])   ? date('M d, Y', strtotime($activeFolder[$docType . '_target_end']))   : null;
            $eStartDate = !empty($activeFolder[$docType . '_eval_start'])   ? date('M d, Y', strtotime($activeFolder[$docType . '_eval_start']))   : null;
            $eEndDate   = !empty($activeFolder[$docType . '_eval_end'])     ? date('M d, Y', strtotime($activeFolder[$docType . '_eval_end']))     : null;

            $targetDateStr = ($tStartDate && $tEndDate) ? ($tStartDate . ' – ' . $tEndDate) : ($tStartDate ?? $tEndDate ?? 'Start – End');
            $evalDateStr   = ($eStartDate && $eEndDate) ? ($eStartDate . ' – ' . $eEndDate) : ($eStartDate ?? $eEndDate ?? 'Start – End');
        ?>

        <!-- RIGHT SIDEBAR (CASCADE DISTRIBUTION FOR ADMINS / FOLDER DETAILS FOR USERS) -->
        <div id="bottom-sheet" class="custom-scrollbar fixed inset-x-0 bottom-0 z-50 shadow-2xl lg:shadow-xl rounded-t-3xl lg:rounded-2xl transition-transform duration-300 transform spms-bottom-sheet-collapsed lg:static lg:translate-y-0 lg:w-80 lg:shrink-0 <?= $canCascade ? 'lg:self-start lg:h-fit' : '' ?> flex flex-col p-4 sm:p-5 gap-4 overflow-hidden lg:overflow-y-auto max-h-[85vh] lg:max-h-none bg-surface dark:bg-zinc-950 border border-surface-border dark:border-zinc-800">
            
            <div class="lg:hidden flex justify-center py-2 cursor-pointer touch-none" onclick="toggleBottomSheet()">
                <div class="w-12 h-1 bg-zinc-300 dark:bg-zinc-600 rounded-full"></div>
            </div>

            <?php if ($canCascade): ?>
                <!-- CASCADE MANAGEMENT SECTION (For Admins & Supervisors) -->
                <div class="flex flex-col gap-3 shrink-0">
                    <?php 
                        $isUserAdmin = (session()->get('role') === 'Admin');
                        $cascadedTeamId = $activeFolder['routing_preset_id'] ?? null; 
                    ?>
                    <div class="flex items-center justify-between shrink-0">
                        <h4 class="text-[10px] font-black uppercase text-text-muted tracking-wider"><?= $isUserAdmin ? 'Cycle Release Management' : 'Cascade Management' ?></h4>
                    </div>

                    <?php 
                        $cActiveStatus = \App\Enums\FolderStatus::canonical($activeFolder['status'] ?? '');
                        $inActiveTargetStatus = $isUserAdmin || in_array($cActiveStatus, [
                            \App\Enums\FolderStatus::DRAFT_TARGET,
                            \App\Enums\FolderStatus::TARGET_SUBMITTED,
                            \App\Enums\FolderStatus::TARGET_APPROVED,
                        ], true);

                        if ($isUserAdmin) {
                            $adminTargetEnds = array_filter([
                                $activeFolder['opcr_target_end'] ?? null,
                                $activeFolder['dpcr_target_end'] ?? null,
                                $activeFolder['ipcr_target_end'] ?? null,
                                $activeFolder['iperf_target_end'] ?? null,
                                $activeFolder['target_date_end'] ?? null,
                            ]);
                            $isTargetDeadlineEnded = !empty($adminTargetEnds) && empty(array_filter(
                                $adminTargetEnds,
                                static fn($d) => strtotime((string)$d) === false || time() <= strtotime((string)$d)
                            ));
                        } else {
                            $targetDeadlineStr = $activeFolder['target_date_end'] ?? null;
                            $isTargetDeadlineEnded = !empty($targetDeadlineStr) && strtotime((string)$targetDeadlineStr) !== false && time() > strtotime((string)$targetDeadlineStr);
                        }

                        $canActuallyCascade = $inActiveTargetStatus && !$isTargetDeadlineEnded && !$isArchived;
                        $hasPresets = !empty($presets);
                        $isAlreadyCascaded = !empty($cascadedTeamId) || !empty($cascadedChildren);
                    ?>

                    <?php if ($isAlreadyCascaded || !$canActuallyCascade): ?>
                        <div class="relative w-full shrink-0">
                            <select id="team-cascade-select" disabled class="w-full bg-zinc-50 dark:bg-zinc-900 text-xs font-bold text-text outline-none pl-3.5 pr-8 py-2.5 rounded-xl appearance-none border border-surface-border opacity-60 cursor-not-allowed">
                                <?php if (!empty($cascadedTeamId)): ?>
                                    <?php foreach ($presets as $preset): ?>
                                        <option value="<?= $preset['id'] ?>" <?= ($cascadedTeamId == $preset['id']) ? 'selected' : '' ?> class="bg-surface text-text">
                                            <?= esc($preset['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php elseif (!empty($cascadedChildren)): ?>
                                    <option value="" selected class="bg-surface text-text">
                                        <?= $isUserAdmin ? 'Released Recipients' : 'Cascaded Staff' ?> (<?= count($cascadedChildren) ?>)
                                    </option>
                                <?php else: ?>
                                    <option value="" selected class="bg-surface text-text-muted">
                                        <?= $isUserAdmin ? 'No Staff Released' : 'No Staff Cascaded' ?>
                                    </option>
                                <?php endif; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>

                        <?php if ($canActuallyCascade): ?>
                            <?php $pendingCount = (int)($pendingTeamMembersCount ?? 0); ?>
                            <?php if ($pendingCount > 0): ?>
                                <!-- SYNC NOTICE: MISSING MEMBERS DETECTED -->
                                <div class="p-3 rounded-xl border border-emerald-300 dark:border-emerald-800/60 bg-emerald-50 dark:bg-emerald-950/40 flex flex-col gap-2 shadow-2xs">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-1.5 font-bold text-xs text-emerald-800 dark:text-emerald-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                            </svg>
                                            <span>New Staff (+<?= $pendingCount ?>)</span>
                                        </div>
                                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-200 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200">
                                            Sync Available
                                        </span>
                                    </div>
                                    <p class="text-[10px] leading-relaxed text-emerald-700 dark:text-emerald-400 font-medium">
                                        <?= $pendingCount ?> member<?= $pendingCount === 1 ? ' was' : 's were' ?> added to this staff list. <?= $isUserAdmin ? 'Sync to release evaluation folders to new members.' : 'Sync to provision their target folders without affecting existing cascaded members.' ?>
                                    </p>
                                    <button id="btn-sync-cascade" onclick="triggerSyncCascade('<?= $activeFolder['id'] ?>')" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs shadow-xs transition-all active:scale-98 cursor-pointer flex items-center justify-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        <span><?= $isUserAdmin ? "Sync & Release (+{$pendingCount} New)" : "Sync Staff (+{$pendingCount} New)" ?></span>
                                    </button>
                                </div>
                            <?php endif; ?>

                            <button onclick="triggerUncascade('<?= $activeFolder['id'] ?>')" class="w-full py-2.5 text-rose-600 dark:text-rose-400 hover:text-white border border-rose-300 dark:border-[#361a1f] bg-rose-50 dark:bg-[#1c1214] hover:bg-rose-600 dark:hover:bg-[#261619] rounded-xl transition-colors cursor-pointer flex justify-center items-center gap-1.5 font-bold text-xs uppercase tracking-wider shrink-0">
                                <?= $isUserAdmin ? 'Revoke Cycle Release' : 'Revoke Cascade' ?>
                            </button>
                        <?php elseif (!$isArchived): ?>
                            <div class="p-3 rounded-xl border border-amber-300 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-950/40 text-amber-900 dark:text-amber-200 flex flex-col gap-1.5">
                                <div class="flex items-center gap-1.5 font-extrabold text-[11px] uppercase tracking-wider text-amber-800 dark:text-amber-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <span><?= $isUserAdmin ? 'Release Period Closed' : 'Cascade Closed' ?></span>
                                </div>
                                <p class="text-[10px] leading-tight text-amber-700 dark:text-amber-400">
                                    <?= $isUserAdmin ? 'The target setting phase has ended for this cycle. Evaluation folders can no longer be released or revoked.' : 'The target setting phase has ended for this cycle. Subordinates can no longer be cascaded or revoked.' ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    <?php elseif ($hasPresets): ?>
                        <div class="flex flex-col gap-2">
                            <label for="team-cascade-select" class="text-[10px] font-bold text-text-muted uppercase tracking-wider">
                                <?= $isUserAdmin ? 'Select Recipient Staff' : 'Select Distribution Staff' ?>
                            </label>
                            <div class="relative w-full">
                                <select id="team-cascade-select" <?= ($isLocked || !$canActuallyCascade) ? 'disabled' : '' ?> class="w-full bg-zinc-50 dark:bg-zinc-900 text-xs font-bold text-text outline-none pl-3.5 pr-8 py-2.5 rounded-xl appearance-none border border-surface-border <?= ($isLocked || !$canActuallyCascade) ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer focus:border-emerald-500/50' ?>">
                                    <?php $onlyOnePreset = (count($presets) === 1); ?>
                                    <?php if (!$onlyOnePreset): ?>
                                        <option value="" disabled selected class="bg-surface text-text-muted"><?= $isUserAdmin ? '-- Select Staff to Release --' : '-- Select Staff to Cascade --' ?></option>
                                    <?php endif; ?>
                                    <?php foreach($presets as $preset): ?>
                                        <?php $mCount = (int)($preset['member_count'] ?? 0); ?>
                                        <option value="<?= $preset['id'] ?>" <?= $onlyOnePreset ? 'selected' : '' ?> class="bg-surface text-text">
                                            <?= esc($preset['name']) ?> (<?= $mCount ?> <?= ($mCount === 1 ? 'member' : 'members') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-text-muted">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <a href="<?= site_url('teams') ?>" class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline inline-flex items-center gap-1">
                                    + Manage / Create Staff
                                </a>
                            </div>
                        </div>

                        <?php if (!$isLocked && $canActuallyCascade): ?>
                            <button onclick="triggerCascade('<?= $activeFolder['id'] ?>')" class="w-full py-3 bg-amber-500 hover:bg-amber-400 text-zinc-950 rounded-xl shadow-md transition-all cursor-pointer flex justify-center items-center gap-1.5 font-black text-xs uppercase tracking-wider active:scale-98">
                                <?= $isUserAdmin ? 'Release to Selected Staff' : ($isOpcrFolder ? 'Cascade OPCR Commitments' : 'Cascade to Selected Staff') ?>
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="p-4 text-center rounded-xl border border-dashed border-surface-border bg-surface/50 flex flex-col items-center gap-2">
                            <div class="w-9 h-9 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="text-xs font-bold text-text"><?= $isUserAdmin ? 'No Recipient Staff Created Yet' : 'No Staff Created Yet' ?></div>
                            <p class="text-[10px] text-text-muted leading-tight"><?= $isUserAdmin ? 'Create a recipient staff list with members in My Staff before you can release evaluation cycles.' : 'Create a distribution staff list with members in My Staff before you can cascade targets.' ?></p>
                            <a href="<?= site_url('teams') ?>" class="mt-1 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs transition-colors shadow-xs inline-flex items-center gap-1.5">
                                <span>Go to My Staff</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- FOLDER MANAGEMENT BUTTONS (Admin Only) -->
                <?php if (session()->get('role') === 'Admin'): ?>
                <div class="flex flex-col gap-3 border-t border-zinc-200 dark:border-zinc-800/60 pt-4 shrink-0">
                    <h4 class="text-[10px] font-black uppercase text-text-muted tracking-wider">Folder Management</h4>
                    
                    <?php if (!empty($activeFolder['deleted_at'])): ?>
                        <div class="flex flex-col gap-2">
                            <button onclick='unarchiveFolder("<?= esc($activeFolder["id"]) ?>", "<?= esc(addslashes($activeFolder["title"])) ?>")'
                                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                </svg>
                                Restore Folder
                            </button>

                            <button class="w-full btn-delete-modal bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 dark:bg-[#1c1214] dark:hover:bg-[#261619] dark:text-rose-400 dark:border-[#361a1f] py-2.5 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer shadow-2xs"
                                data-id="<?= $activeFolder['id'] ?>" data-desc="<?= esc($activeFolder['title']) ?>" data-url="<?= site_url('folder') ?>" data-title="Delete Archived Folder">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete Folder
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="flex flex-col gap-2">
                            <div class="grid grid-cols-2 gap-2">
                                <button onclick='openEditFolderModal(<?= json_encode([
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
                                ]) ?>)'
                                        class="w-full text-emerald-700 dark:text-[#34d399] hover:text-emerald-800 dark:hover:text-[#6ee7b7] bg-emerald-50 hover:bg-emerald-100 dark:bg-zinc-800 border border-emerald-200 dark:border-zinc-700 py-2.5 rounded-xl font-bold text-xs flex items-center justify-center transition-all cursor-pointer shadow-2xs">
                                    Edit
                                </button>

                                <button onclick='archiveFolder("<?= esc($activeFolder["id"]) ?>", "<?= esc(addslashes($activeFolder["title"])) ?>")'
                                        class="w-full text-amber-700 dark:text-amber-400 hover:text-amber-800 dark:hover:text-amber-300 bg-amber-50 hover:bg-amber-100 dark:bg-zinc-800 dark:hover:bg-zinc-700/80 border border-amber-200 dark:border-zinc-700 py-2.5 rounded-xl font-bold text-xs flex items-center justify-center transition-all cursor-pointer shadow-2xs"
                                        title="Close and archive this evaluation cycle (freezes all scores)">
                                    Close & Archive
                                </button>
                            </div>

                            <button class="w-full btn-delete-modal bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 dark:bg-[#1c1214] dark:hover:bg-[#261619] dark:text-rose-400 dark:border-[#361a1f] py-2.5 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer shadow-2xs"
                                data-id="<?= $activeFolder['id'] ?>" data-desc="<?= esc($activeFolder['title']) ?>" data-url="<?= site_url('folder') ?>" data-title="Delete Evaluation Folder">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete Folder
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- 2. FOLDER OVERVIEW & DETAILS SECTION (For Normal Users in exchange for Cascade Distribution) -->
                <?php
                    $now = date('Y-m-d H:i:s');
                    $tEnd = $activeFolder[$docType . '_target_end'] ?? null;
                    $isPastTarget = ($tEnd && $now > $tEnd);
                    $currentPhaseLabel = $isPastTarget ? 'Eval Phase' : 'Target Phase';
                    $phaseDotColor = $isPastTarget ? 'bg-sky-500' : 'bg-emerald-500';

                    $rawStatus = $activeFolder['status'] ?? 'draft_target';
                    $statusBadgeText = \App\Enums\FolderStatus::canonical($rawStatus)->label();

                    // Derive official form type from the primary document inside the folder
                    $primaryDoc = !empty($myDocs) ? reset($myDocs) : null;

                    $pTitleUpper = strtoupper($primaryDoc['title'] ?? '');
                    if (str_contains($pTitleUpper, 'OPCR') || str_contains($pTitleUpper, 'OFFICE')) {
                        $effectiveDocType = 'OPCR';
                    } elseif (str_contains($pTitleUpper, 'DPCR') || str_contains($pTitleUpper, 'DIVISION') || str_contains($pTitleUpper, 'DEPARTMENT')) {
                        $effectiveDocType = 'DPCR';
                    } elseif (str_contains($pTitleUpper, 'IPERF') || str_contains($pTitleUpper, 'NON-TEACHING')) {
                        $effectiveDocType = 'IPERF';
                    } elseif (str_contains($pTitleUpper, 'IPCR')) {
                        $effectiveDocType = 'IPCR';
                    } else {
                        $effectiveDocType = $ownerDocType ?? ($subFolderOwner['doc_type'] ?? 'IPCR');
                    }

                    $formTypeName = strtoupper($effectiveDocType ?: 'IPCR');
                    $formTypeDesc = match($formTypeName) {
                        'OPCR' => 'Office Performance',
                        'DPCR' => 'Department Performance',
                        default => 'Individual Performance'
                    };
                    $isOpcrFolder = ($formTypeName === 'OPCR');
                ?>

                <!-- Submission Summary Card -->
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-[10px] font-black uppercase text-zinc-400 dark:text-[#8ea396] tracking-wider">Submission Summary</h4>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-zinc-100 text-zinc-700 border border-zinc-200 dark:bg-zinc-900 dark:border-zinc-800 dark:text-zinc-300">
                            <span class="w-1.5 h-1.5 rounded-full <?= $phaseDotColor ?> animate-pulse"></span>
                            <?= esc($currentPhaseLabel) ?>
                        </span>
                    </div>

                    <!-- 2 Compact Metric Tiles -->
                    <div class="grid grid-cols-2 gap-2.5">
                        <div class="bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-3 flex flex-col">
                            <span class="text-[10px] font-medium text-zinc-500 dark:text-[#8ea396]">Total Files</span>
                            <span class="text-lg font-black text-zinc-900 dark:text-white mt-0.5"><?= count($myDocs) ?></span>
                        </div>
                        <div class="bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-3 flex flex-col">
                            <span class="text-[10px] font-medium text-zinc-500 dark:text-[#8ea396]">Folder Status</span>
                            <span class="text-xs font-bold text-zinc-800 dark:text-white mt-1.5 truncate"><?= esc($statusBadgeText) ?></span>
                        </div>
                    </div>

                    <!-- Form Commitment Tile -->
                    <div class="bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl px-3 py-2.5 flex items-center justify-between">
                        <span class="text-[10px] font-medium text-zinc-500 dark:text-[#8ea396]">Commitment</span>
                        <span class="text-[11px] font-bold text-zinc-800 dark:text-white truncate pl-2"><?= esc($formTypeName) ?> • <?= esc($formTypeDesc) ?></span>
                    </div>


                    <?php 
                        $cActiveStatus = \App\Enums\FolderStatus::canonical($activeFolder['status'] ?? '');
                    ?>
                    <?php if ($cActiveStatus === \App\Enums\FolderStatus::TARGET_SUBMITTED): ?>
                        <?php if ($activeFolder['user_id'] == session()->get('user_id') && empty($hasSupervisorApproval)): ?>
                            <button onclick="unsubmitTargetFolder('<?= $activeFolder['id'] ?>', this)" 
                                    class="w-full py-2.5 px-3 bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:hover:bg-rose-900/60 dark:text-rose-300 border border-rose-300 dark:border-rose-800 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer shadow-2xs">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                </svg>
                                Unsubmit
                            </button>
                        <?php endif; ?>
                    <?php elseif ($cActiveStatus === \App\Enums\FolderStatus::EVAL_SUBMITTED): ?>
                        <?php if ($activeFolder['user_id'] == session()->get('user_id') && empty($hasSupervisorApproval)): ?>
                            <button onclick="unsubmitEvaluationFolder('<?= $activeFolder['id'] ?>', this)" 
                                    class="w-full py-2.5 px-3 bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:hover:bg-rose-900/60 dark:text-rose-300 border border-rose-300 dark:border-rose-800 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer shadow-2xs">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                </svg>
                                Unsubmit
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!empty($parentFolder) && !$isOpcrFolder): ?>
                        <!-- Superior Basis Cascade Status Tile (Strict SPMS Mode) -->
                        <div class="bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl px-3 py-2.5 flex flex-col gap-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-medium text-zinc-500 dark:text-[#8ea396]">Superior Basis</span>
                                <?php 
                                    $isParentDocOpcr = !empty($isParentDocOpcr) || str_contains(strtoupper($parentFolder['title'] ?? ''), 'OPCR');
                                ?>
                                <?php if ($isParentTargetApproved ?? false): ?>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-extrabold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-[#102a1e] px-1.5 py-0.5 rounded border border-emerald-200 dark:border-[#1b4330]">
                                        <?= $isParentDocOpcr ? 'Institutional Basis' : 'Approved' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-extrabold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/60 px-1.5 py-0.5 rounded border border-amber-200 dark:border-amber-800">
                                        Pending Approval
                                    </span>
                                <?php endif; ?>
                            </div>
                            <span class="text-[11px] font-bold text-zinc-800 dark:text-white truncate" title="<?= esc($parentFolder['title']) ?>">
                                <?= esc($parentFolder['title']) ?>
                            </span>
                            <?php if (!($isParentTargetApproved ?? false)): ?>
                                <p class="text-[9px] text-amber-600 dark:text-amber-400 leading-tight italic">
                                    Target submission locked until superior targets are approved.
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Folder Details -->
                <div class="flex flex-col gap-3 pt-5 border-t border-zinc-200 dark:border-zinc-800 mt-auto">
                    <h4 class="text-[10px] font-black uppercase text-zinc-400 dark:text-[#8ea396] tracking-wider">Folder Details</h4>
                    
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between text-xs py-0.5">
                            <span class="text-zinc-500 dark:text-[#8ea396] font-medium">Target Date</span>
                            <span class="text-zinc-800 dark:text-white font-semibold text-right truncate pl-2"><?= esc($targetDateStr) ?></span>
                        </div>

                        <div class="flex items-center justify-between text-xs py-0.5">
                            <span class="text-zinc-500 dark:text-[#94A3B8] font-medium">Evaluation Date</span>
                            <span class="text-zinc-800 dark:text-white font-semibold text-right truncate pl-2"><?= esc($evalDateStr) ?></span>
                        </div>

                        <div class="flex items-center justify-between text-xs py-0.5">
                            <span class="text-zinc-500 dark:text-[#94A3B8] font-medium">Period</span>
                            <span class="text-zinc-800 dark:text-white font-semibold text-right truncate pl-2">Q3 <?= date('Y', strtotime($activeFolder['created_at'] ?? 'now')) ?></span>
                        </div>

                        <div class="flex items-center justify-between text-xs py-0.5">
                            <span class="text-zinc-500 dark:text-[#94A3B8] font-medium">Owner</span>
                            <span class="text-zinc-800 dark:text-white font-semibold text-right truncate pl-2 max-w-[150px]"><?= esc($displayName) ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
        
        <div id="bottom-sheet-overlay" onclick="toggleBottomSheet()" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"></div>
