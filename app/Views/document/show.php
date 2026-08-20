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
                <!-- Back-to-folders brand mark. text-text (not text-white) so it stays visible on the
                     theme-aware bg-bg header in both light and dark mode. -->
                <div class="flex-shrink-0 flex items-center gap-1 mr-2 sm:mr-6 text-text hover:text-accent transition-colors">
                    <!-- Folder/document icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 sm:h-7 sm:w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="hidden sm:block font-black tracking-tighter text-xl uppercase">SPMS</span>
                </div>
            </a>

            <input type="text" maxlength="100" id="doc-title" value="<?= esc($doc['title']) ?>"
                class="bg-transparent border-none font-bold text-sm text-text focus:ring-0 px-1 sm:px-2 py-1 min-w-0 w-full truncate"
                oninput="AppState.setDirty(true);"
                onblur="restoreTitle(this, '<?= esc($doc['title']) ?>')"
                <?= ($status !== FolderStatus::DRAFT->value || !$isOwner) ? 'disabled' : '' ?>>

            <span id="save-status" class="ml-1 sm:ml-3 shrink-0 text-[10px] uppercase tracking-widest font-bold transition-all"></span>
        </div>
        
        <div class="flex items-center gap-2 sm:gap-4 shrink-0">
            <?php if (!$isGuide): ?>
                <?php if ($doc['is_target'] == 0 && !in_array($status, [FolderStatus::DRAFT->value, FolderStatus::REEVALUATE->value])): ?>
                    <button type="button" disabled class="bg-zinc-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                        Supporting<span class="hidden sm:inline"> Evidence</span>
                    </button>

                <?php elseif ($status === FolderStatus::APPROVED->value): ?>
                    <button type="button" disabled class="bg-success-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                        <span class="hidden sm:inline">Folder </span>Approved ✓
                    </button>
                    
                <?php elseif ($status === FolderStatus::EVALUATED->value): ?>
                    <?php if ($isOwner): ?>
                        <button type="button" disabled class="bg-warning-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                            <span class="hidden sm:inline">Awaiting </span>Approval
                        </button>
                    <?php else: ?>
                        
                        <?php if (session()->get('role') === 'Admin'): ?>
                            <button type="button" disabled class="bg-highlight-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                                Monitoring<span class="hidden sm:inline"> View</span>
                            </button>
                            
                        <?php elseif (isset($routingStatus) && $routingStatus === FolderStatus::APPROVED->value): ?>
                            <button type="button" disabled class="bg-success-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                                Approved ✓
                            </button>
                        <?php else: ?>
                            <div class="flex gap-1.5 sm:gap-2">
                                <button id="btn-return" type="button" 
                                        onclick="saveWith({ after: () => returnFolderRevision() })" 
                                        class="bg-revision-500 hover:bg-revision-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg shadow-revision-500/20 transition-all active:scale-[0.98] cursor-pointer">
                                    Return<span class="hidden sm:inline"> for Revision</span>
                                </button>
                                <button id="btn-approve" type="button" 
                                        onclick="saveWith({ before: () => rate(), after: () => approveFolderEvaluation() })" 
                                        class="bg-success-500 hover:bg-success-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg shadow-success-500/20 transition-all active:scale-[0.98] cursor-pointer">
                                    Approve<span class="hidden sm:inline"> Rating</span>
                                </button>
                            </div>
                        <?php endif; ?>
                        
                    <?php endif; ?>

                <?php elseif ($status === FolderStatus::TO_EVALUATE->value || $status === FolderStatus::REEVALUATE->value): ?>
                    <?php if ($isOwner): ?>
                        <button id="btn-submit" type="button" 
                                onclick="saveWith({ before: () => rate(), after: () => lockFolderEvaluation() })" 
                                class="bg-info-500 hover:bg-info-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg shadow-info-500/20 transition-all active:scale-[0.98] cursor-pointer">
                            <?= $status === FolderStatus::REEVALUATE->value ? 'Submit Revision' : '<span class="sm:hidden">Self-Rate</span><span class="hidden sm:inline">Complete Self-Rating</span>' ?>
                        </button>
                    <?php else: ?>
                        <button type="button" disabled class="bg-zinc-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                            Wait<span class="hidden sm:inline">ing for Employee</span>
                        </button>
                    <?php endif; ?>

                <?php elseif ($status === FolderStatus::UNEVALUATED->value): ?>
                    <button type="button" disabled class="bg-danger-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                        Missed Deadline
                    </button>
                    
                <?php elseif ($status === FolderStatus::DRAFT_TARGET->value): ?>
                    <?php if ($isOwner): ?>
                        <button id="btn-submit-target" type="button" 
                                onclick="saveWith({ after: () => lockFolderTarget() })" 
                                class="bg-info-500 hover:bg-info-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg shadow-info-500/20 transition-all active:scale-[0.98] cursor-pointer">
                            <span class="sm:hidden">Submit Target</span><span class="hidden sm:inline">Submit Targets</span>
                        </button>
                    <?php else: ?>
                        <button type="button" disabled class="bg-zinc-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                            Wait<span class="hidden sm:inline">ing for Employee Targets</span>
                        </button>
                    <?php endif; ?>

                <?php elseif ($status === FolderStatus::PENDING_TARGET_APPROVAL->value): ?>
                    <?php if ($isOwner): ?>
                        <button type="button" disabled class="bg-warning-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                            <span class="hidden sm:inline">Awaiting </span>Target Approval
                        </button>
                    <?php else: ?>
                        <?php if (session()->get('role') === 'Admin'): ?>
                            <button type="button" disabled class="bg-highlight-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                                Monitoring<span class="hidden sm:inline"> View</span>
                            </button>
                        <?php else: ?>
                            <?php $isTargetPeriodEnded = !empty($doc['target_date_end']) && date('Y-m-d H:i:s') > $doc['target_date_end']; ?>
                            <div class="flex gap-1.5 sm:gap-2">
                                <button id="btn-return-target" type="button" 
                                        <?= $isTargetPeriodEnded ? 'disabled' : 'onclick="saveWith({ after: () => returnTargetRevision() })"' ?>
                                        class="<?= $isTargetPeriodEnded ? 'bg-revision-500/50 cursor-not-allowed opacity-80' : 'bg-revision-500 hover:bg-revision-600 shadow-revision-500/20 active:scale-[0.98] cursor-pointer' ?> text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg transition-all">
                                    Return<span class="hidden sm:inline"> Target for Revision</span>
                                </button>
                                <button id="btn-approve-target" type="button" 
                                        <?= $isTargetPeriodEnded ? 'disabled' : 'onclick="saveWith({ after: () => approveFolderTarget() })"' ?>
                                        class="<?= $isTargetPeriodEnded ? 'bg-success-500/50 cursor-not-allowed opacity-80' : 'bg-success-500 hover:bg-success-600 shadow-success-500/20 active:scale-[0.98] cursor-pointer' ?> text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg transition-all">
                                    Approve<span class="hidden sm:inline"> Target</span>
                                </button>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                <?php elseif ($status === FolderStatus::TARGET_APPROVED->value || $status === FolderStatus::SUBMITTED->value): ?>
                    <button type="button" disabled class="bg-highlight-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                        Awaiting Eval<span class="hidden sm:inline"> Window</span>
                    </button>
                <?php elseif ($status === \App\Enums\FolderStatus::TARGET_UNAPPROVED->value): ?>
                    <button type="button" disabled class="bg-revision-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                        Target<span class="hidden sm:inline"> Unapproved</span>
                    </button>

                <?php else: ?>
                    <button type="button" disabled class="bg-zinc-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                        Drafting
                    </button>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="bg-info-50 dark:bg-info-500/10 border border-info-200 dark:border-info-500/20 text-info-600 dark:text-info-400 text-[10px] uppercase tracking-widest font-black py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg shadow-sm cursor-default flex items-center gap-1.5">
                    Guide<span class="hidden sm:inline"> Template</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex-none flex bg-bg border-b border-surface-border px-3 sm:px-6 gap-6 text-sm font-bold pt-2">
        <button type="button" id="tab-target" class="pb-2 border-b-2 border-accent text-accent transition-colors" onclick="switchEditorTab('target')">Target Form</button>
        <button type="button" id="tab-rubrics" class="pb-2 border-b-2 border-transparent text-text-muted hover:text-text transition-colors" onclick="switchEditorTab('rubrics')">Rubrics / Guide</button>
    </div>

    <div class="flex-1 min-h-0 w-full relative bg-white dark:bg-zinc-950 overflow-x-auto" id="editor-container-target">
        <textarea id="editable-doc" name="content"><?= $doc['content'] ?></textarea>
    </div>

    <div class="flex-1 min-h-0 w-full relative bg-white dark:bg-zinc-950 overflow-x-auto hidden" id="editor-container-rubrics">
        <textarea id="editable-rubrics" name="rubrics_content"><?= $doc['rubrics_content'] ?? '' ?></textarea>
    </div>
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
</script> <script src="<?= base_url('assets/js/editor/functions.js?v=' . time()) ?>"></script>
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
    });

    async function lockFolderEvaluation() {
        const editorBody = tinymce.get('editable-doc').getBody();
        const finalScore = editorBody.getAttribute('data-final-score');

        if (!finalScore || isNaN(parseFloat(finalScore))) {
            await window.appAlert("Could not extract a valid final score. Please fill out the tables.");
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

    async function approveFolderEvaluation() {
        const editorBody = tinymce.get('editable-doc').getBody();
        const finalScore = editorBody.getAttribute('data-final-score');

        if (!finalScore || isNaN(parseFloat(finalScore))) {
            await window.appAlert("Could not extract a valid final score.");
            return;
        }

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');
        formData.append('final_rating', finalScore);

        document.getElementById('btn-approve').innerText = 'Approving...';
        apiPost('<?= site_url('folder/approve') ?>', formData, {
            onSuccess: () => window.location.reload()
        });
    }

    async function returnFolderRevision() {
        const ok = await window.appConfirm("Return this to the employee for revision?", { variant: 'warning', confirmText: 'Return' });
        if (!ok) return;
        
        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');

        document.getElementById('btn-return').innerText = 'Returning...';
        apiPost('<?= site_url('folder/return') ?>', formData, {
            onSuccess: () => window.location.reload()
        });
    }

    async function lockFolderTarget() {
        const ok = await window.appConfirm("Submit these targets for approval? You won't be able to edit them until returned.", { confirmText: 'Submit Targets' });
        if (!ok) return;
        
        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');

        document.getElementById('btn-submit-target').innerText = 'Submitting...';
        apiPost('<?= site_url('folder/submit_target') ?>', formData, {
            onSuccess: () => window.location.reload()
        });
    }

    async function approveFolderTarget() {
        const ok = await window.appConfirm("Approve these targets?", { confirmText: 'Approve' });
        if (!ok) return;

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');

        document.getElementById('btn-approve-target').innerText = 'Approving...';
        apiPost('<?= site_url('folder/approve_target') ?>', formData, {
            onSuccess: () => window.location.reload()
        });
    }

    async function returnTargetRevision() {
        const ok = await window.appConfirm("Return targets to the employee for revision?", { variant: 'warning', confirmText: 'Return' });
        if (!ok) return;
        
        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');

        document.getElementById('btn-return-target').innerText = 'Returning...';
        apiPost('<?= site_url('folder/return_target') ?>', formData, {
            onSuccess: () => window.location.reload()
        });
    }
</script>

<script src="<?= base_url('assets/js/editor/plugins.js') ?>"></script>
<script src="<?= base_url('assets/js/editor/TableTools.js') ?>"></script>
<script src="<?= base_url('assets/js/editor/config.js') ?>"></script>

<script>
    window.isGuide = <?= json_encode($isGuide) ?>;
    window.status = <?= json_encode($doc['folder_status']) ?>;
    window.isTarget = <?= json_encode($doc['is_target'] == 1) ?>;
    window.isOwner = <?= json_encode($doc['owner_id'] == session()->get('user_id')) ?>;

    // Enum values exported for JS use
    window.FolderStatus = <?= json_encode([
        'DRAFT_TARGET'            => \App\Enums\FolderStatus::DRAFT_TARGET->value,
        'PENDING_TARGET_APPROVAL' => \App\Enums\FolderStatus::PENDING_TARGET_APPROVAL->value,
        'TARGET_APPROVED'         => \App\Enums\FolderStatus::TARGET_APPROVED->value,
        'TARGET_RETURNED'         => \App\Enums\FolderStatus::TARGET_RETURNED->value,
        'DRAFT'                   => \App\Enums\FolderStatus::DRAFT->value,
        'SUBMITTED'               => \App\Enums\FolderStatus::SUBMITTED->value,
        'TO_EVALUATE'             => \App\Enums\FolderStatus::TO_EVALUATE->value,
        'EVALUATED'               => \App\Enums\FolderStatus::EVALUATED->value,
        'APPROVED'                => \App\Enums\FolderStatus::APPROVED->value,
        'REEVALUATE'              => \App\Enums\FolderStatus::REEVALUATE->value,
        'UNEVALUATED'             => \App\Enums\FolderStatus::UNEVALUATED->value,
    ]) ?>;

    let isFullyLocked = true;
    let useFullEditor = false;
    let useRemarksOnlyEditor = false;

    if (isGuide) {
        // Guide documents can only be edited by their owner (the Admin)
        isFullyLocked = !isOwner;
        useFullEditor = isOwner;
    } else {
        if (status === FolderStatus.DRAFT_TARGET || status === FolderStatus.TARGET_RETURNED) {
            isFullyLocked = !isOwner;
            useFullEditor = isOwner; // Target drafting gets the full editor to build tables
        } else if (status === FolderStatus.PENDING_TARGET_APPROVAL && !isOwner) {
            // Supervisor reviewing targets can only edit the remarks column
            isFullyLocked = !isTarget;
            useFullEditor = false;
            useRemarksOnlyEditor = isTarget; // Only allow editing remarks if it's the target document
        } else if (status === FolderStatus.DRAFT) {
            isFullyLocked = !isOwner;
            useFullEditor = false; // Eval drafting gets plain editor (structure locked)
        } else if ((status === FolderStatus.TO_EVALUATE || status === FolderStatus.REEVALUATE) && isOwner) {
            // Owner can only self-rate the TARGET document.
            isFullyLocked = !isTarget && status === FolderStatus.TO_EVALUATE;
            // REEVALUATE implies revision of eval, or revision of targets?
            // For now, if REEVALUATE, we give them the plain editor so they can fix their ratings/eval.
            // If they need to fix targets, they would need to be back in DRAFT_TARGET.
            useFullEditor = false; 
        } else if (status === FolderStatus.EVALUATED && !isOwner) {
            // Evaluator can only evaluate/rate the TARGET document.
            isFullyLocked = !isTarget;
            useFullEditor = false;
        }
    }

    if (useFullEditor) {
        initEditor();
    } else if (useRemarksOnlyEditor) {
        initRemarksOnlyEditor();
    } else {
        initPlainEditor(isFullyLocked);
    }
</script>

<?= $this->endSection() ?>