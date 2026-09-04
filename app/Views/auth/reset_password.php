<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="min-h-screen flex flex-col bg-gradient-to-br from-[#06442b] via-[#053823] to-[#042819] relative overflow-hidden">
    <?= view('components/govph_masthead') ?>

    <main class="flex-1 flex flex-col justify-center px-6 py-12 lg:px-8 relative">
    
    <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="mx-auto w-full max-w-md border border-emerald-800/30 p-8 sm:p-10 rounded-3xl bg-white dark:bg-[#0b1b13] shadow-2xl relative z-10">
        
        <div class="sm:mx-auto sm:w-full sm:max-w-sm flex flex-col items-center mb-6">
            <a href="<?= site_url('/') ?>" class="mb-3 hover:scale-105 transition-transform" title="Back to Home">
                <img src="<?= base_url('assets/images/spms_logo.png') ?>" alt="SPMS Logo" class="w-14 h-14 rounded-full object-contain shadow-md" />
            </a>
            <h2 class="text-center text-2xl font-heading font-black tracking-tight text-slate-900 dark:text-white">Reset Password</h2>
            <p class="text-center text-xs text-slate-500 dark:text-slate-400 mt-1">We've sent a 6-digit code to your email. It will expire in 5 minutes.</p>
        </div>

        <?= form_open('password/update', ['class' => 'space-y-3']) ?>
            <input type="hidden" name="email" value="<?= esc($email ?? '') ?>">

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">6-Digit Code</label>
                <input type="text" name="code" value="<?= old('code') ?>" required maxlength="6" placeholder="000000"
                       class="w-full text-center tracking-[0.5em] font-black text-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 focus:border-[#064e3b] focus:ring-1 focus:ring-[#064e3b] focus:outline-none text-slate-900 dark:text-white transition-all" />
                <div class="min-h-3 pl-1">
                    <p class="text-danger-500 text-[10px] font-bold mt-1 uppercase tracking-wider text-center"><?= session('error') ?? validation_show_error('code') ?></p>
                </div>
            </div>

            <div class="mt-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">New Password (Min. 8 characters)</label>
                <input type="password" name="password" required minlength="8"
                       class="w-full bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 text-sm focus:border-[#064e3b] focus:ring-1 focus:ring-[#064e3b] focus:outline-none text-slate-900 dark:text-white transition-all" />
                <div class="h-3 pl-1">
                    <p class="text-danger-500 text-[10px] font-bold mt-1 uppercase tracking-wider"><?= validation_show_error('password') ?></p>
                </div>
            </div>

            <div class="mt-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Confirm New Password</label>
                <input type="password" name="confirm-password" required minlength="8"
                       class="w-full bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 text-sm focus:border-[#064e3b] focus:ring-1 focus:ring-[#064e3b] focus:outline-none text-slate-900 dark:text-white transition-all" />
                <div class="h-3 pl-1">
                    <p class="text-danger-500 text-[10px] font-bold mt-1 uppercase tracking-wider"><?= validation_show_error('confirm-password') ?></p>
                </div>
            </div>

            <div class="pt-3">
                <button type="submit" class="w-full bg-[#064e3b] hover:bg-[#085a3a] text-white font-bold py-3.5 rounded-xl cursor-pointer transition-all text-xs uppercase tracking-wider shadow-md active:scale-[0.98]">
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
</div>

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