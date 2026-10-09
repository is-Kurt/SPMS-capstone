<!DOCTYPE html>
<html lang="en" class="h-full font-sans overflow-y-auto">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/spms_logo.png') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/images/spms_logo.png') ?>">

    <script>
        // Applied before first paint so the page never flashes the wrong theme.
        // 1. If user previously chose a theme, use saved preference.
        // 2. Otherwise, adapt to device/OS setting (prefers-color-scheme).
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

            if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

            // Real-time sync with OS theme changes when user has no explicit override saved
            if (window.matchMedia) {
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                    if (!localStorage.getItem('theme')) {
                        document.documentElement.classList.toggle('dark', e.matches);
                    }
                });
            }
        })();

        // Global Theme Toggle Handler with smooth hardware-accelerated transition & micro-spin animation
        let isThemeToggling = false;
        window.handleThemeToggle = function(btn) {
            if (isThemeToggling) return;
            isThemeToggling = true;

            if (btn) {
                btn.classList.remove('theme-toggle-spin');
                void btn.offsetWidth;
                btn.classList.add('theme-toggle-spin');
                setTimeout(() => btn.classList.remove('theme-toggle-spin'), 380);
            }

            const toggleTheme = () => {
                document.documentElement.classList.toggle('dark');
                const isDark = document.documentElement.classList.contains('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');

                if (typeof initEditor === 'function') {
                    initEditor();
                }
            };

            // Use native View Transitions API if supported for 60fps hardware-accelerated cross-fade
            if (document.startViewTransition && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                document.documentElement.classList.add('no-transitions');
                try {
                    const transition = document.startViewTransition(() => {
                        toggleTheme();
                        void document.documentElement.offsetWidth;
                    });

                    transition.ready.finally(() => {
                        document.documentElement.classList.remove('no-transitions');
                    });

                    transition.finished.finally(() => {
                        document.documentElement.classList.remove('no-transitions');
                        isThemeToggling = false;
                    });
                } catch (e) {
                    document.documentElement.classList.remove('no-transitions');
                    toggleTheme();
                    isThemeToggling = false;
                }
            } else {
                // Fallback: apply smooth class transition
                document.documentElement.classList.add('theme-transitioning');
                toggleTheme();
                setTimeout(() => {
                    document.documentElement.classList.remove('theme-transitioning');
                    isThemeToggling = false;
                }, 300);
            }
        };

        // Lets static JS (which can't read the PHP ENVIRONMENT constant directly)
        // know whether it's running in development - used by header.js to decide
        // whether to JIT-poll the email queue at all (production relies on cron instead).
        window.SPMS_ENV = "<?= ENVIRONMENT ?>";
    </script>

    <link rel="preload" href="<?= base_url('assets/fonts/Roboto/Roboto-VariableFont_wdth,wght.ttf') ?>" as="font" type="font/ttf" crossorigin>
    <link rel="stylesheet" href="<?= base_url('assets/css/main/style.css?v=' . filemtime(FCPATH . 'assets/css/main/style.css')) ?>">

    <script src="<?= base_url('assets/vendor/axios/dist/axios.min.js') ?>"></script>

    <meta name="csrf-token-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token-hash" content="<?= csrf_hash() ?>">

    <!-- Theme transition style for toggle animation -->
    <style>
        @keyframes theme-spin {
            0% { transform: rotate(0deg) scale(0.9); }
            50% { transform: rotate(180deg) scale(1.12); }
            100% { transform: rotate(360deg) scale(1); }
        }
        .theme-toggle-spin {
            animation: theme-spin 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Instantaneous state freeze during View Transition snapshot capture */
        .no-transitions,
        .no-transitions *,
        .no-transitions *::before,
        .no-transitions *::after {
            transition: none !important;
        }

        /* View Transition API (Chrome, Edge, Safari 18+) */
        ::view-transition-old(root),
        ::view-transition-new(root) {
            animation-duration: 0.25s;
            animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
            mix-blend-mode: normal;
        }
        ::view-transition-old(root) {
            animation: none;
        }
        ::view-transition-new(root) {
            animation-name: vt-theme-fade-in;
        }
        @keyframes vt-theme-fade-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Fallback CSS transition for browsers without View Transitions */
        html.theme-transitioning,
        html.theme-transitioning body,
        html.theme-transitioning nav,
        html.theme-transitioning header,
        html.theme-transitioning aside,
        html.theme-transitioning main,
        html.theme-transitioning div:not(#header-nav-indicator):not(#dash-view-indicator),
        html.theme-transitioning section,
        html.theme-transitioning table,
        html.theme-transitioning tr,
        html.theme-transitioning td,
        html.theme-transitioning th,
        html.theme-transitioning button,
        html.theme-transitioning input,
        html.theme-transitioning select {
            transition: background-color 0.26s cubic-bezier(0.16, 1, 0.3, 1),
                        border-color 0.26s cubic-bezier(0.16, 1, 0.3, 1),
                        color 0.26s cubic-bezier(0.16, 1, 0.3, 1) !important;
            transition-delay: 0s !important;
        }

        .spms-auth-card {
            border: 1px solid rgba(226, 232, 240, 0.75) !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.02) !important;
        }
        .dark .spms-auth-card {
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6) !important;
        }

        @keyframes spmsSpin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-spin-smooth {
            animation: spmsSpin 0.9s linear infinite;
            transform-origin: center;
        }
    </style>

    <title>SPMS</title>
</head>
<body class="h-full bg-bg text-text antialiased">
    
    <?= view('components/confirm_modal') ?>
    <?= view('components/user_guide_modal') ?>

    <?php 
        $flashError = session('error') ?? session('errors.error') ?? session()->getFlashdata('error');
        $flashSuccess = session('success') ?? session()->getFlashdata('success');
        if (!$flashError && !$flashSuccess && request()->getGet('logged_out') && !session('errors') && !old('email') && !validation_errors()) {
            $flashSuccess = 'You have been successfully signed out of the BSU-SPMS portal.';
        }
    ?>
    <?php if ($flashError || $flashSuccess): ?>
        <div id="global-flash-banner" class="fixed top-4 right-4 z-[200] max-w-md w-full shadow-2xl rounded-2xl p-4 flex items-center gap-3 border <?= $flashError ? 'bg-rose-50 border-rose-200 text-rose-800 dark:bg-rose-950/80 dark:border-rose-900/60 dark:text-rose-200' : 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-950/80 dark:border-emerald-900/60 dark:text-emerald-200' ?>">
            <div class="shrink-0">
                <?php if ($flashError): ?>
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <?php else: ?>
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <?php endif; ?>
            </div>
            <div class="text-xs font-bold flex-1">
                <?= esc($flashError ?? $flashSuccess) ?>
            </div>
            <button onclick="this.closest('#global-flash-banner').remove()" class="text-current opacity-60 hover:opacity-100 cursor-pointer p-1">
                &times;
            </button>
        </div>
        <script>
            if (window.history.replaceState) {
                const url = new URL(window.location);
                if (url.searchParams.has('logged_out')) {
                    url.searchParams.delete('logged_out');
                    window.history.replaceState({}, document.title, url.pathname + (url.search ? url.search : ''));
                }
            }
            setTimeout(() => {
                const b = document.getElementById('global-flash-banner');
                if (b) {
                    b.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    b.style.opacity = '0';
                    b.style.transform = 'translateY(-10px)';
                    setTimeout(() => b.remove(), 400);
                }
            }, 5000);
        </script>
    <?php endif; ?>

    <?= $this->renderSection('content'); ?>

    <script src="<?= base_url('assets/js/main/header.js') ?>"></script>
    <script src="<?= base_url('assets/js/axios/config.js') ?>"></script>
    <script src="<?= base_url('assets/js/axios/api.js') ?>"></script>
    <script src="<?= base_url('assets/js/main/customSelect.js') ?>"></script>
    <script type="module" src="<?= base_url('assets/js/main/modals/confirmModal.js') ?>"></script>
    <script src="<?= base_url('assets/js/main/passwordToggle.js') ?>"></script>
    <script src="<?= base_url('assets/js/main/navigationGuards.js') ?>"></script>
</body>
</html>