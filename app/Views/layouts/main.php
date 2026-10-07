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

        // Global Theme Toggle Handler with smooth transition & micro-spin animation
        window.handleThemeToggle = function(btn) {
            document.documentElement.classList.add('theme-transitioning');
            if (btn) {
                btn.classList.add('theme-toggle-spin');
            }

            document.documentElement.classList.toggle('dark');
            const isDark = document.documentElement.classList.contains('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');

            if (typeof initEditor === 'function') {
                initEditor();
            }

            setTimeout(() => {
                document.documentElement.classList.remove('theme-transitioning');
                if (btn) {
                    btn.classList.remove('theme-toggle-spin');
                }
            }, 380);
        };

        // Lets static JS (which can't read the PHP ENVIRONMENT constant directly)
        // know whether it's running in development - used by header.js to decide
        // whether to JIT-poll the email queue at all (production relies on cron instead).
        window.SPMS_ENV = "<?= ENVIRONMENT ?>";
    </script>

    <link rel="preload" href="<?= base_url('assets/fonts/Roboto/Roboto-VariableFont_wdth,wght.ttf') ?>" as="font" type="font/ttf" crossorigin>
    <link rel="stylesheet" href="<?= base_url('assets/css/main/style.css') ?>">

    <script src="<?= base_url('assets/vendor/tinymce/tinymce.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/axios/dist/axios.min.js') ?>"></script>

    <meta name="csrf-token-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token-hash" content="<?= csrf_hash() ?>">

    <!-- Theme transition style for toggle animation -->
    <style>
        html.theme-transitioning,
        html.theme-transitioning *,
        html.theme-transitioning *::before,
        html.theme-transitioning *::after {
            transition: background-color 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                        border-color 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                        color 0.25s cubic-bezier(0.4, 0, 0.2, 1),
                        fill 0.25s cubic-bezier(0.4, 0, 0.2, 1),
                        stroke 0.25s cubic-bezier(0.4, 0, 0.2, 1),
                        box-shadow 0.35s cubic-bezier(0.4, 0, 0.2, 1) !important;
            transition-delay: 0s !important;
        }

        /* Subtle micro-rotation for theme toggle buttons */
        .theme-toggle-spin {
            animation: theme-spin 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        /* Subtle adaptive auth card border & elevation */
        .spms-auth-card {
            border: 1px solid rgba(226, 232, 240, 0.75) !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.02) !important;
        }
        .dark .spms-auth-card {
            border: 1px solid rgba(16, 185, 129, 0.18) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6) !important;
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