<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>    

<!-- Added w-full and overflow-x-hidden to strictly enforce screen bounds -->
<div class="h-full flex flex-col bg-bg w-full overflow-x-hidden">
    
    <?= form_open('templates/store', ['id' => 'template-form', 'class' => 'flex flex-col h-full m-0 w-full']) ?>
        <input type="hidden" name="template_id" value="<?= $template ? $template['id'] : '' ?>">

        <!-- Adjusted padding, gaps, and added min-w-0 logic so the flexbox can shrink -->
        <div class="flex-none flex items-center justify-between py-2 px-3 sm:px-6 bg-bg border-b border-surface-border gap-2 sm:gap-4 w-full">
            
            <div class="flex items-center gap-1 sm:gap-3 flex-1 min-w-0">
                <!-- Back-to-templates link. text-text (not text-white) so it stays visible on the
                     theme-aware bg-bg header in both light and dark mode. -->
                <a href="<?= site_url('templates') ?>" class="shrink-0">
                    <div class="flex-shrink-0 flex items-center gap-1 mr-2 sm:mr-4 text-text hover:text-accent transition-colors">
                        <!-- Left-arrow icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <!-- Hidden on mobile, shown on larger screens -->
                        <span class="hidden sm:block font-black tracking-tighter text-sm uppercase">Back</span>
                    </div>
                </a>

                <!-- Replaced fixed w-64 with flex-1 min-w-0 and w-full so it shrinks gracefully -->
                <input type="text" name="title" placeholder="Enter Template Title..."
                    value="<?= $template ? esc($template['title']) : '' ?>"
                    class="bg-transparent border-none font-bold text-sm text-text focus:ring-0 px-2 py-1 flex-1 min-w-0 w-full md:w-96 placeholder:text-text-muted/50 truncate">
            </div>

            <div class="flex items-center shrink-0">
                <!-- Abbreviated text on mobile (Save), expanded on desktop (Save Template) -->
                <button type="submit" class="bg-accent hover:bg-accent-hover text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-4 sm:px-6 rounded-lg shadow-lg shadow-accent/20 transition-all active:scale-[0.98] cursor-pointer whitespace-nowrap">
                    Save<span class="hidden sm:inline"> Template</span>
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

    function saveDocument() {
        tinymce.get('editable-doc').save(); 
        document.getElementById('template-form').submit(); 
    }

    document.addEventListener('DOMContentLoaded', () => {
        initEditor();
    });
</script>

<?= $this->endSection() ?>