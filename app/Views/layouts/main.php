<!DOCTYPE html>
<html lang="en" class="h-full dark font-sans">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="preload" href="<?= base_url('assets/fonts/Roboto/Roboto-VariableFont_wdth,wght.ttf') ?>" as="font" type="font/ttf" crossorigin>
    <link rel="stylesheet" href="<?= base_url('assets/css/main/style.css') ?>">

    <script src="<?= base_url('assets/vendor/tinymce/tinymce.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/axios/dist/axios.min.js') ?>"></script>

    <meta name="csrf-token-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token-hash" content="<?= csrf_hash() ?>">

    <title>IPCR System</title>
</head>
<body class="h-full bg-surface text-text antialiased">
    
    <div class="fixed bottom-6 right-6 z-50">
        <button id="theme-toggle" class="flex items-center gap-2 px-4 py-2.5 rounded-full bg-white dark:bg-zinc-800 text-text border border-zinc-200 dark:border-zinc-700 text-xs font-bold shadow-xl hover:scale-105 transition-all cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
            <span class="hidden md:inline">Toggle Theme</span>
        </button>
    </div>
    
    <?= $this->renderSection('content'); ?>

    <script>
        const btn = document.getElementById('theme-toggle');
        btn.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            if (typeof initEditor === 'function') {
                initEditor();
            }
        });
    </script>
    
    <script src="<?= base_url('assets/js/main/header.js') ?>"></script>
    <script src="<?= base_url('assets/js/axios/config.js') ?>"></script>
    <script src="<?= base_url('assets/js/axios/api.js') ?>"></script>
</body>
</html>