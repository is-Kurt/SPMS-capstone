<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= view('components/header') ?>

<main class="flex min-h-[calc(100vh-6rem)] flex-col justify-center px-6 py-12 lg:px-8">
    <div class="mx-auto w-full max-w-md border border-zinc-200 dark:border-zinc-800 p-10 rounded-2xl bg-white dark:bg-zinc-900 shadow-xl shadow-zinc-200/50 dark:shadow-none">
        
        <h2 class="text-center text-3xl font-black tracking-tight text-text mb-2">New Password</h2>
        <p class="text-center text-sm font-medium text-text-muted mb-6">Enter your new password below. It must be at least 8 characters long.</p>

        <?= form_open('profile/password/step2', ['class' => 'space-y-4']) ?>
            <div>
                <label for="new_password" class="block text-sm font-bold text-text">New Password</label>
                <div class="mt-2">
                    <input id="new_password" type="password" name="new_password" required minlength="8"
                           class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text font-bold transition-all" />
                </div>
            </div>

            <div>
                <label for="confirm_password" class="block text-sm font-bold text-text">Confirm Password</label>
                <div class="mt-2">
                    <input id="confirm_password" type="password" name="confirm_password" required minlength="8"
                           class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text font-bold transition-all" />
                </div>
                <div class="min-h-3 pl-1">
                    <p class="text-danger-500 text-[10px] font-bold mt-1 uppercase tracking-wider"><?= session('error') ?? validation_show_error('new_password') ?></p>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-accent text-white font-bold py-3.5 rounded-xl cursor-pointer hover:bg-accent-hover transition-all text-sm shadow-lg shadow-accent/20 active:scale-[0.98]">
                    Update Password
                </button>
            </div>
            
            <div class="text-center mt-4 text-sm font-bold">
                <a href="<?= site_url('profile') ?>" class="text-text-muted hover:text-accent transition-colors">Cancel</a>
            </div>
        <?= form_close() ?>
    </div>
</main>
<?= $this->endSection() ?>
