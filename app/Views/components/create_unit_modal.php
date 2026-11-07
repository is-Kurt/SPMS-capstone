<div id="modal-create-unit" class="fixed inset-0 z-[200] hidden overflow-y-auto items-center justify-center bg-zinc-950/40 backdrop-blur-sm transition-all p-4">
    <div class="relative w-full max-w-md rounded-2xl bg-surface border border-surface-border p-8 shadow-2xl transition-all my-auto">
        <h3 class="text-xl font-black text-text tracking-tight mb-2">Create Department / Unit</h3>
        <p class="text-xs font-bold text-text-muted uppercase tracking-widest mb-6">Departments &amp; Units</p>

        <?= form_open('account/unit/add', ['id' => 'form-create-unit', 'data-ajax' => 'add-unit']) ?>
            <div class="space-y-4">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Department / Unit Name</label>
                    <input type="text" name="name" required placeholder="e.g. College of Forestry, Department of Biology, HRDO" class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all" />
                </div>
                <div class="relative">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Parent Department / Unit</label>
                    <input type="hidden" name="parent_id" id="hidden-parent-id" value="">
                    
                    <div id="parent-dropdown-menu" class="bg-surface border border-surface-border rounded-xl overflow-hidden flex flex-col">
                        <div class="p-2 border-b border-surface-border/50 shrink-0">
                            <div class="relative w-full">
                                <input type="text" id="parent-search-input" placeholder="Search parent department or unit..." class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-700 rounded-lg pl-3 pr-10 py-2 text-xs focus:border-accent focus:outline-none text-text transition-all">
                                <div class="absolute inset-y-0 right-0 flex items-center" style="padding-right: 8px;">
                                    <button type="button" id="clear-parent-search" class="text-text-muted hover:text-text cursor-pointer hidden">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <ul id="parent-options-list" class="overflow-y-auto custom-scrollbar p-1" style="max-height: 220px;">
                            <?= view('components/_unit_parent_options', ['units' => $units, 'optionClass' => 'parent-option', 'topLabel' => 'No Parent (Top Level)']) ?>
                        </ul>
                    </div>
                </div>

                <script>
                    (function() {
                        // Options are looked up on every use (event delegation), so the list can be refreshed in place.
                        const searchInput = document.getElementById('parent-search-input');
                        const optionsList = document.getElementById('parent-options-list');
                        const hiddenInput = document.getElementById('hidden-parent-id');
                        const clearSearchBtn = document.getElementById('clear-parent-search');
                        if (!searchInput || !optionsList) return;
                        const options = () => optionsList.querySelectorAll('.parent-option');
                        const rows = () => Array.from(optionsList.querySelectorAll('.parent-option[data-tl-index]'));
                        const top = () => optionsList.querySelector('.parent-option[data-value=""]');
                        const filter = (term) => {
                            window.TreeList.apply(rows(), term);
                            top().classList.toggle('hidden', !!term && !top().textContent.toLowerCase().includes(term.toLowerCase().trim()));
                        };
                        document.addEventListener('DOMContentLoaded', () => { window.TreeList.init(optionsList, '.parent-option'); filter(''); });
                        const mark = (opt) => {
                            options().forEach(o => {
                                o.classList.remove('bg-accent/10', 'text-accent-ink', 'font-bold');
                                o.classList.add('text-text', 'hover:bg-zinc-50', 'dark:hover:bg-zinc-800/50');
                            });
                            if (opt) {
                                opt.classList.remove('text-text', 'hover:bg-zinc-50', 'dark:hover:bg-zinc-800/50');
                                opt.classList.add('bg-accent/10', 'text-accent-ink', 'font-bold');
                            }
                        };
                        searchInput.addEventListener('input', (e) => {
                            const term = e.target.value.toLowerCase();
                            clearSearchBtn.classList.toggle('hidden', term.length === 0);
                            filter(term);
                        });
                        if (clearSearchBtn) {
                            clearSearchBtn.addEventListener('click', () => {
                                searchInput.value = '';
                                hiddenInput.value = '';
                                clearSearchBtn.classList.add('hidden');
                                filter('');
                                mark(optionsList.querySelector('.parent-option[data-value=""]'));
                                searchInput.focus();
                            });
                        }
                        optionsList.addEventListener('click', (e) => {
                            const opt = e.target.closest('.parent-option');
                            if (!opt || e.target.closest('.js-tl-toggle')) return;
                            const val = opt.getAttribute('data-value');
                            hiddenInput.value = val;
                            searchInput.value = val === "" ? "" : opt.querySelector('.truncate').textContent.trim();
                            clearSearchBtn.classList.toggle('hidden', searchInput.value.length === 0);
                            mark(opt);
                        });
                    })();
                </script>
            </div>

            <div class="mt-8 flex flex-col-reverse sm:flex-row gap-3">
                <button type="button" class="btn-close-modal w-full sm:flex-1 px-6 py-3.5 sm:py-3 rounded-xl border border-surface-border text-sm font-bold text-text hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all cursor-pointer" data-target="modal-create-unit">
                    Cancel
                </button>
                <button type="submit" class="w-full sm:flex-1 px-6 py-3.5 sm:py-3 rounded-xl bg-accent hover:bg-accent-hover disabled:opacity-50 disabled:cursor-not-allowed text-accent-text text-sm font-bold shadow-lg shadow-accent/20 transition-all active:scale-95 cursor-pointer">
                    Create Department / Unit
                </button>
            </div>
        <?= form_close() ?>
    </div>
</div>
