<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="h-screen flex flex-col bg-gray-50 dark:bg-zinc-950 overflow-hidden">
    
    <div class="flex-none flex flex-row items-center justify-between py-2 px-6 bg-white dark:bg-zinc-900 border-b border-gray-200 dark:border-zinc-800 shadow-sm z-10">
        
        <div class="flex items-center gap-4">
            <a href="<?= site_url('submissions?docs=all') ?>" class="text-gray-500 hover:text-blue-600 transition-colors p-1" title="Back to Submissions">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
            </a>

            <div class="flex items-center gap-3">
                <input type="text" id="doc-title" value="<?= esc($doc['title']) ?>"
                    class="text-sm font-bold bg-transparent border-none focus:ring-0 pointer-events-none"
                    readonly>
                
                <div class="h-4 w-[1px] bg-gray-300 dark:bg-zinc-700"></div>
                
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Rating: <span id="rating" class="font-mono text-blue-600 dark:text-blue-400 font-bold">0%</span>
                </p>

                <span id="save-status" class="ml-3 text-[10px] uppercase tracking-widest font-bold transition-all"></span>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <h2 class="hidden md:block text-xs font-bold text-gray-400 uppercase mr-2">Evaluation Matrix</h2>
            <button onclick="calculateAllTables()" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold py-2 px-4 rounded-md shadow-sm transition-transform active:scale-95 flex items-center gap-2">
                <span>🧮</span> Calculate All Tables
            </button>
        </div>

        <div class="flex flex-row gap-2">
            <?php if (!$doc['is_rated']): ?>
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
                            value="<?= date('Y-m-d\TH:i', strtotime($doc['eval_date_start'])) ?>"
                            class="datetime-tight text-xs font-semibold bg-transparent border-none p-0 focus:ring-0 cursor-pointer text-blue-600 dark:text-blue-400 w-[145px]"
                            oninput="AppState.setDirty(true)"
                            readonly
                        >
                        <span class="text-gray-300 dark:text-zinc-600 text-xs px-1">—</span>
                        <input 
                            type="datetime-local" 
                            id="doc-date-end" 
                            value="<?= date('Y-m-d\TH:i', strtotime($doc['eval_date_end'])) ?>"
                            class="datetime-tight text-xs font-semibold bg-transparent border-none p-0 focus:ring-0 cursor-pointer text-blue-600 dark:text-blue-400 w-[145px]"
                            oninput="AppState.setDirty(true)"
                            readonly
                        >
                    </div>
                </div>
                <?= form_open(site_url('submission/rated'), ['id' => 'submit-form']) ?>
                    <input type="hidden" name="_method" value="PATCH">
                    <input type="hidden" name="doc_id" value="<?= $doc['id'] ?>">
                    <button type="button" onclick="prepareAndSubmit();" class="bg-blue-600 text-white text-xs font-bold py-2 px-4 rounded shadow-sm">
                        Submit Rating
                    </button>
                <?= form_close() ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex-1 bg-gray-100 dark:bg-zinc-950 overflow-hidden relative">
        <textarea id="editable-doc" class="h-full w-full"><?= $doc['content'] ?? '' ?></textarea>
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
        saveUrl: '<?= site_url('submission') ?>',
        docId: '<?= $doc['id'] ?>'
    };

    const AppState = {
        isDirty: false,
        setDirty(val) {
            this.isDirty = val;
            console.log(val ? 'Unsaved changes' : 'All saved');
        }
};

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
    initPlainEditor(<?= $doc['is_rated'] ?>);
</script>

<?= $this->endSection() ?>