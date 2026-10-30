<!--
    Edit-folder dialog, loaded via app_shell.php in the 'folders' context.
    Opened via openEditFolderModal(id, title, start, end) (see document/_doc_rows.php),
    which fills the hidden #edit-folder-id + inputs below; submit handling lives in
    public/assets/js/main/modals/editFolderModal.js, posting to Folder::update().
-->
<div id="edit-folder-modal" class="fixed inset-0 z-[150] hidden overflow-y-auto items-center justify-center bg-zinc-950/50 backdrop-blur-md transition-all p-4 sm:p-6">

    <div class="relative w-full max-w-4xl rounded-2xl bg-surface border border-surface-border p-6 sm:p-8 shadow-2xl transition-all my-auto">
        <!-- HEADER -->
        <div class="flex items-start justify-between pb-5 border-b border-surface-border mb-6">
            <div>
                <h3 class="text-xl font-black text-text tracking-tight mb-1">Edit Evaluation Folder</h3>
                <p class="text-xs font-semibold text-text-muted">Configure cycle title and phase deadline windows across institutional tiers</p>
            </div>
            <button type="button" onclick="document.getElementById('btn-close-edit-folder')?.click()" 
                    class="p-2 rounded-xl text-text-muted hover:text-text hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                    aria-label="Close modal">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <?php
            $folderDocTypes = [
                'opcr'  => [
                    'title'      => 'OPCR',
                    'badge'      => 'Institutional / VPAA',
                    'dot'        => 'bg-purple-500',
                    'ring_cls'   => 'ring-purple-500/20',
                    'border_cls' => 'border-l-purple-500',
                    'badge_cls'  => 'bg-purple-500/10 text-purple-600 dark:text-purple-300 border-purple-200 dark:border-purple-800/40',
                ],
                'dpcr'  => [
                    'title'      => 'DPCR',
                    'badge'      => 'Deans & Dept Chairs',
                    'dot'        => 'bg-emerald-500',
                    'ring_cls'   => 'ring-emerald-500/20',
                    'border_cls' => 'border-l-emerald-500',
                    'badge_cls'  => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/40',
                ],
                'ipcr'  => [
                    'title'      => 'IPCR',
                    'badge'      => 'Faculty & Academic',
                    'dot'        => 'bg-sky-500',
                    'ring_cls'   => 'ring-sky-500/20',
                    'border_cls' => 'border-l-sky-500',
                    'badge_cls'  => 'bg-sky-500/10 text-sky-600 dark:text-sky-300 border-sky-200 dark:border-sky-800/40',
                ],
                'iperf' => [
                    'title'      => 'IPERF',
                    'badge'      => 'Non-Teaching Staff',
                    'dot'        => 'bg-teal-500',
                    'ring_cls'   => 'ring-teal-500/20',
                    'border_cls' => 'border-l-teal-500',
                    'badge_cls'  => 'bg-teal-500/10 text-teal-600 dark:text-teal-300 border-teal-200 dark:border-teal-800/40',
                ],
            ];
        ?>

        <form id="form-edit-folder" autocomplete="off">
            <?= csrf_field() ?>
            <input type="hidden" name="folder_id" id="edit-folder-id">

            <div class="space-y-5">
                <!-- 1. RATING CYCLE & GOVERNANCE DETAILS (CONNECTED) -->
                <div class="bg-zinc-50 dark:bg-zinc-800/40 rounded-xl border border-surface-border divide-y divide-surface-border overflow-hidden">
                    <!-- Rating Cycle Details -->
                    <div class="p-4 sm:p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <span class="text-xs font-black uppercase tracking-wider text-text block">Rating Cycle &amp; Evaluation Period</span>
                                <span class="text-[10px] text-text-muted font-medium block">Configure official Civil Service Commission (CSC) performance period coverage and folder title</span>
                            </div>
                        </div>

                        <!-- Performance Period Range -->
                        <div class="mb-3">
                            <label class="block text-[9px] font-bold uppercase tracking-wider text-text-muted mb-1.5">Performance Period Covered (CSC Review Window)</label>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[8px] font-bold uppercase tracking-wider text-text-muted mb-1">Period From</label>
                                    <input type="date" id="edit-rating-period-start"
                                           class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3 py-2 text-xs font-semibold focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all shadow-2xs cursor-pointer" />
                                </div>
                                <div>
                                    <label class="block text-[8px] font-bold uppercase tracking-wider text-text-muted mb-1">Period To</label>
                                    <input type="date" id="edit-rating-period-end"
                                           class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3 py-2 text-xs font-semibold focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all shadow-2xs cursor-pointer" />
                                </div>
                            </div>
                            <input type="hidden" name="rating_period" id="edit-cycle-rating-period" />
                        </div>

                        <div>
                            <label class="block text-[9px] font-bold uppercase tracking-wider text-text-muted mb-1">Folder Name / Batch Title</label>
                            <input type="text" name="title" id="edit-folder-title" placeholder="e.g., July – December 2026 Evaluation"
                                   class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3.5 py-2 text-xs font-semibold focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all shadow-2xs placeholder:text-text-muted/60" />
                        </div>
                    </div>

                    <!-- Approving Authority Section -->
                    <div class="p-4 sm:p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-7 h-7 rounded-lg bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div>
                                <span class="text-xs font-black uppercase tracking-wider text-text block">Approving Authority / Head of Office</span>
                                <span class="text-[10px] text-text-muted font-medium block">Default approving signatory for institutional forms (OPCR) and apex performance reviews</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[9px] font-bold uppercase tracking-wider text-text-muted mb-1">Official Name</label>
                                <input type="text" name="approving_authority_name" id="edit-folder-approving-name"
                                       class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3.5 py-2 text-xs font-semibold focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all shadow-2xs placeholder:text-text-muted/60" />
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold uppercase tracking-wider text-text-muted mb-1">Position / Official Designation</label>
                                <input type="text" name="approving_authority_position" id="edit-folder-approving-position"
                                       class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3.5 py-2 text-xs font-semibold focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all shadow-2xs placeholder:text-text-muted/60" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. SCHEDULE CONFIGURATION (CONNECTED) -->
                <div class="bg-zinc-50 dark:bg-zinc-800/40 rounded-xl border border-surface-border divide-y divide-surface-border overflow-hidden">
                    <!-- Phase Opening Dates -->
                    <div class="p-4 sm:p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <span class="text-xs font-black uppercase tracking-wider text-text block">Phase Opening Dates</span>
                                <span class="text-[10px] text-text-muted font-medium block">All forms open simultaneously on these dates for Target Setting and Evaluation</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-3 bg-white dark:bg-zinc-900 rounded-xl border border-surface-border border-l-4 border-l-emerald-500 shadow-2xs">
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-[9px] font-extrabold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Phase 1: Target Setting Opens</label>
                                    <span class="text-[8px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-700 dark:text-emerald-300">All Forms</span>
                                </div>
                                <input type="datetime-local" name="general_target_start" id="edit-general-target-start"
                                       class="w-full bg-zinc-50 dark:bg-zinc-800 border border-surface-border rounded-lg px-2.5 py-2 text-xs font-semibold focus:border-accent focus:ring-1 focus:ring-accent outline-none text-text dark:[color-scheme:dark] transition-all shadow-2xs">
                            </div>
                            <div class="p-3 bg-white dark:bg-zinc-900 rounded-xl border border-surface-border border-l-4 border-l-sky-500 shadow-2xs">
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-[9px] font-extrabold uppercase tracking-wider text-sky-800 dark:text-sky-300">Phase 2: Evaluation Window Opens</label>
                                    <span class="text-[8px] font-bold px-1.5 py-0.5 rounded bg-sky-500/10 text-sky-700 dark:text-sky-300">All Forms</span>
                                </div>
                                <input type="datetime-local" name="general_eval_start" id="edit-general-eval-start"
                                       class="w-full bg-zinc-50 dark:bg-zinc-800 border border-surface-border rounded-lg px-2.5 py-2 text-xs font-semibold focus:border-accent focus:ring-1 focus:ring-accent outline-none text-text dark:[color-scheme:dark] transition-all shadow-2xs">
                            </div>
                        </div>
                    </div>

                    <!-- Deadlines Section (Header & Content) -->
                    <div>
                        <!-- Deadlines Header & Mode Toggle -->
                        <div class="p-3 sm:px-5 sm:py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-xs font-black uppercase tracking-wider text-text block">Deadlines &amp; Lock Dates</span>
                                    <span class="text-[10px] text-text-muted font-medium block">Configure unified institutional deadlines or stagger deadlines per form tier</span>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-1 p-1 bg-zinc-200/50 dark:bg-zinc-950/50 rounded-lg border border-surface-border text-xs font-bold shrink-0">
                                <button type="button" id="edit-mode-general-btn"
                                        class="px-3.5 py-1.5 rounded-md text-xs font-bold transition-all bg-white dark:bg-zinc-800 text-emerald-700 dark:text-emerald-400 shadow-xs cursor-pointer flex items-center justify-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    General Deadlines
                                </button>
                                <button type="button" id="edit-mode-staggered-btn"
                                        class="px-3.5 py-1.5 rounded-md text-xs font-bold transition-all text-text-muted hover:text-text bg-transparent dark:bg-transparent shadow-none cursor-pointer flex items-center justify-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
                                    </svg>
                                    Stagger Deadlines
                                </button>
                            </div>
                        </div>

                        <!-- Deadlines Content Area -->
                        <div class="px-4 pb-4 sm:px-5 sm:pb-5 pt-1">
                            <input type="hidden" name="schedule_mode" id="edit-schedule-mode" value="general">

                            <!-- 1. GENERAL DEADLINES PANEL (DEFAULT) -->
                            <div id="edit-general-schedule-panel" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                                    <!-- Target Setting Deadline -->
                                    <div>
                                        <div class="flex items-center justify-between gap-2 mb-3">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                    </svg>
                                                </div>
                                                <div class="min-w-0">
                                                    <h4 class="text-xs font-black uppercase tracking-wider text-text truncate">Target Setting Deadline</h4>
                                                </div>
                                            </div>
                                            <span id="edit-gen-target-duration" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-500/20 shrink-0">
                                                Calculating...
                                            </span>
                                        </div>
                                        <div class="bg-white dark:bg-zinc-900 p-3 rounded-xl border border-surface-border border-l-4 border-l-emerald-500 shadow-2xs">
                                            <label class="block text-[8px] font-bold uppercase tracking-wider text-text-muted mb-1">Target Lock Deadline</label>
                                            <input type="datetime-local" name="general_target_end" id="edit-general-target-end"
                                                   class="w-full bg-zinc-50 dark:bg-zinc-800 border border-surface-border rounded-lg px-2.5 py-1.5 text-xs font-semibold focus:border-accent focus:ring-1 focus:ring-accent outline-none text-text dark:[color-scheme:dark] transition-all shadow-2xs">
                                        </div>
                                    </div>

                                    <!-- Evaluation Deadline -->
                                    <div>
                                        <div class="flex items-center justify-between gap-2 mb-3">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <div class="w-7 h-7 rounded-lg bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                                <div class="min-w-0">
                                                    <h4 class="text-xs font-black uppercase tracking-wider text-text truncate">Evaluation Deadline</h4>
                                                </div>
                                            </div>
                                            <span id="edit-gen-eval-duration" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-sky-500/10 text-sky-700 dark:text-sky-300 border border-sky-500/20 shrink-0">
                                                Calculating...
                                            </span>
                                        </div>
                                        <div class="bg-white dark:bg-zinc-900 p-3 rounded-xl border border-surface-border border-l-4 border-l-sky-500 shadow-2xs">
                                            <label class="block text-[8px] font-bold uppercase tracking-wider text-text-muted mb-1">Evaluation Cutoff Deadline</label>
                                            <input type="datetime-local" name="general_eval_end" id="edit-general-eval-end"
                                                   class="w-full bg-zinc-50 dark:bg-zinc-800 border border-surface-border rounded-lg px-2.5 py-1.5 text-xs font-semibold focus:border-accent focus:ring-1 focus:ring-accent outline-none text-text dark:[color-scheme:dark] transition-all shadow-2xs">
                                        </div>
                                    </div>
                                </div>

                                <!-- Sleek Sync Button -->
                                <div class="flex justify-end pt-1">
                                    <button type="button" id="edit-sync-all-btn"
                                            title="Synchronize these deadlines across all individual form tiers"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-amber-700 dark:text-amber-400 bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/25 active:scale-95 transition-all shadow-2xs cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        <span>Sync All Dates</span>
                                    </button>
                                </div>
                            </div>

                            <!-- 2. STAGGERED DEADLINES PANEL -->
                            <div id="edit-staggered-schedule-panel" class="space-y-4 hidden">
                                <!-- 2-COLUMN GRID FOR TARGET DEADLINES VS EVALUATION DEADLINES -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                                    <!-- 1. TARGET PHASE DEADLINES -->
                                    <div>
                                        <div class="flex items-center gap-2.5 mb-3">
                                            <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="text-xs font-black uppercase tracking-wider text-text">Target Setting Deadlines</h4>
                                            </div>
                                        </div>

                                        <div class="space-y-3">
                                            <?php foreach ($folderDocTypes as $docType => $meta): ?>
                                                <div class="bg-white dark:bg-zinc-900 p-3 rounded-xl border border-surface-border border-l-4 <?= esc($meta['border_cls']) ?> shadow-2xs hover:border-zinc-300 dark:hover:border-zinc-700 transition-all">
                                                    <div class="flex items-center justify-between gap-2 mb-2">
                                                        <div class="flex items-center gap-2 min-w-0">
                                                            <span class="inline-block w-2.5 h-2.5 rounded-full <?= esc($meta['dot']) ?> shrink-0"></span>
                                                            <span class="text-xs font-black tracking-tight text-text block truncate"><?= esc($meta['title']) ?></span>
                                                        </div>
                                                        <span class="text-[9px] font-bold px-2 py-0.5 rounded-md border tracking-wide uppercase shrink-0 <?= esc($meta['badge_cls']) ?>">
                                                            <?= esc($meta['badge']) ?>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <label class="block text-[8px] font-bold uppercase tracking-wider text-text-muted mb-1">Target Lock Deadline</label>
                                                        <input type="datetime-local" name="<?= $docType ?>_target_end" id="edit-folder-<?= $docType ?>-target-end" 
                                                            class="w-full bg-zinc-50 dark:bg-zinc-800 border border-surface-border rounded-lg px-2.5 py-1.5 text-xs font-semibold focus:border-accent focus:ring-1 focus:ring-accent outline-none text-text dark:[color-scheme:dark] transition-all shadow-2xs">
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <!-- 2. EVALUATION PHASE DEADLINES -->
                                    <div>
                                        <div class="flex items-center gap-2.5 mb-3">
                                            <div class="w-7 h-7 rounded-lg bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="text-xs font-black uppercase tracking-wider text-text">Evaluation Deadlines</h4>
                                            </div>
                                        </div>

                                        <div class="space-y-3">
                                            <?php foreach ($folderDocTypes as $docType => $meta): ?>
                                                <div class="bg-white dark:bg-zinc-900 p-3 rounded-xl border border-surface-border border-l-4 <?= esc($meta['border_cls']) ?> shadow-2xs hover:border-zinc-300 dark:hover:border-zinc-700 transition-all">
                                                    <div class="flex items-center justify-between gap-2 mb-2">
                                                        <div class="flex items-center gap-2 min-w-0">
                                                            <span class="inline-block w-2.5 h-2.5 rounded-full <?= esc($meta['dot']) ?> shrink-0"></span>
                                                            <span class="text-xs font-black tracking-tight text-text block truncate"><?= esc($meta['title']) ?></span>
                                                        </div>
                                                        <span class="text-[9px] font-bold px-2 py-0.5 rounded-md border tracking-wide uppercase shrink-0 <?= esc($meta['badge_cls']) ?>">
                                                            <?= esc($meta['badge']) ?>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <label class="block text-[8px] font-bold uppercase tracking-wider text-text-muted mb-1">Evaluation Cutoff Deadline</label>
                                                        <input type="datetime-local" name="<?= $docType ?>_eval_end" id="edit-folder-<?= $docType ?>-eval-end" 
                                                            class="w-full bg-zinc-50 dark:bg-zinc-800 border border-surface-border rounded-lg px-2.5 py-1.5 text-xs font-semibold focus:border-accent focus:ring-1 focus:ring-accent outline-none text-text dark:[color-scheme:dark] transition-all shadow-2xs">
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-5 border-t border-surface-border flex gap-3">
                <button id="btn-close-edit-folder" type="button"
                    class="flex-1 px-6 py-3 rounded-xl border border-surface-border text-sm font-bold text-text hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all cursor-pointer">
                    Cancel
                </button>
                <button type="submit" id="btn-submit-edit-folder" class="flex-1 px-6 py-3 rounded-xl bg-accent hover:bg-accent-hover text-white text-sm font-bold shadow-lg shadow-accent/20 transition-all active:scale-95 cursor-pointer">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
