<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="h-full flex flex-col bg-gray-50 dark:bg-zinc-950">
    
    <div class="flex-none flex items-center justify-between py-2 px-6 bg-white dark:bg-zinc-900 border-b border-gray-200 dark:border-zinc-800">
        <div class="flex items-center gap-4">
            <a href="<?= site_url('documents?docs=all') ?>" class="text-gray-400 hover:text-blue-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                </svg>
            </a>
            <input type="text" id="doc-title" value="<?= esc($doc['title']) ?>"
                class="bg-transparent border-none font-bold text-sm focus:ring-0 px-2"
                oninput="autoResize(this); AppState.setDirty(true);"
                onblur="restoreTitle(this, '<?= esc($doc['title']) ?>')">
            <span id="save-status" class="ml-3 text-[10px] uppercase tracking-widest font-bold transition-all"></span>
        </div>

        <div class="flex flex-row gap-2">
            <div class="flex items-center gap-3 bg-gray-50 dark:bg-zinc-800/50 px-3 py-1.5 rounded-full border border-gray-200 dark:border-zinc-700 shadow-sm">
                <div class="flex items-center gap-1.5 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-[10px] uppercase tracking-wider font-extrabold text-gray-500 dark:text-zinc-400">Period</span>
                </div>

                <div class="flex items-center">
                    <input 
                        type="datetime-local"   
                        id="doc-date-start" 
                        value="<?= $doc['eval_date_start'] 
                            ? date('Y-m-d\TH:i', strtotime($doc['eval_date_start'])) 
                            : date('Y-m-d\T12:00', strtotime($doc['created_at'])) ?>"
                        class="datetime-tight text-xs font-semibold bg-transparent border-none p-0 focus:ring-0 cursor-pointer text-blue-600 dark:text-blue-400 w-[145px]"
                        oninput="AppState.setDirty(true)"
                    >
                    <span class="text-gray-300 dark:text-zinc-600 text-xs px-1">—</span>
                    <input 
                        type="datetime-local" 
                        id="doc-date-end" 
                        value="<?= $doc['eval_date_end'] 
                            ? date('Y-m-d\TH:i', strtotime($doc['eval_date_end']))
                            : date('Y-m-d\T12:00', strtotime('+1 day', strtotime($doc['created_at']))) ?>"
                        class="datetime-tight text-xs font-semibold bg-transparent border-none p-0 focus:ring-0 cursor-pointer text-blue-600 dark:text-blue-400 w-[145px]"
                        oninput="AppState.setDirty(true)"
                    >
                </div>
            </div>

            <?= form_open(site_url('submission'), ['id' => 'submit-form']) ?>
                <input type="hidden" name="doc_id" value="<?= $doc['id'] ?>">
                <button type="button" onclick="prepareAndSubmit()" class="bg-blue-600 text-white text-xs font-bold py-2 px-4 rounded shadow-sm">
                    Submit IPCR
                </button>
            <?= form_close() ?>
        </div>
    </div> 

    <div class="flex-1 min-h-0 w-full relative">
        <textarea id="editable-doc" class="h-full w-full"><?= $doc['content'] ?></textarea>
    </div>

</div>

<script src="<?= base_url('assets/editor/js/functions.js') ?>"></script>
<script src="<?= base_url('assets/editor/js/saveFunctions.js') ?>"></script>

<script>
    const AppConfig = {
        editorCss: '<?= base_url('assets/editor/css/editor.css') ?>',
        ciDebug: <?= (ENVIRONMENT === 'development') ? 'true' : 'false' ?>,
        csrfToken: '<?= csrf_token() ?>',
        csrfHash: '<?= csrf_hash() ?>',
        saveUrl: '<?= site_url('document') ?>',
        docId: '<?= $doc['id'] ?>'
    };

    const AppState = {
        isDirty: false,
        setDirty(val) {
            this.isDirty = val;
            console.log(val ? 'Unsaved changes' : 'All saved');
        }
};
    document.addEventListener('DOMContentLoaded', () => {
        autoSave();

        const el = document.getElementById('doc-title');
        autoResize(el);
    });

    window.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            saveDocument();
        }
    });

    window.addEventListener('beforeunload', (event) => {
        if (AppState.isDirty) {
            const message = "You have unsaved changes. Are you sure you want to leave?";
            event.preventDefault();
            event.returnValue = message; 
            return message;
        }
    });
</script>

<script src="<?= base_url('assets/editor/js/TableTools.js') ?>"></script>
<script src="<?= base_url('assets/editor/js/editor.js') ?>"></script>

<script>
    initEditor();
</script>

<?= $this->endSection() ?>