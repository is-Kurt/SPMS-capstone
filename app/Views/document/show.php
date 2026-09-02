<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php 
    use App\Enums\FolderStatus;

    $status = $doc['folder_status']; 
    $isOwner = ($doc['owner_id'] == session()->get('user_id'));
    
    $ownerDocType = strtolower($doc['doc_type'] ?? 'ipcr');
    $now = date('Y-m-d H:i:s');
    $targetEndCol = $ownerDocType . '_target_end';
    $tEnd = $doc[$targetEndCol] ?? null;
    $isPastTargetDate = (!empty($tEnd) && $now > $tEnd);

    $evalPhaseStatuses = [
        FolderStatus::SUBMITTED->value,
        FolderStatus::TO_EVALUATE->value,
        FolderStatus::REEVALUATE->value,
        FolderStatus::EVALUATED->value,
        FolderStatus::APPROVED->value,
        FolderStatus::TWG_APPROVED->value,
        FolderStatus::TWG_DISAPPROVED->value,
        FolderStatus::UNEVALUATED->value,
    ];

    $isEvaluationPhase = in_array($status, $evalPhaseStatuses) || $isPastTargetDate;
    $isTargetPhase = !$isEvaluationPhase;

    $canEditTargets = ($isOwner && in_array($status, [
        FolderStatus::DRAFT_TARGET->value,
        FolderStatus::TARGET_RETURNED->value,
        FolderStatus::TARGET_UNAPPROVED->value,
        FolderStatus::DRAFT->value
    ]) && !$isPastTargetDate && !$isGuide);

    $canEditEvaluation = ($isEvaluationPhase && (
        ($isOwner && in_array($status, [FolderStatus::TO_EVALUATE->value, FolderStatus::REEVALUATE->value])) ||
        (!$isOwner && isset($routingStatus) && in_array($status, [FolderStatus::SUBMITTED->value, FolderStatus::EVALUATED->value]))
    ) && !$isGuide);

    $editableStatuses = [
        FolderStatus::DRAFT_TARGET->value,
        FolderStatus::TARGET_RETURNED->value
    ];
    $isEditable = ($canEditTargets || $canEditEvaluation);
?>

<style>
    .spms-sheet-container {
        width: 100%;
        max-width: 1280px;
        background: #ffffff;
        color: #000000;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
        border: 1px solid #cbd5e1;
        padding: 36px 40px;
        box-sizing: border-box;
        font-family: inherit;
        display: block !important;
        height: auto !important;
        min-height: fit-content !important;
    }
    .spms-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #000000;
        font-size: 11px;
        height: auto !important;
        table-layout: auto !important;
        display: table !important;
    }
    .spms-table tbody {
        display: table-row-group !important;
        height: auto !important;
    }
    .spms-table th, .spms-table td {
        border: 1px solid #000000;
    }
    .spms-textarea {
        width: 100%;
        min-height: 48px;
        background: transparent;
        border: 1px solid transparent;
        padding: 4px;
        font-size: 11px;
        line-height: 1.35;
        color: #0f172a;
        resize: vertical;
        box-sizing: border-box;
        border-radius: 4px;
        font-family: inherit;
    }
    .spms-textarea:hover:not(:disabled) {
        border-color: #cbd5e1;
    }
    .spms-textarea:focus {
        border-color: #0284c7;
        background: #f8fafc;
        outline: none;
    }
    .spms-textarea:disabled {
        color: #475569;
        background: #f8fafc;
        border-color: #f1f5f9;
        cursor: not-allowed;
        resize: none;
    }
    .field-mfo:disabled {
        color: #0f172a !important;
        font-weight: 600;
        background: #fcfcfc;
    }
    .spms-score-input {
        width: 100%;
        text-align: center;
        font-weight: 800;
        font-size: 12px;
        color: #0f172a;
        background: #ffffff;
        border: 1px solid #94a3b8;
        border-radius: 4px;
        padding: 4px 2px;
        box-sizing: border-box;
    }
    .spms-score-input:focus {
        border-color: #0284c7;
        outline: none;
        background: #f0f9ff;
    }
    .spms-score-input:disabled {
        background: #f8fafc;
        border-color: #e2e8f0;
        color: #94a3b8;
        cursor: not-allowed;
    }
    .btn-add-dashed {
        display: block;
        width: 100%;
        background: #f8fafc;
        border: 1.5px dashed #0284c7;
        color: #0284c7;
        font-weight: 700;
        font-size: 12px;
        padding: 10px 16px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.15s;
        text-align: center;
    }
    .btn-add-dashed:hover {
        background: #f0f9ff;
        border-color: #0369a1;
        color: #0369a1;
    }
    .btn-del-row {
        color: #dc2626;
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .btn-del-row:hover {
        background: #fee2e2;
    }
    .subtotal-badge {
        font-size: 10px;
        font-weight: 800;
        background: #0284c7;
        color: #ffffff;
        padding: 3px 8px;
        border-radius: 12px;
        display: inline-block;
    }
    .spms-category-header {
        background: #e2e8f0;
        border-top: 2px solid #000000;
        border-bottom: 1px solid #000000;
    }
    @media print {
        @page {
            size: letter landscape;
            margin: 8mm 6mm;
        }
        html, body {
            background: #ffffff !important;
            color: #000000 !important;
            font-size: 10px !important;
            width: 100% !important;
            height: auto !important;
            overflow: visible !important;
        }
        header, nav, aside, .print-hide, #tab-bar, .tox {
            display: none !important;
        }
        main, #editor-container, #spms-form-workspace {
            padding: 0 !important;
            margin: 0 !important;
            background: #ffffff !important;
            overflow: visible !important;
            display: block !important;
            width: 100% !important;
            height: auto !important;
        }
        .spms-sheet-container {
            box-shadow: none !important;
            border: none !important;
            max-width: 100% !important;
            width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
            display: block !important;
        }
        .spms-table {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 9.5px !important;
            page-break-inside: auto;
        }
        .spms-table tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .spms-table thead {
            display: table-header-group !important;
        }
        .spms-table th, .spms-table td {
            border: 1px solid #000000 !important;
            color: #000000 !important;
        }
        .spms-textarea, .spms-score-input, input {
            border: none !important;
            background: transparent !important;
            color: #000000 !important;
            resize: none !important;
            box-shadow: none !important;
            padding: 2px !important;
        }
        .spms-textarea::placeholder, .spms-score-input::placeholder, input::placeholder {
            color: transparent !important;
        }
        .btn-add-dashed, .btn-del-row, #tfoot-add-core, #tfoot-add-strategic, #tfoot-add-support {
            display: none !important;
        }
        .spms-textarea {
            overflow: hidden !important;
            height: auto !important;
        }
    }
</style>

<div class="h-full flex flex-col bg-bg">
    
    <div class="flex-none flex items-center justify-between py-2 px-3 sm:px-6 bg-bg gap-2 sm:gap-4 print-hide">
        
        <div class="flex items-center gap-1 sm:gap-3 min-w-0 flex-1">
            <!-- Return to Folder Button -->
            <a href="<?= site_url('folders/' . ($doc['document_folder_id'] ?? '')) ?>" 
               onclick="if (window.history.length > 1) { history.back(); return false; }"
               class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 bg-surface-border/20 hover:bg-surface-border/40 text-text text-xs font-bold rounded-lg border border-surface-border transition-colors shrink-0 shadow-sm mr-1 sm:mr-2 cursor-pointer"
               title="Return to Folder">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span class="font-extrabold uppercase text-[11px] tracking-wider">Return</span>
            </a>

            <a href="<?= site_url('folders/' . ($doc['document_folder_id'] ?? '')) ?>" 
               onclick="if (window.history.length > 1) { history.back(); return false; }"
               class="cursor-pointer shrink-0">
                <!-- Back-to-folders brand mark. text-text (not text-white) so it stays visible on the
                     theme-aware bg-bg header in both light and dark mode. -->
                <div class="flex-shrink-0 flex items-center gap-1 mr-2 sm:mr-4 text-text hover:text-accent transition-colors">
                    <!-- Folder/document icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 sm:h-7 sm:w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="hidden sm:block font-black tracking-tighter text-xl uppercase">SPMS</span>
                </div>
            </a>

            <input type="text" maxlength="100" id="doc-title" value="<?= esc($doc['title']) ?>"
                class="bg-transparent border-none font-bold text-sm text-text focus:ring-0 px-1 sm:px-2 py-1 min-w-[50px]"
                oninput="AppState.setDirty(true); autoResize(this);"
                onblur="restoreTitle(this, '<?= esc($doc['title']) ?>'); autoResize(this);"
                onload="autoResize(this);"
                <?= (!$isEditable) ? 'disabled' : '' ?>>

            <span id="save-status" class="ml-1 sm:ml-3 shrink-0 text-[10px] uppercase tracking-widest font-bold transition-all"></span>
        </div>
        
        <!-- Call autoResize immediately after the element is in DOM -->
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const titleInput = document.getElementById('doc-title');
                if (titleInput) autoResize(titleInput);
            });
        </script>
        
        <div class="flex items-center gap-2 sm:gap-4 shrink-0">
            <!-- Print / Export PDF Button -->
            <button type="button" onclick="exportToPdf()" 
                    class="inline-flex items-center gap-1.5 px-3 sm:px-3.5 py-2 sm:py-2.5 bg-surface-border/20 hover:bg-surface-border/40 text-text text-[10px] sm:text-xs font-bold rounded-lg border border-surface-border transition-all cursor-pointer shadow-sm print-hide"
                    title="Print Document or Export to PDF">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#FFB800]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span class="hidden md:inline">Print / Export PDF</span>
                <span class="md:hidden">Print</span>
            </button>
            <?php if (!$isGuide): ?>
                <?php if ($doc['is_target'] == 0 && !in_array($status, [FolderStatus::DRAFT->value, FolderStatus::REEVALUATE->value])): ?>
                    <button type="button" disabled class="bg-zinc-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                        Supporting<span class="hidden sm:inline"> Evidence</span>
                    </button>

                <?php elseif ($status === FolderStatus::APPROVED->value || $status === FolderStatus::TWG_APPROVED->value || $status === FolderStatus::TWG_DISAPPROVED->value): ?>
                    <?php if (session()->get('role') === 'TWG'): ?>
                        <div class="flex gap-1.5 sm:gap-2">
                            <button id="btn-twg-disapprove" type="button" 
                                    onclick="setTwgStatus('twg_disapproved')" 
                                    class="<?= $status === FolderStatus::TWG_DISAPPROVED->value ? 'bg-danger-600 ring-2 ring-danger-400' : 'bg-danger-500 hover:bg-danger-600' ?> text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg shadow-danger-500/20 transition-all active:scale-[0.98] cursor-pointer">
                                Disapprove
                            </button>
                            <button id="btn-twg-approve" type="button" 
                                    onclick="setTwgStatus('twg_approved')" 
                                    class="<?= $status === FolderStatus::TWG_APPROVED->value ? 'bg-success-600 ring-2 ring-success-400' : 'bg-success-500 hover:bg-success-600' ?> text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg shadow-success-500/20 transition-all active:scale-[0.98] cursor-pointer">
                                Approve
                            </button>
                        </div>
                    <?php else: ?>
                        <?php 
                            $canRemoveEvalApproval = false;
                            if (session()->get('role') === 'Admin' && $status === FolderStatus::APPROVED->value) $canRemoveEvalApproval = true;
                            if (isset($routingStatus) && $routingStatus === FolderStatus::APPROVED->value && $status === FolderStatus::APPROVED->value) $canRemoveEvalApproval = true;
                        ?>
                        <?php if ($canRemoveEvalApproval): ?>
                            <?php 
                                $ownerDocType = strtolower($doc['doc_type'] ?? 'ipcr');
                                $evalEndCol = $ownerDocType . '_eval_end';
                                $isEvalPeriodEnded = !empty($doc[$evalEndCol]) && date('Y-m-d H:i:s') > $doc[$evalEndCol]; 
                            ?>
                            <button id="btn-unapprove-evaluation" type="button" 
                                    <?= $isEvalPeriodEnded ? 'disabled' : 'onclick="saveWith({ after: () => unapproveFolderEvaluation() })"' ?>
                                    class="<?= $isEvalPeriodEnded ? 'bg-warning-500/50 cursor-not-allowed opacity-80' : 'bg-warning-500 hover:bg-warning-600 shadow-warning-500/20 active:scale-[0.98] cursor-pointer' ?> text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg transition-all">
                                Remove<span class="hidden sm:inline"> Approval</span>
                            </button>
                        <?php else: ?>
                            <button type="button" disabled class="bg-success-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                                <span class="hidden sm:inline">Folder </span><?= $status === FolderStatus::TWG_APPROVED->value ? 'TWG Approved' : ($status === FolderStatus::TWG_DISAPPROVED->value ? 'TWG Disapproved' : 'Approved ✓') ?>
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                    
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
                        
                        <?php elseif (session()->get('role') === 'TWG'): ?>
                            <div class="flex gap-1.5 sm:gap-2">
                                <button id="btn-twg-disapprove" type="button" 
                                        onclick="setTwgStatus('twg_disapproved')" 
                                        class="bg-danger-500 hover:bg-danger-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg shadow-danger-500/20 transition-all active:scale-[0.98] cursor-pointer">
                                    Disapprove
                                </button>
                                <button id="btn-twg-approve" type="button" 
                                        onclick="setTwgStatus('twg_approved')" 
                                        class="bg-success-500 hover:bg-success-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg shadow-success-500/20 transition-all active:scale-[0.98] cursor-pointer">
                                    Approve
                                </button>
                            </div>
                        <?php elseif (isset($routingStatus) && $routingStatus === FolderStatus::APPROVED->value): ?>
                            <?php 
                                $ownerDocType = strtolower($doc['doc_type'] ?? 'ipcr');
                                $evalEndCol = $ownerDocType . '_eval_end';
                                $isEvalPeriodEnded = !empty($doc[$evalEndCol]) && date('Y-m-d H:i:s') > $doc[$evalEndCol]; 
                            ?>
                            <button id="btn-unapprove-evaluation" type="button" 
                                    <?= $isEvalPeriodEnded ? 'disabled' : 'onclick="saveWith({ after: () => unapproveFolderEvaluation() })"' ?>
                                    class="<?= $isEvalPeriodEnded ? 'bg-warning-500/50 cursor-not-allowed opacity-80' : 'bg-warning-500 hover:bg-warning-600 shadow-warning-500/20 active:scale-[0.98] cursor-pointer' ?> text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg transition-all">
                                Remove<span class="hidden sm:inline"> Approval</span>
                            </button>
                        <?php else: ?>
                            <div class="flex gap-1.5 sm:gap-2">
                                <button type="button" 
                                        onclick="rate()" 
                                        class="flex items-center justify-center bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700 text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-sm transition-all active:scale-[0.98] cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    Calculate
                                </button>
                                <button id="btn-return" type="button" 
                                        onclick="saveWith({ after: () => returnFolderRevision() })" 
                                        class="bg-revision-500 hover:bg-revision-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg shadow-revision-500/20 transition-all active:scale-[0.98] cursor-pointer">
                                    Return<span class="hidden sm:inline"> for Revision</span>
                                </button>
                                <button id="btn-approve" type="button" 
                                        onclick="saveWith({ after: () => approveFolderEvaluation() })" 
                                        class="bg-success-500 hover:bg-success-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg shadow-success-500/20 transition-all active:scale-[0.98] cursor-pointer">
                                    Approve<span class="hidden sm:inline"> Rating</span>
                                </button>
                            </div>
                        <?php endif; ?>
                        
                    <?php endif; ?>

                <?php elseif ($status === FolderStatus::TO_EVALUATE->value || $status === FolderStatus::REEVALUATE->value): ?>
                    <?php if ($isOwner): ?>
                        <div class="flex gap-1.5 sm:gap-2">
                            <button type="button" 
                                    onclick="rate()" 
                                    class="flex items-center justify-center bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700 text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-sm transition-all active:scale-[0.98] cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                Calculate
                            </button>
                            <button id="btn-submit" type="button" 
                                    onclick="saveWith({ after: () => lockFolderEvaluation() })" 
                                    class="bg-info-500 hover:bg-info-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg shadow-info-500/20 transition-all active:scale-[0.98] cursor-pointer">
                                <?= $status === FolderStatus::REEVALUATE->value ? 'Submit Revision' : '<span class="sm:hidden">Self-Rate</span><span class="hidden sm:inline">Complete Self-Rating</span>' ?>
                            </button>
                        </div>
                    <?php else: ?>
                        <button type="button" disabled class="bg-zinc-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                            Wait<span class="hidden sm:inline">ing for Employee</span>
                        </button>
                    <?php endif; ?>

                <?php elseif ($status === FolderStatus::UNEVALUATED->value): ?>
                    <button type="button" disabled class="bg-danger-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                        Missed Deadline
                    </button>
                    
                <?php elseif ($status === FolderStatus::DRAFT_TARGET->value || $status === FolderStatus::TARGET_RETURNED->value): ?>
                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <a href="<?= site_url('folders/' . ($doc['document_folder_id'] ?? '')) ?>" 
                           onclick="if (window.history.length > 1) { history.back(); return false; }"
                           class="inline-flex items-center justify-center bg-zinc-700/80 hover:bg-zinc-700 text-zinc-200 hover:text-white border border-zinc-600/60 text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg shadow-sm transition-all active:scale-[0.98] cursor-pointer"
                           title="Return to Folder">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>Return</span>
                        </a>
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
                    </div>

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
                            <?php 
                                $ownerDocType = strtolower($doc['doc_type'] ?? 'ipcr');
                                $targetEndCol = $ownerDocType . '_target_end';
                                $isTargetPeriodEnded = !empty($doc[$targetEndCol]) && date('Y-m-d H:i:s') > $doc[$targetEndCol]; 
                            ?>
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
                    <?php if (!$isOwner && $status === FolderStatus::TARGET_APPROVED->value): ?>
                        <?php 
                            $ownerDocType = strtolower($doc['doc_type'] ?? 'ipcr');
                            $targetEndCol = $ownerDocType . '_target_end';
                            $isTargetPeriodEnded = !empty($doc[$targetEndCol]) && date('Y-m-d H:i:s') > $doc[$targetEndCol]; 
                        ?>
                        <button id="btn-unapprove-target" type="button" 
                                <?= $isTargetPeriodEnded ? 'disabled' : 'onclick="saveWith({ after: () => unapproveFolderTarget() })"' ?>
                                class="<?= $isTargetPeriodEnded ? 'bg-warning-500/50 cursor-not-allowed opacity-80' : 'bg-warning-500 hover:bg-warning-600 shadow-warning-500/20 active:scale-[0.98] cursor-pointer' ?> text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg transition-all">
                            Remove<span class="hidden sm:inline"> Approval</span>
                        </button>
                    <?php else: ?>
                        <button type="button" disabled class="bg-highlight-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                            Awaiting Eval<span class="hidden sm:inline"> Window</span>
                        </button>
                    <?php endif; ?>
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

    <div class="flex-none flex bg-bg border-b border-surface-border px-3 sm:px-6 <?= $isEditable ? 'gap-2' : 'gap-4' ?> text-sm font-bold pt-2 overflow-x-auto whitespace-nowrap scrollbar-hide print-hide" id="tab-bar">
        <!-- Tabs injected here via JS -->
    </div>

    <div class="flex-1 min-h-0 w-full relative bg-[#031c12] dark:bg-[#031c12] overflow-x-auto" id="editor-container">
        <!-- SPMS Structured Form Builder Container -->
        <div id="spms-form-workspace" class="hidden w-full h-full overflow-y-auto p-3 sm:p-6 lg:p-8 flex justify-center items-start custom-scrollbar print:p-0 print:bg-white print:overflow-visible">
            <article id="printable-form" class="spms-sheet-container block space-y-5">
                
                <!-- INSTITUTIONAL FORM HEADER -->
                <div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 12px;">
                    <h1 style="font-size: 15px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 6px 0;" id="spms-doc-title">
                        Individual Performance Commitment and Review (IPCR) — Faculty / Professors
                    </h1>
                    <p style="font-size: 11px; color: #334155; margin: 6px 0 0 0; line-height: 1.6;">
                        I, <input type="text" id="ratee-name" value="" placeholder="Full Name Here" style="font-weight: bold; color: #dc2626; border: none; border-bottom: 1px solid #f87171; text-align: center; min-width: 170px; outline: none; padding: 2px 4px;">, 
                        <input type="text" id="ratee-position" value="" placeholder="Position & Designation" style="color: #dc2626; border: none; border-bottom: 1px solid #f87171; text-align: center; min-width: 170px; outline: none; padding: 2px 4px;"> 
                        of the <input type="text" id="ratee-dept" value="" placeholder="Office / College Name" style="color: #dc2626; border: none; border-bottom: 1px solid #f87171; text-align: center; min-width: 170px; outline: none; padding: 2px 4px;">, 
                        commit to deliver and agree to be rated on the attainment of faculty targets for 
                        <input type="text" id="ratee-period" value="" placeholder="Period (e.g. 1st Semester, AY 2026–2027)" style="font-weight: bold; color: #dc2626; border: none; border-bottom: 1px solid #f87171; text-align: center; min-width: 220px; outline: none; padding: 2px 4px;">.
                    </p>
                </div>

                <!-- APPROVER, RATEE, AND RATING SCALE MATRIX -->
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 11px;">
                    <tr>
                        <!-- Approver Block -->
                        <td style="width: 35%; border: 1px solid #000; padding: 10px; vertical-align: top;">
                            <div style="font-weight: 800; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; color: #0f172a; margin-bottom: 8px;">
                                Approved by (Dean / Chair):
                            </div>
                            <table style="width: 100%; border-collapse: collapse; border: none; font-size: 11px;">
                                <tr>
                                    <td style="width: 60px; color: #64748b; font-weight: 600; border: none; padding: 3px 0;">Name:</td>
                                    <td style="border: none; padding: 3px 0;">
                                        <input type="text" id="approver-name" value="" placeholder="Name of Approving Authority" style="width: 100%; font-weight: bold; color: #dc2626; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px;">
                                    </td>
                                </tr>
                                <tr>
                                    <td style="color: #64748b; font-weight: 600; border: none; padding: 3px 0;">Position:</td>
                                    <td style="border: none; padding: 3px 0;">
                                        <input type="text" id="approver-pos" value="" placeholder="Official Designation" style="width: 100%; color: #1e293b; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px;">
                                    </td>
                                </tr>
                                <tr>
                                    <td style="color: #64748b; font-weight: 600; border: none; padding: 3px 0;">Date:</td>
                                    <td style="border: none; padding: 3px 0;">
                                        <input type="text" id="approver-date" value="" placeholder="Date Approved" style="width: 100%; color: #1e293b; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px;">
                                    </td>
                                </tr>
                            </table>
                        </td>

                        <!-- Ratee Sign-off Block -->
                        <td style="width: 35%; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle;">
                            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%;">
                                <input type="text" id="ratee-sign-name" value="" placeholder="Name of Employee" style="font-weight: bold; color: #dc2626; border: none; border-bottom: 1px solid #94a3b8; text-align: center; width: 85%; outline: none; font-size: 11px; padding: 2px 0;">
                                <span style="color: #334155; font-size: 10px; font-weight: 600; margin-top: 4px;">Faculty Member / Professor</span>
                                <div style="margin-top: 10px; font-size: 11px; color: #64748b;">
                                    Date: <input type="text" id="ratee-sign-date" value="" placeholder="Date" style="border: none; border-bottom: 1px solid #cbd5e1; text-align: center; width: 100px; outline: none; font-size: 11px;">
                                </div>
                            </div>
                        </td>

                        <!-- CHED / CSC / BSU Rating Scale -->
                        <td style="width: 30%; border: 1px solid #000; padding: 8px 12px; background: #f8fafc; vertical-align: top; font-size: 10px;">
                            <div style="font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #0f172a; margin-bottom: 4px;">
                                BSU / CSC Faculty Rating Scale:
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 2px; color: #334155;">
                                <div style="display: flex; justify-content: space-between; font-weight: bold;"><span>5 — Outstanding</span> <span>(4.500 – 5.000)</span></div>
                                <div style="display: flex; justify-content: space-between;"><span>4 — Very Satisfactory</span> <span>(3.500 – 4.499)</span></div>
                                <div style="display: flex; justify-content: space-between;"><span>3 — Satisfactory</span> <span>(2.500 – 3.499)</span></div>
                                <div style="display: flex; justify-content: space-between;"><span>2 — Unsatisfactory</span> <span>(1.500 – 2.499)</span></div>
                                <div style="display: flex; justify-content: space-between;"><span>1 / 0 — Poor / Unmet</span> <span>(Below 1.499)</span></div>
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- MAIN TABLE OF DELIVERABLES & RATINGS -->
                <div style="width: 100%; overflow: visible; min-height: fit-content; display: block;">
                    <table class="spms-table">
                        <!-- Column Widths (9 total columns matching BSU Annex B IPCR) -->
                        <colgroup>
                            <col style="width: 24%;">
                            <col style="width: 24%;">
                            <col style="width: 24%;">
                            <col style="width: 4%;">
                            <col style="width: 4%;">
                            <col style="width: 4%;">
                            <col style="width: 5%;">
                            <col style="width: 12%;">
                            <col style="width: 3%;">
                        </colgroup>

                        <!-- Two-Row Header: Q, T, E, Ave side-by-side -->
                        <thead>
                            <tr style="background: #f1f5f9; text-align: center; font-weight: bold; border-bottom: 1px solid #000;">
                                <th rowspan="2" style="padding: 8px;">ACADEMIC FUNCTION /<br>MAJOR FINAL OUTPUT</th>
                                <th rowspan="2" style="padding: 8px;">SUCCESS INDICATORS<br><span style="font-size: 9px; font-weight: normal; text-transform: none;">(Targets + Measures)</span></th>
                                <th rowspan="2" style="padding: 8px;">ACTUAL ACCOMPLISHMENTS<br><span style="font-size: 9px; font-weight: normal; text-transform: none;">(Grades submitted, Papers published)</span></th>
                                <th colspan="4" style="padding: 4px;">RATING</th>
                                <th rowspan="2" style="padding: 8px;">REMARKS</th>
                                <th rowspan="2" style="padding: 4px;" class="print-hide">ACT</th>
                            </tr>
                            <tr style="background: #f1f5f9; text-align: center; font-weight: bold; border-bottom: 2px solid #000; font-size: 10px;">
                                <th style="padding: 4px;">Q</th>
                                <th style="padding: 4px;">T</th>
                                <th style="padding: 4px;">E</th>
                                <th style="padding: 4px;">Ave.</th>
                            </tr>
                        </thead>

                        <!-- 1. CORE FUNCTIONS -->
                        <tbody id="tbody-core">
                            <tr style="background: #f8fafc; border-top: 2px solid #000; border-bottom: 1px solid #000;">
                                <td colspan="6" id="label-cat-core" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #0f172a;">
                                    1. Core Functions — Instruction & Teaching Load (70%)
                                </td>
                                <td colspan="3" style="padding: 6px 12px; text-align: right;">
                                    <span style="display: inline-block; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 800; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                        Core Subtotal: <span id="badge-core-subtotal">0.000</span>
                                    </span>
                                </td>
                            </tr>
                            <tr class="table-row-core" style="border-bottom: 1px solid #000000;">
                                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                                    <textarea class="spms-textarea field-mfo" rows="3" placeholder="Enter academic function / major final output..." <?= $canEditTargets ? '' : 'disabled title="Locked (Only modifiable during Target Phase)"' ?>></textarea>
                                </td>
                                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                                    <textarea class="spms-textarea field-indicators" rows="3" placeholder="Enter success indicators (targets + measures)..." <?= $canEditEvaluation ? '' : 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' ?>></textarea>
                                </td>
                                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                                    <textarea class="spms-textarea field-accomplishments" rows="3" placeholder="Enter actual accomplishments..." <?= $canEditEvaluation ? '' : 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' ?>></textarea>
                                </td>
                                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                                    <input type="number" min="0" max="5" step="1" value="" placeholder="—" <?= $canEditEvaluation ? 'oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)"' : 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' ?> class="spms-score-input field-q">
                                </td>
                                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                                    <input type="number" min="0" max="5" step="1" value="" placeholder="—" <?= $canEditEvaluation ? 'oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)"' : 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' ?> class="spms-score-input field-t">
                                </td>
                                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                                    <input type="number" min="0" max="5" step="1" value="" placeholder="—" <?= $canEditEvaluation ? 'oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)"' : 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' ?> class="spms-score-input field-e">
                                </td>
                                <td style="padding: 4px 2px; text-align: center; vertical-align: middle; background: #f0f9ff; border: 1px solid #000;">
                                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                        <span class="field-row-avg" style="font-weight: 900; color: #0369a1; font-size: 11px;">—</span>
                                        <span style="font-size: 8px; font-weight: 800; color: #0284c7; text-transform: uppercase;">auto</span>
                                    </div>
                                </td>
                                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                                    <textarea class="spms-textarea field-remarks" rows="3" placeholder="Enter remarks..." <?= $canEditEvaluation ? '' : 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' ?>></textarea>
                                </td>
                                <td style="padding: 2px; text-align: center; vertical-align: middle; border: 1px solid #000;" class="print-hide">
                                    <?php if ($canEditTargets): ?>
                                    <button type="button" onclick="deleteTableRow(this)" title="Delete Row" class="btn-del-row">
                                        <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                        <!-- Add Row Footer for Core -->
                        <tbody class="print-hide" id="tfoot-add-core" style="<?= $canEditTargets ? '' : 'display: none;' ?>">
                            <tr>
                                <td colspan="9" style="padding: 6px; background: #fafafa; text-align: center; border: 1px solid #000;">
                                    <button type="button" onclick="addTableRow('core')" class="btn-add-dashed">
                                        + Add Deliverable Row to Core Functions
                                    </button>
                                </td>
                            </tr>
                        </tbody>

                        <!-- 2. STRATEGIC FUNCTIONS -->
                        <tbody id="tbody-strategic">
                            <tr style="background: #f8fafc; border-top: 2px solid #000; border-bottom: 1px solid #000;">
                                <td colspan="6" id="label-cat-strategic" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #0f172a;">
                                    2. Strategic Functions — Research, Citations & Extension Services (20%)
                                </td>
                                <td colspan="3" style="padding: 6px 12px; text-align: right;">
                                    <span style="display: inline-block; background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd; font-weight: 800; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                        Strategic Subtotal: <span id="badge-strategic-subtotal">0.000</span>
                                    </span>
                                </td>
                            </tr>
                            <tr class="table-row-strategic" style="border-bottom: 1px solid #000000;">
                                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                                    <textarea class="spms-textarea field-mfo" rows="3" placeholder="Enter academic function / major final output..." <?= $canEditTargets ? '' : 'disabled title="Locked (Only modifiable during Target Phase)"' ?>></textarea>
                                </td>
                                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                                    <textarea class="spms-textarea field-indicators" rows="3" placeholder="Enter success indicators (targets + measures)..." <?= $canEditEvaluation ? '' : 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' ?>></textarea>
                                </td>
                                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                                    <textarea class="spms-textarea field-accomplishments" rows="3" placeholder="Enter actual accomplishments..." <?= $canEditEvaluation ? '' : 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' ?>></textarea>
                                </td>
                                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                                    <input type="number" min="0" max="5" step="1" value="" placeholder="—" <?= $canEditEvaluation ? 'oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)"' : 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' ?> class="spms-score-input field-q">
                                </td>
                                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                                    <input type="number" min="0" max="5" step="1" value="" placeholder="—" <?= $canEditEvaluation ? 'oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)"' : 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' ?> class="spms-score-input field-t">
                                </td>
                                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                                    <input type="number" min="0" max="5" step="1" value="" placeholder="—" <?= $canEditEvaluation ? 'oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)"' : 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' ?> class="spms-score-input field-e">
                                </td>
                                <td style="padding: 4px 2px; text-align: center; vertical-align: middle; background: #f0f9ff; border: 1px solid #000;">
                                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                        <span class="field-row-avg" style="font-weight: 900; color: #0369a1; font-size: 11px;">—</span>
                                        <span style="font-size: 8px; font-weight: 800; color: #0284c7; text-transform: uppercase;">auto</span>
                                    </div>
                                </td>
                                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                                    <textarea class="spms-textarea field-remarks" rows="3" placeholder="Enter remarks..." <?= $canEditEvaluation ? '' : 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' ?>></textarea>
                                </td>
                                <td style="padding: 2px; text-align: center; vertical-align: middle; border: 1px solid #000;" class="print-hide">
                                    <?php if ($canEditTargets): ?>
                                    <button type="button" onclick="deleteTableRow(this)" title="Delete Row" class="btn-del-row">
                                        <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                        <!-- Add Row Footer for Strategic -->
                        <tbody class="print-hide" id="tfoot-add-strategic" style="<?= $canEditTargets ? '' : 'display: none;' ?>">
                            <tr>
                                <td colspan="9" style="padding: 6px; background: #fafafa; text-align: center; border: 1px solid #000;">
                                    <button type="button" onclick="addTableRow('strategic')" class="btn-add-dashed">
                                        + Add Deliverable Row to Strategic Functions
                                    </button>
                                </td>
                            </tr>
                        </tbody>

                        <!-- 3. SUPPORT FUNCTIONS -->
                        <tbody id="tbody-support">
                            <tr style="background: #f8fafc; border-top: 2px solid #000; border-bottom: 1px solid #000;">
                                <td colspan="6" id="label-cat-support" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #0f172a;">
                                    3. Support Functions — Committee Work, Thesis Advising & Governance (10%)
                                </td>
                                <td colspan="3" style="padding: 6px 12px; text-align: right;">
                                    <span style="display: inline-block; background: #fffbeb; color: #92400e; border: 1px solid #fde68a; font-weight: 800; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                        Support Subtotal: <span id="badge-support-subtotal">0.000</span>
                                    </span>
                                </td>
                            </tr>
                            <tr class="table-row-support" style="border-bottom: 1px solid #000000;">
                                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                                    <textarea class="spms-textarea field-mfo" rows="3" placeholder="Enter academic function / major final output..." <?= $canEditTargets ? '' : 'disabled title="Locked (Only modifiable during Target Phase)"' ?>></textarea>
                                </td>
                                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                                    <textarea class="spms-textarea field-indicators" rows="3" placeholder="Enter success indicators (targets + measures)..." <?= $canEditEvaluation ? '' : 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' ?>></textarea>
                                </td>
                                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                                    <textarea class="spms-textarea field-accomplishments" rows="3" placeholder="Enter actual accomplishments..." <?= $canEditEvaluation ? '' : 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' ?>></textarea>
                                </td>
                                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                                    <input type="number" min="0" max="5" step="1" value="" placeholder="—" <?= $canEditEvaluation ? 'oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)"' : 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' ?> class="spms-score-input field-q">
                                </td>
                                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                                    <input type="number" min="0" max="5" step="1" value="" placeholder="—" <?= $canEditEvaluation ? 'oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)"' : 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' ?> class="spms-score-input field-t">
                                </td>
                                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                                    <input type="number" min="0" max="5" step="1" value="" placeholder="—" <?= $canEditEvaluation ? 'oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)"' : 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' ?> class="spms-score-input field-e">
                                </td>
                                <td style="padding: 4px 2px; text-align: center; vertical-align: middle; background: #f0f9ff; border: 1px solid #000;">
                                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                        <span class="field-row-avg" style="font-weight: 900; color: #0369a1; font-size: 11px;">—</span>
                                        <span style="font-size: 8px; font-weight: 800; color: #0284c7; text-transform: uppercase;">auto</span>
                                    </div>
                                </td>
                                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                                    <textarea class="spms-textarea field-remarks" rows="3" placeholder="Enter remarks..." <?= $canEditEvaluation ? '' : 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' ?>></textarea>
                                </td>
                                <td style="padding: 2px; text-align: center; vertical-align: middle; border: 1px solid #000;" class="print-hide">
                                    <?php if ($canEditTargets): ?>
                                    <button type="button" onclick="deleteTableRow(this)" title="Delete Row" class="btn-del-row">
                                        <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                        <!-- Add Row Footer for Support -->
                        <tbody class="print-hide" id="tfoot-add-support" style="<?= $canEditTargets ? '' : 'display: none;' ?>">
                            <tr>
                                <td colspan="9" style="padding: 6px; background: #fafafa; text-align: center; border: 1px solid #000;">
                                    <button type="button" onclick="addTableRow('support')" class="btn-add-dashed">
                                        + Add Deliverable Row to Support Functions
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- GRAND SUMMARY & NAVY RATING BAR (BULLETPROOF TABLE LAYOUT) -->
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 11px;">
                    <tr>
                        <!-- Left: Formula Explanation -->
                        <td style="width: 35%; padding: 12px; border: 1px solid #000; vertical-align: top; background: #fafafa;">
                            <div style="font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Formula Weights:</div>
                            <div id="formula-desc-text" style="font-size: 11px; color: #334155; margin-top: 6px; line-height: 1.5;">
                                Core Function (70%) + Strategic Function (20%) + Support Functions (10%).
                            </div>
                            <div style="font-size: 10px; color: #94a3b8; font-style: italic; margin-top: 14px; border-top: 1px solid #e2e8f0; padding-top: 6px;">
                                Validated against standard Civil Service Commission SPMS Guidelines.
                            </div>
                        </td>

                        <!-- Right: Calculation Breakdown & Final Navy Bar -->
                        <td style="width: 65%; padding: 0; vertical-align: top; border: 1px solid #000;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <!-- Core Row -->
                                <tr style="border-bottom: 1px solid #000;">
                                    <td style="padding: 8px 12px; font-weight: 800; font-size: 11px; text-transform: uppercase; width: 45%; color: #0f172a;">
                                        CORE FUNCTION
                                    </td>
                                    <td style="padding: 8px 12px; font-weight: 900; color: #047857; font-size: 13px; width: 20%;" id="sum-core-score">
                                        0.000
                                    </td>
                                    <td style="padding: 8px 12px; font-size: 11px; color: #64748b; text-align: right; width: 35%;">
                                        (Average: <span id="sum-core-avg">0.000</span> × <span id="mult-core-val">0.70</span>)
                                    </td>
                                </tr>

                                <!-- Strategic Row -->
                                <tr style="border-bottom: 1px solid #000;">
                                    <td style="padding: 8px 12px; font-weight: 800; font-size: 11px; text-transform: uppercase; color: #0f172a;">
                                        STRATEGIC FUNCTION
                                    </td>
                                    <td style="padding: 8px 12px; font-weight: 900; color: #0284c7; font-size: 13px;" id="sum-strategic-score">
                                        0.000
                                    </td>
                                    <td style="padding: 8px 12px; font-size: 11px; color: #64748b; text-align: right;">
                                        (Average: <span id="sum-strategic-avg">0.000</span> × <span id="mult-strategic-val">0.20</span>)
                                    </td>
                                </tr>

                                <!-- Support Row -->
                                <tr style="border-bottom: 1px solid #000;">
                                    <td style="padding: 8px 12px; font-weight: 800; font-size: 11px; text-transform: uppercase; color: #0f172a;">
                                        SUPPORT FUNCTIONS
                                    </td>
                                    <td style="padding: 8px 12px; font-weight: 900; color: #d97706; font-size: 13px;" id="sum-support-score">
                                        0.000
                                    </td>
                                    <td style="padding: 8px 12px; font-size: 11px; color: #64748b; text-align: right;">
                                        (Average: <span id="sum-support-avg">0.000</span> × <span id="mult-support-val">0.10</span>)
                                    </td>
                                </tr>

                                <!-- Dark Navy Grand Total Banner (Matching Reference Mockup) -->
                                <tr style="background: #0a192f; color: #ffffff;">
                                    <td style="padding: 12px 14px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; font-size: 11px; color: #e2e8f0;">
                                        FINAL AVERAGE RATING
                                    </td>
                                    <td style="padding: 12px 14px; font-weight: 900; font-size: 24px; color: #38bdf8; font-family: monospace;" id="grand-score">
                                        0.000
                                    </td>
                                    <td style="padding: 12px 14px; text-align: right;">
                                        <span id="adjectival-badge" style="display: inline-block; background: #475569; color: #ffffff; font-weight: 900; font-size: 11px; text-transform: uppercase; padding: 4px 12px; border-radius: 4px; letter-spacing: 0.05em; margin-right: 8px;">
                                            PENDING EVALUATION
                                        </span>
                                        <span id="grand-formula" style="font-size: 9px; color: #94a3b8; font-family: monospace;">
                                            (Core 0.000 + Strategic 0.000 + Support 0.000)
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- PMT & DEAN REMARKS -->
                <div style="border: 1px solid #000; padding: 10px; box-sizing: border-box;">
                    <div style="font-weight: 800; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; color: #0f172a; margin-bottom: 4px;">
                        College Performance Management Team (PMT) & Dean Remarks / Recommendations:
                    </div>
                    <textarea id="pmt-remarks" rows="2" placeholder="Enter PMT & Dean remarks and recommendations here..." class="spms-textarea" style="width: 100%; border: 1px solid transparent; font-style: italic;"></textarea>
                </div>

                <!-- 3 BOTTOM SIGNATORIES -->
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 11px; text-align: center;">
                    <tr>
                        <!-- Discussed with (Ratee) -->
                        <td style="width: 33.33%; border: 1px solid #000; padding: 12px; vertical-align: top;">
                            <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #475569; text-align: left; margin-bottom: 16px;">
                                Discussed with (Faculty Ratee):
                            </div>
                            <input type="text" id="sig-ratee-name" value="" placeholder="Name of Faculty Ratee" style="font-weight: 900; color: #dc2626; border: none; border-bottom: 1px solid #94a3b8; text-align: center; width: 85%; outline: none; font-size: 11px; padding: 2px 0;">
                            <div style="font-size: 10px; color: #475569; margin-top: 3px;">Faculty Member / Professor</div>
                            <div style="font-size: 10px; color: #64748b; margin-top: 3px;">
                                Date: <input type="text" id="sig-ratee-date" placeholder="Date" style="border: none; border-bottom: 1px solid #cbd5e1; text-align: center; width: 85px; outline: none; font-size: 10px;">
                            </div>
                        </td>

                        <!-- Assessed by (Dean) -->
                        <td style="width: 33.33%; border: 1px solid #000; padding: 12px; vertical-align: top;">
                            <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #475569; text-align: left; margin-bottom: 16px;">
                                Assessed by (College Dean):
                            </div>
                            <input type="text" id="sig-dean-name" value="" placeholder="Name of Dean / Supervisor" style="font-weight: 900; color: #dc2626; border: none; border-bottom: 1px solid #94a3b8; text-align: center; width: 85%; outline: none; font-size: 11px; padding: 2px 0;">
                            <div style="font-size: 10px; color: #475569; margin-top: 3px;">College Dean / Unit Head</div>
                            <div style="font-size: 10px; color: #64748b; margin-top: 3px;">
                                Date: <input type="text" id="sig-dean-date" placeholder="Date" style="border: none; border-bottom: 1px solid #cbd5e1; text-align: center; width: 85px; outline: none; font-size: 10px;">
                            </div>
                        </td>

                        <!-- Final Approval (VP) -->
                        <td style="width: 33.33%; border: 1px solid #000; padding: 12px; vertical-align: top;">
                            <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #475569; text-align: left; margin-bottom: 16px;">
                                Final Approval (VP for Academic Affairs):
                            </div>
                            <input type="text" id="sig-vp-name" value="" placeholder="Name of Approving Authority" style="font-weight: 900; color: #dc2626; border: none; border-bottom: 1px solid #94a3b8; text-align: center; width: 85%; outline: none; font-size: 11px; padding: 2px 0;">
                            <div style="font-size: 10px; color: #475569; margin-top: 3px;">Vice President for Academic Affairs</div>
                            <div style="font-size: 10px; color: #64748b; margin-top: 3px;">
                                Date: <input type="text" id="sig-vp-date" placeholder="Date" style="border: none; border-bottom: 1px solid #cbd5e1; text-align: center; width: 85px; outline: none; font-size: 10px;">
                            </div>
                        </td>
                    </tr>
                </table>

            </article>
        </div>

        <!-- Fallback TinyMCE Editor Container for Free-Form Documents -->
        <div id="tinymce-wrapper" class="w-full h-full bg-white dark:bg-zinc-950">
            <textarea id="editable-doc" name="content"></textarea>
        </div>
    </div>
</div>

<script>
    <?php
    $tabsJson = '[]';
    if ($doc && !empty($doc['tabs'])) {
        $tabsJson = is_string($doc['tabs']) ? $doc['tabs'] : json_encode($doc['tabs']);
    }
    ?>
    let tabs = <?= $tabsJson ?>;

    if (!tabs || tabs.length === 0) {
        tabs = [
            { id: 'tab-' + Date.now(), title: 'Target Form', content: '' }
        ];
    }

    let activeTabId = tabs[0].id;
    
    const canEditTargets = <?= json_encode($canEditTargets) ?>;
    const canEditEvaluation = <?= json_encode($canEditEvaluation) ?>;
    const isTargetPhase = <?= json_encode($isTargetPhase) ?>;
    const isEvaluationPhase = <?= json_encode($isEvaluationPhase) ?>;
    
    // Set initial content for TinyMCE initialization
    document.getElementById('editable-doc').value = tabs[0].content;

    function getUniqueTitle(baseTitle, excludeTabId = null) {
        let title = baseTitle;
        let counter = 1;
        const existingTitles = tabs.filter(t => t.id !== excludeTabId).map(t => t.title.toLowerCase());
        while (existingTitles.includes(title.toLowerCase())) {
            title = `${baseTitle} (${counter})`;
            counter++;
        }
        return title;
    }

    function renderTabs() {
        const tabBar = document.getElementById('tab-bar');
        tabBar.innerHTML = '';
        
        tabs.forEach(tab => {
            const isActive = tab.id === activeTabId;
            
            const btn = document.createElement('div');
            btn.className = `group flex items-center gap-1 pb-2 border-b-2 transition-colors select-none ${isActive ? 'border-accent text-accent' : 'border-transparent text-text-muted hover:text-text cursor-pointer'}`;
            btn.onclick = () => switchEditorTab(tab.id);
            
            const span = document.createElement('span');
            span.textContent = tab.title;
            
            <?php if ($isEditable): ?>
            span.contentEditable = "true";
            span.title = 'Click to edit tab name';
            span.className = 'outline-none cursor-text px-1 min-w-[20px] inline-block rounded focus:bg-surface-border/30';
            
            span.onblur = (e) => {
                const newName = e.target.textContent.trim();
                if (newName !== '' && newName !== tab.title) {
                    tab.title = getUniqueTitle(newName, tab.id);
                    AppState.setDirty(true);
                }
                e.target.textContent = tab.title;
            };
            
            span.onkeydown = (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    e.target.blur();
                }
            };
            <?php endif; ?>
            
            btn.appendChild(span);

            <?php if ($isEditable): ?>
            if (tabs.length > 1) {
                const delBtn = document.createElement('span');
                delBtn.innerHTML = '&times;';
                delBtn.className = `text-text-muted/40 hover:text-danger-600 transition-colors font-black ml-1 px-1 rounded hover:bg-danger-500/10`;
                delBtn.title = 'Delete tab';
                delBtn.onclick = (e) => { e.stopPropagation(); deleteTab(tab.id); };
                btn.appendChild(delBtn);
            }
            <?php endif; ?>

            tabBar.appendChild(btn);
        });
        
        <?php if ($isEditable): ?>
        const addBtn = document.createElement('button');
        addBtn.innerHTML = '＋';
        addBtn.title = 'Add Tab';
        addBtn.className = 'pb-2 border-b-2 border-transparent text-text-muted hover:text-text transition-colors text-lg font-black px-2';
        addBtn.onclick = () => addTab();
        tabBar.appendChild(addBtn);
        <?php endif; ?>
    }

    function switchEditorTab(tabId) {
        if (tabId === activeTabId) return;

        if (window.isSpmsFormActive && typeof window.syncSpmsActiveTab === 'function') {
            window.syncSpmsActiveTab();
        } else {
            const editor = tinymce.get('editable-doc');
            if (editor) {
                const activeTab = tabs.find(t => t.id === activeTabId);
                if (activeTab) activeTab.content = editor.getContent();
            }
        }
        
        activeTabId = tabId;
        renderTabs();
        initActiveTabView();
    }

    function addTab() {
        const title = getUniqueTitle('New Section');
        const newTab = {
            id: 'tab-' + Date.now(),
            title: title,
            content: ''
        };
        
        if (window.isSpmsFormActive && typeof window.syncSpmsActiveTab === 'function') {
            window.syncSpmsActiveTab();
        } else {
            const editor = tinymce.get('editable-doc');
            if (editor) {
                const activeTab = tabs.find(t => t.id === activeTabId);
                if (activeTab) activeTab.content = editor.getContent();
            }
        }

        tabs.push(newTab);
        activeTabId = newTab.id;
        
        renderTabs();
        initActiveTabView();
        AppState.setDirty(true);
    }

    function renameTab(tabId) {
        const tab = tabs.find(t => t.id === tabId);
        if (!tab) return;
        
        const newName = prompt('Enter new name for tab:', tab.title);
        if (newName && newName.trim() !== '') {
            tab.title = getUniqueTitle(newName.trim(), tabId);
            renderTabs();
            AppState.setDirty(true);
        }
    }

    async function deleteTab(tabId) {
        if (tabs.length <= 1) return;
        
        const tabToDelete = tabs.find(t => t.id === tabId);
        if (!tabToDelete) return;

        const confirmed = await window.appConfirm(`Are you sure you want to delete '${tabToDelete.title}'? This cannot be undone.`, {
            title: 'Delete Tab',
            confirmText: 'Delete',
            isDanger: true
        });
        
        if (!confirmed) return;
        
        const index = tabs.findIndex(t => t.id === tabId);
        if (index === -1) return;
        
        tabs.splice(index, 1);
        
        if (activeTabId === tabId) {
            activeTabId = tabs[Math.min(index, tabs.length - 1)].id;
            const newActive = tabs.find(t => t.id === activeTabId);
            const editor = tinymce.get('editable-doc');
            if (editor && newActive) {
                editor.setContent(newActive.content);
            }
        }
        
        renderTabs();
        AppState.setDirty(true);
    }

    // Initialize tabs UI
    renderTabs();
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
        let finalScore = '';
        if (window.isSpmsFormActive) {
            finalScore = document.getElementById('grand-score')?.innerText?.trim() || '';
            if (finalScore === '0.000' || finalScore === '—') finalScore = '';
        } else {
            const editorBody = tinymce.get('editable-doc')?.getBody();
            finalScore = editorBody?.getAttribute('data-final-score') || '';
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
        let finalScore = '';
        if (window.isSpmsFormActive) {
            finalScore = document.getElementById('grand-score')?.innerText?.trim() || '';
            if (finalScore === '0.000' || finalScore === '—') finalScore = '';
        } else {
            const editorBody = tinymce.get('editable-doc')?.getBody();
            finalScore = editorBody?.getAttribute('data-final-score') || '';
        }

        const ok = await window.appConfirm("Complete and approve this evaluation?", { confirmText: 'Approve' });
        if (!ok) return;

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');
        formData.append('final_score', finalScore);

        document.getElementById('btn-approve').innerText = 'Approving...';
        apiPost('<?= site_url('folder/approve') ?>', formData, {
            onSuccess: () => window.location.reload()
        });
    }

    async function unapproveFolderEvaluation() {
        const ok = await window.appConfirm("Remove your approval and revert to To Evaluate?", { confirmText: 'Remove Approval' });
        if (!ok) return;

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');

        document.getElementById('btn-unapprove-evaluation').innerText = 'Removing...';
        apiPost('<?= site_url('folder/unapprove') ?>', formData, {
            onSuccess: () => window.location.reload()
        });
    }

    async function setTwgStatus(status) {
        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');
        formData.append('status', status);

        apiPost('<?= site_url('folder/twg_approve') ?>', formData, {
            onSuccess: () => window.location.reload(),
            onError: async (errMsg) => {
                await window.appAlert(errMsg || "An error occurred.");
                window.location.reload();
            }
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

    async function unapproveFolderTarget() {
        const ok = await window.appConfirm("Remove approval and revert to pending?", { confirmText: 'Remove Approval' });
        if (!ok) return;

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');

        document.getElementById('btn-unapprove-target').innerText = 'Removing...';
        apiPost('<?= site_url('folder/unapprove_target') ?>', formData, {
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

    // =========================================================================
    // SPMS FORM BUILDER ENGINE (Matches templates/editor.php Exactly)
    // =========================================================================
    window.isSpmsFormActive = false;

    // Default Seed Blueprint (Clean empty rows upon creation)
    const DEFAULT_BLUEPRINT = {
        core: [
            { mfo: "", indicators: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ],
        strategic: [
            { mfo: "", indicators: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ],
        support: [
            { mfo: "", indicators: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ]
    };

    const CATEGORY_WEIGHTS = {
        core: 0.70,
        strategic: 0.20,
        support: 0.10
    };

    function initActiveTabView() {
        const activeTab = tabs.find(t => t.id === activeTabId);
        if (!activeTab) return;

        const hasSpmsData = activeTab.formData && typeof activeTab.formData === 'object';
        const hasSpmsContent = activeTab.content && (activeTab.content.includes('spms-table') || activeTab.content.includes('printable-form') || activeTab.content.includes('ACADEMIC') || activeTab.content.includes('MAJOR FINAL OUTPUT'));
        const isEmptyTab = !activeTab.content || activeTab.content.trim() === '';

        // SPMS Form is default for performance documents
        if (hasSpmsData || hasSpmsContent || isEmptyTab) {
            window.isSpmsFormActive = true;
            document.getElementById('spms-form-workspace').classList.remove('hidden');
            document.getElementById('tinymce-wrapper').classList.add('hidden');
            populateSpmsForm(activeTab.formData);
        } else {
            window.isSpmsFormActive = false;
            document.getElementById('spms-form-workspace').classList.add('hidden');
            document.getElementById('tinymce-wrapper').classList.remove('hidden');
            
            const editor = tinymce.get('editable-doc');
            if (editor) {
                editor.setContent(activeTab.content || '');
            } else {
                document.getElementById('editable-doc').value = activeTab.content || '';
                initTinyMceIfNeeded();
            }
        }
    }

    function populateSpmsForm(formData) {
        const data = formData || {};

        const titleText = (data.title || '<?= esc($doc['title'] ?? '') ?>').toUpperCase();
        const isOpcr = titleText.includes('OPCR') || titleText.includes('OFFICE');
        const isDpcr = titleText.includes('DPCR') || titleText.includes('DIVISION') || titleText.includes('DEPARTMENT');

        let coreW = 0.70, stratW = 0.20, suppW = 0.10;
        let defaultDocTitle = 'Individual Performance Commitment and Review (IPCR) — Faculty / Staff';
        let coreTitle = "1. Core Functions — Instruction & Teaching Load (70%)";
        let stratTitle = "2. Strategic Functions — Research, Citations & Extension Services (20%)";
        let suppTitle = "3. Support Functions — Committee Work, Thesis Advising & Governance (10%)";

        if (isOpcr) {
            coreW = 0.60;
            stratW = 0.25;
            suppW = 0.15;
            defaultDocTitle = "Office Performance Commitment and Review (OPCR) — Executive / College";
            coreTitle = "1. Core Office Mandate (60%)";
            stratTitle = "2. Strategic Functions — Research, Citations & Extension Services (25%)";
            suppTitle = "3. Support Functions — Institutional Governance & Operations (15%)";
            if (!data.title || data.title.includes('IPCR')) {
                data.title = defaultDocTitle;
            }
        } else if (isDpcr) {
            coreW = 0.60;
            stratW = 0.25;
            suppW = 0.15;
            defaultDocTitle = "Division Performance Commitment and Review (DPCR) — Division / Department";
            coreTitle = "1. Core Division Functions (60%)";
            stratTitle = "2. Strategic Functions — Division Extension & Research (25%)";
            suppTitle = "3. Support Functions — Governance & Admin Support (15%)";
            if (!data.title || data.title.includes('IPCR')) {
                data.title = defaultDocTitle;
            }
        }

        CATEGORY_WEIGHTS.core = coreW;
        CATEGORY_WEIGHTS.strategic = stratW;
        CATEGORY_WEIGHTS.support = suppW;

        // Header info
        const titleEl = document.getElementById('spms-doc-title');
        if (titleEl) {
            titleEl.innerText = data.title || defaultDocTitle;
        }

        const elCore = document.getElementById('label-cat-core');
        if (elCore) elCore.innerText = coreTitle;
        const elStrat = document.getElementById('label-cat-strategic');
        if (elStrat) elStrat.innerText = stratTitle;
        const elSupp = document.getElementById('label-cat-support');
        if (elSupp) elSupp.innerText = suppTitle;

        const elFDesc = document.getElementById('formula-desc-text');
        if (elFDesc) elFDesc.innerText = `Core Function (${(coreW * 100).toFixed(0)}%) + Strategic Function (${(stratW * 100).toFixed(0)}%) + Support Functions (${(suppW * 100).toFixed(0)}%).`;

        const elMC = document.getElementById('mult-core-val');
        if (elMC) elMC.innerText = coreW.toFixed(2);
        const elMS = document.getElementById('mult-strategic-val');
        if (elMS) elMS.innerText = stratW.toFixed(2);
        const elMP = document.getElementById('mult-support-val');
        if (elMP) elMP.innerText = suppW.toFixed(2);

        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.value = (val !== undefined && val !== null) ? val : '';
        };

        setVal('ratee-name', data.ratee?.name || '');
        setVal('ratee-position', data.ratee?.position || '');
        setVal('ratee-dept', data.ratee?.dept || '');
        setVal('ratee-period', data.ratee?.period || '');

        setVal('approver-name', data.approver?.name || '');
        setVal('approver-pos', data.approver?.position || '');
        setVal('approver-date', data.approver?.date || '');

        setVal('ratee-sign-name', data.rateeSign?.name || '');
        setVal('ratee-sign-date', data.rateeSign?.date || '');

        setVal('pmt-remarks', data.pmtRemarks || '');

        setVal('sig-ratee-name', data.signatories?.ratee || '');
        setVal('sig-ratee-date', data.signatories?.rateeDate || '');
        setVal('sig-dean-name', data.signatories?.dean || '');
        setVal('sig-dean-date', data.signatories?.deanDate || '');
        setVal('sig-vp-name', data.signatories?.vp || '');
        setVal('sig-vp-date', data.signatories?.vpDate || '');

        // Clear existing rows
        document.querySelectorAll('.table-row-core, .table-row-strategic, .table-row-support').forEach(tr => tr.remove());

        // Populate category rows
        const cats = data.categories || DEFAULT_BLUEPRINT;
        const coreRows = cats.core && cats.core.length > 0 ? cats.core : DEFAULT_BLUEPRINT.core;
        coreRows.forEach(row => addTableRow('core', row));

        const stratRows = cats.strategic && cats.strategic.length > 0 ? cats.strategic : DEFAULT_BLUEPRINT.strategic;
        stratRows.forEach(row => addTableRow('strategic', row));

        const suppRows = cats.support && cats.support.length > 0 ? cats.support : DEFAULT_BLUEPRINT.support;
        suppRows.forEach(row => addTableRow('support', row));

        // Update add buttons visibility based on phase permissions
        ['core', 'strategic', 'support'].forEach(cat => {
            const tfoot = document.getElementById(`tfoot-add-${cat}`);
            if (tfoot) {
                tfoot.style.display = canEditTargets ? '' : 'none';
            }
        });

        // Update header ratee inputs based on phase permissions
        ['ratee-name', 'ratee-position', 'ratee-dept', 'ratee-period'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.disabled = !canEditTargets;
        });

        recalculateForm();
    }

    function addTableRow(category, rowData = null) {
        const tbody = document.getElementById(`tbody-${category}`);
        if (!tbody) return;

        const data = rowData || {
            mfo: "",
            indicators: "",
            accomplishments: "",
            q: "", t: "", e: "",
            remarks: ""
        };

        const mfoDisabled = !canEditTargets;
        const evalDisabled = !canEditEvaluation;

        const tr = document.createElement('tr');
        tr.className = `table-row-${category}`;
        tr.style.borderBottom = '1px solid #000000';

        tr.innerHTML = `
            <!-- Major Final Output (Only modifiable during Target Phase) -->
            <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                <textarea class="spms-textarea field-mfo" rows="3" placeholder="Enter academic function / major final output..." ${mfoDisabled ? 'disabled title="Locked (Only modifiable during Target Phase)"' : ''}>${escapeHtml(data.mfo)}</textarea>
            </td>

            <!-- Success Indicators (Unlocked during evaluation phase) -->
            <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                <textarea class="spms-textarea field-indicators" rows="3" placeholder="Enter success indicators (targets + measures)..." ${evalDisabled ? 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' : ''}>${escapeHtml(data.indicators)}</textarea>
            </td>

            <!-- Actual Accomplishments (Unlocked during evaluation phase) -->
            <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                <textarea class="spms-textarea field-accomplishments" rows="3" placeholder="Enter actual accomplishments..." ${evalDisabled ? 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' : ''}>${escapeHtml(data.accomplishments)}</textarea>
            </td>

            <!-- Rating Q, T, E Inputs (Unlocked during evaluation phase) -->
            <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                <input type="number" min="0" max="5" step="1" 
                    value="${data.q !== undefined && data.q !== null && data.q !== '' ? data.q : ''}" 
                    placeholder="—" 
                    ${evalDisabled ? 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' : 'title="Enter 0 to 5. Double-click, press Esc, or backspace to clear back to null" oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)"'} 
                    class="spms-score-input field-q">
            </td>
            <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                <input type="number" min="0" max="5" step="1" 
                    value="${data.t !== undefined && data.t !== null && data.t !== '' ? data.t : ''}" 
                    placeholder="—" 
                    ${evalDisabled ? 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' : 'title="Enter 0 to 5. Double-click, press Esc, or backspace to clear back to null" oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)"'} 
                    class="spms-score-input field-t">
            </td>
            <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                <input type="number" min="0" max="5" step="1" 
                    value="${data.e !== undefined && data.e !== null && data.e !== '' ? data.e : ''}" 
                    placeholder="—" 
                    ${evalDisabled ? 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' : 'title="Enter 0 to 5. Double-click, press Esc, or backspace to clear back to null" oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)"'} 
                    class="spms-score-input field-e">
            </td>

            <!-- Row Average -->
            <td style="padding: 4px 2px; text-align: center; vertical-align: middle; background: #f0f9ff; border: 1px solid #000;">
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <span class="field-row-avg" style="font-weight: 900; color: #0369a1; font-size: 11px;">—</span>
                    <span style="font-size: 8px; font-weight: 800; color: #0284c7; text-transform: uppercase;">auto</span>
                </div>
            </td>

            <!-- Remarks (Unlocked during evaluation phase) -->
            <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                <textarea class="spms-textarea field-remarks" rows="3" placeholder="Enter remarks..." ${evalDisabled ? 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' : ''}>${escapeHtml(data.remarks)}</textarea>
            </td>

            <!-- Delete Action (Only available during Target Phase) -->
            <td style="padding: 2px; text-align: center; vertical-align: middle; border: 1px solid #000;" class="print-hide">
                ${canEditTargets ? `
                <button type="button" onclick="deleteTableRow(this)" title="Delete Row" class="btn-del-row">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>` : ''}
            </td>
        `;

        tbody.appendChild(tr);

        // Track changes for autoSave
        tr.querySelectorAll('textarea, input').forEach(el => {
            el.addEventListener('input', () => {
                window.syncSpmsActiveTab();
                AppState.setDirty(true);
            });
        });

        recalculateForm();
    }

    function deleteTableRow(button) {
        const tr = button.closest('tr');
        if (!tr) return;
        tr.remove();
        recalculateForm();
        window.syncSpmsActiveTab();
        AppState.setDirty(true);
    }

    function clearScore(input) {
        input.value = '';
        recalculateForm();
        window.syncSpmsActiveTab();
        AppState.setDirty(true);
    }

    function handleScoreKeydown(e, input) {
        if (e.key === 'Escape') {
            e.preventDefault();
            clearScore(input);
            return;
        }
        if (e.key === 'ArrowDown' && input.value === '0') {
            e.preventDefault();
            clearScore(input);
            return;
        }
    }

    function handleScoreInput(input) {
        const raw = input.value.trim();
        if (raw === '') {
            recalculateForm();
            window.syncSpmsActiveTab();
            AppState.setDirty(true);
            return;
        }
        const num = parseInt(raw, 10);
        if (isNaN(num) || num < 0 || num > 5) {
            input.value = '';
        } else {
            input.value = num;
        }
        recalculateForm();
        window.syncSpmsActiveTab();
        AppState.setDirty(true);
    }

    function parseWholeScore(val) {
        if (val === null || val === undefined) return null;
        const str = String(val).trim();
        if (str === '') return null;
        const num = parseInt(str, 10);
        if (isNaN(num) || num < 0 || num > 5) return null;
        return num;
    }

    function recalculateForm() {
        let categoryResults = {
            core: calculateCategory('core'),
            strategic: calculateCategory('strategic'),
            support: calculateCategory('support')
        };

        let totalRated = categoryResults.core.count + categoryResults.strategic.count + categoryResults.support.count;

        // Update Subtotal Badges
        const bCore = document.getElementById('badge-core-subtotal');
        const bStrat = document.getElementById('badge-strategic-subtotal');
        const bSupp = document.getElementById('badge-support-subtotal');
        if (bCore) bCore.innerText = categoryResults.core.count > 0 ? categoryResults.core.subtotal.toFixed(3) : '0.000';
        if (bStrat) bStrat.innerText = categoryResults.strategic.count > 0 ? categoryResults.strategic.subtotal.toFixed(3) : '0.000';
        if (bSupp) bSupp.innerText = categoryResults.support.count > 0 ? categoryResults.support.subtotal.toFixed(3) : '0.000';

        // Update Summary Section Breakdown
        const sCoreScore = document.getElementById('sum-core-score');
        const sCoreAvg = document.getElementById('sum-core-avg');
        if (sCoreScore) sCoreScore.innerText = categoryResults.core.count > 0 ? categoryResults.core.subtotal.toFixed(3) : '0.000';
        if (sCoreAvg) sCoreAvg.innerText = categoryResults.core.count > 0 ? categoryResults.core.avg.toFixed(3) : '0.000';

        const sStratScore = document.getElementById('sum-strategic-score');
        const sStratAvg = document.getElementById('sum-strategic-avg');
        if (sStratScore) sStratScore.innerText = categoryResults.strategic.count > 0 ? categoryResults.strategic.subtotal.toFixed(3) : '0.000';
        if (sStratAvg) sStratAvg.innerText = categoryResults.strategic.count > 0 ? categoryResults.strategic.avg.toFixed(3) : '0.000';

        const sSuppScore = document.getElementById('sum-support-score');
        const sSuppAvg = document.getElementById('sum-support-avg');
        if (sSuppScore) sSuppScore.innerText = categoryResults.support.count > 0 ? categoryResults.support.subtotal.toFixed(3) : '0.000';
        if (sSuppAvg) sSuppAvg.innerText = categoryResults.support.count > 0 ? categoryResults.support.avg.toFixed(3) : '0.000';

        // Calculate Grand Final Rating
        let grandScore = categoryResults.core.subtotal + categoryResults.strategic.subtotal + categoryResults.support.subtotal;
        const gScoreEl = document.getElementById('grand-score');
        if (gScoreEl) gScoreEl.innerText = totalRated > 0 ? grandScore.toFixed(3) : '0.000';

        // Calculate Adjectival Rating
        let adjectival = getAdjectivalRating(grandScore, totalRated);
        const badgeEl = document.getElementById('adjectival-badge');
        if (badgeEl) {
            badgeEl.innerText = adjectival.text;
            badgeEl.style.background = adjectival.color;
        }

        // Formula text
        const gFormulaEl = document.getElementById('grand-formula');
        if (gFormulaEl) {
            gFormulaEl.innerText = 
                `(Core ${categoryResults.core.subtotal.toFixed(3)} + Strategic ${categoryResults.strategic.subtotal.toFixed(3)} + Support ${categoryResults.support.subtotal.toFixed(3)})`;
        }
    }

    function calculateCategory(category) {
        const rows = document.querySelectorAll(`.table-row-${category}`);
        if (rows.length === 0) {
            return { avg: 0, subtotal: 0, count: 0 };
        }

        let sumOfRowAvgs = 0;
        let count = 0;

        rows.forEach(row => {
            const q = parseWholeScore(row.querySelector('.field-q')?.value);
            const t = parseWholeScore(row.querySelector('.field-t')?.value);
            const e = parseWholeScore(row.querySelector('.field-e')?.value);

            let rowSum = 0;
            let rowInputs = 0;

            if (q !== null) { rowSum += q; rowInputs++; }
            if (t !== null) { rowSum += t; rowInputs++; }
            if (e !== null) { rowSum += e; rowInputs++; }

            let rowAvg = rowInputs > 0 ? (rowSum / rowInputs) : 0;
            const avgEl = row.querySelector('.field-row-avg');
            if (avgEl) avgEl.innerText = rowInputs > 0 ? rowAvg.toFixed(2) : '—';

            if (rowInputs > 0) {
                sumOfRowAvgs += rowAvg;
                count++;
            }
        });

        let categoryAvg = count > 0 ? (sumOfRowAvgs / count) : 0;
        let weight = CATEGORY_WEIGHTS[category] || 0;
        let subtotal = categoryAvg * weight;

        return {
            avg: categoryAvg,
            subtotal: subtotal,
            count: count
        };
    }

    function getAdjectivalRating(score, totalRated = 0) {
        if (totalRated === 0) return { text: 'PENDING EVALUATION', color: '#64748b' };
        if (score >= 4.500) return { text: 'OUTSTANDING', color: '#059669' };
        if (score >= 3.500) return { text: 'VERY SATISFACTORY', color: '#2563eb' };
        if (score >= 2.500) return { text: 'SATISFACTORY', color: '#d97706' };
        if (score >= 1.500) return { text: 'UNSATISFACTORY', color: '#ea580c' };
        return { text: 'POOR', color: '#dc2626' };
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function extractRowsData(category) {
        const rows = document.querySelectorAll(`.table-row-${category}`);
        const result = [];

        rows.forEach(row => {
            const q = parseWholeScore(row.querySelector('.field-q')?.value);
            const t = parseWholeScore(row.querySelector('.field-t')?.value);
            const e = parseWholeScore(row.querySelector('.field-e')?.value);

            result.push({
                mfo: row.querySelector('.field-mfo')?.value || '',
                indicators: row.querySelector('.field-indicators')?.value || '',
                accomplishments: row.querySelector('.field-accomplishments')?.value || '',
                q: q !== null ? q : '',
                t: t !== null ? t : '',
                e: e !== null ? e : '',
                remarks: row.querySelector('.field-remarks')?.value || ''
            });
        });

        return result;
    }

    window.syncSpmsActiveTab = function() {
        if (!window.isSpmsFormActive) return;
        const activeTab = tabs.find(t => t.id === activeTabId);
        if (!activeTab) return;

        const getVal = (id) => document.getElementById(id)?.value || '';

        const formData = {
            title: document.getElementById('spms-doc-title')?.innerText || document.getElementById('doc-title')?.value || 'Individual Performance Commitment and Review (IPCR) — Faculty / Professors',
            ratee: {
                name: getVal('ratee-name'),
                position: getVal('ratee-position'),
                dept: getVal('ratee-dept'),
                period: getVal('ratee-period')
            },
            approver: {
                name: getVal('approver-name'),
                position: getVal('approver-pos'),
                date: getVal('approver-date')
            },
            rateeSign: {
                name: getVal('ratee-sign-name'),
                date: getVal('ratee-sign-date')
            },
            categories: {
                core: extractRowsData('core'),
                strategic: extractRowsData('strategic'),
                support: extractRowsData('support')
            },
            pmtRemarks: getVal('pmt-remarks'),
            signatories: {
                ratee: getVal('sig-ratee-name'),
                rateeDate: getVal('sig-ratee-date'),
                dean: getVal('sig-dean-name'),
                deanDate: getVal('sig-dean-date'),
                vp: getVal('sig-vp-name'),
                vpDate: getVal('sig-vp-date')
            }
        };

        activeTab.formData = formData;
        activeTab.content = document.getElementById('printable-form')?.innerHTML || '';
    };

    // Attach input listeners on header and signatory inputs for autosave
    document.addEventListener('DOMContentLoaded', () => {
        const pf = document.getElementById('printable-form');
        if (pf) {
            pf.querySelectorAll('input, textarea').forEach(el => {
                el.addEventListener('input', () => {
                    window.syncSpmsActiveTab();
                    AppState.setDirty(true);
                });
            });
        }
    });

    const originalRate = window.rate;
    window.rate = async function() {
        if (window.isSpmsFormActive) {
            recalculateForm();
            window.syncSpmsActiveTab();
            AppState.setDirty(true);
            return 'calculated';
        } else if (typeof originalRate === 'function') {
            return originalRate();
        }
    };

    function initTinyMceIfNeeded() {
        if (window.isSpmsFormActive) return;
        if (useFullEditor) {
            initEditor();
        } else if (useRemarksOnlyEditor) {
            initRemarksOnlyEditor();
        } else {
            initPlainEditor(isFullyLocked);
        }
    }

    function exportToPdf() {
        if (window.isSpmsFormActive) {
            // Auto-expand all textareas so multi-line text is never clipped during printing
            document.querySelectorAll('#printable-form textarea').forEach(ta => {
                ta.style.height = 'auto';
                ta.style.height = Math.max(ta.scrollHeight, 38) + 'px';
            });
            window.print();
        } else {
            const editor = tinymce.get('editable-doc');
            if (editor) {
                editor.execCommand('mcePrint');
            } else {
                window.print();
            }
        }
    }

    // Launch active view on page load
    document.addEventListener('DOMContentLoaded', () => {
        initActiveTabView();
    });
</script>

<?= $this->endSection() ?>