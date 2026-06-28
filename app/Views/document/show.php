<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php 
    use App\Enums\FolderStatus;

    $status = $doc['folder_status']; 
    $isOwner = ($doc['owner_id'] == session()->get('user_id'));

?>

<div class="h-full flex flex-col bg-bg">
    
    <div class="flex-none flex items-center justify-between py-2 px-3 sm:px-6 bg-bg border-b border-surface-border shadow-sm gap-2 sm:gap-4">
        
        <div class="flex items-center gap-1 sm:gap-3 min-w-0 flex-1">
            <a href="javascript:history.back()" class="cursor-pointer shrink-0">
                <div class="flex-shrink-0 flex items-center gap-1 mr-2 sm:mr-6 text-white hover:text-accent transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 sm:h-7 sm:w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="hidden sm:block font-black tracking-tighter text-xl uppercase">IPCR</span>
                </div>
            </a>

            <input type="text" maxlength="100" id="doc-title" value="<?= esc($doc['title']) ?>"
                class="bg-transparent border-none font-bold text-sm text-text focus:ring-0 px-1 sm:px-2 py-1 min-w-0 w-full truncate"
                oninput="autoResize(this); AppState.setDirty(true);"
                onblur="restoreTitle(this, '<?= esc($doc['title']) ?>')"
                <?= $status === FolderStatus::SUBMITTED->value ? 'disabled' : '' ?>>

            <span id="save-status" class="ml-1 sm:ml-3 shrink-0 text-[10px] uppercase tracking-widest font-bold transition-all"></span>
        </div>
        
        <div class="flex items-center gap-2 sm:gap-4 shrink-0">
            <?php if (!$isGuide): ?>
                <?php if ($status === FolderStatus::APPROVED->value): ?>
                    <button type="button" disabled class="bg-emerald-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                        <span class="hidden sm:inline">Folder </span>Approved ✓
                    </button>
                    
                <?php elseif ($status === FolderStatus::EVALUATED->value): ?>
                    <?php if ($isOwner): ?>
                        <button type="button" disabled class="bg-amber-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                            <span class="hidden sm:inline">Awaiting </span>Approval
                        </button>
                    <?php else: ?>
                        <?php if (isset($routingStatus) && $routingStatus === FolderStatus::APPROVED->value): ?>
                            <button type="button" disabled class="bg-emerald-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                                Approved ✓
                            </button>
                        <?php else: ?>
                            <div class="flex gap-1.5 sm:gap-2">
                                <button id="btn-return" type="button" 
                                        onclick="saveWith({ after: () => returnFolderRevision() })" 
                                        class="bg-orange-500 hover:bg-orange-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg shadow-orange-500/20 transition-all active:scale-[0.98] cursor-pointer">
                                    Return<span class="hidden sm:inline"> for Revision</span>
                                </button>
                                <button id="btn-approve" type="button" 
                                        onclick="saveWith({ before: () => rate(), after: () => approveFolderEvaluation() })" 
                                        class="bg-emerald-500 hover:bg-emerald-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg shadow-emerald-500/20 transition-all active:scale-[0.98] cursor-pointer">
                                    Approve<span class="hidden sm:inline"> Rating</span>
                                </button>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                <?php elseif ($status === FolderStatus::TO_EVALUATE->value || $status === FolderStatus::REEVALUATE->value): ?>
                    <?php if ($isOwner): ?>
                        <button id="btn-submit" type="button" 
                                onclick="saveWith({ before: () => rate(), after: () => lockFolderEvaluation() })" 
                                class="bg-blue-500 hover:bg-blue-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg shadow-blue-500/20 transition-all active:scale-[0.98] cursor-pointer">
                            <?= $status === FolderStatus::REEVALUATE->value ? 'Submit Revision' : '<span class="sm:hidden">Self-Rate</span><span class="hidden sm:inline">Complete Self-Rating</span>' ?>
                        </button>
                    <?php else: ?>
                        <button type="button" disabled class="bg-zinc-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                            Wait<span class="hidden sm:inline">ing for Employee</span>
                        </button>
                    <?php endif; ?>

                <?php elseif ($status === FolderStatus::UNEVALUATED->value): ?>
                    <button type="button" disabled class="bg-red-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                        Missed Deadline
                    </button>
                    
                <?php elseif ($status === FolderStatus::SUBMITTED->value): ?>
                    <button type="button" disabled class="bg-indigo-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                        Awaiting Eval<span class="hidden sm:inline"> Window</span>
                    </button>

                <?php else: ?>
                    <button type="button" disabled class="bg-zinc-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                        Drafting
                    </button>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 text-blue-600 dark:text-blue-400 text-[10px] uppercase tracking-widest font-black py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg shadow-sm cursor-default flex items-center gap-1.5">
                    Guide<span class="hidden sm:inline"> Template</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex-1 min-h-0 w-full relative">
        <textarea id="editable-doc"><?= $doc['content'] ?></textarea>
    </div>

</div> <script src="<?= base_url('assets/js/editor/functions.js') ?>"></script>
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
        if (el) autoResize(el);
    });

    function lockFolderEvaluation() {
        const editorBody = tinymce.get('editable-doc').getBody();
        const finalScore = editorBody.getAttribute('data-final-score');
        
        if (!finalScore || isNaN(parseFloat(finalScore))) {
            alert("Could not extract a valid final score. Please fill out the tables.");
            return;
        }

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');
        formData.append('final_rating', finalScore);

        document.getElementById('btn-submit').innerText = 'Locking...';
        apiPost('<?= site_url('folder/evaluate') ?>', formData, {
            onSuccess: () => window.location.reload()
        });
    }

    function approveFolderEvaluation() {
        const editorBody = tinymce.get('editable-doc').getBody();
        const finalScore = editorBody.getAttribute('data-final-score');
        
        if (!finalScore || isNaN(parseFloat(finalScore))) {
            alert("Could not extract a valid final score."); return;
        }

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');
        formData.append('final_rating', finalScore);

        document.getElementById('btn-approve').innerText = 'Approving...';
        apiPost('<?= site_url('folder/approve') ?>', formData, {
            onSuccess: () => window.location.reload()
        });
    }

    function returnFolderRevision() {
        if(!confirm("Return this to the employee for revision?")) return;
        
        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');

        document.getElementById('btn-return').innerText = 'Returning...';
        apiPost('<?= site_url('folder/return') ?>', formData, {
            onSuccess: () => window.location.reload()
        });
    }
</script>

<script src="<?= base_url('assets/js/editor/plugins.js') ?>"></script>
<script src="<?= base_url('assets/js/editor/TableTools.js') ?>"></script>
<script src="<?= base_url('assets/js/editor/config.js') ?>"></script>

<script>
    const isGuide = <?= json_encode($isGuide) ?>;
    const status = <?= json_encode($doc['folder_status']) ?>;
    const isOwner = <?= json_encode($doc['owner_id'] == session()->get('user_id')) ?>;

    // Enum values exported for JS use
    const FolderStatus = <?= json_encode([
        'DRAFT'       => \App\Enums\FolderStatus::DRAFT->value,
        'SUBMITTED'   => \App\Enums\FolderStatus::SUBMITTED->value,
        'TO_EVALUATE' => \App\Enums\FolderStatus::TO_EVALUATE->value,
        'EVALUATED'   => \App\Enums\FolderStatus::EVALUATED->value,
        'APPROVED'    => \App\Enums\FolderStatus::APPROVED->value,
        'REEVALUATE'  => \App\Enums\FolderStatus::REEVALUATE->value,
        'UNEVALUATED' => \App\Enums\FolderStatus::UNEVALUATED->value,
    ]) ?>;

    let isFullyLocked = true;

    if (!isGuide) {
        if ((status === FolderStatus.TO_EVALUATE || status === FolderStatus.REEVALUATE) && isOwner) {
            isFullyLocked = false;
        } else if (status === FolderStatus.EVALUATED && !isOwner) {
            isFullyLocked = false;
        }
    }

    if (status === FolderStatus.DRAFT) {
        initEditor();
    } else {
        initPlainEditor(isFullyLocked);
    }
</script>

<?= $this->endSection() ?>