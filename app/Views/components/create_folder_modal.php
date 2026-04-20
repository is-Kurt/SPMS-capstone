<div id="create-folder-modal" class="fixed inset-0 z-[60] hidden overflow-y-auto items-center justify-center">
    <div class="fixed inset-0 bg-zinc-950/40 backdrop-blur-sm transition-opacity" onclick="closeFolderModal()"></div>
    
    <div class="relative w-full max-w-md rounded-2xl bg-surface border border-surface-border p-8 shadow-2xl transition-all">
        <h3 class="text-xl font-black text-text tracking-tight mb-2">Create Evaluation Folder</h3>
        <p class="text-xs font-bold text-text-muted uppercase tracking-widest mb-6">New Rating Batch</p>

        <form id="form-create-folder">
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-text-muted mb-2">Folder Name / Batch Title</label>
                    <input type="text" name="title" required placeholder="e.g., 2026 IPCR - 1st Semester"
                           class="w-full bg-input border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all" />
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <button type="button" onclick="closeFolderModal()" class="flex-1 px-6 py-3 rounded-xl border border-surface-border text-sm font-bold text-text hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-6 py-3 rounded-xl bg-accent hover:bg-accent-hover text-white text-sm font-bold shadow-lg shadow-accent/20 transition-all active:scale-95 cursor-pointer">
                    Create Folder
                </button>
            </div>
        </form>
    </div>
</div>