<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="min-h-screen flex flex-col bg-zinc-100 dark:bg-[#071710] text-zinc-800 dark:text-zinc-100 transition-colors">
    <?= view('components/govph_masthead') ?>

    <main class="flex-1 flex flex-col justify-center px-6 py-12 lg:px-8 relative">

    <div class="mx-auto w-full max-w-md bg-white dark:bg-[#0c1d15] border border-zinc-200 dark:border-[#163e2a] p-8 sm:p-10 rounded-xl shadow-xs relative z-10 transition-colors">

        <div class="flex justify-end mb-1">
            <button type="button" onclick="handleThemeToggle(this)" class="w-7 h-7 rounded-lg flex items-center justify-center text-zinc-500 hover:text-zinc-800 dark:text-emerald-400 dark:hover:text-white bg-zinc-100 hover:bg-zinc-200 dark:bg-emerald-950/60 dark:hover:bg-emerald-900/60 border border-zinc-200 dark:border-emerald-800/60 transition-all cursor-pointer shadow-2xs" title="Toggle Light / Dark Mode">
                <svg class="w-3.5 h-3.5 hidden dark:block text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                <svg class="w-3.5 h-3.5 block dark:hidden text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
            </button>
        </div>

        <div class="sm:mx-auto sm:w-full sm:max-w-sm flex flex-col items-center mb-6">
            <a href="<?= site_url('/') ?>" class="mb-3 hover:scale-105 transition-transform" title="Back to Home">
                <img src="<?= base_url('assets/images/spms_logo.png') ?>" alt="SPMS Logo" class="w-14 h-14 rounded-full object-contain shadow-md" />
            </a>
            <h2 class="text-center text-2xl font-heading font-black tracking-tight text-zinc-900 dark:text-white">Two-Factor Authentication</h2>
            <p class="mt-2 text-center text-xs text-zinc-500 dark:text-zinc-400">
                We've sent a 6-digit code to your email address. It will expire in 10 minutes.
            </p>
        </div>

        <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-sm">
            <?= form_open('login/2fa', ['class' => 'space-y-4']) ?>
                
                <div>
                    <label for="code" class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1.5">Verification Code</label>
                    <div class="mt-1">
                        <input id="code" type="text" name="code" placeholder="123456"
                               class="w-full text-center text-2xl tracking-[0.5em] font-mono bg-zinc-50 dark:bg-[#071710] border border-zinc-300 dark:border-[#163e2a] rounded-lg px-4 py-2.5 focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 focus:outline-none text-zinc-900 dark:text-white transition-colors" />
                    </div>
                    <?php if (session('errors.error')): ?>
                        <div class="h-3 pl-1">
                            <p class="text-danger-500 text-[10px] font-bold mt-1 uppercase tracking-wider"><?= session('errors.error') ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if (session('success')): ?>
                        <div class="h-3 pl-1">
                            <p class="text-[#064e3b] dark:text-emerald-400 text-[10px] font-bold mt-1 uppercase tracking-wider"><?= session('success') ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mt-6">
                    <button type="submit"
                            class="flex w-full justify-center rounded-lg bg-[#064e3b] hover:bg-[#085a3a] px-4 py-3 text-xs font-bold uppercase tracking-wider text-white shadow-xs transition-colors cursor-pointer">
                        Verify Code
                    </button>
                </div>
            <?= form_close() ?>
            
            <div class="text-center mt-6 pt-4 border-t border-zinc-100 dark:border-zinc-800/80">
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-2">Didn't receive the code?</p>
                <?= form_open('login/2fa/resend', ['class' => 'inline']) ?>
                    <button type="submit" id="btn-resend" disabled class="text-sm font-bold text-text-muted transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        Resend Code (60s)
                    </button>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</main>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('btn-resend');
    if (!btn) return;
    
    const storageKey = 'resendCooldown_2fa';
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
