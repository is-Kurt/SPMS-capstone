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
                                <li class="px-4 py-3 text-sm font-bold text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 cursor-pointer transition-colors js-select-option <?= $isSelected ? 'bg-accent/10 text-accent' : '' ?>" data-value="<?= $role['id'] ?>" data-label="<?= esc($role['name']) ?>">
                                    <?= esc($role['name']) ?>
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

                <div class="flex justify-end pt-4 shrink-0">
                    <button type="submit" class="w-full lg:w-auto flex items-center justify-center gap-2 bg-accent hover:bg-accent-hover text-white text-xs font-bold py-3.5 lg:py-3 px-8 rounded-xl shadow-lg shadow-accent/20 transition-all active:scale-[0.98] cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                        Queue Invitations
                    </button>
                </div>
            <?= form_close() ?>
        </div>