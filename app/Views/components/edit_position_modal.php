<div id="modal-edit-position" class="fixed inset-0 z-[200] hidden overflow-y-auto items-center justify-center bg-zinc-950/40 backdrop-blur-sm transition-all p-4">
    <div class="relative w-full max-w-md rounded-2xl bg-surface border border-surface-border p-8 shadow-2xl transition-all my-auto">
        <h3 class="text-xl font-black text-text tracking-tight mb-2">Edit / Move Position</h3>
        <p class="text-xs font-bold text-text-muted uppercase tracking-widest mb-6">Job Positions</p>

        <?= form_open('account/position/update', ['id' => 'form-edit-position', 'data-ajax' => 'update-position']) ?>
            <div class="space-y-4">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Position Title</label>
                    <input type="hidden" name="id" id="edit-position-id" value="">
                    <input type="text" name="title" id="edit-position-title" required placeholder="e.g. Guidance Counselor" class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all" />
                </div>
                <div>
                    <div id="edit-position-parent-wrap">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Under (parent position)</label>
                    <?= view('components/_position_parent_select', ['positionTree' => $positionTree ?? [], 'name' => 'parent_id', 'id' => 'edit-position-parent']) ?>
                    <p class="text-[10px] text-text-muted font-medium mt-2">A position with positions under it is a head position. Put positions anyone can hold under General.</p>
                    </div>
                </div>
                <div>
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-text-muted font-bold p-1 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors w-max">
                        <input type="checkbox" name="is_teaching" id="edit-position-teaching" value="1" class="checkbox rounded border-surface-border text-accent-ink focus:ring-accent cursor-pointer">
                        Is Teaching Staff?
                    </label>
                </div>
            </div>

            <div class="mt-8 flex flex-col-reverse sm:flex-row gap-3">
                <button type="button" class="btn-close-modal w-full sm:flex-1 px-6 py-3.5 sm:py-3 rounded-xl border border-surface-border text-sm font-bold text-text hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all cursor-pointer" data-target="modal-edit-position">
                    Cancel
                </button>
                <button type="submit" class="w-full sm:flex-1 px-6 py-3.5 sm:py-3 rounded-xl bg-accent hover:bg-accent-hover disabled:opacity-50 disabled:cursor-not-allowed text-accent-text text-sm font-bold shadow-lg shadow-accent/20 transition-all active:scale-95 cursor-pointer">
                    Save Changes
                </button>
            </div>
        <?= form_close() ?>
    </div>
</div>
<script>
    window.openEditPositionModal = function (id, title, isTeaching, parentId, isGeneralRoot) {
        const modal = document.getElementById('modal-edit-position');
        if (!modal) return;
        document.getElementById('edit-position-id').value = id;
        document.getElementById('edit-position-title').value = title;
        document.getElementById('edit-position-teaching').checked = !!isTeaching;
        const sel = document.getElementById('edit-position-parent');
        sel.value = parentId ? String(parentId) : '';
        // a position cannot go under itself; the General group always stays at the top
        Array.from(sel.options).forEach(o => { o.disabled = (o.value === String(id)); });
        sel.dispatchEvent(new Event('change'));
        document.getElementById('edit-position-parent-wrap').classList.toggle('hidden', !!isGeneralRoot);
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };
</script>
