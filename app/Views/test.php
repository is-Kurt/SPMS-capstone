<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <title>Smart HRMO Document Editor</title>
    <link rel="stylesheet" href="<?= base_url('assets/main/css/style.css') ?>">
    
    <script src="assets/tinymce/tinymce.min.js"></script>
</head>
<body class="bg-slate-50 h-full flex flex-col p-4 md:p-8 font-sans">
        
        <div class="mb-4 flex flex-col sm:flex-row justify-between items-center gap-4">
            <h2 class="text-2xl font-bold text-gray-800">Performance Evaluation Matrix</h2>
            <p>Rating: <span id="rating">0%</span></p>
            <button onclick="calculateAllTables()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow transition cursor-pointer">
                🧮 Calculate All Tables
            </button>
        </div>
        
        <div class="flex-1 border-gray-300">
            <textarea id="editable-doc">
                </textarea>
        </div>
        

        <script>
            const AppConfig = {
                editorCss: '<?= base_url('assets/editor/css/editor.css') ?>'
            };
        </script>
        
        <script src="<?= base_url('assets/editor/js/TableTools.js') ?>"></script>
        <script src="<?= base_url('assets/editor/js/editor.js') ?>"></script>
</body>
</html>