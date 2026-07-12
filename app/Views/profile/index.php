<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= view('components/header') ?>

<div class="p-8 max-w-4xl mx-auto flex flex-col gap-8 pb-20 h-[calc(100vh-6rem)] overflow-y-auto custom-scrollbar">
    <div class="bg-surface border border-surface-border rounded-2xl shadow-sm overflow-hidden shrink-0 flex flex-col md:flex-row">
        
        <div class="p-8 flex flex-col items-center justify-center border-b md:border-b-0 md:border-r border-surface-border bg-zinc-50/50 dark:bg-zinc-800/10 min-w-[250px]">
            <div id="avatar-preview-wrap">
                <?php if (session('avatar_image')): ?>
                    <img id="avatar-preview-img" src="<?= base_url('uploads/avatars/' . session('avatar_image')) ?>" alt="Avatar" class="w-32 h-32 rounded-full object-cover shadow-lg border-4 border-white dark:border-zinc-800">
                <?php else: ?>
                    <div id="avatar-preview-initials" class="w-32 h-32 rounded-full flex items-center justify-center text-white text-5xl font-black shadow-lg border-4 border-white dark:border-zinc-800" style="background-color: <?= esc(session('avatar_color')) ?>;">
                        <?= esc(session('avatar_letter')) ?>
                    </div>
                <?php endif; ?>
            </div>
            <p class="mt-4 text-xs font-bold text-text-muted uppercase tracking-widest">Current Avatar</p>
        </div>

        <div class="p-8 flex-1 flex flex-col gap-6">

            <?= form_open_multipart('profile/updateAvatar', ['data-ajax' => 'update-avatar']) ?>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">Upload Custom Image</label>
                <div class="flex items-center gap-3">
                    <input type="file" name="avatar_file" accept="image/png, image/jpeg, image/webp" required
                           class="block w-full text-sm text-text-muted file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-accent/10 file:text-accent hover:file:bg-accent/20 transition-all cursor-pointer">
                    <button type="submit" class="bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 px-4 py-2.5 rounded-xl text-xs font-bold transition-all hover:scale-95 whitespace-nowrap">Upload</button>
                </div>
                <p class="field-error hidden text-danger-500 text-[10px] font-bold uppercase tracking-wider mt-1 pl-1" data-field="avatar_file"></p>
            <?= form_close() ?>

            <div class="h-px bg-surface-border w-full"></div>

            <?= form_open('profile/updateAvatar', ['class' => 'flex flex-col gap-4', 'data-ajax' => 'update-avatar']) ?>
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">Background Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="avatar_color" value="<?= esc(session('avatar_color')) ?>" class="h-10 w-16 p-0.5 rounded-lg border border-surface-border cursor-pointer">
                            <span class="text-xs text-text-muted font-mono"><?= esc(session('avatar_color')) ?></span>
                        </div>
                    </div>

                    <div class="flex-1">
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">Display Letter</label>
                        <input type="text" name="avatar_letter" value="<?= esc(session('avatar_letter')) ?>" maxlength="2" required
                               class="w-20 bg-zinc-50 dark:bg-zinc-800/50 border border-surface-border rounded-xl px-4 py-2 text-center font-bold text-text focus:border-accent outline-none">
                    </div>
                </div>

                <div class="flex items-center gap-3 mt-2">
                    <button type="submit" class="bg-accent text-white px-6 py-2.5 rounded-xl text-xs font-bold transition-all hover:scale-95 shadow-md shadow-accent/20">Save Initials</button>
                    <button type="submit" name="remove_image" value="1" id="btn-remove-avatar-image"
                            class="text-danger-500 hover:bg-danger-50 dark:hover:bg-danger-500/10 px-4 py-2.5 rounded-xl text-xs font-bold transition-all <?= session('avatar_image') ? '' : 'hidden' ?>">Remove Image</button>
                </div>
                <p class="field-error hidden text-danger-500 text-[10px] font-bold uppercase tracking-wider mt-1 pl-1" data-field="_general"></p>
            <?= form_close() ?>

        </div>
    </div>
    
    <?= form_open('profile/general', ['class' => 'bg-surface border border-surface-border rounded-2xl shadow-sm overflow-hidden shrink-0', 'data-ajax' => 'update-general']) ?>
        <div class="px-8 py-5 border-b border-surface-border bg-zinc-50 dark:bg-zinc-800/30">
            <h2 class="text-[11px] font-black uppercase tracking-widest text-text">General Information</h2>
        </div>

        <div class="p-8 flex flex-col gap-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">First Name</label>
                    <input type="text" name="first_name" value="<?= esc($user['first_name']) ?>" required
                        class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold">
                    <p class="field-error hidden text-danger-500 text-[10px] font-bold uppercase tracking-wider mt-1 pl-1" data-field="first_name"></p>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">Last Name</label>
                    <input type="text" name="last_name" value="<?= esc($user['last_name']) ?>" required
                        class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold">
                    <p class="field-error hidden text-danger-500 text-[10px] font-bold uppercase tracking-wider mt-1 pl-1" data-field="last_name"></p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">Email Address</label>
                    <input type="email" name="email" value="<?= esc($user['email']) ?>" required
                        class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold">
                    <p class="field-error hidden text-danger-500 text-[10px] font-bold uppercase tracking-wider mt-1 pl-1" data-field="email"></p>
                </div>
            </div>
            <p class="field-error hidden text-danger-500 text-[10px] font-bold uppercase tracking-wider mt-1 pl-1" data-field="_general"></p>
        </div>

        <div class="px-8 py-5 border-t border-surface-border bg-zinc-50 dark:bg-zinc-800/30">
            <h2 class="text-[11px] font-black uppercase tracking-widest text-text">Positions & Units</h2>
        </div>
        <div class="p-8 flex flex-col gap-6 bg-zinc-50/50 dark:bg-zinc-900/10">
            <div id="positions-container" class="flex flex-col gap-4">
                <?php foreach ($currentPlantilla as $index => $plantilla): ?>
                <div class="position-card border border-surface-border p-6 rounded-2xl bg-white dark:bg-zinc-900 shadow-sm relative group">
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-accent text-white flex items-center justify-center num-badge"><?= $index + 1 ?></span>
                            Designation
                        </div>
                        <!-- Trash icon (icon-only button): removes this extra position/designation card client-side. Hidden on the first card since at least one is required -->
                        <button type="button" class="btn-remove hidden text-danger-500 hover:text-danger-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                        <div class="col-span-1">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-text mb-1">Department / Unit</label>
                            <div class="relative">
                                <select name="units[]" required class="appearance-none w-full bg-zinc-50 dark:bg-zinc-800/50 border border-surface-border rounded-xl pl-4 pr-10 py-3 text-sm focus:border-accent focus:ring-1 focus:outline-none text-text cursor-pointer font-bold">
                                    <option value="" disabled <?= empty($plantilla['unit_id']) ? 'selected' : '' ?>>Select Unit...</option>
                                    <?php foreach($units as $unit): ?>
                                        <option value="<?= $unit['id'] ?>" <?= (isset($plantilla['unit_id']) && $plantilla['unit_id'] == $unit['id']) ? 'selected' : '' ?>><?= esc($unit['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                    <svg class="h-4 w-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                            </div>
                        </div>

                        <div class="col-span-1">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-text mb-1">Official Position</label>
                            <div class="relative">
                                <select name="positions[]" required class="appearance-none w-full bg-zinc-50 dark:bg-zinc-800/50 border border-surface-border rounded-xl pl-4 pr-10 py-3 text-sm focus:border-accent focus:ring-1 focus:outline-none text-text cursor-pointer font-bold">
                                    <option value="" disabled <?= empty($plantilla['position_id']) ? 'selected' : '' ?>>Select Position...</option>
                                    <?php foreach($positions as $pos): ?>
                                        <option value="<?= $pos['id'] ?>" <?= (isset($plantilla['position_id']) && $plantilla['position_id'] == $pos['id']) ? 'selected' : '' ?>><?= esc($pos['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                    <svg class="h-4 w-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <button type="button" id="btn-add-position" class="w-full py-4 border-2 border-dashed border-surface-border rounded-2xl text-sm font-bold text-text-muted hover:text-accent hover:border-accent hover:bg-accent/5 transition-all flex items-center justify-center gap-2 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                Add Additional Position / Designation
            </button>
        </div>

        <div class="px-8 py-4 bg-zinc-50 dark:bg-zinc-800/30 border-t border-surface-border flex justify-end">
            <button type="submit" class="bg-accent hover:bg-accent-hover text-white text-xs font-bold py-2.5 px-6 rounded-lg shadow-lg shadow-accent/20 transition-all active:scale-[0.98] cursor-pointer">
                Save Profile
            </button>
        </div>
    <?= form_close() ?>

    <?= form_open('profile/password', ['class' => 'bg-surface border border-surface-border rounded-2xl shadow-sm overflow-hidden shrink-0', 'data-ajax' => 'update-password']) ?>
        <div class="px-8 py-5 border-b border-surface-border bg-zinc-50 dark:bg-zinc-800/30">
            <h2 class="text-[11px] font-black uppercase tracking-widest text-text">Security & Authentication</h2>
        </div>

        <div class="p-8 flex flex-col gap-8">
            <div class="flex flex-col gap-6">
                <div>
                    <h3 class="text-sm font-bold text-text">Change Password</h3>
                    <p class="text-xs text-text-muted mt-1 font-medium italic">Ensure your account is using a long, random password to stay secure.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">Current Password</label>
                        <input type="password" name="current_password" required
                            class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold">
                        <p class="field-error hidden text-danger-500 text-[10px] font-bold uppercase tracking-wider mt-1 pl-1" data-field="current_password"></p>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">New Password</label>
                        <input type="password" name="new_password" required minlength="8"
                            class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold">
                        <p class="field-error hidden text-danger-500 text-[10px] font-bold uppercase tracking-wider mt-1 pl-1" data-field="new_password"></p>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">Confirm Password</label>
                        <input type="password" name="confirm_password" required minlength="8"
                            class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold">
                        <p class="field-error hidden text-danger-500 text-[10px] font-bold uppercase tracking-wider mt-1 pl-1" data-field="confirm_password"></p>
                    </div>
                </div>
                <p class="field-error hidden text-danger-500 text-[10px] font-bold uppercase tracking-wider mt-1 pl-1" data-field="_general"></p>

                <div class="flex justify-start mt-2">
                    <button type="submit" class="bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 hover:opacity-90 text-xs font-bold py-2.5 px-6 rounded-lg transition-all active:scale-[0.98] cursor-pointer">
                        Update Password
                    </button>
                </div>
            </div>
        </div>
    <?= form_close() ?>

</div>

<script>
    // ==========================================
    // AJAX FORM HANDLING (no more full-page reloads for profile actions)
    // Registered synchronously (not inside DOMContentLoaded) so this listener
    // attaches before confirmModal.js's global data-confirm auto-submit
    // listener does - stopImmediatePropagation() below then keeps that older
    // listener from ever seeing these same submits.
    // ==========================================
    document.addEventListener('submit', async (e) => {
        const form = e.target;
        const action = form.dataset.ajax;
        if (!action) return;

        e.preventDefault();
        e.stopImmediatePropagation();

        clearFormErrors(form);

        // Pass the submitter so a clicked button's name/value (e.g. remove_image=1)
        // is included - FormData(form) alone omits button data entirely.
        const formData = new FormData(form, e.submitter);
        const submitBtn = e.submitter;
        if (submitBtn) submitBtn.disabled = true;

        apiPost(form.getAttribute('action'), formData, {
            onSuccess: (data) => {
                handleProfileAjaxSuccess(action, form, data);
                if (submitBtn) submitBtn.disabled = false;
            },
            onError: (errMsg, data) => {
                showFormErrors(form, errMsg, data && data.errors);
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    });

    // Success is confirmed via appAlert(); errors are shown inline next to the
    // relevant field instead, keyed by the server's {errors: {field: message}}.
    // Falls back to a form-level "_general" slot, and to appAlert() as a last
    // resort if a form has no matching slot at all, so an error can never be
    // silently dropped.
    function clearFormErrors(form) {
        form.querySelectorAll('.field-error').forEach(el => {
            el.textContent = '';
            el.classList.add('hidden');
        });
    }

    function showFormErrors(form, message, errors) {
        clearFormErrors(form);

        let shown = false;
        if (errors) {
            for (const [field, msg] of Object.entries(errors)) {
                const slot = form.querySelector(`.field-error[data-field="${field}"]`);
                if (slot) {
                    slot.textContent = msg;
                    slot.classList.remove('hidden');
                    shown = true;
                }
            }
        }

        if (!shown) {
            const fallback = form.querySelector('.field-error[data-field="_general"]');
            if (fallback) {
                fallback.textContent = message || 'Something went wrong.';
                fallback.classList.remove('hidden');
            } else {
                window.appAlert(message || 'Something went wrong.', { variant: 'danger' });
            }
        }
    }

    function updateAvatarPreview(avatar) {
        const wrap = document.getElementById('avatar-preview-wrap');
        if (wrap) {
            if (avatar.image) {
                wrap.innerHTML = `<img id="avatar-preview-img" src="${avatar.image}" alt="Avatar" class="w-32 h-32 rounded-full object-cover shadow-lg border-4 border-white dark:border-zinc-800">`;
            } else {
                wrap.innerHTML = `<div id="avatar-preview-initials" class="w-32 h-32 rounded-full flex items-center justify-center text-white text-5xl font-black shadow-lg border-4 border-white dark:border-zinc-800" style="background-color: ${avatar.color};">${escapeHtml(avatar.letter || '')}</div>`;
            }
        }

        const removeBtn = document.getElementById('btn-remove-avatar-image');
        if (removeBtn) removeBtn.classList.toggle('hidden', !avatar.image);
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function handleProfileAjaxSuccess(action, form, data) {
        switch (action) {
            case 'update-general': {
                window.appAlert(data.message, { title: 'Profile Updated', variant: 'info' });
                break;
            }

            case 'update-password': {
                form.reset();
                window.appAlert(data.message, { title: 'Password Updated', variant: 'info' });
                break;
            }

            case 'update-avatar': {
                if (data.avatar) updateAvatarPreview(data.avatar);

                // Reset the "remove image" trigger and file input so a re-submit doesn't resend stale data.
                const fileInput = form.querySelector('input[name="avatar_file"]');
                if (fileInput) fileInput.value = '';

                window.appAlert(data.message, { title: 'Avatar Updated', variant: 'info' });
                break;
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {

        // --- DYNAMIC POSITION CARDS LOGIC ---
        const container = document.getElementById('positions-container');
        if(container) {
            const btnAdd = document.getElementById('btn-add-position');

            function updateCardUI() {
                const cards = container.querySelectorAll('.position-card');
                cards.forEach((card, index) => {
                    // Update badge numbers (starting at 1)
                    card.querySelector('.num-badge').textContent = index + 1;
                    
                    // Show remove button on all EXCEPT the first card
                    const removeBtn = card.querySelector('.btn-remove');
                    if (index === 0) {
                        removeBtn.classList.add('hidden');
                    } else {
                        removeBtn.classList.remove('hidden');
                        removeBtn.onclick = async function() {
                            const ok = await window.appConfirm('Remove this position/designation?', {
                                title: 'Remove Position',
                                confirmText: 'Remove'
                            });
                            if (!ok) return;
                            card.remove();
                            updateCardUI();
                        };
                    }
                });
            }

            btnAdd.addEventListener('click', () => {
                const firstCard = container.querySelector('.position-card');
                const clone = firstCard.cloneNode(true);
                
                // Reset values for the new clone
                clone.querySelectorAll('select').forEach(select => select.value = "");
                
                container.appendChild(clone);
                updateCardUI();
            });

            updateCardUI();
        }

        // --- 2FA TOGGLE LOGIC (If applicable) ---
        const toggleBtn = document.getElementById('toggle-2fa');
        if(toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                const isEnabled = this.getAttribute('aria-checked') === 'true';
                this.setAttribute('aria-checked', !isEnabled);
                
                const circle = document.getElementById('toggle-circle');
                
                if (!isEnabled) {
                    this.classList.remove('bg-zinc-300', 'dark:bg-zinc-700');
                    this.classList.add('bg-accent');
                    circle.classList.remove('translate-x-1');
                    circle.classList.add('translate-x-6');
                } else {
                    this.classList.add('bg-zinc-300', 'dark:bg-zinc-700');
                    this.classList.remove('bg-accent');
                    circle.classList.remove('translate-x-6');
                    circle.classList.add('translate-x-1');
                }
            });
        }
    });
</script>

<?= $this->endSection() ?>