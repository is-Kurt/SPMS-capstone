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
                <p class="text-xs font-semibold text-text-muted">Initialize a new evaluation cycle and configure phase deadline windows across institutional tiers</p>
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
                'cdpcr' => [
                    'title'      => 'DPCR Dean',
                    'badge'      => 'College Deans',
                    'dot'        => 'bg-emerald-500',
                    'ring_cls'   => 'ring-emerald-500/20',
                    'border_cls' => 'border-l-emerald-500',
                    'badge_cls'  => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/40',
                ],
                'dpcr'  => [
                    'title'      => 'DPCR Department Chair',
                    'badge'      => 'Department Chairs',
                    'dot'        => 'bg-amber-500',
                    'ring_cls'   => 'ring-amber-500/20',
                    'border_cls' => 'border-l-amber-500',
                    'badge_cls'  => 'bg-amber-500/10 text-amber-600 dark:text-amber-300 border-amber-200 dark:border-amber-800/40',
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
            <div class="space-y-6">
                <!-- FOLDER TITLE INPUT -->
                <div class="bg-zinc-50 dark:bg-zinc-800/40 p-4 rounded-xl border border-surface-border">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Folder Name / Batch Title</label>
                    <input type="text" name="title" id="create-folder-title" placeholder="e.g., 2026-2027 Evaluation Cycle"
                           class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-4 py-2.5 text-sm font-semibold focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all shadow-2xs placeholder:text-text-muted/60" />
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
