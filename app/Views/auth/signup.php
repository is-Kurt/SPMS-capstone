<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<main class="flex min-h-screen flex-col justify-center px-6 py-12 lg:px-8 bg-gray-50 dark:bg-zinc-950">
    <div class="mx-auto w-full max-w-xl border border-zinc-200 dark:border-zinc-800 p-10 rounded-2xl bg-white dark:bg-zinc-900 shadow-xl shadow-zinc-200/50 dark:shadow-none">

        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <h2 class="text-center text-3xl font-black tracking-tight text-text">Sign up</h2>
        </div>

        <div class="mt-10 w-full">
            <?= form_open('signup', ['class' => 'grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-4']) ?>

                <div class="col-span-1">
                    <label for="first_name" class="block text-sm font-bold text-text">First Name</label>
                    <div class="mt-2">
                        <input id="first_name" type="text" name="first_name" value="<?= old('first_name') ?>" 
                               class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all" />
                    </div>
                    <div class="h-3 pl-1">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-wider"><?= validation_show_error('first_name') ?></p>
                    </div>
                </div>

                <div class="col-span-1">
                    <label for="last_name" class="block text-sm font-bold text-text">Last Name</label>
                    <div class="mt-2">
                        <input id="last_name" type="text" name="last_name" value="<?= old('last_name') ?>" 
                               class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all" />
                    </div>
                    <div class="h-3 pl-1">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-wider"><?= validation_show_error('last_name') ?></p>
                    </div>
                </div>

                <div class="col-span-1 sm:col-span-2">
                    <label for="email" class="block text-sm font-bold text-text">Email address</label>
                    <div class="mt-2">
                        <input id="email" type="text" name="email" value="<?= old('email') ?>" 
                               class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all" />
                    </div>
                    <div class="h-3 pl-1">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-wider"><?= validation_show_error('email') ?></p>
                    </div>
                </div>

                <div class="col-span-1">
                    <label for="password" class="block text-sm font-bold text-text">Password</label>
                    <div class="mt-2">
                        <input id="password" type="password" name="password" 
                               class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all" />
                    </div>
                    <div class="h-3 pl-1">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-wider"><?= validation_show_error('password') ?></p>
                    </div>
                </div>

                <div class="col-span-1">
                    <label for="confirm-password" class="block text-sm font-bold text-text">Confirm Password</label>
                    <div class="mt-2">
                        <input id="confirm-password" type="password" name="confirm-password" 
                               class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all" />
                    </div>
                    <div class="h-3 pl-1">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-wider"><?= validation_show_error('confirm-password') ?></p>
                    </div>
                </div>

                <div class="col-span-1">
                    <label for="department" class="block text-sm font-bold text-text">Department</label>
                    <div class="relative mt-2">
                        <select id="department" name="department" 
                                class="appearance-none w-full bg-input border border-transparent dark:border-zinc-800 rounded-xl pl-4 pr-10 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all cursor-pointer">
                            <option class="bg-input text-text" value="" disabled <?= empty(old('department')) ? 'selected' : '' ?>>Select Department</option>
                            <option class="bg-input text-text" value="HR" <?= old('department') === 'HR' ? 'selected' : '' ?>>Human Resources (HR)</option>
                            <option class="bg-input text-text" value="ICT" <?= old('department') === 'ICT' ? 'selected' : '' ?>>Information & Comms Tech (ICT)</option>
                            <option class="bg-input text-text" value="Academics" <?= old('department') === 'Academics' ? 'selected' : '' ?>>Academics</option>
                            <option class="bg-input text-text" value="Finance" <?= old('department') === 'Finance' ? 'selected' : '' ?>>Finance & Accounting</option>
                            <option class="bg-input text-text" value="Administration" <?= old('department') === 'Administration' ? 'selected' : '' ?>>Administration</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                            <svg class="h-4 w-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                    <div class="h-3 pl-1">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-wider"><?= validation_show_error('department') ?></p>
                    </div>
                </div>

                <div class="col-span-1">
                    <label for="position" class="block text-sm font-bold text-text">Position</label>
                    <div class="relative mt-2">
                        <select id="position" name="position" 
                                class="appearance-none w-full bg-input border border-transparent dark:border-zinc-800 rounded-xl pl-4 pr-10 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all cursor-pointer">
                            <option class="bg-input text-text" value="" disabled <?= empty(old('position')) ? 'selected' : '' ?>>Select Position</option>
                            <option class="bg-input text-text" value="Instructor 1" <?= old('position') === 'Instructor 1' ? 'selected' : '' ?>>Instructor 1</option>
                            <option class="bg-input text-text" value="Instructor 2" <?= old('position') === 'Instructor 2' ? 'selected' : '' ?>>Instructor 2</option>
                            <option class="bg-input text-text" value="Instructor 3" <?= old('position') === 'Instructor 3' ? 'selected' : '' ?>>Instructor 3</option>
                            <option class="bg-input text-text" value="Assistant Professor" <?= old('position') === 'Assistant Professor' ? 'selected' : '' ?>>Assistant Professor</option>
                            <option class="bg-input text-text" value="Associate Professor" <?= old('position') === 'Associate Professor' ? 'selected' : '' ?>>Associate Professor</option>
                            <option class="bg-input text-text" value="Staff" <?= old('position') === 'Staff' ? 'selected' : '' ?>>Support Staff</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                            <svg class="h-4 w-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                    <div class="h-3 pl-1">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-wider"><?= validation_show_error('position') ?></p>
                    </div>
                </div>
                
                <div class="col-span-1 sm:col-span-2 mt-4">
                    <button type="submit" class="w-full bg-accent text-white font-bold py-3.5 rounded-xl cursor-pointer hover:bg-accent-hover transition-all text-sm shadow-lg shadow-accent/20 active:scale-[0.98]">
                        Sign up
                    </button>
                </div>

            <?= form_close() ?>

            <p class="mt-8 text-center text-sm text-text-muted font-medium">
                Already have an account? 
                <a href="login" class="font-bold text-accent hover:text-accent-hover transition-colors">Log in</a>
            </p>
        </div>

    </div>
</main>

<?= $this->endSection() ?>