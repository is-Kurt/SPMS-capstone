<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $isLocked = strtotime($doc['eval_date_start']) > time() ? false : true?>
<?php $isEditable = ($doc['is_rated'] || strtotime($doc['eval_date_end']) < time() || !$isLocked) ? true : false ?>

<div class="h-screen flex flex-col bg-gray-50 dark:bg-zinc-950 overflow-hidden">
    
    <div class="flex-none flex items-center justify-between py-3 px-6 bg-white dark:bg-zinc-900 border-b border-gray-200 dark:border-zinc-800 shadow-sm z-10">
        
        <div class="flex items-center gap-4">
            <a href="<?= site_url('submissions') ?>">
                <div class="flex-shrink-0 flex items-center gap-1 mr-6 text-white hover:text-accent transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="font-black tracking-tighter text-xl uppercase">IPCR</span>
                </div>
            </a>

            <div class="flex items-center gap-4">
                <input type="text" id="doc-title" value="<?= esc($doc['title']) ?>"
                    class="text-sm font-black text-text bg-transparent border-none focus:ring-0 pointer-events-none p-0"
                    readonly>
                
                <div class="h-5 w-[1px] bg-zinc-200 dark:bg-zinc-700"></div>
                
                <div class="flex items-center gap-2 bg-accent/10 px-3 py-1 rounded-full border border-accent/20">
                    <p class="text-[10px] font-black text-accent uppercase tracking-widest">
                        Rating: <span id="rating" class="font-mono text-accent text-xs">0%</span>
                    </p>
                </div>

                <span id="save-status" class="ml-2 text-[10px] uppercase tracking-widest font-bold text-text-muted transition-all"></span>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div class="flex items-center bg-surface rounded-lg px-3 py-1 border border-surface-border">
                <!-- Date icon -->
                <div class="flex items-center pr-3 mr-3 border-r-2 border-surface-border text-text">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative group">
                        <span id="display-date-start" class="text-xs font-semibold text-text-muted transition-colors">
                            <?= $doc['eval_date_start'] 
                                ? date('F j, Y g:ia', strtotime($doc['eval_date_start'])) 
                                : date('F j, Y', strtotime($doc['created_at'])) . ' 12:00am' ?>
                        </span>
                    </div>

                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>

                    <div class="relative group">
                        <span id="display-date-end" class="text-xs font-semibold text-text-muted transition-colors">
                            <?= $doc['eval_date_end']
                                ? date('F j, Y g:ia', strtotime($doc['eval_date_end'])) 
                                : date('F j, Y', strtotime('+1 day', strtotime($doc['created_at']))) . ' 12:00am' ?>
                        </span>
                    </div>
                </div>
            </div>

            <?php if(!$isEditable): ?>
                <button type="button" onclick="
                        calculateAllTables();
                        prepareAndSubmit(); 
                        initPlainEditor(true);
                        disableSubmitBtn('Evaluated');
                        " id="submit-btn";
                        class="bg-accent hover:bg-accent-hover text-white text-xs font-bold py-2.5 px-6 rounded-lg shadow-lg shadow-accent/20 transition-all active:scale-[0.98] cursor-pointer">
                    Submit Rating
                </button>
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
        saveUrl: '<?= site_url('submission') ?>',
        submitUrl: '<?= site_url('submission/rated') ?>',
        docId: '<?= $doc['id'] ?>',
        csrfToken: '<?= csrf_token() ?>',
        csrfHash: '<?= csrf_hash() ?>',
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

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden' && AppState.isDirty) {
            saveOnExit();
        }
    });

    window.addEventListener('beforeunload', () => {
        if (AppState.isDirty) {
            saveOnExit();
        }
    });
</script>

<script src="<?= base_url('assets/editor/js/tinyMcePlugins.js') ?>"></script>
<script src="<?= base_url('assets/editor/js/TableTools.js') ?>"></script>
<script src="<?= base_url('assets/editor/js/editor.js') ?>"></script>

<script>
    initPlainEditor(<?= $isEditable ?>);
</script>

<?= $this->endSection() ?>