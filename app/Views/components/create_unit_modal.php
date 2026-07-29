<div id="modal-create-unit" class="fixed inset-0 z-[200] hidden overflow-y-auto items-center justify-center bg-zinc-950/40 backdrop-blur-sm transition-all">
    <div class="relative w-full max-w-md rounded-2xl bg-surface border border-surface-border p-8 shadow-2xl transition-all m-4">
        <h3 class="text-xl font-black text-text tracking-tight mb-2">Create New Unit</h3>
        <p class="text-xs font-bold text-text-muted uppercase tracking-widest mb-6">Department & Unit</p>

        <?= form_open('account/unit/add', ['id' => 'form-create-unit', 'data-ajax' => 'add-unit']) ?>
            <div class="space-y-4">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Unit Name</label>
                    <input type="text" name="name" required placeholder="e.g. IT Department" class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all" />
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Parent Unit</label>
                    <select id="select-unit-parent" name="parent_id" class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text cursor-pointer transition-all">
                        <option value="">No Parent (Top Level)</option>
                        <?php foreach ($units as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= esc($u['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mt-8 flex flex-col-reverse sm:flex-row gap-3">
                <button type="button" class="btn-close-modal w-full sm:flex-1 px-6 py-3.5 sm:py-3 rounded-xl border border-surface-border text-sm font-bold text-text hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all cursor-pointer" data-target="modal-create-unit">
                    Cancel
                </button>
                <button type="submit" class="w-full sm:flex-1 px-6 py-3.5 sm:py-3 rounded-xl bg-accent hover:bg-accent-hover disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold shadow-lg shadow-accent/20 transition-all active:scale-95 cursor-pointer">
                    Create Unit
                </button>
            </div>
        <?= form_close() ?>
    </div>
</div>
