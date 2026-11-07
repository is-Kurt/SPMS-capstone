<div id="tab-content-create" class="tab-content <?= $activeTab === 'create' ? 'flex' : 'hidden' ?> flex-col lg:absolute lg:inset-0 overflow-y-auto custom-scrollbar bg-surface lg:bg-transparent rounded-xl border border-surface-border lg:border-none shadow-sm lg:shadow-none">
            <?= form_open('account/sendInvites', ['class' => 'p-4 lg:p-6 flex flex-col gap-6 h-full', 'data-ajax' => 'send-invites']) ?>
                
                <div class="shrink-0">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Assign Role</label>
                    <div class="relative w-full lg:max-w-md js-custom-select">
                        <?php
                            $defaultRoleId = '';
                            $defaultRoleName = 'Select a role...';
                            foreach($roles as $role) {
                                if (strtolower($role['name']) === 'employee') {
                                    $defaultRoleId = $role['id'];
                                    $defaultRoleName = esc($role['name']);
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
                            <?php foreach($roles as $role): ?>
                                <?php $isSelected = (strtolower($role['name']) === 'employee'); ?>
                                <li class="px-4 py-3 text-sm font-bold text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 cursor-pointer transition-colors js-select-option <?= $isSelected ? 'bg-accent/10 text-accent-ink' : '' ?>" data-value="<?= $role['id'] ?>" data-label="<?= esc($role['name']) ?>">
                                    <?= esc($role['name']) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <div class="shrink-0" id="invite-unit-wrap">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Department / Unit</label>
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
                    <select data-themed-select name="unit_id" id="select-invite-unit" class="w-full lg:max-w-md bg-surface border border-surface-border rounded-xl px-4 py-3 text-sm focus:border-accent outline-none text-text cursor-pointer font-bold">
                        <option value="">Select the department / unit...</option>
                        <?php $renderInviteUnits(0, 0); ?>
                    </select>
                    <p id="invite-unit-hint" class="text-[10px] lg:text-xs text-text-muted mt-2 font-medium">Everyone in this batch joins this department / unit. A Supervisor becomes its head; an Employee picks their position at sign-up.</p>
                </div>

                <div class="flex-1 flex flex-col">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Mass Email Invitations</label>
                    <p class="text-[10px] lg:text-xs text-text-muted mb-4 font-medium">Paste a list of email addresses below. Separate emails with commas, semicolons, or newlines.</p>
                    
                    <textarea name="emails" required placeholder="john.doe@school.edu&#10;jane.smith@school.edu" 
                        class="w-full flex-1 min-h-[250px] lg:min-h-[300px] bg-zinc-50 dark:bg-zinc-800/50 border border-surface-border rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-medium resize-none"></textarea>
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
                    return opt ? opt.textContent.trim() : '';
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
                        hint.textContent = 'Everyone in this batch joins this department / unit. A Supervisor becomes its head; an Employee picks their position at sign-up.';
                    }
                }
                new MutationObserver(update).observe(roleInput, { attributes: true, attributeFilter: ['value'] });
                roleInput.addEventListener('change', update);
                document.addEventListener('click', e => { if (e.target.closest('.js-select-option')) setTimeout(update, 0); });
                unitSel.addEventListener('change', update);
                update();
            })();
            </script>
        </div>