<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<main class="flex min-h-screen flex-col justify-center px-6 py-12 lg:px-8 bg-gray-50 dark:bg-zinc-950">
    <div class="mx-auto w-full max-w-md border border-zinc-200 dark:border-zinc-800 p-10 rounded-2xl bg-white dark:bg-zinc-900 shadow-xl shadow-zinc-200/50 dark:shadow-none">

        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <h2 class="text-center text-3xl font-black tracking-tight text-text">Log in</h2>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-sm">
            
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
            <?= form_open('login', ['class' => 'space-y-3']) ?>
                
                <div>
                    <label for="email" class="block text-sm font-bold text-text">Email address</label>
                    <div class="mt-2">
                        <input id="email" type="text" name="email" value="<?= old('email') ?>" 
                               class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all" />
                    </div>
                    <div class="h-3 pl-1">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-wider"><?= validation_show_error('email') ?></p>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-sm font-bold text-text">Password</label>
                    </div>
                    <div class="mt-2">
                        <input id="password" type="password" name="password" 
                               class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all" />
                    </div>
                    <div class="h-3 pl-1">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-wider"><?= session('errors.error') ?? validation_show_error('password') ?></p>
                    </div>
                </div>

                <div class="flex flex-col gap-3 mt-5 sm:flex-row sm:items-center sm:justify-between sm:gap-0">
                    <div class="flex items-center gap-2">
                        <input id="remember-me" name="remember-me" type="checkbox" class="checkbox" />
                        <label for="remember-me" class="block text-sm font-medium text-text-muted cursor-pointer">
                            Remember me
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="<?= site_url('password/forgot') ?>" class="font-bold text-accent hover:text-accent-hover transition-colors">
                            Forgot password?
                        </a>
                    </div>
                </div>

                <div class="mt-8">
                    <button type="submit" class="w-full bg-accent text-white font-bold py-3.5 rounded-xl cursor-pointer hover:bg-accent-hover transition-all text-sm shadow-lg shadow-accent/20 active:scale-[0.98]">
                        Log in
                    </button>
                </div>

            <?= form_close() ?>
        </div>

    </div>
</main>

<?= $this->endSection() ?>