<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<main class="flex min-h-screen flex-col justify-center px-6 py-12 lg:px-8 bg-gray-50 dark:bg-zinc-950">
    <div class="mx-auto w-full max-w-md border border-zinc-200 dark:border-zinc-800 p-10 rounded-2xl bg-white dark:bg-zinc-900 shadow-xl shadow-zinc-200/50 dark:shadow-none">
        
        <h2 class="text-center text-3xl font-black tracking-tight text-text mb-2">Reset Password</h2>
        <p class="text-center text-sm font-medium text-text-muted mb-6">We've sent a 6-digit code to your email. It will expire in 5 minutes.</p>

        <?= form_open('password/update', ['class' => 'space-y-3']) ?>
            <input type="hidden" name="email" value="<?= esc($email ?? '') ?>">

            <div>
                <label class="block text-sm font-bold text-text mb-2">6-Digit Code</label>
                <input type="text" name="code" value="<?= old('code') ?>" required maxlength="6" placeholder="000000"
                       class="w-full text-center tracking-[0.5em] font-black text-2xl bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all" />
                <div class="min-h-3 pl-1">
                    <p class="text-danger-500 text-[10px] font-bold mt-1 uppercase tracking-wider text-center"><?= session('error') ?? validation_show_error('code') ?></p>
                </div>
            </div>

            <div class="mt-2">
                <label class="block text-sm font-bold text-text mb-2">New Password (Min. 8 characters)</label>
                <input type="password" name="password" required minlength="8"
                       class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:outline-none text-text transition-all" />
                <div class="h-3 pl-1">
                    <p class="text-danger-500 text-[10px] font-bold mt-1 uppercase tracking-wider"><?= validation_show_error('password') ?></p>
                </div>
            </div>

            <div class="mt-2">
                <label class="block text-sm font-bold text-text mb-2">Confirm New Password</label>
                <input type="password" name="confirm-password" required minlength="8"
                       class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:outline-none text-text transition-all" />
                <div class="h-3 pl-1">
                    <p class="text-danger-500 text-[10px] font-bold mt-1 uppercase tracking-wider"><?= validation_show_error('confirm-password') ?></p>
                </div>
            </div>

            <div class="pt-5">
                <button type="submit" class="w-full bg-accent text-white font-bold py-3.5 rounded-xl cursor-pointer hover:bg-accent-hover transition-all text-sm shadow-lg shadow-accent/20 active:scale-[0.98]">
                    Confirm & Update Password
                </button>
            </div>
        <?= form_close() ?>

        <div class="text-center mt-6">
            <p class="text-xs text-text-muted mb-2">Didn't receive the code?</p>
            <?= form_open('password/send', ['class' => 'inline']) ?>
                <input type="hidden" name="email" value="<?= esc($email ?? '') ?>">
                <button type="submit" id="btn-resend" disabled class="text-sm font-bold text-text-muted transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    Resend Code (60s)
                </button>
            <?= form_close() ?>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('btn-resend');
    if (!btn) return;
    
    const storageKey = 'resendCooldown_pwd';
    let lastSent = localStorage.getItem(storageKey);
    let timeLeft = 0;
    
    if (!lastSent) {
        lastSent = Date.now();
        localStorage.setItem(storageKey, lastSent);
    }
    
    const elapsed = Math.floor((Date.now() - parseInt(lastSent)) / 1000);
    timeLeft = Math.max(0, 60 - elapsed);
    
    const form = btn.closest('form');
    if (form) {
        form.addEventListener('submit', () => {
            localStorage.setItem(storageKey, Date.now());
        });
    }
    
    const updateUI = () => {
        if (timeLeft <= 0) {
            btn.disabled = false;
            btn.textContent = 'Resend Code Now';
            btn.classList.remove('text-text-muted');
            btn.classList.add('text-accent', 'hover:text-accent-hover');
        } else {
            btn.disabled = true;
            btn.textContent = `Resend Code (${timeLeft}s)`;
            btn.classList.add('text-text-muted');
            btn.classList.remove('text-accent', 'hover:text-accent-hover');
        }
    };
    
    updateUI();
    
    if (timeLeft > 0) {
        const tick = setInterval(() => {
            timeLeft--;
            updateUI();
            if (timeLeft <= 0) {
                clearInterval(tick);
            }
        }, 1000);
    }
});
</script>
<?= $this->endSection() ?>