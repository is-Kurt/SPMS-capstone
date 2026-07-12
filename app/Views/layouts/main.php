<!DOCTYPE html>
<html lang="en" class="h-full font-sans">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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
<body class="h-full bg-surface text-text antialiased">
    
    <?= view('components/confirm_modal') ?>

    <?= $this->renderSection('content'); ?>

    <script src="<?= base_url('assets/js/main/header.js') ?>"></script>
    <script src="<?= base_url('assets/js/axios/config.js') ?>"></script>
    <script src="<?= base_url('assets/js/axios/api.js') ?>"></script>
    <script type="module" src="<?= base_url('assets/js/main/modals/confirmModal.js') ?>"></script>
    <script src="<?= base_url('assets/js/main/passwordToggle.js') ?>"></script>
</body>
</html>