<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>    

<div class="h-full flex flex-col bg-bg">
    
    <div class="flex-none flex items-center justify-between py-2 px-6 bg-bg">
    <div class="flex items-center gap-3">
        <?php 
            $backLink = $isGuide ? site_url('folders') : site_url('folders?folder_id=' . $doc['document_folder_id']);
        ?>
        <a href="<?= $backLink ?>">
            <div class="flex-shrink-0 flex items-center gap-1 mr-6 text-white hover:text-accent transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="font-black tracking-tighter text-xl uppercase">IPCR</span>
            </div>
        </a>

            <input type="text"  maxlength="100" id="doc-title" value="<?= esc($doc['title']) ?>"
                class="bg-transparent border-none font-bold text-sm text-text focus:ring-0 px-2 py-1"
                oninput="autoResize(this); AppState.setDirty(true);"
                onblur="restoreTitle(this, '<?= esc($doc['title']) ?>')">

            <span id="save-status" class="ml-3 text-[10px] uppercase tracking-widest font-bold transition-all"></span>
        </div>

        <div class="flex items-center gap-4">
            <div class="flex items-center bg-surface rounded-lg px-3 py-1 border border-surface-border">
                <div class="flex items-center pr-3 mr-3 border-r-2 border-surface-border text-text">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative group">
                        <span id="display-date-start" class="text-xs font-semibold text-text-muted <?php if (session()->get('role') === 'Admin'): ?> group-hover:text-accent transition-colors cursor-pointer <?php endif; ?>">
                            <?= date('F j, Y g:ia', strtotime($doc['eval_date_start'])) ?>
                        </span>
                        <?php  // if (session()->get('role') === 'Admin'): ?>
                        <input type="datetime-local" id="doc-date-start" 
                            value="<?= $doc['eval_date_start'] ?>"
                            class="dark:[color-scheme:dark] datetime-tight absolute inset-0 opacity-0 cursor-pointer w-full"
                            oninput="updateDisplayDate(this, 'display-date-start'); AppState.setDirty(true)"
                            onclick="this.showPicker()">
                        <?php // endif ?>
                    </div>

                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>

                    <div class="relative group">
                        <span id="display-date-end" class="text-xs font-semibold text-text-muted <?php if (session()->get('role') === 'Admin'): ?> group-hover:text-accent transition-colors cursor-pointer <?php endif; ?>">
                            <?= date('F j, Y g:ia', strtotime($doc['eval_date_end'])) ?>
                        </span>
                        <?php // if (session()->get('role') === 'Admin'): ?>
                        <input type="datetime-local" id="doc-date-end" 
                            value="<?= $doc['eval_date_end'] ?>"
                            min="<?= $doc['eval_date_start'] ?>"
                            class="datetime-tight absolute inset-0 opacity-0 cursor-pointer w-full"
                            oninput="updateDisplayDate(this, 'display-date-end'); AppState.setDirty(true)"
                            onclick="this.showPicker()">
                        <?php // endif ?>
                    </div>
                </div>
            </div>

            <?php if (!$isGuide): ?>
                <?php
                    $isEvaluated = $doc['status'] === 'evaluated';
                    $isSubmitted = $doc['status'] === 'submitted';
                    
                    // Time Lock Logic
                    $currentTime = time();
                    $evalStartTime = strtotime($doc['eval_date_start']);
                    $isEvaluationPhase = ($currentTime >= $evalStartTime);
                ?>

                <?php if ($isEvaluated): ?>
                    <button type="button" disabled class="bg-emerald-500 text-white text-xs font-bold py-2.5 px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                        Evaluated (<?= number_format($doc['final_rating'], 3) ?>) ✓
                    </button>
                    
                <?php elseif ($isEvaluationPhase): ?>
                    <button id="btn-submit" type="button" 
                            onclick="saveWith({ before: () => calculateAllTables(), after: () => evaluateDocument() })" 
                            class="bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold py-2.5 px-6 rounded-lg shadow-lg shadow-amber-500/20 transition-all active:scale-[0.98] cursor-pointer">
                        Evaluate & Lock
                    </button>

                <?php else: ?>
                    <?php 
                        $btnText = $isSubmitted ? 'Unsubmit' : 'Submit';
                        $btnColor = $isSubmitted ? 'bg-zinc-600 hover:bg-zinc-700' : 'bg-accent hover:bg-accent-hover';
                        $action = $isSubmitted ? 'unsubmit' : 'submit';
                    ?>
                    <button id="btn-submit" type="button" 
                            onclick="saveWith({ after: () => toggleSubmit('<?= $action ?>') })" 
                            class="<?= $btnColor ?> text-white text-xs font-bold py-2.5 px-6 rounded-lg shadow-lg transition-all active:scale-[0.98] cursor-pointer">
                        <?= $btnText ?>
                    </button>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 text-blue-600 dark:text-blue-400 text-[10px] uppercase tracking-widest font-black py-2.5 px-4 rounded-lg shadow-sm cursor-default flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Guide Template
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="flex-1 min-h-0 w-full relative">
        <textarea id="editable-doc"><?= $doc['content'] ?></textarea>
    </div>

</div>

<script src="<?= base_url('assets/js/editor/functions.js') ?>"></script>
<script src="<?= base_url('assets/js/editor/saveDocument.js') ?>"></script>

<script>
    const AppConfig = {
        editorCss: '<?= base_url('assets/css/editor/style.css') ?>',
        ciDebug: <?= (ENVIRONMENT === 'development') ? 'true' : 'false' ?>,
        baseUrl: '<?= site_url('document') ?>',
        docId: '<?= $doc['id'] ?>'
    };

    const AppState = {
        isDirty: false,
        setDirty(val) {
            this.isDirty = val;
        }
    };
    
    document.addEventListener('DOMContentLoaded', () => {
        autoSave();
        
        const el = document.getElementById('doc-title');
        autoResize(el);
    });

</script>

<script src="<?= base_url('assets/js/editor/plugins.js') ?>"></script>
<script src="<?= base_url('assets/js/editor/TableTools.js') ?>"></script>
<script src="<?= base_url('assets/js/editor/config.js') ?>"></script>

<script>
    // Safely check if this is a Guide template using our new flag
    const isGuide = <?= json_encode($isGuide) ?>;

    const isEvaluated = <?= json_encode($doc['status'] === 'evaluated') ?>;
    const isSubmitted = <?= json_encode($doc['status'] === 'submitted') ?>;
    const isEvaluationPhase = <?= json_encode(time() >= strtotime($doc['eval_date_start'])) ?>;

    // Logic: If it's a guide, OR past the drafting phase, use the plain editor
    if (isGuide || isEvaluated || isEvaluationPhase || isSubmitted) {
        initPlainEditor(isGuide || isEvaluated);
    } else {
        initEditor();
    }
</script>

<?= $this->endSection() ?>