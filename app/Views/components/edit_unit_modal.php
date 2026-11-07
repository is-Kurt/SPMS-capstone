<div id="modal-edit-unit" class="fixed inset-0 z-[200] hidden overflow-y-auto items-center justify-center bg-zinc-950/40 backdrop-blur-sm transition-all p-4">
    <div class="relative w-full max-w-md rounded-2xl bg-surface border border-surface-border p-8 shadow-2xl transition-all my-auto">
        <h3 class="text-xl font-black text-text tracking-tight mb-2">Edit / Transfer Department / Unit</h3>
        <p class="text-xs font-bold text-text-muted uppercase tracking-widest mb-6">Modify details or reassign parent department / unit</p>

        <?= form_open('account/unit/update', ['id' => 'form-edit-unit', 'data-ajax' => 'update-unit']) ?>
            <input type="hidden" name="id" id="edit-unit-id" value="">

            <div class="space-y-4">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Department / Unit Name</label>
                    <input type="text" name="name" id="edit-unit-name" required placeholder="e.g. Department of Biology" class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all" />
                </div>
                <div class="relative">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Parent Department / Unit</label>
                    <input type="hidden" name="parent_id" id="edit-hidden-parent-id" value="">
                    
                    <div id="edit-parent-dropdown-menu" class="bg-surface border border-surface-border rounded-xl overflow-hidden flex flex-col">
                        <div class="p-2 border-b border-surface-border/50 shrink-0">
                            <div class="relative w-full">
                                <input type="text" id="edit-parent-search-input" placeholder="Search parent department or unit..." class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-700 rounded-lg pl-3 pr-10 py-2 text-xs focus:border-accent focus:outline-none text-text transition-all">
                                <div class="absolute inset-y-0 right-0 flex items-center" style="padding-right: 8px;">
                                    <button type="button" id="edit-clear-parent-search" class="text-text-muted hover:text-text cursor-pointer hidden">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <ul id="edit-parent-options-list" class="overflow-y-auto custom-scrollbar p-1" style="max-height: 220px;">
                            <?= view('components/_unit_parent_options', ['units' => $units, 'optionClass' => 'edit-parent-option', 'topLabel' => 'No Parent (Top Level Node)']) ?>
                        </ul>
                    </div>
                </div>

                <script>
                    // While a cycle is open, ask before a change that moves people in My Staff / the dashboards.
                    (function () {
                        const form = document.getElementById('form-edit-unit');
                        if (!form || <?= !empty($hasOpenCycle) ? 'false' : 'true' ?>) return;
                        form.addEventListener('submit', function () {
                            const parentNow = document.getElementById('edit-hidden-parent-id').value || '';
                            const headNow = (document.getElementById('edit-unit-head') || {}).value || '';
                            if (parentNow !== (form.dataset.origParent || '') || headNow !== (form.dataset.origHead || '')) {
                                form.dataset.confirm = 'A rating cycle is open. This change moves people in My Staff reach and on the dashboards right away (existing routings stay as they are). Continue?';
                                form.dataset.confirmTitle = 'Change during an open cycle';
                                form.dataset.confirmText = 'Save Changes';
                            } else {
                                delete form.dataset.confirm;
                            }
                        });
                    })();
                    (function() {
                        // Options are looked up on every use (event delegation), so the list can be refreshed in place.
                        const searchInput = document.getElementById('edit-parent-search-input');
                        const optionsList = document.getElementById('edit-parent-options-list');
                        const hiddenInput = document.getElementById('edit-hidden-parent-id');
                        const clearSearchBtn = document.getElementById('edit-clear-parent-search');
                        if (!searchInput || !optionsList) return;
                        const options = () => optionsList.querySelectorAll('.edit-parent-option');
                        const rows = () => Array.from(optionsList.querySelectorAll('.edit-parent-option[data-tl-index]'));
                        const top = () => optionsList.querySelector('.edit-parent-option[data-value=""]');
                        const filter = (term) => {
                            window.TreeList.apply(rows(), term);
                            top().classList.toggle('hidden', !!term && !top().textContent.toLowerCase().includes(term.toLowerCase().trim()));
                        };
                        document.addEventListener('DOMContentLoaded', () => { window.TreeList.init(optionsList, '.edit-parent-option'); filter(''); });
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
                                mark(optionsList.querySelector('.edit-parent-option[data-value=""]'));
                                searchInput.focus();
                            });
                        }
                        optionsList.addEventListener('click', (e) => {
                            const opt = e.target.closest('.edit-parent-option');
                            if (!opt || e.target.closest('.js-tl-toggle')) return;
                            const val = opt.getAttribute('data-value');
                            hiddenInput.value = val;
                            searchInput.value = val === "" ? "" : opt.querySelector('.truncate').textContent.trim();
                            clearSearchBtn.classList.toggle('hidden', searchInput.value.length === 0);
                            mark(opt);
                        });
                    })();

                    window.openEditUnitModal = function(id, name, parentId, headPositionId) {
                        const modal = document.getElementById('modal-edit-unit');
                        if (!modal) return;
                        const form = document.getElementById('form-edit-unit');
                        const headSel = document.getElementById('edit-unit-head');
                        if (headSel) { headSel.value = headPositionId ? String(headPositionId) : ''; headSel.dispatchEvent(new Event('change')); }
                        form.dataset.origParent = parentId ? String(parentId) : '';
                        form.dataset.origHead = headPositionId ? String(headPositionId) : '';

                        document.getElementById('edit-unit-id').value = id;
                        document.getElementById('edit-unit-name').value = name;
                        
                        const hiddenInput = document.getElementById('edit-hidden-parent-id');
                        const searchInput = document.getElementById('edit-parent-search-input');
                        const optionsList = document.getElementById('edit-parent-options-list');
                        const rows = Array.from(optionsList.querySelectorAll('.edit-parent-option[data-tl-index]'));
                        const top = optionsList.querySelector('.edit-parent-option[data-value=""]');

                        hiddenInput.value = parentId ? String(parentId) : '';
                        document.getElementById('edit-clear-parent-search').classList.toggle('hidden', !parentId);

                        // A unit cannot move under itself or under one of its own sub-units: hide that branch.
                        // Start with every branch closed except the path to the current parent.
                        [top, ...rows].forEach(o => {
                            o.classList.remove('hidden', 'bg-accent/10', 'text-accent-ink', 'font-bold');
                            o.classList.add('text-text', 'hover:bg-zinc-50', 'dark:hover:bg-zinc-800/50');
                        });
                        rows.forEach(o => { o.dataset.open = '0'; o.dataset.excluded = o.dataset.value === String(id) ? '1' : '0'; });
                        const selectedOpt = parentId ? optionsList.querySelector(`.edit-parent-option[data-value="${parentId}"]`) : top;
                        if (selectedOpt) {
                            selectedOpt.classList.remove('text-text', 'hover:bg-zinc-50', 'dark:hover:bg-zinc-800/50');
                            selectedOpt.classList.add('bg-accent/10', 'text-accent-ink', 'font-bold');
                        }
                        searchInput.value = parentId && selectedOpt ? selectedOpt.querySelector('.truncate').textContent.trim() : '';
                        if (parentId) window.TreeList.reveal(rows, parentId);
                        window.TreeList.apply(rows, '');

                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                    };
                </script>
            </div>

            <div class="mt-4">
                <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Head position</label>
                <select data-themed-select name="head_position_id" id="edit-unit-head" class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold cursor-pointer">
                    <option value="">Inherit from the parent unit (one level down)</option>
                    <?php foreach (($positionTree ?? []) as $p): ?>
                        <?php if ($p['is_general']) continue; ?>
                        <option value="<?= $p['id'] ?>" data-level="<?= (int) $p['level'] ?>"><?= esc($p['title']) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="text-[10px] text-text-muted font-medium mt-2">Link a position only where this unit differs from its parent's line (e.g. an office headed by a Unit Head). Its own link overrides what it would inherit; sub-units follow it.</p>
                <?php if (!empty($hasOpenCycle)): ?>
                    <p class="text-[10px] font-bold text-warning-500 mt-2">A rating cycle is open. Moving this unit or changing its head position changes My Staff reach and dashboards right away; you will be asked to confirm.</p>
                <?php endif; ?>
            </div>

            <div class="mt-8 flex flex-col-reverse sm:flex-row gap-3">
                <button type="button" class="btn-close-modal w-full sm:flex-1 px-6 py-3.5 sm:py-3 rounded-xl border border-surface-border text-sm font-bold text-text hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all cursor-pointer" data-target="modal-edit-unit">
                    Cancel
                </button>
                <button type="submit" class="w-full sm:flex-1 px-6 py-3.5 sm:py-3 rounded-xl bg-accent hover:bg-accent-hover disabled:opacity-50 disabled:cursor-not-allowed text-accent-text text-sm font-bold shadow-lg shadow-accent/20 transition-all active:scale-95 cursor-pointer">
                    Save Changes
                </button>
            </div>
        <?= form_close() ?>
    </div>
</div>
