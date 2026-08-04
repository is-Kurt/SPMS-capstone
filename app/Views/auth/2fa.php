<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<main class="flex min-h-screen flex-col justify-center px-6 py-12 lg:px-8 bg-gray-50 dark:bg-zinc-950">
    <div class="mx-auto w-full max-w-md border border-zinc-200 dark:border-zinc-800 p-10 rounded-2xl bg-white dark:bg-zinc-900 shadow-xl shadow-zinc-200/50 dark:shadow-none">

        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <h2 class="text-center text-3xl font-black tracking-tight text-text">Two-Factor Authentication</h2>
            <p class="mt-4 text-center text-sm text-text-muted">
                We've sent a 6-digit code to your email address. It will expire in 10 minutes.
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-sm">
            <?= form_open('login/2fa', ['class' => 'space-y-4']) ?>
                
                <div>
                    <label for="code" class="block text-sm font-bold text-text">Verification Code</label>
                    <div class="mt-2">
                        <input id="code" type="text" name="code" placeholder="123456"
                               class="w-full text-center text-2xl tracking-[0.5em] font-mono bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all" />
                    </div>
                    <?php if (session('errors.error')): ?>
                        <div class="h-3 pl-1">
                            <p class="text-danger-500 text-[10px] font-bold mt-1 uppercase tracking-wider"><?= session('errors.error') ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if (session('success')): ?>
                        <div class="h-3 pl-1">
                            <p class="text-accent text-[10px] font-bold mt-1 uppercase tracking-wider"><?= session('success') ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mt-8">
                    <button type="submit"
                            class="flex w-full justify-center rounded-xl bg-accent px-3 py-3 text-sm font-bold text-white shadow-sm hover:bg-accent-hover focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all">
                        Verify Code
                    </button>
                </div>
            <?= form_close() ?>
            
            <div class="text-center mt-6">
                <p class="text-xs text-text-muted mb-2">Didn't receive the code?</p>
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
    
    let timeLeft = 60;
    
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
