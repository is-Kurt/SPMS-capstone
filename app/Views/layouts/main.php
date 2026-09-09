<!DOCTYPE html>
<html lang="en" class="h-full font-sans overflow-y-scroll">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/spms_logo.png') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/images/spms_logo.png') ?>">

    <script>
        // Applied before first paint so the page never flashes the wrong theme.
        // Defaults to dark when no preference has been saved yet.
        if (localStorage.getItem('theme') !== 'light') {
            document.documentElement.classList.add('dark');
        }

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

    <title>SPMS</title>
</head>
<body class="h-full bg-bg text-text antialiased">
    
    <?= view('components/confirm_modal') ?>

    <?php 
        $flashError = session('error') ?? session('errors.error') ?? session()->getFlashdata('error');
        $flashSuccess = session('success') ?? session()->getFlashdata('success');
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