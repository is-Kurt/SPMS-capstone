<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="h-full flex flex-col bg-bg">
    
    <div class="flex-none flex items-center justify-between py-2 px-6 bg-bg">
        <div class="flex items-center gap-3">
            <a href="<?= site_url('documents?docs=all') ?>" class="text-text hover:text-accent transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                </svg>
            </a>

            <input type="text" id="doc-title" value="<?= esc($doc['title']) ?>"
                class="bg-transparent border-none font-bold text-sm text-text focus:ring-0 px-2 py-1"
                oninput="autoResize(this); AppState.setDirty(true);"
                onblur="restoreTitle(this, '<?= esc($doc['title']) ?>')">

            <span id="save-status" class="ml-3 text-[10px] uppercase tracking-widest font-bold transition-all"></span>
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
                        <span id="display-date-start" class="text-xs font-semibold text-text-muted group-hover:text-accent transition-colors cursor-pointer">
                            <?= $doc['eval_date_start'] 
                                ? date('F j, Y g:ia', strtotime($doc['eval_date_start'])) 
                                : date('F j, Y', strtotime($doc['created_at'])) . ' 12:00am' ?>
                        </span>
                        <input type="datetime-local" id="doc-date-start" 
                            value="<?= $doc['eval_date_start'] 
                                ? date('Y-m-d\TH:i', strtotime($doc['eval_date_start'])) 
                                : date('Y-m-d', strtotime($doc['created_at'])) . 'T00:00' ?>"
                            class="datetime-tight absolute inset-0 opacity-0 cursor-pointer w-full"
                            oninput="updateDisplayDate(this, 'display-date-start'); AppState.setDirty(true)"
                            onclick="this.showPicker()">
                    </div>

                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>

                    <div class="relative group">
                        <span id="display-date-end" class="text-xs font-semibold text-text-muted group-hover:text-accent transition-colors cursor-pointer">
                            <?= $doc['eval_date_end']
                                ? date('F j, Y g:ia', strtotime($doc['eval_date_end'])) 
                                : date('F j, Y', strtotime('+1 day', strtotime($doc['created_at']))) . ' 12:00am' ?>
                        </span>
                        <input type="datetime-local" id="doc-date-end" 
                            value="<?= $doc['eval_date_end'] 
                                ? date('Y-m-d\TH:i', strtotime($doc['eval_date_end'])) 
                                : date('Y-m-d', strtotime('+1 day', strtotime($doc['created_at']))) . 'T00:00' ?>"
                            min="<?= $doc['eval_date_start'] 
                                ? date('Y-m-d\TH:i', strtotime($doc['eval_date_start'])) 
                                : date('Y-m-d', strtotime($doc['created_at'])) . 'T00:00' ?>"
                            class="datetime-tight absolute inset-0 opacity-0 cursor-pointer w-full"
                            oninput="updateDisplayDate(this, 'display-date-end'); AppState.setDirty(true)"
                            onclick="this.showPicker()">
                    </div>
                </div>
            </div>

            <button type="button" onclick="prepareAndSubmit()" class="bg-accent hover:bg-accent-hover text-accent-text text-s font-bold py-2 px-12 rounded-lg shadow-sm transition-all cursor-pointer">
                Submit
            </button>
        </div>
    </div> 

    <div class="flex-1 min-h-0 w-full relative">
        <textarea id="editable-doc"><?= $doc['content'] ?></textarea>
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
        submitUrl: '<?= site_url('submission') ?>',
        redirectUrl: '<?= site_url('submissions') ?>',
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
        // autoSave();

        const el = document.getElementById('doc-title');
        autoResize(el);
    });

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
    initEditor();
</script>

<?= $this->endSection() ?>