<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<main class="flex min-h-screen flex-col justify-center px-6 py-12 lg:px-8 bg-gray-50 dark:bg-zinc-950">
    <div class="mx-auto w-full max-w-md border border-zinc-200 dark:border-zinc-800 p-10 rounded-2xl bg-white dark:bg-zinc-900 shadow-xl shadow-zinc-200/50 dark:shadow-none">

        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <h2 class="text-center text-2xl font-black tracking-tight text-text">
                <?= $targetEmail ? 'This link is for a different account' : "You don't have access to this with your current account" ?>
            </h2>
            <p class="mt-3 text-center text-sm text-text-muted">
                <?php if ($targetEmail): ?>
                    You're signed in as <span class="font-bold text-text"><?= esc($currentEmail) ?></span>, but this link was sent to <span class="font-bold text-text"><?= esc($targetEmail) ?></span>.
                <?php else: ?>
                    You're signed in as <span class="font-bold text-text"><?= esc($currentEmail) ?></span>. If this was sent to a different account, log out and sign in as that account instead.
                <?php endif; ?>
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-sm flex flex-col gap-3">
            <?= form_open('account-mismatch/switch') ?>
                <?php if ($targetEmail): ?>
                    <input type="hidden" name="email" value="<?= esc($targetEmail) ?>">
                    <button type="submit" class="w-full flex flex-col items-center gap-0.5 bg-accent text-white px-5 py-3 rounded-xl cursor-pointer hover:bg-accent-hover transition-all shadow-lg shadow-accent/20 active:scale-[0.98]">
                        <span class="text-xs font-semibold text-white/80">Log out and continue as</span>
                        <span class="text-sm font-bold break-all text-center leading-snug"><?= esc($targetEmail) ?></span>
                    </button>
                <?php else: ?>
                    <button type="submit" class="w-full bg-accent text-white px-5 py-3.5 rounded-xl cursor-pointer hover:bg-accent-hover transition-all shadow-lg shadow-accent/20 active:scale-[0.98] text-sm font-bold">
                        Log out
                    </button>
                <?php endif; ?>
            <?= form_close() ?>

            <a href="<?= site_url('folders') ?>" class="w-full text-center bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 text-text font-bold py-3.5 rounded-xl hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all text-sm">
                Continue to my own Folders
            </a>
        </div>

    </div>
</main>

<?= $this->endSection() ?>
