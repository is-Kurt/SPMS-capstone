<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="min-h-screen flex flex-col bg-zinc-50 dark:bg-zinc-950">
    <?= view('components/govph_masthead') ?>

    <main class="flex-1 flex flex-col items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
    
    <div class="w-full max-w-2xl mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-text">Account Setup</h2>
            <p class="text-text-muted font-medium mt-1 text-xs sm:text-sm">Complete your profile to access the system.</p>
        </div>
        <button type="button" onclick="handleThemeToggle(this)" class="w-9 h-9 rounded-xl flex items-center justify-center text-zinc-600 dark:text-emerald-400 bg-white dark:bg-zinc-900 border border-surface-border shadow-xs hover:scale-105 transition-all cursor-pointer shrink-0" title="Toggle Light / Dark Mode">
            <svg class="w-4 h-4 hidden dark:block text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
            <svg class="w-4 h-4 block dark:hidden text-zinc-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
        </button>
    </div>

    <div class="w-full max-w-2xl">
        <?php if (session()->has('success')): ?>
            <p class="text-sm font-bold text-success-500 dark:text-success-400 mb-6"><?= session('success') ?></p>
        <?php endif; ?>

        <?php if (session()->has('error')): ?>
            <p class="text-sm font-bold text-danger-500 dark:text-danger-400 mb-6"><?= session('error') ?></p>
        <?php endif; ?>
    </div>
    <?= form_open('signup', ['class' => 'w-full max-w-2xl flex flex-col gap-6', 'novalidate' => 'novalidate']) ?>
        <input type="hidden" name="token" value="<?= esc($invitation['token']) ?>">

        <div class="border border-surface-border p-8 rounded-2xl bg-white dark:bg-zinc-900 shadow-sm">
            <h3 class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-6 flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-accent text-accent-text flex items-center justify-center">1</span>
                Basic Profile
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                <div class="col-span-1 sm:col-span-2">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text mb-1">Email Address (Locked)</label>
                    <input type="text" value="<?= esc($invitation['email']) ?>" readonly 
                           class="w-full bg-zinc-100 dark:bg-zinc-800/80 border border-transparent rounded-xl px-4 py-3 text-sm text-text-muted cursor-not-allowed" />
                </div>

                <div class="col-span-1">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text mb-1">First Name</label>
                    <input type="text" name="first_name" value="<?= old('first_name') ?>" required
                           class="w-full bg-zinc-50 dark:bg-zinc-950 border border-surface-border rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:outline-none text-text transition-all" />
                    <div class="h-3 pl-1 mt-1">
                        <p class="text-danger-500 text-[10px] font-bold uppercase tracking-wider"><?= validation_show_error('first_name') ?></p>
                    </div>
                </div>

                <div class="col-span-1">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text mb-1">Last Name</label>
                    <input type="text" name="last_name" value="<?= old('last_name') ?>" required
                           class="w-full bg-zinc-50 dark:bg-zinc-950 border border-surface-border rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:outline-none text-text transition-all" />
                    <div class="h-3 pl-1 mt-1">
                        <p class="text-danger-500 text-[10px] font-bold uppercase tracking-wider"><?= validation_show_error('last_name') ?></p>
                    </div>
                </div>

                <div class="col-span-1">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text mb-1">Password</label>
                    <input type="password" name="password" value="<?= old('password') ?>" required minlength="8"
                           class="w-full bg-zinc-50 dark:bg-zinc-950 border border-surface-border rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:outline-none text-text transition-all" />
                    <div class="h-3 pl-1 mt-1">
                        <p class="text-danger-500 text-[10px] font-bold uppercase tracking-wider"><?= validation_show_error('password') ?></p>
                    </div>
                </div>

                <div class="col-span-1">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text mb-1">Confirm Password</label>
                    <input type="password" name="confirm-password" value="<?= old('confirm-password') ?>" required
                           class="w-full bg-zinc-50 dark:bg-zinc-950 border border-surface-border rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:outline-none text-text transition-all" />
                    <div class="h-3 pl-1 mt-1">
                        <p class="text-danger-500 text-[10px] font-bold uppercase tracking-wider"><?= validation_show_error('confirm-password') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($isAdminInvite): ?>
            <div class="border border-highlight-200 dark:border-highlight-500/20 p-6 rounded-2xl bg-highlight-50/50 dark:bg-highlight-500/5 shadow-sm text-center">
                <div class="inline-flex p-3 rounded-full bg-highlight-100 dark:bg-highlight-500/20 text-highlight-600 dark:text-highlight-400 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3 class="text-sm font-black tracking-tight text-highlight-900 dark:text-highlight-300">Administrator Setup</h3>
            </div>
        <?php elseif (isset($isTwgInvite) && $isTwgInvite): ?>
            <div class="border border-info-200 dark:border-info-500/20 p-6 rounded-2xl bg-info-50/50 dark:bg-info-500/5 shadow-sm text-center">
                <div class="inline-flex p-3 rounded-full bg-info-100 dark:bg-info-500/20 text-info-600 dark:text-info-400 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h3 class="text-sm font-black tracking-tight text-info-900 dark:text-info-300">TWG Account Setup</h3>
            </div>
        <?php elseif (isset($isPmtInvite) && $isPmtInvite): ?>
            <div class="border border-indigo-200 dark:border-indigo-500/20 p-6 rounded-2xl bg-indigo-50/50 dark:bg-indigo-500/5 shadow-sm text-center">
                <div class="inline-flex p-3 rounded-full bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3 class="text-sm font-black tracking-tight text-indigo-900 dark:text-indigo-300">PMT Committee Account Setup</h3>
            </div>
        <?php else: ?>
            <?php
                $asg = $assignment ?? null;
                $oldStatus = old('employment_status') ?: 'Permanent';
                $statusNames = array_column($employmentStatuses, 'name');
                if (!in_array($oldStatus, $statusNames, true)) $oldStatus = $statusNames[0] ?? '';
            ?>
            <div class="border border-surface-border p-8 rounded-2xl bg-white dark:bg-zinc-900 shadow-sm mb-6">
                <h3 class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-6 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-accent text-accent-text flex items-center justify-center">2</span>
                    Designation
                </h3>
                <?php if (!$asg || !$asg['unit']): ?>
                    <p class="text-sm font-bold text-danger-500">This invitation has no department. Ask the administrator to send a new invitation.</p>
                <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                    <div>
                        <span class="block text-[10px] font-black uppercase tracking-widest text-text mb-1">Department / Unit</span>
                        <div class="w-full rounded-xl px-4 py-3 text-sm font-bold text-text bg-zinc-50 dark:bg-zinc-800/50"><?= esc($asg['unit']['name']) ?></div>
                        <p class="text-[10px] text-text-muted font-medium mt-1 pl-1">Set by the administrator's invitation.</p>
                    </div>
                    <div>
                        <label for="signup-position" class="block text-[10px] font-black uppercase tracking-widest text-text mb-1">Position</label>
                        <?php if (count($asg['positions']) === 1): ?>
                            <div class="w-full rounded-xl px-4 py-3 text-sm font-bold text-text bg-zinc-50 dark:bg-zinc-800/50"><?= esc($asg['positions'][0]['title']) ?></div>
                            <input type="hidden" name="position_id" value="<?= (int) $asg['positions'][0]['id'] ?>">
                        <?php elseif (empty($asg['positions'])): ?>
                            <p class="text-sm font-bold text-danger-500">No positions are set up for this unit yet. Ask the administrator.</p>
                        <?php else: ?>
                            <select name="position_id" id="signup-position" required class="w-full bg-surface hover:bg-zinc-50 dark:hover:bg-zinc-800/50 border border-surface-border rounded-xl px-4 py-3 text-sm focus:border-accent outline-none text-text cursor-pointer font-bold">
                                <option value="" disabled <?= old('position_id') ? '' : 'selected' ?>>Select your position...</option>
                                <?php foreach ($asg['positions'] as $pos): ?>
                                    <option value="<?= (int) $pos['id'] ?>" <?= (string) old('position_id') === (string) $pos['id'] ? 'selected' : '' ?>><?= esc($pos['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                        <div class="h-3 pl-1 mt-1"><p class="text-danger-500 text-[10px] font-bold uppercase tracking-wider"><?= validation_show_error('position_id') ?></p></div>
                    </div>
                    <div>
                        <label for="signup-status" class="block text-[10px] font-black uppercase tracking-widest text-text mb-1">Employment Status</label>
                        <select name="employment_status" id="signup-status" required class="w-full bg-surface hover:bg-zinc-50 dark:hover:bg-zinc-800/50 border border-surface-border rounded-xl px-4 py-3 text-sm focus:border-accent outline-none text-text cursor-pointer font-bold">
                            <?php foreach ($statusNames as $st): ?>
                                <option value="<?= esc($st) ?>" <?= $st === $oldStatus ? 'selected' : '' ?>><?= esc($st) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="h-3 pl-1 mt-1"><p class="text-danger-500 text-[10px] font-bold uppercase tracking-wider"><?= validation_show_error('employment_status') ?></p></div>
                    </div>
                    <div>
                        <label for="signup-doc" class="block text-[10px] font-black uppercase tracking-widest text-text mb-1">Form Type</label>
                        <select name="doc_type" id="signup-doc" class="w-full bg-surface hover:bg-zinc-50 dark:hover:bg-zinc-800/50 border border-surface-border rounded-xl px-4 py-3 text-sm focus:border-accent outline-none text-text cursor-pointer font-bold"></select>
                        <div id="signup-doc-fixed" class="hidden w-full rounded-xl px-4 py-3 text-sm font-bold text-text bg-zinc-50 dark:bg-zinc-800/50"></div>
                        <p id="signup-doc-note" class="text-[10px] text-text-muted font-medium mt-1 pl-1"></p>
                        <div class="h-3 pl-1 mt-1"><p class="text-danger-500 text-[10px] font-bold uppercase tracking-wider"><?= validation_show_error('doc_type') ?></p></div>
                    </div>
                </div>
                <script>
                // Form types follow the employment status (the server checks the same rules).
                (function () {
                    const DOCS = <?= json_encode($asg['docsByStatus'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
                    const OLD = <?= json_encode(old('doc_type') ?: '', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
                    const status = document.getElementById('signup-status'), sel = document.getElementById('signup-doc');
                    const fixed = document.getElementById('signup-doc-fixed'), note = document.getElementById('signup-doc-note');
                    function update() {
                        const allowed = DOCS[status.value] || [];
                        sel.innerHTML = allowed.map(t => `<option value="${t}" ${t === OLD ? 'selected' : ''}>${t}</option>`).join('');
                        const one = allowed.length === 1, none = allowed.length === 0;
                        sel.classList.toggle('hidden', one || none);
                        fixed.classList.toggle('hidden', !(one || none));
                        fixed.textContent = one ? allowed[0] : 'None allowed';
                        note.textContent = one ? 'Set by your role and employment status.'
                            : (none ? 'No form type is allowed for this status yet. You can still sign up; the administrator will set it.' : 'Pick the form you will fill in.');
                    }
                    status.addEventListener('change', update);
                    update();
                })();
                </script>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <button type="submit" class="w-full mt-4 bg-accent text-accent-text font-black uppercase tracking-widest py-4 rounded-2xl shadow-lg shadow-accent/20 hover:bg-accent-hover active:scale-[0.98] transition-all cursor-pointer">
            Finalize Account Setup
        </button>
    <?= form_close() ?>
</main>
</div>



<?= $this->endSection() ?>