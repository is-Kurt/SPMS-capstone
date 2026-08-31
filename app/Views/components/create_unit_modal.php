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
                <div class="relative">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Parent Unit</label>
                    <input type="hidden" name="parent_id" id="hidden-parent-id" value="">
                    
                    <div id="parent-dropdown-menu" class="bg-surface border border-surface-border rounded-xl overflow-hidden flex flex-col">
                        <div class="p-2 border-b border-surface-border/50 shrink-0">
                            <div class="relative w-full">
                                <input type="text" id="parent-search-input" placeholder="Search units..." class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-700 rounded-lg pl-3 pr-10 py-2 text-xs focus:border-accent focus:outline-none text-text transition-all">
                                <div class="absolute inset-y-0 right-0 flex items-center" style="padding-right: 8px;">
                                    <button type="button" id="clear-parent-search" class="text-text-muted hover:text-text cursor-pointer hidden">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <ul id="parent-options-list" class="overflow-y-auto custom-scrollbar p-1" style="max-height: 220px;">
                            <li class="parent-option px-3 py-2 text-sm rounded-lg cursor-pointer transition-colors bg-accent/10 text-accent font-bold" data-value="">
                                No Parent (Top Level)
                            </li>
                            <?php foreach ($units as $u): ?>
                                <li class="parent-option px-3 py-2 text-sm text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 rounded-lg cursor-pointer transition-colors truncate" data-value="<?= $u['id'] ?>">
                                    <?= esc($u['name']) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <script>
                    (function() {
                        const searchInput = document.getElementById('parent-search-input');
                        const optionsList = document.getElementById('parent-options-list');
                        const hiddenInput = document.getElementById('hidden-parent-id');
                        const clearSearchBtn = document.getElementById('clear-parent-search');

                        if (!searchInput || !optionsList) return;

                        const options = optionsList.querySelectorAll('.parent-option');

                        // Filter options
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

                        // Clear search and reset to No Parent
                        if (clearSearchBtn) {
                            clearSearchBtn.addEventListener('click', () => {
                                searchInput.value = '';
                                hiddenInput.value = '';
                                clearSearchBtn.classList.add('hidden');
                                
                                // Unhide all options
                                options.forEach(o => o.classList.remove('hidden'));
                                
                                // Reset all options visually
                                options.forEach(o => {
                                    o.classList.remove('bg-accent/10', 'text-accent', 'font-bold');
                                    o.classList.add('text-text', 'hover:bg-zinc-50', 'dark:hover:bg-zinc-800/50');
                                });
                                
                                // Highlight No Parent
                                const noParentOpt = optionsList.querySelector('.parent-option[data-value=""]');
                                if (noParentOpt) {
                                    noParentOpt.classList.remove('text-text', 'hover:bg-zinc-50', 'dark:hover:bg-zinc-800/50');
                                    noParentOpt.classList.add('bg-accent/10', 'text-accent', 'font-bold');
                                }
                                
                                searchInput.focus();
                            });
                        }

                        // Select option
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
                                
                                // Reset all options visually
                                options.forEach(o => {
                                    o.classList.remove('bg-accent/10', 'text-accent', 'font-bold');
                                    o.classList.add('text-text', 'hover:bg-zinc-50', 'dark:hover:bg-zinc-800/50');
                                });
                                
                                // Highlight selected option
                                opt.classList.remove('text-text', 'hover:bg-zinc-50', 'dark:hover:bg-zinc-800/50');
                                opt.classList.add('bg-accent/10', 'text-accent', 'font-bold');
                            });
                        });
                    })();
                </script>
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
