<div id="create-folder-modal" class="fixed inset-0 z-100 hidden overflow-y-auto items-center justify-center bg-zinc-950/40 backdrop-blur-sm transition-all">
    
    <div class="relative w-full max-w-md rounded-2xl bg-surface border border-surface-border p-8 shadow-2xl transition-all">
        <h3 id="folder-modal-heading" class="text-xl font-black text-text tracking-tight mb-2">Create Evaluation Folder</h3>
        <p id="folder-modal-subheading" class="text-xs font-bold text-text-muted uppercase tracking-widest mb-6">New Rating Batch</p>

        <form id="form-create-folder">
            <input type="hidden" name="folder_id" id="folder-id-input">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Folder Name / Batch Title</label>
                    <input type="text" name="title" id="folder-title-input" placeholder="e.g., 2026-2027 IPCR"
                           class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all" />
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-1">Evaluation Start</label>
                    <input type="datetime-local" name="eval_date_start" id="folder-date-start" required
                        class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent rounded-xl px-4 py-3 text-sm focus:border-accent outline-none text-text dark:[color-scheme:dark]">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-1">Evaluation End</label>
                    <input type="datetime-local" name="eval_date_end" id="folder-date-end" required
                        class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent rounded-xl px-4 py-3 text-sm focus:border-accent outline-none text-text dark:[color-scheme:dark]">
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <button id="btn-close-folder" type="button"
                    class="flex-1 px-6 py-3 rounded-xl border border-surface-border text-sm font-bold text-text hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all cursor-pointer">
                    Cancel
                </button>
                <button type="submit" id="btn-submit-folder" class="flex-1 px-6 py-3 rounded-xl bg-accent hover:bg-accent-hover text-white text-sm font-bold shadow-lg shadow-accent/20 transition-all active:scale-95 cursor-pointer">
                    Create Folder
                </button>
            </div>
        </form>
    </div>
</div>