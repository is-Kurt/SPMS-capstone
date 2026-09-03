<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<main class="flex min-h-screen flex-col justify-center px-6 py-12 lg:px-8 bg-gradient-to-br from-[#06442b] via-[#053823] to-[#042819] relative overflow-hidden">
    
    <!-- Subtle Background Glow -->
    <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="mx-auto w-full max-w-md border border-emerald-800/30 p-8 sm:p-10 rounded-3xl bg-white dark:bg-[#0b1b13] shadow-2xl relative z-10">

        <!-- Top Navigation: Return to Landing Page -->
        <div class="mb-5 flex items-center justify-between">
            <a href="<?= site_url('/') ?>" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-[#064e3b] dark:hover:text-emerald-400 transition-colors group" title="Return to SPMS Landing Page">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Back to Home</span>
            </a>
            <span class="text-[10px] font-bold uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950 text-[#064e3b] dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60 px-2 py-0.5 rounded-full">
                BSU PORTAL
            </span>
        </div>

        <div class="sm:mx-auto sm:w-full sm:max-w-sm flex flex-col items-center">
            <a href="<?= site_url('/') ?>" class="mb-3 hover:scale-105 transition-transform" title="Back to Home">
                <img src="<?= base_url('assets/images/spms_logo.png') ?>" alt="SPMS Logo" class="w-16 h-16 rounded-full object-contain shadow-md" />
            </a>
            <h2 class="text-center text-2xl font-heading font-black tracking-tight text-slate-900 dark:text-white">Log in</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 text-center mt-1">Benguet State University • SPMS</p>
        </div>

        <div class="mt-7 sm:mx-auto sm:w-full sm:max-w-sm">

            <?= form_open('login', ['class' => 'space-y-3']) ?>
                
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Email address</label>
                    <div class="mt-1.5">
                        <input id="email" type="text" name="email" value="<?= esc(old('email', $prefillEmail ?? '')) ?>"
                               class="w-full bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 text-sm focus:border-[#064e3b] focus:ring-1 focus:ring-[#064e3b] focus:outline-none text-slate-900 dark:text-white transition-all" />
                    </div>
                    <div class="h-3 pl-1">
                        <p class="text-danger-500 text-[10px] font-bold mt-1 uppercase tracking-wider"><?= validation_show_error('email') ?></p>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Password</label>
                    </div>
                    <div class="mt-1.5">
                        <input id="password" type="password" name="password" 
                               class="w-full bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 text-sm focus:border-[#064e3b] focus:ring-1 focus:ring-[#064e3b] focus:outline-none text-slate-900 dark:text-white transition-all" />
                    </div>
                    <div class="h-3 pl-1">
                        <p class="text-danger-500 text-[10px] font-bold mt-1 uppercase tracking-wider"><?= session('errors.error') ?? validation_show_error('password') ?></p>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-4">
                    <div class="flex items-center gap-2">
                        <input id="remember-me" name="remember-me" type="checkbox" class="checkbox" />
                        <label for="remember-me" class="block text-xs font-medium text-slate-600 dark:text-slate-400 cursor-pointer">
                            Remember me
                        </label>
                    </div>

                    <div class="text-xs hidden sm:block">
                        <a href="<?= site_url('password/forgot') ?>" class="font-bold text-[#064e3b] dark:text-emerald-400 hover:underline transition-colors">
                            Forgot password?
                        </a>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="w-full bg-[#064e3b] hover:bg-[#085a3a] text-white font-bold py-3.5 rounded-xl cursor-pointer transition-all text-xs uppercase tracking-wider shadow-md active:scale-[0.98]">
                        Log in
                    </button>
                </div>

                <div class="mt-4 text-center sm:hidden">
                    <a href="<?= site_url('password/forgot') ?>" class="text-xs font-bold text-[#064e3b] dark:text-emerald-400 hover:underline transition-colors">
                        Forgot password?
                    </a>
                </div>

                <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-800/80 text-center">
                    <a href="<?= site_url('/') ?>" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-[#064e3b] dark:hover:text-emerald-400 transition-colors">
                        <span>&larr; Return to SPMS Landing Page</span>
                    </a>
                </div>

            <?= form_close() ?>
        </div>

    </div>
</main>

<script src="<?= base_url('assets/vendor/fingerprintjs/fp.min.js') ?>"></script>
<script>
    // Initialize FingerprintJS and populate the hidden device_id field
    const fpPromise = FingerprintJS.load();
    fpPromise
      .then(fp => fp.get())
      .then(result => {
        const deviceId = result.visitorId;
        
        // Find the login form and append a hidden input for the device ID
        const form = document.querySelector('form');
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'device_id';
        input.value = deviceId;
        form.appendChild(input);

        // Prevent double submit and show loading feedback
        form.addEventListener('submit', function () {
            const btn = form.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-70', 'cursor-not-allowed');
                btn.innerText = 'Logging in...';
            }
        });
      });
</script>

<?= $this->endSection() ?>