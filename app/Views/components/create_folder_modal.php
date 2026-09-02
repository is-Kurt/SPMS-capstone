<!--
    Create-folder dialog, loaded via app_shell.php in the 'folders' context.
    Opened by #btn-create-folder-modal / #btn-create-folder-modal-mobile; submit
    handling lives in public/assets/js/main/modals/createFolderModal.js. This is the
    create-only counterpart to edit_folder_modal.php - kept separate since they
    post to different endpoints (Folder::store() vs Folder::update()).
-->
<div id="create-folder-modal" class="fixed inset-0 z-[150] hidden overflow-y-auto items-center justify-center bg-zinc-950/40 backdrop-blur-sm transition-all">

    <div class="relative w-full max-w-4xl rounded-2xl bg-surface border border-surface-border p-8 shadow-2xl transition-all">
        <h3 class="text-xl font-black text-text tracking-tight mb-2">Create Evaluation Folder</h3>
        <p class="text-xs font-bold text-text-muted uppercase tracking-widest mb-6">New Rating Batch</p>

        <form id="form-create-folder">
            <div class="space-y-4">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Folder Name / Batch Title</label>
                    <input type="text" name="title" id="create-folder-title" placeholder="e.g., 2026-2027 IPCR"
                           class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all" />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-4">
                    <!-- TARGET PHASE DATES -->
                    <div>
                        <h4 class="text-[10px] font-black uppercase tracking-widest text-text mb-3 pt-2 border-t border-surface-border">Target Setting Windows</h4>
                        <div class="space-y-4">
                            <?php foreach (['ipcr', 'dpcr', 'opcr', 'iperf'] as $docType): ?>
                                <div class="bg-zinc-50 dark:bg-zinc-800/30 p-3 rounded-xl border border-surface-border">
                                    <h5 class="text-xs font-bold text-accent mb-2 uppercase"><?= strtoupper($docType) ?></h5>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-[9px] font-bold uppercase tracking-widest text-text-muted mb-1">Start</label>
                                            <input type="datetime-local" name="<?= $docType ?>_target_start" id="create-folder-<?= $docType ?>-target-start" 
                                                class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-lg px-3 py-2 text-xs focus:border-accent outline-none text-text dark:[color-scheme:dark]">
                                        </div>
                                        <div>
                                            <label class="block text-[9px] font-bold uppercase tracking-widest text-text-muted mb-1">End</label>
                                            <input type="datetime-local" name="<?= $docType ?>_target_end" id="create-folder-<?= $docType ?>-target-end" 
                                                class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-lg px-3 py-2 text-xs focus:border-accent outline-none text-text dark:[color-scheme:dark]">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- EVALUATION PHASE DATES -->
                    <div>
                        <h4 class="text-[10px] font-black uppercase tracking-widest text-text mb-3 pt-2 border-t border-surface-border">Evaluation Windows</h4>
                        <div class="space-y-4">
                            <?php foreach (['ipcr', 'dpcr', 'opcr', 'iperf'] as $docType): ?>
                                <div class="bg-zinc-50 dark:bg-zinc-800/30 p-3 rounded-xl border border-surface-border">
                                    <h5 class="text-xs font-bold text-accent mb-2 uppercase"><?= strtoupper($docType) ?></h5>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-[9px] font-bold uppercase tracking-widest text-text-muted mb-1">Start</label>
                                            <input type="datetime-local" name="<?= $docType ?>_eval_start" id="create-folder-<?= $docType ?>-eval-start" 
                                                class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-lg px-3 py-2 text-xs focus:border-accent outline-none text-text dark:[color-scheme:dark]">
                                        </div>
                                        <div>
                                            <label class="block text-[9px] font-bold uppercase tracking-widest text-text-muted mb-1">End</label>
                                            <input type="datetime-local" name="<?= $docType ?>_eval_end" id="create-folder-<?= $docType ?>-eval-end" 
                                                class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-lg px-3 py-2 text-xs focus:border-accent outline-none text-text dark:[color-scheme:dark]">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex gap-3">
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
