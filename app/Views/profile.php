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
    document.getElementById('toggle-2fa').addEventListener('click', function() {
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
</script>

<?= $this->endSection() ?>