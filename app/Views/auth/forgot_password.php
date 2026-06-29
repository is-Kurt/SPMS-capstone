<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<main class="flex min-h-screen flex-col justify-center px-6 py-12 lg:px-8 bg-gray-50 dark:bg-zinc-950">
    <div class="mx-auto w-full max-w-md border border-zinc-200 dark:border-zinc-800 p-10 rounded-2xl bg-white dark:bg-zinc-900 shadow-xl shadow-zinc-200/50 dark:shadow-none">
        <h2 class="text-center text-3xl font-black tracking-tight text-text mb-2">Forgot Password</h2>
        <p class="text-center text-sm font-medium text-text-muted mb-8">Enter your email address and we'll send you a 6-digit code to reset your password.</p>

        <?= form_open('password/send', ['class' => 'space-y-4']) ?>
            <div>
                <label for="email" class="block text-sm font-bold text-text">Email address</label>
                <div class="mt-2">
                    <input id="email" type="email" name="email" required
                           class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all" />
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-accent text-white font-bold py-3.5 rounded-xl cursor-pointer hover:bg-accent-hover transition-all text-sm shadow-lg shadow-accent/20 active:scale-[0.98]">
                    Send Reset Code
                </button>
            </div>
            
            <div class="text-center mt-4 text-sm font-bold">
                <a href="<?= site_url('login') ?>" class="text-text-muted hover:text-accent transition-colors">Back to Login</a>
            </div>
        <?= form_close() ?>
    </div>
</main>
<?= $this->endSection() ?>