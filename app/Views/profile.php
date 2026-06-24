<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= view('components/header') ?>

<div class="p-8 max-w-4xl mx-auto flex flex-col gap-8 pb-20 h-[calc(100vh-6rem)] overflow-y-auto custom-scrollbar">
    
    <?= form_open('profile/general', ['class' => 'bg-surface border border-surface-border rounded-2xl shadow-sm overflow-hidden shrink-0']) ?>
        <div class="px-8 py-5 border-b border-surface-border bg-zinc-50 dark:bg-zinc-800/30">
            <h2 class="text-[11px] font-black uppercase tracking-widest text-text">General Information</h2>
        </div>
        
        <div class="p-8 flex flex-col gap-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">First Name</label>
                    <input type="text" name="first_name" value="<?= old('first_name', esc($user['first_name'])) ?>" required
                        class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold">
                    <div class="h-3 pl-1 mt-1">
                        <p class="text-red-500 text-[10px] font-bold uppercase tracking-wider"><?= validation_show_error('first_name') ?></p>
                    </div>
                </div>
                
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">Last Name</label>
                    <input type="text" name="last_name" value="<?= old('last_name', esc($user['last_name'])) ?>" required
                        class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold">
                    <div class="h-3 pl-1 mt-1">
                        <p class="text-red-500 text-[10px] font-bold uppercase tracking-wider"><?= validation_show_error('last_name') ?></p>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">Email Address</label>
                    <input type="email" name="email" value="<?= old('email', esc($user['email'])) ?>" required
                        class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold">
                    <div class="h-3 pl-1 mt-1">
                        <p class="text-red-500 text-[10px] font-bold uppercase tracking-wider"><?= validation_show_error('email') ?></p>
                    </div>
                </div>
            </div>
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
                        <button type="button" class="btn-remove hidden text-red-500 hover:text-red-700 transition-colors">
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

    <?= form_open('profile/password', ['class' => 'bg-surface border border-surface-border rounded-2xl shadow-sm overflow-hidden shrink-0']) ?>
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
                        <div class="h-3 pl-1 mt-1">
                            <p class="text-red-500 text-[10px] font-bold uppercase tracking-wider">
                                <?= session('errors.current_password') ?? validation_show_error('current_password') ?>
                            </p>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">New Password</label>
                        <input type="password" name="new_password" required
                            class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold">
                        <div class="h-3 pl-1 mt-1">
                            <p class="text-red-500 text-[10px] font-bold uppercase tracking-wider"><?= validation_show_error('new_password') ?></p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">Confirm Password</label>
                        <input type="password" name="confirm_password" required
                            class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold">
                        <div class="h-3 pl-1 mt-1">
                            <p class="text-red-500 text-[10px] font-bold uppercase tracking-wider"><?= validation_show_error('confirm_password') ?></p>
                        </div>
                    </div>
                </div>

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
                        removeBtn.onclick = function() {
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