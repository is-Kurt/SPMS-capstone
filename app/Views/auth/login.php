<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<style>
    @keyframes portalFadeUp {
        from {
            opacity: 0;
            transform: translateY(14px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .animate-portal-left {
        animation: portalFadeUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .animate-portal-right {
        animation: portalFadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.08s both;
    }
    @media (prefers-reduced-motion: reduce) {
        .animate-portal-left, .animate-portal-right {
            animation: none !important;
            opacity: 1 !important;
            transform: none !important;
        }
    }
</style>

<div class="min-h-screen flex flex-col bg-zinc-100 dark:bg-zinc-950 text-zinc-800 dark:text-zinc-100 transition-colors">
    <?= view('components/govph_masthead') ?>

    <main class="flex-1 flex items-center justify-center py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl w-full mx-auto">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- LEFT COLUMN: Official University Portal Gateway & Advisories -->
                <div class="lg:col-span-7 flex flex-col gap-6 animate-portal-left">
                    
                    <!-- Institutional Identity Header -->
                    <div class="flex items-center gap-4">
                        <img src="<?= base_url('assets/images/bsu_seal.png') ?>" alt="Benguet State University Seal" 
                             class="w-16 h-16 rounded-full object-contain shrink-0 border border-emerald-700/30 bg-white p-0.5 shadow-xs" />
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-400">Republic of the Philippines</span>
                                <span class="text-zinc-300 dark:text-zinc-600 hidden sm:inline">•</span>
                                <span class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400">Chartered State University</span>
                            </div>
                            <h1 class="text-xl sm:text-2xl font-black text-zinc-900 dark:text-white tracking-tight leading-tight mt-0.5">
                                Benguet State University
                            </h1>
                            <p class="text-xs font-bold uppercase tracking-wide text-amber-700 dark:text-amber-400 mt-0.5">
                                Strategic Performance Management System (BSU-SPMS)
                            </p>
                        </div>
                    </div>

                    <!-- Official HRDO Notice Card -->
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 sm:p-6 shadow-xs">
                        <div class="flex items-center justify-between gap-3 pb-3 mb-4 border-b border-zinc-100 dark:border-zinc-800">
                            <div class="flex items-center gap-2">
                                <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-600 dark:bg-emerald-400"></span>
                                <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-white">Institutional Performance Advisory</h2>
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                CSC Mandated
                            </span>
                        </div>

                        <p class="text-xs text-zinc-600 dark:text-zinc-300 leading-relaxed mb-4">
                            In compliance with <strong class="text-zinc-900 dark:text-white">CSC Memorandum Circular No. 6, s. 2012</strong>, all faculty, department chairs, collegiate deans, and administrative personnel must formulate and submit their individual and divisional performance commitments for the active academic cycle.
                        </p>


                        <!-- Quick Resources Links -->
                        <div class="flex flex-wrap items-center gap-3 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                            <button type="button" onclick="if(typeof openUserGuideModal === 'function') openUserGuideModal()" 
                                    class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-700 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300 transition-colors cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <span>SPMS Operations Manual &amp; Guide</span>
                            </button>
                            <span class="text-zinc-300 dark:text-zinc-700">•</span>
                            <a href="http://www.bsu.edu.ph" target="_blank" rel="noopener noreferrer" 
                               class="text-xs font-semibold text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white transition-colors">
                                University Website (bsu.edu.ph)
                            </a>
                        </div>
                    </div>


                </div>

                <!-- RIGHT COLUMN: Secure Login Card -->
                <div class="lg:col-span-5 animate-portal-right">
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 sm:p-8 shadow-xs">
                        
                        <!-- Top Bar: Title & Theme Toggle -->
                        <div class="flex items-center justify-between pb-4 mb-5 border-b border-zinc-100 dark:border-zinc-800">
                            <div class="flex items-center gap-2">
                                <img src="<?= base_url('assets/images/spms_logo.png') ?>" alt="SPMS Logo" class="w-7 h-7 rounded-full object-contain" />
                                <span class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-white">Portal Access</span>
                            </div>
                            <button type="button" onclick="handleThemeToggle(this)" 
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-zinc-500 hover:text-zinc-800 dark:text-emerald-400 dark:hover:text-white bg-zinc-50 hover:bg-zinc-100 dark:bg-zinc-950 dark:hover:bg-[#0a2318] border border-zinc-200 dark:border-zinc-800 transition-colors cursor-pointer" 
                                    title="Toggle Light / Dark Mode">
                                <svg class="w-4 h-4 hidden dark:block text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                <svg class="w-4 h-4 block dark:hidden text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                            </button>
                        </div>

                        <div class="mb-5">
                            <h2 class="text-lg font-bold text-zinc-900 dark:text-white tracking-tight">Account Sign In</h2>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Enter your institutional credentials to access your evaluation workspace.</p>
                        </div>

                        <?= form_open('login', ['id' => 'login-form', 'class' => 'space-y-4']) ?>
                            
                            <div>
                                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1.5">
                                    Email Address
                                </label>
                                <input id="email" type="email" name="email" value="<?= esc(old('email', $prefillEmail ?? '')) ?>" required autofocus
                                       placeholder="e.g. name@bsu.edu.ph"
                                       class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 focus:outline-none text-zinc-900 dark:text-white placeholder:text-zinc-400 dark:placeholder:text-zinc-600 transition-colors" />
                                <?php if (validation_show_error('email')): ?>
                                    <p class="text-rose-600 dark:text-rose-400 text-[11px] font-bold mt-1"><?= validation_show_error('email') ?></p>
                                <?php endif; ?>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                                        Password
                                    </label>
                                    <a href="<?= site_url('password/forgot') ?>" class="text-xs font-semibold text-emerald-800 dark:text-emerald-400 hover:underline">
                                        Forgot?
                                    </a>
                                </div>
                                <input id="password" type="password" name="password" required
                                       class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 focus:outline-none text-zinc-900 dark:text-white transition-colors" />
                                <?php if (session('errors.error') ?? validation_show_error('password')): ?>
                                    <p class="text-rose-600 dark:text-rose-400 text-[11px] font-bold mt-1"><?= session('errors.error') ?? validation_show_error('password') ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="flex items-center gap-2 pt-1">
                                <input id="remember-me" name="remember-me" type="checkbox" class="checkbox" />
                                <label for="remember-me" class="text-xs font-medium text-zinc-600 dark:text-zinc-400 cursor-pointer select-none">
                                    Keep me signed in on this computer
                                </label>
                            </div>

                            <?php if (getenv('CI_ENVIRONMENT') !== 'development'): ?>
                            <div class="pt-2 flex justify-center">
                                <div class="cf-turnstile" data-sitekey="<?= esc(getenv('TURNSTILE_SITE_KEY')) ?>"></div>
                            </div>
                            <?php endif; ?>

                            <div class="pt-2">
                                <button id="login-submit-btn" type="submit" 
                                        class="w-full bg-[#064e3b] hover:bg-[#085a3a] text-white font-bold py-3 rounded-lg cursor-pointer transition-all duration-200 text-xs uppercase tracking-wider shadow-xs flex items-center justify-center gap-2">
                                    <span id="login-btn-content" class="inline-flex items-center justify-center gap-2">
                                        <span>Log In to Workspace</span>
                                    </span>
                                </button>
                            </div>

                        <?= form_close() ?>

                        <div class="mt-5 pt-4 border-t border-zinc-100 dark:border-zinc-800 text-center">
                            <button type="button" onclick="if(typeof openUserGuideModal === 'function') openUserGuideModal()" 
                                     class="text-xs font-semibold text-zinc-500 hover:text-emerald-800 dark:text-zinc-400 dark:hover:text-emerald-300 transition-colors inline-flex items-center gap-1 cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Need help? Open the SPMS User Guide</span>
                            </button>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </main>

    <!-- Standard Institutional Footer -->
    <footer class="border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-[#05140d] py-4 px-4 sm:px-6 lg:px-8 text-center text-xs text-zinc-500 dark:text-zinc-400">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
            <span>&copy; <?= date('Y') ?> Benguet State University &bull; Human Resource Development Office (HRDO)</span>
            <div class="flex items-center gap-4 text-[11px]">
                <a href="https://www.gov.ph" target="_blank" rel="noopener noreferrer" class="hover:underline">GOVPH</a>
                <span>&bull;</span>
                <a href="https://www.csc.gov.ph" target="_blank" rel="noopener noreferrer" class="hover:underline">Civil Service Commission</a>
                <span>&bull;</span>
                <a href="http://www.bsu.edu.ph" target="_blank" rel="noopener noreferrer" class="hover:underline">BSU Official Portal</a>
            </div>
        </div>
    </footer>
    <!-- Seamless Screen Transition Overlay -->
    <div id="login-transition-overlay" class="fixed inset-0 z-[300] bg-zinc-950/80 hidden items-center justify-center transition-opacity duration-300 opacity-0 pointer-events-none">
        <div class="flex flex-col items-center justify-center gap-4 text-center">
            <!-- Glowing Emblem with Spinner Ring -->
            <div class="relative w-20 h-20 flex items-center justify-center">
                <div class="absolute inset-0 rounded-full border-4 border-emerald-500/20"></div>
                <div class="absolute inset-0 rounded-full border-4 border-emerald-500 border-t-transparent animate-spin"></div>
                <img src="<?= base_url('assets/images/bsu_seal.png') ?>" alt="Benguet State University Seal" class="w-12 h-12 rounded-full object-contain drop-shadow-md" />
            </div>
            <!-- Clean, Simple Title -->
            <p class="text-sm font-bold text-white tracking-wide">
                Opening Workspace...
            </p>
        </div>
    </div>
</div>

<?php if (getenv('CI_ENVIRONMENT') !== 'development'): ?>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<?php endif; ?>
<script src="<?= base_url('assets/vendor/fingerprintjs/fp.min.js') ?>"></script>
<script>
    // Submit loading feedback with animated spinner & transition overlay
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('login-form') || document.querySelector('form');
        const btn = document.getElementById('login-submit-btn');
        const content = document.getElementById('login-btn-content');
        const overlay = document.getElementById('login-transition-overlay');

        if (form && btn) {
            form.addEventListener('submit', function () {
                if (!form.checkValidity()) return;

                btn.style.pointerEvents = 'none';
                btn.classList.add('opacity-90', 'cursor-wait');
                if (content) {
                    content.innerHTML = `
                        <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Signing in...</span>
                    `;
                }

                if (overlay) {
                    overlay.classList.remove('hidden');
                    overlay.classList.add('flex');
                    requestAnimationFrame(() => {
                        overlay.classList.remove('opacity-0');
                        overlay.classList.add('opacity-100');
                    });
                }
            });
        }
    });

    // Initialize FingerprintJS and populate the hidden device_id field
    const fpPromise = FingerprintJS.load();
    fpPromise
      .then(fp => fp.get())
      .then(result => {
        const deviceId = result.visitorId;
        const form = document.getElementById('login-form') || document.querySelector('form');
        if (form) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'device_id';
            input.value = deviceId;
            form.appendChild(input);
        }
      })
      .catch(() => {});
</script>

<?= $this->endSection() ?>