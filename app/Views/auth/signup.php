
<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<main class="flex min-h-screen flex-col justify-center px-6 py-12 lg:px-8">
    <div class="mx-auto w-full max-w-md border border-main-border p-10 rounded-xl bg-card-bg shadow-2xl">

        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <h2 class="text-center text-2xl/9 font-bold tracking-tight">Sign up</h2>
        </div>

        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
            <?= form_open('signup', ['class' => 'space-y-3']) ?>

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
                    <label for="password" class="block text-sm/6 font-medium">Password</label>
                    <div class="mt-2">
                        <input id="password" type="password" name="password" class="input-field" />
                    </div>
                    <div class="h-3 pl-1">
                        <p class="text-red-500 text-xs mt-1"><?= validation_show_error('password') ?></p>
                    </div>
                </div>

                <div>
                    <label for="confirm-password" class="block text-sm/6 font-medium">Confirm Password</label>
                    <div class="mt-2">
                        <input id="confirm-password" type="password" name="confirm-password" class="input-field" />
                    </div>
                    <div class="h-3 pl-1">
                        <p class="text-red-500 text-xs mt-1"><?= validation_show_error('confirm-password') ?></p>
                    </div>
                </div>
                
                <div class="px-10 mt-6">
                    <button type="submit" class="button w-full">Sign up</button>
                </div>

            <?= form_close() ?>

            <p class="mt-5 text-center text-sm/6 text-sub-text">
                Already have an account? 
                <a href="login" class="font-semibold text-highlight hover:text-highlight-hover">Log in</a>
            </p>
        </div>

    </div>
</main>

<?= $this->endSection() ?>