<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>    

<div class="h-full flex flex-col bg-bg">
    
    <?= form_open('templates/store', ['id' => 'template-form', 'class' => 'flex flex-col h-full m-0']) ?>
        <input type="hidden" name="template_id" value="<?= $template ? $template['id'] : '' ?>">

        <div class="flex-none flex items-center justify-between py-2 px-6 bg-bg border-b border-surface-border">
            <div class="flex items-center gap-3">
                <a href="<?= site_url('templates') ?>">
                    <div class="flex-shrink-0 flex items-center gap-1 mr-4 text-white hover:text-accent transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span class="font-black tracking-tighter text-sm uppercase">Back</span>
                    </div>
                </a>

                <input type="text" name="title" required placeholder="Enter Template Title..."
                    value="<?= $template ? esc($template['title']) : '' ?>"
                    class="bg-transparent border-none font-bold text-sm text-text focus:ring-0 px-2 py-1 w-64 md:w-96 placeholder:text-text-muted/50">
            </div>

            <div class="flex items-center gap-4">
                <button type="submit" class="bg-accent hover:bg-accent-hover text-white text-xs font-bold py-2.5 px-6 rounded-lg shadow-lg shadow-accent/20 transition-all active:scale-[0.98] cursor-pointer">
                    Save Template
                </button>
            </div>
        </div>
        
        <div class="flex-1 min-h-0 w-full relative bg-white dark:bg-zinc-950">
            <textarea id="editable-doc" name="content"><?= $template ? $template['content'] : '' ?></textarea>
        </div>
    <?= form_close() ?>

</div>

<script src="<?= base_url('assets/js/editor/functions.js') ?>"></script>
<script src="<?= base_url('assets/js/editor/plugins.js') ?>"></script>
<script src="<?= base_url('assets/js/editor/TableTools.js') ?>"></script>
<script src="<?= base_url('assets/js/editor/config.js') ?>"></script>

<script>
    // 1. Fake the AppConfig and AppState so your plugins.js and config.js don't crash!
    const AppConfig = {
        editorCss: '<?= base_url('assets/css/editor/style.css') ?>',
        ciDebug: <?= (ENVIRONMENT === 'development') ? 'true' : 'false' ?>
    };

    const AppState = {
        isDirty: false,
        setDirty(val) {
            this.isDirty = val;
        }
    };

    // 2. Override saveDocument()! 
    // We don't load your massive saveDocument.js here because templates don't use AJAX.
    // Instead, if the Admin hits Ctrl+S, we just submit the standard form.
    function saveDocument() {
        tinymce.get('editable-doc').save(); // Sync the editor content to the textarea
        document.getElementById('template-form').submit(); // Submit the form back to Template.php
    }

    // 3. Fire your custom initEditor function from config.js
    document.addEventListener('DOMContentLoaded', () => {
        initEditor();
    });
</script>

<?= $this->endSection() ?>