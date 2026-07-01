<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<main class="flex min-h-screen flex-col items-center justify-center px-4 py-12 sm:px-6 lg:px-8 bg-zinc-50 dark:bg-zinc-950">
    
    <div class="w-full max-w-2xl mb-6 text-center">
        <h2 class="text-3xl font-black tracking-tight text-text">Account Setup</h2>
        <p class="text-text-muted font-medium mt-2">Complete your profile to access the system.</p>
    </div>

    <div class="w-full max-w-2xl">
        <?php if (session()->has('success')): ?>
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 flex items-start gap-3">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <p class="text-sm font-bold text-emerald-800 dark:text-emerald-400 leading-tight"><?= session('success') ?></p>
            </div>
        <?php endif; ?>

        <?php if (session()->has('error')): ?>
            <div class="mb-6 p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <p class="text-sm font-bold text-red-800 dark:text-red-400 leading-tight"><?= session('error') ?></p>
            </div>
        <?php endif; ?>
    </div>
    <?= form_open('signup', ['class' => 'w-full max-w-2xl flex flex-col gap-6']) ?>
        <input type="hidden" name="token" value="<?= esc($invitation['token']) ?>">

        <div class="border border-surface-border p-8 rounded-2xl bg-white dark:bg-zinc-900 shadow-sm">
            <h3 class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-6 flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-accent text-white flex items-center justify-center">1</span>
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
                        <p class="text-red-500 text-[10px] font-bold uppercase tracking-wider"><?= validation_show_error('first_name') ?></p>
                    </div>
                </div>

                <div class="col-span-1">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text mb-1">Last Name</label>
                    <input type="text" name="last_name" value="<?= old('last_name') ?>" required
                           class="w-full bg-zinc-50 dark:bg-zinc-950 border border-surface-border rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:outline-none text-text transition-all" />
                    <div class="h-3 pl-1 mt-1">
                        <p class="text-red-500 text-[10px] font-bold uppercase tracking-wider"><?= validation_show_error('last_name') ?></p>
                    </div>
                </div>

                <div class="col-span-1">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text mb-1">Password</label>
                    <input type="password" name="password" required minlength="8"
                           class="w-full bg-zinc-50 dark:bg-zinc-950 border border-surface-border rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:outline-none text-text transition-all" />
                    <div class="h-3 pl-1 mt-1">
                        <p class="text-red-500 text-[10px] font-bold uppercase tracking-wider"><?= validation_show_error('password') ?></p>
                    </div>
                </div>

                <div class="col-span-1">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text mb-1">Confirm Password</label>
                    <input type="password" name="confirm-password" required
                           class="w-full bg-zinc-50 dark:bg-zinc-950 border border-surface-border rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:outline-none text-text transition-all" />
                    <div class="h-3 pl-1 mt-1">
                        <p class="text-red-500 text-[10px] font-bold uppercase tracking-wider"><?= validation_show_error('confirm-password') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div id="positions-container" class="flex flex-col gap-4">
            
            <div class="position-card border border-surface-border p-8 rounded-2xl bg-white dark:bg-zinc-900 shadow-sm relative group">
                <h3 class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-6 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-accent text-white flex items-center justify-center num-badge">2</span>
                        Position & Unit
                    </div>
                    <button type="button" class="btn-remove hidden text-red-500 hover:text-red-700 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                    <div class="col-span-1">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-text mb-1">Department / Unit</label>
                        <div class="relative">
                            <select name="units[]" required class="appearance-none w-full bg-zinc-50 dark:bg-zinc-950 border border-surface-border rounded-xl pl-4 pr-10 py-3 text-sm focus:border-accent focus:ring-1 focus:outline-none text-text cursor-pointer">
                                <option value="" disabled selected>Select Unit...</option>
                                <?php foreach($units as $unit): ?>
                                    <option value="<?= $unit['id'] ?>"><?= esc($unit['name']) ?></option>
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
                            <select name="positions[]" required class="appearance-none w-full bg-zinc-50 dark:bg-zinc-950 border border-surface-border rounded-xl pl-4 pr-10 py-3 text-sm focus:border-accent focus:ring-1 focus:outline-none text-text cursor-pointer">
                                <option value="" disabled selected>Select Position...</option>
                                <?php foreach($positions as $pos): ?>
                                    <option value="<?= $pos['id'] ?>"><?= esc($pos['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                <svg class="h-4 w-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button type="button" id="btn-add-position" class="w-full py-4 border-2 border-dashed border-surface-border rounded-2xl text-sm font-bold text-text-muted hover:text-accent hover:border-accent hover:bg-accent/5 transition-all flex items-center justify-center gap-2 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
            Add Additional Position / Designation
        </button>

        <button type="submit" class="w-full mt-4 bg-accent text-white font-black uppercase tracking-widest py-4 rounded-2xl shadow-lg shadow-accent/20 hover:bg-accent-hover active:scale-[0.98] transition-all cursor-pointer">
            Finalize Account Setup
        </button>
    <?= form_close() ?>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('positions-container');
        const btnAdd = document.getElementById('btn-add-position');

        function updateCardUI() {
            const cards = container.querySelectorAll('.position-card');
            cards.forEach((card, index) => {
                // Update badge numbers (starting at 2 because Basic Info is 1)
                card.querySelector('.num-badge').textContent = index + 2;
                
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
            
            // Reset values
            clone.querySelectorAll('select').forEach(select => select.value = "");
            
            container.appendChild(clone);
            updateCardUI();
        });

        // Initialize UI
        updateCardUI();
    });
</script>

<?= $this->endSection() ?>