<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<main class="flex min-h-screen flex-col justify-center px-6 py-12 lg:px-8 bg-gradient-to-br from-[#06442b] via-[#053823] to-[#042819] relative overflow-hidden">
    
    <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="mx-auto w-full max-w-md border border-emerald-800/30 p-8 sm:p-10 rounded-3xl bg-white dark:bg-[#0b1b13] shadow-2xl relative z-10">

        <div class="sm:mx-auto sm:w-full sm:max-w-sm flex flex-col items-center mb-6">
            <a href="<?= site_url('/') ?>" class="mb-3 hover:scale-105 transition-transform" title="Back to Home">
                <img src="<?= base_url('assets/images/spms_logo.png') ?>" alt="SPMS Logo" class="w-14 h-14 rounded-full object-contain shadow-md" />
            </a>
            <h2 class="text-center text-2xl font-heading font-black tracking-tight text-slate-900 dark:text-white">Two-Factor Authentication</h2>
            <p class="mt-2 text-center text-xs text-slate-500 dark:text-slate-400">
                We've sent a 6-digit code to your email address. It will expire in 10 minutes.
            </p>
        </div>

        <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-sm">
            <?= form_open('login/2fa', ['class' => 'space-y-4']) ?>
                
                <div>
                    <label for="code" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Verification Code</label>
                    <div class="mt-1">
                        <input id="code" type="text" name="code" placeholder="123456"
                               class="w-full text-center text-2xl tracking-[0.5em] font-mono bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 focus:border-[#064e3b] focus:ring-1 focus:ring-[#064e3b] focus:outline-none text-slate-900 dark:text-white transition-all" />
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
                            class="flex w-full justify-center rounded-xl bg-[#064e3b] hover:bg-[#085a3a] px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-white shadow-md active:scale-[0.98] transition-all">
                        Verify Code
                    </button>
                </div>
            <?= form_close() ?>
            
            <div class="text-center mt-6 pt-4 border-t border-slate-100 dark:border-slate-800/80">
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">Didn't receive the code?</p>
                <?= form_open('login/2fa/resend', ['class' => 'inline']) ?>
                    <button type="submit" id="btn-resend" disabled class="text-sm font-bold text-text-muted transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        Resend Code (60s)
                    </button>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</main>

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
