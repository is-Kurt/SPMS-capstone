<div id="tab-content-create" class="tab-content <?= $activeTab === 'create' ? 'flex' : 'hidden' ?> flex-col lg:absolute lg:inset-0 overflow-y-auto custom-scrollbar bg-surface lg:bg-transparent rounded-xl border border-surface-border lg:border-none shadow-sm lg:shadow-none">
            <?= form_open('account/sendInvites', ['class' => 'p-4 lg:p-6 flex flex-col gap-6 h-full', 'data-ajax' => 'send-invites']) ?>
              <div class="invite-cols">
               <div class="invite-main">
                <div class="shrink-0">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Assign Role</label>
                    <div class="relative w-full lg:max-w-md js-custom-select">
                        <?php
                            // Staff accounts show as "Staff: member" / "Staff: unit head"; the role itself (Employee /
                            // Supervisor) follows the position they pick at sign-up (2026-11-07).
                            $order = ['Employee' => 0, 'Supervisor' => 1];
                            $inviteRoles = $roles;
                            usort($inviteRoles, fn($a, $b) => [$order[$a['name']] ?? 2, $a['name']] <=> [$order[$b['name']] ?? 2, $b['name']]);
                            $defaultRoleId = '';
                            $defaultRoleName = 'Select a role...';
                            foreach ($inviteRoles as $role) {
                                if ($role['name'] === 'Employee') {
                                    $defaultRoleId = $role['id'];
                                    $defaultRoleName = staff_role_label($role['name']);
                                    break;
                                }
                            }
                        ?>
                        <input type="hidden" name="role_id" id="select-invite-role" value="<?= $defaultRoleId ?>" required>
                        <button type="button" class="js-select-button w-full bg-surface border border-surface-border rounded-xl px-4 py-3 text-sm focus:border-accent outline-none text-text cursor-pointer font-bold flex justify-between items-center transition-colors hover:border-accent/50">
                            <span class="js-select-label"><?= $defaultRoleName ?></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-text-muted transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <ul class="js-select-dropdown absolute z-50 w-full mt-2 bg-surface border border-surface-border rounded-xl shadow-lg shadow-black/5 dark:shadow-black/20 overflow-hidden hidden transform origin-top transition-all duration-200 scale-95 opacity-0">
                            <?php foreach ($inviteRoles as $role): ?>
                                <?php $isSelected = ($role['name'] === 'Employee'); ?>
                                <li class="px-4 py-3 text-sm font-bold text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 cursor-pointer transition-colors js-select-option <?= $isSelected ? 'bg-accent/10 text-accent-ink' : '' ?>" data-value="<?= $role['id'] ?>" data-role="<?= esc($role['name']) ?>" data-label="<?= esc(staff_role_label($role['name'])) ?>">
                                    <?= esc(staff_role_label($role['name'])) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <div class="flex-1 flex flex-col">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Mass Email Invitations</label>
                    <p class="text-[10px] lg:text-xs text-text-muted mb-4 font-medium">Paste a list of email addresses below. Separate emails with commas, semicolons, or newlines.</p>
                    
                    <textarea name="emails" required placeholder="john.doe@school.edu&#10;jane.smith@school.edu" 
                        class="w-full flex-1 min-h-[250px] lg:min-h-[300px] bg-zinc-50 dark:bg-zinc-800/50 border border-surface-border rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-medium resize-none"></textarea>
                </div>
               </div>

               <!-- Department / unit: its own card with search and a collapsible tree (Admin / TWG / PMT have none) -->
               <div class="invite-units rounded-xl border border-surface-border overflow-hidden" id="invite-unit-wrap">
                    <div class="px-4 py-3 bg-zinc-50 dark:bg-zinc-800/30 border-b border-surface-border">
                        <span class="text-[10px] font-black uppercase tracking-widest text-text-muted whitespace-nowrap">Department / unit</span>
                    </div>
                    <!-- The choice gets its own row: long names wrap here instead of squeezing the header -->
                    <div class="px-4 py-2.5 border-b border-surface-border flex items-start justify-between gap-3">
                        <span id="invite-unit-chosen" class="text-sm font-bold text-text-muted">No department / unit selected</span>
                        <button type="button" id="invite-unit-deselect" class="hidden shrink-0 text-text-muted hover:text-text cursor-pointer" style="margin-top: 0.125rem;" aria-label="Clear the selected department / unit" title="Clear selection">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div class="p-2 border-b border-surface-border/50">
                        <div class="relative w-full">
                            <input type="text" id="invite-unit-search" placeholder="Search departments and units..." autocomplete="off" class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-700 rounded-lg pl-3 pr-10 py-2 text-xs focus:border-accent focus:outline-none text-text transition-all">
                            <div class="absolute inset-y-0 right-0 flex items-center" style="padding-right: 8px;">
                                <button type="button" id="invite-unit-clear" class="text-text-muted hover:text-text cursor-pointer hidden" aria-label="Clear search">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <ul id="invite-unit-list" class="invite-unit-scroll custom-scrollbar p-1">
                        <?= view('components/_unit_parent_options', ['units' => $units, 'optionClass' => 'invite-unit-option', 'topLabel' => '']) ?>
                    </ul>
                    <p id="invite-unit-empty" class="hidden px-4 py-6 text-center text-xs font-bold text-text-muted">No department or unit matches.</p>
                    <?php
                        $inviteUnitTree = [];
                        foreach ($units as $u) $inviteUnitTree[$u['parent_id'] ?: 0][] = $u;
                        $renderInviteUnits = function ($parent, $level) use (&$renderInviteUnits, $inviteUnitTree, $unitHeads) {
                            foreach ($inviteUnitTree[$parent] ?? [] as $u) {
                                $heads = $unitHeads[$u['id']]['titles'] ?? [];
                                $holders = $unitHeads[$u['id']]['holders'] ?? [];
                                echo '<option value="' . (int) $u['id'] . '" data-level="' . (int) $level . '" data-heads="' . esc(implode(' / ', $heads)) . '" data-holders="' . esc(implode(', ', $holders)) . '">'
                                   . esc($u['name']) . '</option>';
                                $renderInviteUnits($u['id'], $level + 1);
                            }
                        };
                    ?>
                    <?php // The value the form sends (and the head / holder data for the hint); the card above sets it. ?>
                    <select name="unit_id" id="select-invite-unit" class="hidden" tabindex="-1" aria-hidden="true">
                        <option value="" data-placeholder>Select the department / unit...</option>
                        <?php $renderInviteUnits(0, 0); ?>
                    </select>
                    <p id="invite-unit-hint" class="px-4 py-3 border-t border-surface-border text-[10px] lg:text-xs text-text-muted font-medium">Everyone in this batch joins this department / unit. Staff: unit head takes its head position; Staff: member picks a position under it at sign-up.</p>
                </div>
              </div>

                <div class="flex justify-end pt-4 shrink-0">
                    <button type="submit" class="w-full lg:w-auto flex items-center justify-center gap-2 bg-accent hover:bg-accent-hover text-accent-text text-xs font-bold py-3.5 lg:py-3 px-8 rounded-xl shadow-lg shadow-accent/20 transition-all active:scale-[0.98] cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                        Queue Invitations
                    </button>
                </div>
            <?= form_close() ?>
            <script>
            // Admin, TWG and PMT accounts have no department; for a Supervisor, show the head position they will hold.
            (function () {
                const roleInput = document.getElementById('select-invite-role');
                const wrap = document.getElementById('invite-unit-wrap');
                const unitSel = document.getElementById('select-invite-unit');
                const hint = document.getElementById('invite-unit-hint');
                if (!roleInput || !wrap || !unitSel) return;
                const roleName = () => {
                    const opt = document.querySelector('.js-select-option[data-value="' + roleInput.value + '"]');
                    return opt ? (opt.dataset.role || opt.textContent.trim()) : '';
                };
                function update() {
                    const role = roleName();
                    const needsUnit = role === 'Employee' || role === 'Supervisor';
                    wrap.classList.toggle('hidden', !needsUnit);
                    unitSel.disabled = !needsUnit;
                    const o = unitSel.options[unitSel.selectedIndex];
                    if (role === 'Supervisor' && o && o.value) {
                        hint.textContent = o.dataset.heads
                            ? 'They will be its head: ' + o.dataset.heads + (o.dataset.holders ? '. Already held by ' + o.dataset.holders + ' (they will be co-heads).' : '.')
                            : 'This unit has no head position yet: link one in System Data first.';
                    } else {
                        hint.textContent = 'Everyone in this batch joins this department / unit. Staff: unit head takes its head position; Staff: member picks a position under it at sign-up.';
                    }
                }
                // The department card: search, open / close branches, click to pick (sets the hidden select).
                const list = document.getElementById('invite-unit-list'), search = document.getElementById('invite-unit-search');
                const clear = document.getElementById('invite-unit-clear'), chosen = document.getElementById('invite-unit-chosen');
                const rows = () => Array.from(list.querySelectorAll('.invite-unit-option[data-tl-index]'));
                function sync() {
                    rows().forEach(r => {
                        const on = r.dataset.value === unitSel.value;
                        r.classList.toggle('bg-accent/10', on); r.classList.toggle('text-accent-ink', on); r.classList.toggle('font-bold', on);
                        r.classList.toggle('text-text', !on);
                    });
                    const o = unitSel.options[unitSel.selectedIndex], picked = !!(o && o.value);
                    chosen.textContent = picked ? o.text.trim() : 'No department / unit selected';
                    chosen.classList.toggle('text-accent-ink', picked); chosen.classList.toggle('text-text-muted', !picked);
                    document.getElementById('invite-unit-deselect').classList.toggle('hidden', !picked);
                    if (!search.value && unitSel.value) window.TreeList.reveal(rows(), unitSel.value);
                    filter();
                }
                function filter() {
                    const shown = window.TreeList.apply(rows(), search.value);
                    clear.classList.toggle('hidden', !search.value);
                    document.getElementById('invite-unit-empty').classList.toggle('hidden', shown > 0);
                }
                list.addEventListener('click', e => {
                    const row = e.target.closest('.invite-unit-option');
                    if (!row || e.target.closest('.js-tl-toggle')) return;
                    unitSel.value = row.dataset.value;
                    unitSel.dispatchEvent(new Event('change'));
                });
                document.getElementById('invite-unit-deselect').addEventListener('click', () => { unitSel.value = ''; unitSel.dispatchEvent(new Event('change')); });
                search.addEventListener('input', filter);
                clear.addEventListener('click', () => { search.value = ''; sync(); search.focus(); });
                unitSel.addEventListener('change', sync);
                unitSel.closest('form').addEventListener('reset', () => setTimeout(() => { search.value = ''; sync(); update(); }, 0));
                window.inviteUnitPickerSync = sync;          // after System Data refreshes the list in place
                document.addEventListener('DOMContentLoaded', () => { window.TreeList.init(list, '.invite-unit-option'); sync(); });

                new MutationObserver(update).observe(roleInput, { attributes: true, attributeFilter: ['value'] });
                roleInput.addEventListener('change', update);
                document.addEventListener('click', e => { if (e.target.closest('.js-select-option')) setTimeout(update, 0); });
                unitSel.addEventListener('change', update);
                update();
            })();
            </script>
        </div>