<div id="modal-edit-unit" class="fixed inset-0 z-[200] hidden overflow-y-auto items-center justify-center bg-zinc-950/40 backdrop-blur-sm transition-all">
    <div class="relative w-full max-w-md rounded-2xl bg-surface border border-surface-border p-8 shadow-2xl transition-all m-4">
        <h3 class="text-xl font-black text-text tracking-tight mb-2">Edit / Transfer Unit</h3>
        <p class="text-xs font-bold text-text-muted uppercase tracking-widest mb-6">Modify details or reassign parent college</p>

        <?= form_open('account/unit/update', ['id' => 'form-edit-unit', 'data-ajax' => 'update-unit']) ?>
            <input type="hidden" name="id" id="edit-unit-id" value="">

            <div class="space-y-4">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Unit / Department Name</label>
                    <input type="text" name="name" id="edit-unit-name" required placeholder="e.g. Department of Biology" class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all" />
                </div>
                <div class="relative">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Parent Unit / College</label>
                    <input type="hidden" name="parent_id" id="edit-hidden-parent-id" value="">
                    
                    <div id="edit-parent-dropdown-menu" class="bg-surface border border-surface-border rounded-xl overflow-hidden flex flex-col">
                        <div class="p-2 border-b border-surface-border/50 shrink-0">
                            <div class="relative w-full">
                                <input type="text" id="edit-parent-search-input" placeholder="Search parent college or unit..." class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-700 rounded-lg pl-3 pr-10 py-2 text-xs focus:border-accent focus:outline-none text-text transition-all">
                                <div class="absolute inset-y-0 right-0 flex items-center" style="padding-right: 8px;">
                                    <button type="button" id="edit-clear-parent-search" class="text-text-muted hover:text-text cursor-pointer hidden">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <ul id="edit-parent-options-list" class="overflow-y-auto custom-scrollbar p-1" style="max-height: 220px;">
                            <li class="edit-parent-option px-3 py-2 text-sm rounded-lg cursor-pointer transition-colors bg-accent/10 text-accent font-bold" data-value="">
                                No Parent (Top Level Node)
                            </li>
                            <?php foreach ($units as $u): ?>
                                <li class="edit-parent-option px-3 py-2 text-sm text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 rounded-lg cursor-pointer transition-colors truncate" data-value="<?= $u['id'] ?>">
                                    <?= esc($u['name']) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <script>
                    (function() {
                        const searchInput = document.getElementById('edit-parent-search-input');
                        const optionsList = document.getElementById('edit-parent-options-list');
                        const hiddenInput = document.getElementById('edit-hidden-parent-id');
                        const clearSearchBtn = document.getElementById('edit-clear-parent-search');

                        if (!searchInput || !optionsList) return;

                        const options = optionsList.querySelectorAll('.edit-parent-option');

                        searchInput.addEventListener('input', (e) => {
                            const term = e.target.value.toLowerCase();
                            if (term.length > 0) {
                                clearSearchBtn.classList.remove('hidden');
                            } else {
                                clearSearchBtn.classList.add('hidden');
                            }

                            options.forEach(opt => {
                                if (opt.textContent.toLowerCase().includes(term)) {
                                    opt.classList.remove('hidden');
                                } else {
                                    opt.classList.add('hidden');
                                }
                            });
                        });

                        if (clearSearchBtn) {
                            clearSearchBtn.addEventListener('click', () => {
                                searchInput.value = '';
                                hiddenInput.value = '';
                                clearSearchBtn.classList.add('hidden');
                                options.forEach(o => o.classList.remove('hidden'));
                                options.forEach(o => {
                                    o.classList.remove('bg-accent/10', 'text-accent', 'font-bold');
                                    o.classList.add('text-text', 'hover:bg-zinc-50', 'dark:hover:bg-zinc-800/50');
                                });
                                const noParentOpt = optionsList.querySelector('.edit-parent-option[data-value=""]');
                                if (noParentOpt) {
                                    noParentOpt.classList.remove('text-text', 'hover:bg-zinc-50', 'dark:hover:bg-zinc-800/50');
                                    noParentOpt.classList.add('bg-accent/10', 'text-accent', 'font-bold');
                                }
                                searchInput.focus();
                            });
                        }

                        options.forEach(opt => {
                            opt.addEventListener('click', () => {
                                const val = opt.getAttribute('data-value');
                                hiddenInput.value = val;
                                searchInput.value = val === "" ? "" : opt.textContent.trim();
                                
                                if (searchInput.value.length > 0) {
                                    clearSearchBtn.classList.remove('hidden');
                                } else {
                                    clearSearchBtn.classList.add('hidden');
                                }
                                
                                options.forEach(o => {
                                    o.classList.remove('bg-accent/10', 'text-accent', 'font-bold');
                                    o.classList.add('text-text', 'hover:bg-zinc-50', 'dark:hover:bg-zinc-800/50');
                                });
                                
                                opt.classList.remove('text-text', 'hover:bg-zinc-50', 'dark:hover:bg-zinc-800/50');
                                opt.classList.add('bg-accent/10', 'text-accent', 'font-bold');
                            });
                        });
                    })();

                    window.openEditUnitModal = function(id, name, parentId) {
                        const modal = document.getElementById('modal-edit-unit');
                        if (!modal) return;

                        document.getElementById('edit-unit-id').value = id;
                        document.getElementById('edit-unit-name').value = name;
                        
                        const hiddenInput = document.getElementById('edit-hidden-parent-id');
                        const searchInput = document.getElementById('edit-parent-search-input');
                        const optionsList = document.getElementById('edit-parent-options-list');
                        const options = optionsList.querySelectorAll('.edit-parent-option');

                        hiddenInput.value = parentId ? String(parentId) : '';

                        options.forEach(o => {
                            o.classList.remove('hidden', 'bg-accent/10', 'text-accent', 'font-bold');
                            o.classList.add('text-text', 'hover:bg-zinc-50', 'dark:hover:bg-zinc-800/50');
                            // Hide the current unit itself to prevent selecting self as parent
                            if (o.getAttribute('data-value') === String(id)) {
                                o.classList.add('hidden');
                            }
                        });

                        const selectedOpt = optionsList.querySelector(`.edit-parent-option[data-value="${parentId || ''}"]`);
                        if (selectedOpt) {
                            selectedOpt.classList.remove('text-text', 'hover:bg-zinc-50', 'dark:hover:bg-zinc-800/50');
                            selectedOpt.classList.add('bg-accent/10', 'text-accent', 'font-bold');
                            searchInput.value = (parentId && selectedOpt) ? selectedOpt.textContent.trim() : '';
                        } else {
                            searchInput.value = '';
                        }

                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                    };
                </script>
            </div>

            <div class="mt-8 flex flex-col-reverse sm:flex-row gap-3">
                <button type="button" class="btn-close-modal w-full sm:flex-1 px-6 py-3.5 sm:py-3 rounded-xl border border-surface-border text-sm font-bold text-text hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all cursor-pointer" data-target="modal-edit-unit">
                    Cancel
                </button>
                <button type="submit" class="w-full sm:flex-1 px-6 py-3.5 sm:py-3 rounded-xl bg-accent hover:bg-accent-hover disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold shadow-lg shadow-accent/20 transition-all active:scale-95 cursor-pointer">
                    Save Changes
                </button>
            </div>
        <?= form_close() ?>
    </div>
</div>
