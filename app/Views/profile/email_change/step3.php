<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= view('components/header') ?>

<main class="flex min-h-[calc(100vh-6rem)] flex-col justify-center px-6 py-12 lg:px-8">
    <div class="mx-auto w-full max-w-md border border-zinc-200 dark:border-zinc-800 p-10 rounded-2xl bg-white dark:bg-zinc-900 shadow-xl shadow-zinc-200/50 dark:shadow-none">
        
        <h2 class="text-center text-3xl font-black tracking-tight text-text mb-2">Verify New Email</h2>
        <p class="text-center text-sm font-medium text-text-muted mb-6">Enter the 6-digit code we sent to your new email address to confirm the change.</p>

        <?= form_open('profile/email/step3', ['class' => 'space-y-4']) ?>
            <div>
                <label for="code" class="block text-sm font-bold text-text text-center">Verification Code</label>
                <div class="mt-2 text-center">
                    <input id="code" type="text" name="code" value="<?= old('code') ?>" required maxlength="6"
                           class="w-48 mx-auto text-center tracking-[0.5em] text-2xl bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 font-bold focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all" />
                </div>
                <div class="min-h-3 text-center pl-1">
                    <p class="text-danger-500 text-[10px] font-bold mt-1 uppercase tracking-wider"><?= session('error') ?? validation_show_error('code') ?></p>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-accent text-white font-bold py-3.5 rounded-xl cursor-pointer hover:bg-accent-hover transition-all text-sm shadow-lg shadow-accent/20 active:scale-[0.98]">
                    Complete Email Change
                </button>
            </div>
            
            <div class="text-center mt-4 text-sm font-bold">
                <a href="<?= site_url('profile') ?>" class="text-text-muted hover:text-accent transition-colors">Cancel</a>
            </div>
        <?= form_close() ?>

        <div class="text-center mt-6">
            <p class="text-xs text-text-muted mb-2">Didn't receive the code?</p>
            <?= form_open('profile/email/step2', ['class' => 'inline']) ?>
                <input type="hidden" name="new_email" value="<?= esc($new_email ?? '') ?>">
                <button type="submit" id="btn-resend" <?= $timeLeft > 0 ? 'disabled' : '' ?> class="text-sm font-bold <?= $timeLeft > 0 ? 'text-text-muted' : 'text-accent hover:text-accent-hover' ?> transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    <?= $timeLeft > 0 ? "Resend Code ({$timeLeft}s)" : 'Resend Code Now' ?>
                </button>
            <?= form_close() ?>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('btn-resend');
    if (!btn) return;
    
    let timeLeft = <?= esc($timeLeft ?? 0) ?>;
    
    if (timeLeft <= 0) return;
    
    const tick = setInterval(() => {
        timeLeft--;
        if (timeLeft <= 0) {
            clearInterval(tick);
            btn.disabled = false;
            btn.textContent = 'Resend Code Now';
            btn.classList.remove('text-text-muted');
            btn.classList.add('text-accent', 'hover:text-accent-hover');
        } else {
            btn.textContent = `Resend Code (${timeLeft}s)`;
        }
    }, 1000);
});
</script>
<?= $this->endSection() ?>
