<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<main class="flex min-h-screen flex-col justify-center px-6 py-12 lg:px-8">
    <div class="mx-auto w-full max-w-md border border-main-border p-10 rounded-xl bg-card-bg">

        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <h2 class="text-center text-2xl/9 font-bold tracking-tight">Log in</h2>
        </div>

        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
            <?= form_open('login', ['class' => 'space-y-3']) ?>
                
                <div>
                    <label for="email" class="block text-sm/6 font-medium">Email address</label>
                    <div class="mt-2">
                        <input id="email" type="text" name="email" value="<?= old('email') ?>" class="input-field" />
                    </div>
                    <div class="h-3 pl-1">
                        <p class="text-red-500 text-xs mt-1"><?= validation_show_error('email') ?></p>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-sm/6 font-medium">Password</label>
                    </div>
                    <div class="mt-2">
                        <input id="password" type="password" name="password" class="input-field" />
                    </div>
                    <div class="h-3 pl-1">
                        <p class="text-red-500 text-xs mt-1"><?= session('errors.password') ?? validation_show_error('password') ?></p>
                    </div>
                </div>

                <div class="flex flex-col gap-3 mt-4 sm:flex-row sm:items-center sm:justify-between sm:gap-0">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" class="checkbox" />
                        <label for="remember-me" class="ml-2 block text-sm font-medium cursor-pointer">
                            Remember me
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="#" class="font-semibold text-highlight hover:text-highlight-hover">
                            Forgot password?
                        </a>
                    </div>
                </div>

                <div class="px-10 mt-6">
                    <button type="submit" class="button w-full">Log in</button>
                </div>

            <?= form_close() ?>

            <p class="mt-8 text-center text-sm/6 text-sub-text">
                Don't have an account?
                <a href="signup" class="font-semibold text-highlight hover:text-highlight-hover">Sign up</a>
            </p>
        </div>

    </div>
</main>

<?= $this->endSection() ?>