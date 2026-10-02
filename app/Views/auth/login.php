<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="min-h-screen flex flex-col bg-gradient-to-br from-[#06442b] via-[#053823] to-[#042819] relative overflow-hidden">
    <?= view('components/govph_masthead') ?>

    <main class="flex-1 flex flex-col justify-center px-6 py-12 lg:px-8 relative">
    
    <!-- Subtle Background Glow -->
    <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="mx-auto w-full max-w-md border border-emerald-800/30 p-8 sm:p-10 rounded-3xl bg-white dark:bg-[#0b1b13] shadow-2xl relative z-10">

        <!-- Top Status & Help -->
        <div class="mb-5 flex items-center justify-between">
            <button type="button" onclick="if(typeof openUserGuideModal === 'function') openUserGuideModal()" class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-600 hover:text-amber-500 dark:text-amber-400 dark:hover:text-amber-300 transition-colors cursor-pointer group" title="Open SPMS Visual User Guide">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>SPMS User Guide</span>
            </button>
            <span class="text-[10px] font-bold uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950 text-[#064e3b] dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60 px-2.5 py-0.5 rounded-full">
                BSU SECURE PORTAL
            </span>
        </div>

        <?php if (request()->getGet('logged_out')): ?>
            <div class="mb-4 p-3 rounded-xl text-xs font-semibold flex items-center gap-2.5 transition-all"
                 style="background-color: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.35); color: #34d399;">
                <svg class="w-4 h-4 shrink-0" style="color: #34d399;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span class="text-slate-800 dark:text-emerald-200 font-bold">You have been successfully signed out.</span>
            </div>
        <?php endif; ?>

        <div class="sm:mx-auto sm:w-full sm:max-w-sm flex flex-col items-center">
            <div class="mb-3">
                <img src="<?= base_url('assets/images/spms_logo.png') ?>" alt="SPMS Logo" class="w-16 h-16 rounded-full object-contain shadow-md" />
            </div>
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

                <?php if (getenv('CI_ENVIRONMENT') !== 'development'): ?>
                <div class="mt-4 flex justify-center">
                    <div class="cf-turnstile" data-sitekey="<?= esc(getenv('TURNSTILE_SITE_KEY')) ?>"></div>
                </div>
                <?php endif; ?>

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

                <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-800/80 text-center flex items-center justify-center gap-4 text-xs font-semibold text-slate-500 dark:text-slate-400">
                    <button type="button" onclick="if(typeof openUserGuideModal === 'function') openUserGuideModal()" class="hover:text-[#064e3b] dark:hover:text-emerald-400 transition-colors inline-flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>How SPMS Works</span>
                    </button>
                    <span class="text-slate-300 dark:text-slate-700">•</span>
                    <a href="<?= site_url('about') ?>" class="hover:text-[#064e3b] dark:hover:text-emerald-400 transition-colors">
                        <span>About SPMS</span>
                    </a>
                </div>

            <?= form_close() ?>
        </div>

    </div>
</main>
</div>

<?php if (getenv('CI_ENVIRONMENT') !== 'development'): ?>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<?php endif; ?>
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