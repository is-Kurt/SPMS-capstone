<div id="tab-content-create" class="tab-content <?= $activeTab === 'create' ? 'flex' : 'hidden' ?> flex-col lg:absolute lg:inset-0 overflow-y-auto custom-scrollbar bg-surface lg:bg-transparent rounded-xl border border-surface-border lg:border-none shadow-sm lg:shadow-none">
            <?= form_open('account/sendInvites', ['class' => 'p-4 md:p-8 flex flex-col gap-6 h-full', 'data-ajax' => 'send-invites']) ?>
                
                <div class="shrink-0">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Assign Role</label>
                    <div class="relative w-full lg:max-w-md">
                        <select name="role_id" id="select-invite-role" required class="appearance-none w-full bg-zinc-50 lg:bg-white dark:bg-zinc-900 dark:[color-scheme:dark] border border-surface-border rounded-xl px-4 py-3 text-sm focus:border-accent outline-none text-text cursor-pointer font-bold">
                            <option value="" disabled selected>Select a role for this batch...</option>
                            <?php foreach($roles as $role): ?>
                                <option value="<?= $role['id'] ?>"><?= esc($role['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-text-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>
                </div>

                <div class="flex-1 flex flex-col">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Mass Email Invitations</label>
                    <p class="text-[10px] lg:text-xs text-text-muted mb-4 font-medium">Paste a list of email addresses below. Separate emails with commas, semicolons, or newlines.</p>
                    
                    <textarea name="emails" required placeholder="john.doe@school.edu&#10;jane.smith@school.edu" 
                        class="w-full flex-1 min-h-[250px] lg:min-h-[300px] bg-zinc-50 dark:bg-zinc-800/50 border border-surface-border rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-medium resize-none"></textarea>
                </div>

                <div class="flex justify-end pt-4 border-t border-surface-border shrink-0">
                    <button type="submit" class="w-full lg:w-auto flex items-center justify-center gap-2 bg-accent hover:bg-accent-hover text-white text-xs font-bold py-3.5 lg:py-3 px-8 rounded-xl shadow-lg shadow-accent/20 transition-all active:scale-[0.98] cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                        Queue Invitations
                    </button>
                </div>
            <?= form_close() ?>
        </div>