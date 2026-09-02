<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= view('components/header') ?>

<main class="flex min-h-[calc(100vh-6rem)] flex-col justify-center px-6 py-12 lg:px-8">
    <div class="mx-auto w-full max-w-md border border-zinc-200 dark:border-zinc-800 p-10 rounded-2xl bg-white dark:bg-zinc-900 shadow-xl shadow-zinc-200/50 dark:shadow-none">
        
        <h2 class="text-center text-3xl font-black tracking-tight text-text mb-2">New Email Address</h2>
        <p class="text-center text-sm font-medium text-text-muted mb-6">Enter the new email address you'd like to use. We will send a verification code to this new address.</p>

        <?= form_open('profile/email/step2', ['class' => 'space-y-4']) ?>
            <div>
                <label for="new_email" class="block text-sm font-bold text-text">New Email address</label>
                <div class="mt-2">
                    <input id="new_email" type="email" name="new_email" value="<?= old('new_email') ?>" required
                           class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text font-bold transition-all" />
                </div>
                <div class="min-h-3 pl-1">
                    <p class="text-danger-500 text-[10px] font-bold mt-1 uppercase tracking-wider"><?= session('error') ?? validation_show_error('new_email') ?></p>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-accent text-white font-bold py-3.5 rounded-xl cursor-pointer hover:bg-accent-hover transition-all text-sm shadow-lg shadow-accent/20 active:scale-[0.98]">
                    Send Verification Code
                </button>
            </div>
            
            <div class="text-center mt-4 text-sm font-bold">
                <a href="<?= site_url('profile') ?>" class="text-text-muted hover:text-accent transition-colors">Cancel</a>
            </div>
        <?= form_close() ?>
    </div>
</main>
<?= $this->endSection() ?>
