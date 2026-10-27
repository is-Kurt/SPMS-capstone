<!--
    Create-folder dialog, loaded via app_shell.php in the 'folders' context.
    Opened by #btn-create-folder-modal / #btn-create-folder-modal-mobile; submit
    handling lives in public/assets/js/main/modals/createFolderModal.js. This is the
    create-only counterpart to edit_folder_modal.php - kept separate since they
    post to different endpoints (Folder::store() vs Folder::update()).
-->
<div id="create-folder-modal" class="fixed inset-0 z-[150] hidden overflow-y-auto items-center justify-center bg-zinc-950/50 backdrop-blur-md transition-all p-4 sm:p-6">

    <div class="relative w-full max-w-4xl rounded-2xl bg-surface border border-surface-border p-6 sm:p-8 shadow-2xl transition-all my-auto">
        <!-- HEADER -->
        <div class="flex items-start justify-between pb-5 border-b border-surface-border mb-6">
            <div>
                <h3 class="text-xl font-black text-text tracking-tight mb-1">Create Evaluation Folder</h3>
                <p class="text-xs font-semibold text-text-muted">Initialize a new evaluation cycle and configure institutional phase deadlines</p>
            </div>
            <button type="button" onclick="document.getElementById('btn-close-create-folder')?.click()" 
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

        <form id="form-create-folder">
            <?= csrf_field() ?>
            <div class="space-y-6">
                <!-- RATING CYCLE DETAILS -->
                <div class="bg-zinc-50 dark:bg-zinc-800/40 p-4 rounded-xl border border-surface-border">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-xs font-black uppercase tracking-wider text-text block">Rating Cycle &amp; Evaluation Period</span>
                            <span class="text-[10px] text-text-muted font-medium block">Configure cycle term and official Civil Service Commission (CSC) coverage dates</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
                        <div>
                            <label class="block text-[9px] font-bold uppercase tracking-wider text-text-muted mb-1">Academic / Calendar Year</label>
                            <input type="text" name="academic_year" id="create-cycle-academic-year" placeholder="e.g., 2026-2027"
                                   class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3.5 py-2 text-xs font-semibold focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all shadow-2xs placeholder:text-text-muted/60" />
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold uppercase tracking-wider text-text-muted mb-1">Semester / Term</label>
                            <select name="semester" id="create-cycle-semester"
                                    class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3.5 py-2 text-xs font-semibold focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all shadow-2xs cursor-pointer">
                                <option value="1st Semester">1st Semester (Aug – Dec)</option>
                                <option value="2nd Semester">2nd Semester (Jan – Jun)</option>
                                <option value="Summer / Mid-Year">Summer / Mid-Year</option>
                                <option value="Annual / Full Year">Annual / Full Year</option>
                                <option value="Custom Term">Custom Term</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold uppercase tracking-wider text-text-muted mb-1">Rating Period (CSC Header)</label>
                            <input type="text" name="rating_period" id="create-cycle-rating-period" placeholder="e.g., July - December 2026"
                                   class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3.5 py-2 text-xs font-semibold focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all shadow-2xs placeholder:text-text-muted/60" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-[9px] font-bold uppercase tracking-wider text-text-muted mb-1 flex items-center justify-between">
                            <span>Folder Name / Batch Title</span>
                            <span class="text-[8px] text-text-muted font-normal">Auto-generated or custom</span>
                        </label>
                        <input type="text" name="title" id="create-folder-title" placeholder="e.g., 1st Semester AY 2026–2027"
                               class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3.5 py-2 text-xs font-semibold focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all shadow-2xs placeholder:text-text-muted/60" />
                    </div>
                </div>

                <!-- APPROVING AUTHORITY / HEAD OF AGENCY SIGNATORY -->
                <div class="bg-zinc-50 dark:bg-zinc-800/40 p-4 rounded-xl border border-surface-border">
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
                            <input type="text" name="approving_authority_name" id="create-folder-approving-name"
                                   class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3.5 py-2 text-xs font-semibold focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all shadow-2xs placeholder:text-text-muted/60" />
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold uppercase tracking-wider text-text-muted mb-1">Position / Official Designation</label>
                            <input type="text" name="approving_authority_position" id="create-folder-approving-position"
                                   class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3.5 py-2 text-xs font-semibold focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all shadow-2xs placeholder:text-text-muted/60" />
                        </div>
                    </div>
                </div>

                <!-- SCHEDULE MODE TOGGLE BAR -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-2 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-surface-border">
                    <div class="flex items-center gap-2.5 px-2">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-xs font-black uppercase tracking-wider text-text block">Schedule Configuration</span>
                            <span class="text-[10px] text-text-muted font-medium block">Choose between unified institutional dates or staggered deadlines per form</span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-1 p-1 bg-zinc-100 dark:bg-zinc-900 rounded-lg border border-surface-border text-xs font-bold">
                        <button type="button" id="create-mode-general-btn"
                                class="px-3.5 py-1.5 rounded-md text-xs font-bold transition-all bg-white dark:bg-zinc-800 text-emerald-700 dark:text-emerald-400 shadow-xs cursor-pointer flex items-center justify-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            General Range
                        </button>
                        <button type="button" id="create-mode-staggered-btn"
                                class="px-3.5 py-1.5 rounded-md text-xs font-bold transition-all text-text-muted hover:text-text cursor-pointer flex items-center justify-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
                            </svg>
                            Stagger by Paper
                        </button>
                    </div>
                </div>

                <input type="hidden" name="schedule_mode" id="create-schedule-mode" value="general">

                <!-- 1. GENERAL SCHEDULE PANEL (DEFAULT) -->
                <div id="create-general-schedule-panel" class="space-y-4">
                    <!-- Informative Banner -->
                    <div class="flex items-center justify-between p-3 rounded-xl bg-emerald-500/5 border border-emerald-500/20 text-emerald-800 dark:text-emerald-300">
                        <div class="flex items-center gap-2 text-xs font-semibold">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Unified Institutional Schedule applies across all tiers: <strong>OPCR, DPCR, IPCR, and IPERF</strong></span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded bg-emerald-600/10 text-emerald-700 dark:text-emerald-300 border border-emerald-600/20">All Papers Synced</span>
                        </div>
                    </div>

                    <!-- 2 Hero Date Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Target Setting Window -->
                        <div class="bg-zinc-50 dark:bg-zinc-800/40 p-4 rounded-xl border border-surface-border border-l-4 border-l-emerald-500 shadow-2xs">
                            <div class="flex items-center justify-between pb-3 mb-3 border-b border-surface-border">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-black uppercase tracking-wider text-text">Target Setting Window</h4>
                                        <p class="text-[10px] text-text-muted font-medium">Formulation & commitment phase</p>
                                    </div>
                                </div>
                                <span id="create-gen-target-duration" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-500/20">
                                    Calculating...
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[9px] font-bold uppercase tracking-wider text-text-muted mb-1.5 flex items-center justify-between">
                                        <span>Start Date</span>
                                        <span class="text-[8px] text-emerald-600 dark:text-emerald-400 font-semibold">Opens</span>
                                    </label>
                                    <input type="datetime-local" name="general_target_start" id="create-general-target-start"
                                           class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-lg px-2.5 py-2 text-xs font-semibold focus:border-accent focus:ring-1 focus:ring-accent outline-none text-text dark:[color-scheme:dark] transition-all shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold uppercase tracking-wider text-text-muted mb-1.5 flex items-center justify-between">
                                        <span>Target Deadline</span>
                                        <span class="text-[8px] text-amber-600 dark:text-amber-400 font-semibold">Locks</span>
                                    </label>
                                    <input type="datetime-local" name="general_target_end" id="create-general-target-end"
                                           class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-lg px-2.5 py-2 text-xs font-semibold focus:border-accent focus:ring-1 focus:ring-accent outline-none text-text dark:[color-scheme:dark] transition-all shadow-2xs">
                                </div>
                            </div>
                        </div>

                        <!-- Evaluation Window -->
                        <div class="bg-zinc-50 dark:bg-zinc-800/40 p-4 rounded-xl border border-surface-border border-l-4 border-l-sky-500 shadow-2xs">
                            <div class="flex items-center justify-between pb-3 mb-3 border-b border-surface-border">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-black uppercase tracking-wider text-text">Evaluation Window</h4>
                                        <p class="text-[10px] text-text-muted font-medium">Scoring & calibration phase</p>
                                    </div>
                                </div>
                                <span id="create-gen-eval-duration" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-sky-500/10 text-sky-700 dark:text-sky-300 border border-sky-500/20">
                                    Calculating...
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[9px] font-bold uppercase tracking-wider text-text-muted mb-1.5 flex items-center justify-between">
                                        <span>Start Date</span>
                                        <span class="text-[8px] text-sky-600 dark:text-sky-400 font-semibold">Opens</span>
                                    </label>
                                    <input type="datetime-local" name="general_eval_start" id="create-general-eval-start"
                                           class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-lg px-2.5 py-2 text-xs font-semibold focus:border-accent focus:ring-1 focus:ring-accent outline-none text-text dark:[color-scheme:dark] transition-all shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold uppercase tracking-wider text-text-muted mb-1.5 flex items-center justify-between">
                                        <span>Cycle Closes</span>
                                        <span class="text-[8px] text-rose-600 dark:text-rose-400 font-semibold">Final Cutoff</span>
                                    </label>
                                    <input type="datetime-local" name="general_eval_end" id="create-general-eval-end"
                                           class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-lg px-2.5 py-2 text-xs font-semibold focus:border-accent focus:ring-1 focus:ring-accent outline-none text-text dark:[color-scheme:dark] transition-all shadow-2xs">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Visual Timeline / Calendar Track -->
                    <div class="p-3.5 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-surface-border">
                        <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-text-muted mb-2">
                            <span>Cycle Timeline Overview</span>
                            <span id="create-timeline-summary" class="text-text font-black">Timeline Synchronized</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <div class="flex items-center gap-2 p-2 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                <div class="min-w-0">
                                    <div class="text-[9px] font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Phase 1: Target Window</div>
                                    <div id="create-timeline-target-dates" class="text-xs font-extrabold text-emerald-900 dark:text-emerald-100 truncate">Loading...</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 p-2 rounded-lg bg-sky-500/10 border border-sky-500/20">
                                <span class="w-2 h-2 rounded-full bg-sky-500 shrink-0"></span>
                                <div class="min-w-0">
                                    <div class="text-[9px] font-bold uppercase tracking-wider text-sky-800 dark:text-sky-300">Phase 2: Evaluation Window</div>
                                    <div id="create-timeline-eval-dates" class="text-xs font-extrabold text-sky-900 dark:text-sky-100 truncate">Loading...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. STAGGERED SCHEDULE PANEL (COLLAPSED BY DEFAULT) -->
                <div id="create-staggered-schedule-panel" class="space-y-4 hidden">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-amber-500/5 border border-amber-500/20 text-amber-800 dark:text-amber-300">
                        <div class="text-xs font-semibold">
                            Staggered Mode allows customizing specific target and evaluation deadlines for each institutional paper tier.
                        </div>
                        <button type="button" id="create-sync-all-btn" class="px-3 py-1 rounded-lg text-[11px] font-bold bg-amber-500/10 text-amber-700 dark:text-amber-300 border border-amber-500/30 hover:bg-amber-500/20 transition-all cursor-pointer shrink-0">
                            Copy General Dates to All
                        </button>
                    </div>

                    <!-- 2-COLUMN GRID FOR TARGET WINDOWS VS EVALUATION WINDOWS -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                        <!-- 1. TARGET PHASE DATES -->
                        <div>
                            <div class="flex items-center gap-2.5 pb-2.5 mb-3.5 border-b border-surface-border">
                                <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-xs font-black uppercase tracking-wider text-text">Target Setting Windows</h4>
                                    <p class="text-[10px] text-text-muted font-medium">Formulation & commitment phase</p>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <?php foreach ($folderDocTypes as $docType => $meta): ?>
                                    <div class="bg-zinc-50 dark:bg-zinc-800/40 p-3.5 rounded-xl border border-surface-border border-l-4 <?= esc($meta['border_cls']) ?> shadow-2xs hover:border-zinc-300 dark:hover:border-zinc-700 transition-all">
                                        <div class="flex items-center justify-between gap-2 mb-2.5">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <span class="inline-block w-2.5 h-2.5 rounded-full <?= esc($meta['dot']) ?> shrink-0"></span>
                                                <span class="text-xs font-black tracking-tight text-text block truncate"><?= esc($meta['title']) ?></span>
                                            </div>
                                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-md border tracking-wide uppercase shrink-0 <?= esc($meta['badge_cls']) ?>">
                                                <?= esc($meta['badge']) ?>
                                            </span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2.5">
                                            <div>
                                                <label class="block text-[9px] font-bold uppercase tracking-wider text-text-muted mb-1">Start</label>
                                                <input type="datetime-local" name="<?= $docType ?>_target_start" id="create-folder-<?= $docType ?>-target-start" 
                                                    class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-lg px-2.5 py-1.5 text-xs font-semibold focus:border-accent focus:ring-1 focus:ring-accent outline-none text-text dark:[color-scheme:dark] transition-all shadow-2xs">
                                            </div>
                                            <div>
                                                <label class="block text-[9px] font-bold uppercase tracking-wider text-text-muted mb-1">End</label>
                                                <input type="datetime-local" name="<?= $docType ?>_target_end" id="create-folder-<?= $docType ?>-target-end" 
                                                    class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-lg px-2.5 py-1.5 text-xs font-semibold focus:border-accent focus:ring-1 focus:ring-accent outline-none text-text dark:[color-scheme:dark] transition-all shadow-2xs">
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- 2. EVALUATION PHASE DATES -->
                        <div>
                            <div class="flex items-center gap-2.5 pb-2.5 mb-3.5 border-b border-surface-border">
                                <div class="w-7 h-7 rounded-lg bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-xs font-black uppercase tracking-wider text-text">Evaluation Windows</h4>
                                    <p class="text-[10px] text-text-muted font-medium">Scoring & review phase</p>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <?php foreach ($folderDocTypes as $docType => $meta): ?>
                                    <div class="bg-zinc-50 dark:bg-zinc-800/40 p-3.5 rounded-xl border border-surface-border border-l-4 <?= esc($meta['border_cls']) ?> shadow-2xs hover:border-zinc-300 dark:hover:border-zinc-700 transition-all">
                                        <div class="flex items-center justify-between gap-2 mb-2.5">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <span class="inline-block w-2.5 h-2.5 rounded-full <?= esc($meta['dot']) ?> shrink-0"></span>
                                                <span class="text-xs font-black tracking-tight text-text block truncate"><?= esc($meta['title']) ?></span>
                                            </div>
                                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-md border tracking-wide uppercase shrink-0 <?= esc($meta['badge_cls']) ?>">
                                                <?= esc($meta['badge']) ?>
                                            </span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2.5">
                                            <div>
                                                <label class="block text-[9px] font-bold uppercase tracking-wider text-text-muted mb-1">Start</label>
                                                <input type="datetime-local" name="<?= $docType ?>_eval_start" id="create-folder-<?= $docType ?>-eval-start" 
                                                    class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-lg px-2.5 py-1.5 text-xs font-semibold focus:border-accent focus:ring-1 focus:ring-accent outline-none text-text dark:[color-scheme:dark] transition-all shadow-2xs">
                                            </div>
                                            <div>
                                                <label class="block text-[9px] font-bold uppercase tracking-wider text-text-muted mb-1">End</label>
                                                <input type="datetime-local" name="<?= $docType ?>_eval_end" id="create-folder-<?= $docType ?>-eval-end" 
                                                    class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-lg px-2.5 py-1.5 text-xs font-semibold focus:border-accent focus:ring-1 focus:ring-accent outline-none text-text dark:[color-scheme:dark] transition-all shadow-2xs">
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-5 border-t border-surface-border flex gap-3">
                <button id="btn-close-create-folder" type="button"
                    class="flex-1 px-6 py-3 rounded-xl border border-surface-border text-sm font-bold text-text hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all cursor-pointer">
                    Cancel
                </button>
                <button type="submit" id="btn-submit-create-folder" class="flex-1 px-6 py-3 rounded-xl bg-accent hover:bg-accent-hover text-white text-sm font-bold shadow-lg shadow-accent/20 transition-all active:scale-95 cursor-pointer">
                    Create Folder
                </button>
            </div>
        </form>
    </div>
</div>
