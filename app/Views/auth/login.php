<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<main class="flex min-h-screen flex-col justify-center px-6 py-12 lg:px-8 bg-gray-50 dark:bg-zinc-950">
    <div class="mx-auto w-full max-w-md border border-zinc-200 dark:border-zinc-800 p-10 rounded-2xl bg-white dark:bg-zinc-900 shadow-xl shadow-zinc-200/50 dark:shadow-none">

        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <h2 class="text-center text-3xl font-black tracking-tight text-text">Log in</h2>
        </div>

        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
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