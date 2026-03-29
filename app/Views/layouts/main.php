<!DOCTYPE html>
<html lang="en" class="h-full dark font-sans ">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preload" href="<?= base_url('assets/fonts/Roboto/Roboto-VariableFont_wdth,wght.ttf') ?>" as="font" type="font/ttf" crossorigin>
    <link rel="stylesheet" href="<?= base_url('assets/main/css/style.css') ?>">
    <script src="<?= base_url('assets/tinymce/tinymce.min.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <title>Document</title>
</head>
<body class="h-full bg-page-bg text-main-text">
    <div class="fixed bottom-4 right-4 z-50">
        <button id="theme-toggle" class="px-4 py-2 rounded-lg bg-card-bg text-main-text border border-main-border text-xs font-bold shadow-lg">
            TOGGLE THEME
        </button>
    </div>
    
    <?= $this->renderSection('content'); ?>

    <script>
        const btn = document.getElementById('theme-toggle');
        btn.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            initPlainEditor();
        });

    </script>
    <script src="<?= base_url('assets/main/js/header.js') ?>"></script>
</body>
</html>