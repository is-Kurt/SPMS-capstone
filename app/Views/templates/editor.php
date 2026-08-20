<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>    

<!-- Added w-full to strictly enforce screen bounds -->
<div class="h-full flex flex-col bg-bg w-full">
    
    <?= form_open('templates/store', ['id' => 'template-form', 'class' => 'flex flex-col h-full m-0 w-full', 'onsubmit' => 'event.preventDefault(); saveDocument();']) ?>
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
                <span id="save-status" class=""></span>
                <!-- Abbreviated text on mobile (Save), expanded on desktop (Save Template) -->
                <button type="button" onclick="saveDocument()" class="ml-2 bg-accent hover:bg-accent-hover text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-4 sm:px-6 rounded-lg shadow-lg shadow-accent/20 transition-all active:scale-[0.98] cursor-pointer whitespace-nowrap">
                    Save<span class="hidden sm:inline"> Template</span>
                </button>
            </div>
        </div>
        
        <div class="flex-none flex bg-bg border-b border-surface-border px-3 sm:px-6 gap-6 text-sm font-bold pt-2">
            <button type="button" id="tab-target" class="pb-2 border-b-2 border-accent text-accent transition-colors" onclick="switchEditorTab('target')">Target Form</button>
            <button type="button" id="tab-rubrics" class="pb-2 border-b-2 border-transparent text-text-muted hover:text-text transition-colors" onclick="switchEditorTab('rubrics')">Rubrics / Guide</button>
        </div>
        
        <div class="flex-1 min-h-0 w-full relative bg-white dark:bg-zinc-950 overflow-x-auto" id="editor-container-target">
            <textarea id="editable-doc" name="content"><?= $template ? $template['content'] : '' ?></textarea>
        </div>

        <div class="flex-1 min-h-0 w-full relative bg-white dark:bg-zinc-950 overflow-x-auto hidden" id="editor-container-rubrics">
            <textarea id="editable-rubrics" name="rubrics_content"><?= $template ? ($template['rubrics_content'] ?? '') : '' ?></textarea>
        </div>
    <?= form_close() ?>

</div>

<script>
    function switchEditorTab(tab) {
        const targetTab = document.getElementById('tab-target');
        const rubricsTab = document.getElementById('tab-rubrics');
        const targetContainer = document.getElementById('editor-container-target');
        const rubricsContainer = document.getElementById('editor-container-rubrics');

        if (tab === 'target') {
            targetTab.classList.replace('border-transparent', 'border-accent');
            targetTab.classList.replace('text-text-muted', 'text-accent');
            rubricsTab.classList.replace('border-accent', 'border-transparent');
            rubricsTab.classList.replace('text-accent', 'text-text-muted');
            
            targetContainer.classList.remove('hidden');
            rubricsContainer.classList.add('hidden');
        } else {
            rubricsTab.classList.replace('border-transparent', 'border-accent');
            rubricsTab.classList.replace('text-text-muted', 'text-accent');
            targetTab.classList.replace('border-accent', 'border-transparent');
            targetTab.classList.replace('text-accent', 'text-text-muted');
            
            rubricsContainer.classList.remove('hidden');
            targetContainer.classList.add('hidden');
        }
    }
</script>

<script src="<?= base_url('assets/js/editor/functions.js?v=' . time()) ?>"></script>
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

    let savePromise = null;
    let saveStatusTimeout = null;
    let isClosing = false;

    function saveDocument(manualSave = true) {
        if (savePromise && !isClosing) return savePromise;

        savePromise = new Promise((resolve, reject) => {
            const editor = tinymce.get('editable-doc');
            const editorRubrics = tinymce.get('editable-rubrics');
            if (!editor && !editorRubrics) return resolve(); 

            const content = editor ? editor.getContent() : '';
            const rubricsContent = editorRubrics ? editorRubrics.getContent() : '';
            const title = document.querySelector('input[name="title"]').value.trim();
            const templateIdInput = document.querySelector('input[name="template_id"]');
            
            const saveStatus = document.getElementById('save-status');
            if (saveStatus && manualSave) {
                if (saveStatusTimeout) {
                    clearTimeout(saveStatusTimeout);
                    saveStatusTimeout = null;
                }
                saveStatus.innerText = '● Saving...';
                saveStatus.className = 'text-[10px] uppercase tracking-widest font-bold transition-all text-info-500 animate-pulse';
            }

            const formData = new FormData();
            formData.append('template_id', templateIdInput.value);
            formData.append('content', content);
            formData.append('rubrics_content', rubricsContent);
            formData.append('title', title);

            apiPost('<?= site_url('templates/store') ?>', formData, {
                onSuccess: (data) => { 
                    AppState.setDirty(false); 

                    // If it was a new template, save the ID so subsequent autosaves update it instead of creating a new one
                    if (data.template_id) {
                        templateIdInput.value = data.template_id;
                    }

                    if (saveStatus && manualSave) {
                        saveStatus.innerText = '✓ Saved';
                        saveStatus.className = 'text-[10px] uppercase tracking-widest font-bold transition-all text-success-500';

                        saveStatusTimeout = setTimeout(() => {
                            saveStatus.innerText = '';
                            saveStatus.className = '';
                            saveStatusTimeout = null;
                        }, 2000);
                    }
                    resolve(data);
                },
                onError: (errorMessage) => {
                    if (saveStatus && manualSave) {
                        saveStatus.innerText = '✗ Save Failed';
                        saveStatus.className = 'text-[10px] uppercase tracking-widest font-bold transition-all text-danger-500';
                    }
                    reject(new Error(errorMessage));
                },
                config: { keepalive: true }
            });
        }).finally(() => {
            savePromise = null;
        });

        return savePromise;
    }

    function autoSave() {
        setTimeout(async () => {
            if (AppState.isDirty && !savePromise) {
                try {
                    await saveDocument(false);
                } catch (error) {}
            }
            autoSave();
        }, 2000);
    }

    window.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            saveDocument();
        }
    });

    window.addEventListener('beforeunload', () => {
        isClosing = true;
        if (AppState.isDirty) {
            saveDocument(false);
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        initEditor();
        autoSave();
    });
</script>

<?= $this->endSection() ?>