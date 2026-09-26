<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php 
    use App\Enums\FolderStatus;

    $status = $doc['folder_status']; 
    $isOwner = ($doc['owner_id'] == session()->get('user_id'));
    
    $ownerDocType = strtolower($doc['doc_type'] ?? 'ipcr');
    $isDocDpcr = in_array($ownerDocType, ['dpcr', 'cdpcr']) || stripos($doc['title'] ?? '', 'dpcr') !== false;
    $ownerPos = strtolower($ownerInfo['position'] ?? '');
    $isOwnerDean = str_contains($ownerPos, 'dean');
    $isDocOpcr = (!$isDocDpcr) && ($ownerDocType === 'opcr' || stripos($doc['title'] ?? '', 'opcr') !== false || str_contains($ownerPos, 'vice president'));
    $isDocIperf = (!$isDocDpcr && !$isDocOpcr) && ($ownerDocType === 'iperf' || stripos($doc['title'] ?? '', 'iperf') !== false);
    if (($ownerDocType === 'dpcr' || $ownerDocType === 'cdpcr') && $isOwnerDean) {
        $targetEndCol = (!empty($doc['cdpcr_target_end'])) ? 'cdpcr_target_end' : 'dpcr_target_end';
    } else {
        $targetEndCol = $ownerDocType . '_target_end';
    }
    $now = date('Y-m-d H:i:s');
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

    $canEditApprover = ($canEditTargets || (!$isOwner && in_array($status, [
        FolderStatus::PENDING_TARGET_APPROVAL->value,
        FolderStatus::TARGET_APPROVED->value
    ]) && !$isGuide && empty($isCycleArchived)));

    $canEditEvaluation = ($isEvaluationPhase && (
        ($isOwner && in_array($status, [FolderStatus::DRAFT->value, FolderStatus::TARGET_APPROVED->value, FolderStatus::TO_EVALUATE->value, FolderStatus::REEVALUATE->value])) ||
        (!$isOwner && isset($routingStatus) && in_array($status, [FolderStatus::SUBMITTED->value, FolderStatus::EVALUATED->value]))
    ) && !$isGuide);

    $editableStatuses = [
        FolderStatus::DRAFT_TARGET->value,
        FolderStatus::TARGET_RETURNED->value
    ];
    $isEditable = ($canEditTargets || $canEditEvaluation);

    if (!empty($isCycleArchived)) {
        $canEditTargets = false;
        $canEditEvaluation = false;
        $canEditApprover = false;
        $isEditable = false;
    }
?>

<style>
    .spms-sheet-container {
        width: 100%;
        max-width: <?= $isDocDpcr ? '1400px' : ($isDocOpcr ? '1350px' : ($isDocIperf ? '1280px' : '1280px')) ?>;
        background: #ffffff;
        color: #000000;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
        border: 1px solid #cbd5e1;
        padding: 16px 12px;
        box-sizing: border-box;
        font-family: inherit;
        display: block !important;
        height: auto !important;
        min-height: fit-content !important;
        border-radius: 8px;
    }
    @media (min-width: 640px) {
        .spms-sheet-container {
            padding: 24px 28px;
        }
    }
    @media (min-width: 1024px) {
        .spms-sheet-container {
            padding: 36px 40px;
            border-radius: 4px;
        }
    }
    .spms-table-responsive-wrapper {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin-bottom: 0.5rem;
        position: relative;
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
    @media screen and (max-width: 1023px) {
        .spms-table {
            min-width: <?= $isDocDpcr ? '1180px' : ($isDocOpcr ? '1050px' : ($isDocIperf ? '850px' : '880px')) ?> !important;
        }
    }
    @media print {
        .spms-sheet-container {
            max-width: 100% !important;
            width: 100% !important;
            min-width: <?= $isDocDpcr ? '1180px' : ($isDocOpcr ? '1050px' : ($isDocIperf ? '850px' : '880px')) ?> !important;
            box-shadow: none !important;
        }
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
        -moz-appearance: textfield;
        appearance: textfield;
    }
    .spms-score-input::-webkit-outer-spin-button,
    .spms-score-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
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
    .budget-input-wrapper {
        display: flex;
        align-items: center;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        background: #ffffff;
        padding: 1px 3px;
        gap: 2px;
        box-sizing: border-box;
        transition: border-color 0.15s ease, background-color 0.15s ease;
        overflow: hidden;
    }
    .budget-input-wrapper:hover:not(:has(:disabled)) {
        border-color: #94a3b8;
    }
    .budget-input-wrapper:focus-within {
        border-color: #0284c7;
        box-shadow: 0 0 0 1px #0284c7;
        background: #f0f9ff;
    }
    .budget-input-wrapper:has(:disabled) {
        background: #f8fafc;
        border-color: #e2e8f0;
        cursor: not-allowed;
    }
    .header-budget-currency {
        border: none;
        border-bottom: 1px dashed #0284c7;
        background: transparent;
        color: #ba372a;
        outline: none;
        font-size: 9.5px;
        text-align: center;
        font-weight: bold;
        cursor: pointer;
    }
    .header-budget-currency:focus {
        border-bottom-style: solid;
        color: #0284c7;
    }
    @media print {
        .budget-input-wrapper {
            border: none !important;
            padding: 0 !important;
            background: transparent !important;
        }
        .header-budget-currency {
            border: none !important;
        }
        .field-budget-currency {
            border-right: none !important;
            font-size: 8.5px !important;
            font-weight: 700 !important;
        }
        .field-budget {
            font-size: 8.5px !important;
            letter-spacing: -0.2px !important;
            text-align: right !important;
            font-weight: 600 !important;
            width: 100% !important;
        }
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

    /* Responsive Mobile Meta, Summary & Signatories Matrix Layouts */
    @media screen and (max-width: 767px) {
        .spms-meta-matrix,
        .spms-meta-matrix > tbody,
        .spms-meta-matrix > tbody > tr,
        .spms-meta-matrix > tr {
            display: block !important;
            width: 100% !important;
        }
        .spms-meta-matrix > tbody > tr > td,
        .spms-meta-matrix > tr > td {
            display: block !important;
            width: 100% !important;
            box-sizing: border-box !important;
            border-left: none !important;
            border-right: none !important;
            border-top: none !important;
            border-bottom: 1px solid #000 !important;
        }
        .spms-meta-matrix > tbody > tr > td:last-child,
        .spms-meta-matrix > tr > td:last-child {
            border-bottom: none !important;
        }

        .spms-summary-matrix,
        .spms-summary-matrix > tbody,
        .spms-summary-matrix > tbody > tr,
        .spms-summary-matrix > tr {
            display: block !important;
            width: 100% !important;
        }
        .spms-summary-matrix > tbody > tr > td,
        .spms-summary-matrix > tr > td {
            display: block !important;
            width: 100% !important;
            box-sizing: border-box !important;
            border-left: none !important;
            border-right: none !important;
            border-top: none !important;
            border-bottom: 1px solid #000 !important;
        }
        .spms-summary-matrix > tbody > tr > td:last-child,
        .spms-summary-matrix > tr > td:last-child {
            border-bottom: none !important;
        }

        .spms-signatories-matrix,
        .spms-signatories-matrix > tbody,
        .spms-signatories-matrix > tbody > tr,
        .spms-signatories-matrix > tr {
            display: block !important;
            width: 100% !important;
        }
        .spms-signatories-matrix > tbody > tr > td,
        .spms-signatories-matrix > tr > td {
            display: block !important;
            width: 100% !important;
            box-sizing: border-box !important;
            border-left: none !important;
            border-right: none !important;
            border-top: none !important;
            border-bottom: 1px solid #000 !important;
        }
        .spms-signatories-matrix > tbody > tr > td:last-child,
        .spms-signatories-matrix > tr > td:last-child {
            border-bottom: none !important;
        }
    }

    @media screen and (max-width: 639px) {
        .spms-navy-bar {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            text-align: center !important;
            padding: 12px 10px !important;
            gap: 8px !important;
        }
        .spms-navy-bar > td {
            display: block !important;
            width: 100% !important;
            padding: 2px 0 !important;
            text-align: center !important;
            border: none !important;
        }
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
        .spms-table-responsive-wrapper {
            overflow: visible !important;
            width: 100% !important;
        }
        .spms-table {
            width: 100% !important;
            min-width: 100% !important;
            border-collapse: collapse !important;
            font-size: 9.5px !important;
            page-break-inside: auto;
        }
        .spms-table tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .spms-meta-matrix, .spms-navy-bar, .spms-signatories-matrix, .spms-sheet-container > div, .spms-table-responsive-wrapper {
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
    <?= view('components/govph_masthead') ?>
    
    <div class="flex-none flex items-center justify-between py-2 px-3 sm:px-6 bg-bg gap-2 sm:gap-4 print-hide">
        
        <?php if (!($isEmbed ?? false)): ?>
        <?php 
            $sysRole = session()->get('role');
            $homeUrl = ($sysRole === 'TWG') ? site_url('ratings') : site_url('folders');
            $returnUrl = $isOwner 
                ? site_url('folders/' . ($doc['document_folder_id'] ?? ''))
                : site_url('ratings' . (!empty($rootFolderId) ? '/' . $rootFolderId : ''));
        ?>
        <div class="flex items-center gap-1 sm:gap-3 min-w-0 flex-1">
            <!-- Return to Folder Button -->
            <a href="<?= $returnUrl ?>" 
               class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 bg-surface-border/20 hover:bg-surface-border/40 text-text text-xs font-bold rounded-lg border border-surface-border transition-colors shrink-0 shadow-sm mr-1 sm:mr-2 cursor-pointer"
               title="Return to <?= $isOwner ? 'Folder' : 'Ratings Dashboard' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span class="font-extrabold uppercase text-[11px] tracking-wider hidden sm:inline">Return</span>
            </a>

            <a href="<?= $homeUrl ?>" 
               class="cursor-pointer shrink-0"
               title="Return to SPMS Home">
                <!-- Back-to-folders brand mark. text-text (not text-white) so it stays visible on the
                     theme-aware bg-bg header in both light and dark mode. -->
                <div class="flex-shrink-0 flex items-center gap-1 mr-1 sm:mr-4 text-text hover:text-accent transition-colors">
                    <!-- Folder/document icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 sm:h-7 sm:w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="hidden sm:block font-black tracking-tighter text-xl uppercase">SPMS</span>
                </div>
            </a>

            <?php if (!empty($rateeNav)): ?>
                <!-- Quick Ratee Navigator Widget for Evaluators/Supervisors -->
                <div class="flex items-center bg-surface-border/25 border border-surface-border rounded-lg p-0.5 text-xs font-bold shadow-2xs shrink-0 mr-1 sm:mr-3">
                    <?php if (!empty($rateeNav['prev'])): ?>
                        <a href="<?= site_url('ratings/show/' . $rateeNav['prev']['folder_id']) ?>" 
                           class="flex items-center gap-1 px-2 py-1 hover:bg-surface-border/50 text-text rounded-md transition-colors cursor-pointer"
                           title="Previous Ratee: <?= esc($rateeNav['prev']['name']) ?><?= !empty($rateeNav['prev']['position']) ? ' (' . esc($rateeNav['prev']['position']) . ')' : '' ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                            <span class="hidden md:inline text-[11px]">Prev</span>
                        </a>
                    <?php else: ?>
                        <span class="flex items-center gap-1 px-2 py-1 text-text-muted/30 rounded-md cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                            <span class="hidden md:inline text-[11px]">Prev</span>
                        </span>
                    <?php endif; ?>

                    <span class="px-2 py-0.5 text-[11px] font-black text-text border-x border-surface-border/50 whitespace-nowrap"
                          title="Reviewing Ratee <?= $rateeNav['currentIndex'] ?> of <?= $rateeNav['totalRatees'] ?>">
                        <span class="text-accent font-extrabold"><?= $rateeNav['currentIndex'] ?></span>
                        <span class="text-text-muted font-normal">/</span>
                        <span><?= $rateeNav['totalRatees'] ?></span>
                    </span>

                    <?php if (!empty($rateeNav['next'])): ?>
                        <a href="<?= site_url('ratings/show/' . $rateeNav['next']['folder_id']) ?>" 
                           class="flex items-center gap-1 px-2 py-1 hover:bg-surface-border/50 text-text rounded-md transition-colors cursor-pointer"
                           title="Next Ratee: <?= esc($rateeNav['next']['name']) ?><?= !empty($rateeNav['next']['position']) ? ' (' . esc($rateeNav['next']['position']) . ')' : '' ?>">
                            <span class="hidden md:inline text-[11px]">Next</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    <?php else: ?>
                        <span class="flex items-center gap-1 px-2 py-1 text-text-muted/30 rounded-md cursor-not-allowed">
                            <span class="hidden md:inline text-[11px]">Next</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
        <div class="flex items-center gap-2 min-w-0 flex-1">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-sky-100 text-sky-800 dark:bg-sky-950/80 dark:text-sky-300 text-[11px] font-bold border border-sky-200 dark:border-sky-800">
                Reference Guide (Read-Only)
            </span>
            <span class="font-bold text-xs text-text truncate"><?= esc($doc['title']) ?></span>
        </div>
        <?php endif; ?>

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
            <?php if ($canEditTargets || $canEditEvaluation): ?>
            <!-- Save Button with hover (Ctrl + S) -->
            <div class="relative group inline-flex items-center print-hide">
                <button type="button" onclick="saveDocument(true)" title="Save (Ctrl + S)"
                        class="inline-flex items-center gap-1.5 px-2.5 sm:px-3.5 py-2 sm:py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] sm:text-xs font-bold rounded-lg shadow-sm transition-all active:scale-[0.98] cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    <span class="hidden sm:inline">Save</span>
                </button>
                <div class="absolute top-full left-1/2 -translate-x-1/2 mt-2 hidden group-hover:flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-900 text-white shadow-xl pointer-events-none whitespace-nowrap z-50">
                    <span>Save</span>
                    <span class="text-slate-400 font-mono text-[9px] bg-slate-800 px-1 py-0.5 rounded border border-slate-700">(Ctrl + S)</span>
                    <div class="absolute -top-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45"></div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($isOwner || session()->get('role') === 'Admin'): ?>
            <!-- Print / Export PDF Button -->
            <button type="button" onclick="exportToPdf()" 
                    class="inline-flex items-center gap-1.5 px-2.5 sm:px-3.5 py-2 sm:py-2.5 bg-surface-border/20 hover:bg-surface-border/40 text-text text-[10px] sm:text-xs font-bold rounded-lg border border-surface-border transition-all cursor-pointer shadow-sm print-hide"
                    title="Print Document or Export to PDF">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#FFB800]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span class="hidden md:inline">Print / Export PDF</span>
                <span class="hidden sm:inline md:hidden">Print</span>
            </button>

            <!-- Export to Excel Button -->
            <button type="button" onclick="exportToExcel()" 
                    class="inline-flex items-center gap-1.5 px-2.5 sm:px-3.5 py-2 sm:py-2.5 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-[10px] sm:text-xs font-bold rounded-lg border border-emerald-500/30 hover:border-emerald-500/50 transition-all cursor-pointer shadow-sm active:scale-[0.98] print-hide"
                    title="Export document to official Civil Service Commission Excel spreadsheet (.xlsx)">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="hidden md:inline">Export Excel</span>
                <span class="hidden sm:inline md:hidden">Excel</span>
            </button>
            <?php endif; ?>

            <!-- CSC Scoring Rubric Button -->
            <button type="button" id="btn-toggle-rubric" onclick="toggleRubricDrawer()" 
                    class="inline-flex items-center gap-1.5 px-2.5 sm:px-3.5 py-2 sm:py-2.5 bg-surface-border/20 hover:bg-surface-border/40 text-text text-[10px] sm:text-xs font-bold rounded-lg border border-surface-border transition-all cursor-pointer shadow-sm active:scale-[0.98] print-hide"
                    title="View official CSC 5-point rating rubric for Quality, Timeliness, and Efficiency">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span class="hidden md:inline">Rubric Guide</span>
                <span class="hidden sm:inline md:hidden">Rubric</span>
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
                                    <?= $isEvalPeriodEnded ? 'disabled' : 'onclick="unapproveFolderEvaluation()"' ?>
                                    class="<?= $isEvalPeriodEnded ? 'bg-warning-500/50 cursor-not-allowed opacity-80' : 'bg-warning-500 hover:bg-warning-600 shadow-warning-500/20 active:scale-[0.98] cursor-pointer' ?> text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg transition-all">
                                Remove<span class="hidden sm:inline"> Approval</span>
                            </button>
                        <?php else: ?>
                            <button type="button" disabled class="bg-success-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                                <span class="hidden sm:inline">Folder </span><?= $status === FolderStatus::TWG_APPROVED->value ? 'TWG Approved' : ($status === FolderStatus::TWG_DISAPPROVED->value ? 'TWG Disapproved' : 'Approved') ?>
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                <?php elseif ($status === FolderStatus::EVALUATED->value): ?>
                    <?php if ($isOwner): ?>
                        <?php 
                            $ownerDocType = strtolower($doc['doc_type'] ?? 'ipcr');
                            $evalEndCol = $ownerDocType . '_eval_end';
                            $isEvalPeriodEnded = !empty($doc[$evalEndCol]) && date('Y-m-d H:i:s') > $doc[$evalEndCol]; 
                        ?>
                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <button type="button" disabled class="bg-amber-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg shadow-sm opacity-90 cursor-not-allowed flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="hidden sm:inline">Awaiting Evaluation Review</span>
                                <span class="sm:hidden">Awaiting Review</span>
                            </button>
                            <button id="btn-unsubmit-eval" type="button" 
                                    <?= $isEvalPeriodEnded ? 'disabled' : 'onclick="unsubmitEvaluationDocument()"' ?>
                                    class="<?= $isEvalPeriodEnded ? 'bg-rose-50/50 text-rose-300 border-rose-200 cursor-not-allowed opacity-60' : 'bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:hover:bg-rose-900/60 dark:text-rose-300 border border-rose-300 dark:border-rose-800 shadow-sm active:scale-[0.98] cursor-pointer' ?> text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg transition-all flex items-center gap-1"
                                    title="<?= $isEvalPeriodEnded ? 'Evaluation period has ended' : 'Revoke your self-rating to make edits' ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                </svg>
                                <span>Revoke Self-Rating</span>
                            </button>
                        </div>
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
                                    <?= $isEvalPeriodEnded ? 'disabled' : 'onclick="unapproveFolderEvaluation()"' ?>
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
                                        onclick="returnFolderRevision()" 
                                        class="bg-revision-500 hover:bg-revision-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg shadow-revision-500/20 transition-all active:scale-[0.98] cursor-pointer">
                                    Return<span class="hidden sm:inline"> for Revision</span>
                                </button>
                                <button id="btn-approve" type="button" 
                                        onclick="approveFolderEvaluation()" 
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
                        <?php if ($isOwner): ?>
                            <?php if (session()->get('role') === 'Admin'): ?>
                                <div class="flex items-center gap-1.5 bg-emerald-50 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg shadow-xs">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    <span>Master Cycle Template</span>
                                </div>
                            <?php elseif (isset($isParentTargetApproved) && !$isParentTargetApproved): ?>
                                <button type="button" disabled 
                                        class="bg-amber-500/70 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-sm cursor-not-allowed flex items-center gap-1.5" 
                                        title="Waiting for Superior Target Approval. Under SPMS rules, individual commitments require approved superior targets as a basis.">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="hidden sm:inline">Waiting for Superior Approval</span><span class="sm:hidden">Waiting</span>
                                </button>
                            <?php else: ?>
                                <button id="btn-submit-target" type="button" 
                                        onclick="saveWith({ after: () => lockFolderTarget() })" 
                                        class="bg-info-500 hover:bg-info-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg shadow-info-500/20 transition-all active:scale-[0.98] cursor-pointer">
                                    <span class="sm:hidden">Submit Target</span><span class="hidden sm:inline">Submit Targets</span>
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <button type="button" disabled class="bg-zinc-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                                Wait<span class="hidden sm:inline">ing for Employee Targets</span>
                            </button>
                        <?php endif; ?>
                    </div>

                <?php elseif ($status === FolderStatus::PENDING_TARGET_APPROVAL->value): ?>
                    <?php if ($isOwner): ?>
                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <button type="button" disabled class="bg-amber-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg shadow-sm opacity-90 cursor-not-allowed flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="hidden sm:inline">Awaiting Target Approval</span>
                                <span class="sm:hidden">Awaiting Approval</span>
                            </button>
                            <button id="btn-unsubmit-target" type="button" 
                                    onclick="unsubmitTargetDocument()" 
                                    class="bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:hover:bg-rose-900/60 dark:text-rose-300 border border-rose-300 dark:border-rose-800 text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg shadow-sm transition-all active:scale-[0.98] cursor-pointer flex items-center gap-1"
                                    title="Revoke your submission to make edits">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                </svg>
                                <span>Revoke Submission</span>
                            </button>
                        </div>
                    <?php else: ?>
                        <?php 
                            $ownerDocType = strtolower($doc['doc_type'] ?? 'ipcr');
                            $ownerPos = strtolower($ownerInfo['position'] ?? '');
                            $isOwnerDean = str_contains($ownerPos, 'dean');
                            $isOwnerChair = str_contains($ownerPos, 'chair') || str_contains($ownerPos, 'head');
                            if (($ownerDocType === 'dpcr' || $ownerDocType === 'cdpcr') && $isOwnerDean) {
                                $targetEndCol = (!empty($doc['cdpcr_target_end'])) ? 'cdpcr_target_end' : 'dpcr_target_end';
                            } else {
                                $targetEndCol = $ownerDocType . '_target_end';
                            }
                            $isTargetPeriodEnded = !empty($doc[$targetEndCol]) && date('Y-m-d H:i:s') > $doc[$targetEndCol]; 
                            $isAdmin = session()->get('role') === 'Admin';
                            $userPosLower = strtolower(session()->get('position') ?? '');
                            $isSupervisor = session()->get('role') === 'Supervisor' || str_contains($userPosLower, 'chair') || str_contains($userPosLower, 'head');
                            $docTitleUpper = strtoupper(trim($doc['title'] ?? ''));
                            $isTrueOpcr = str_contains($docTitleUpper, 'OPCR') || str_contains($docTitleUpper, 'OFFICE') || (strtoupper($doc['doc_type'] ?? '') === 'OPCR');
                            $isTrueDpcr = str_contains($docTitleUpper, 'DPCR') || str_contains($docTitleUpper, 'DIVISION') || str_contains($docTitleUpper, 'DEPARTMENT') || (strtoupper($doc['doc_type'] ?? '') === 'DPCR');

                            $canRelease = false;
                            $releaseScope = null;
                            $releaseLabel = '';
                            $releaseTitle = '';

                            if ($isTrueOpcr && $isAdmin) {
                                $canRelease = true;
                                $releaseScope = 'deans';
                                $releaseLabel = 'to Deans';
                                $releaseTitle = 'Approve this OPCR and automatically distribute it to all College Deans as their target basis';
                            } elseif ($isTrueDpcr && ($isAdmin || $isSupervisor)) {
                                $canRelease = false;
                            }
                        ?>
                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <button id="btn-return-target" type="button" 
                                    <?= $isTargetPeriodEnded ? 'disabled' : 'onclick="returnTargetRevision()"' ?>
                                    class="<?= $isTargetPeriodEnded ? 'bg-revision-500/50 cursor-not-allowed opacity-80' : 'bg-revision-500 hover:bg-revision-600 shadow-revision-500/20 active:scale-[0.98] cursor-pointer' ?> text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg shadow-lg transition-all">
                                Return<span class="hidden sm:inline"> Target for Revision</span>
                            </button>

                            <?php if ($canRelease): ?>
                                <button id="btn-approve-release-target" type="button" 
                                        <?= $isTargetPeriodEnded ? 'disabled' : 'onclick="approveFolderTarget(\'' . $releaseScope . '\')"' ?>
                                        class="<?= $isTargetPeriodEnded ? 'bg-emerald-600/50 cursor-not-allowed opacity-80' : 'bg-gradient-to-r from-emerald-600 to-[#064e3b] hover:from-emerald-700 hover:to-[#085a3a] shadow-emerald-600/30 active:scale-[0.98] cursor-pointer' ?> text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg shadow-lg transition-all flex items-center gap-1.5"
                                        title="<?= esc($releaseTitle) ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    <span>Approve & Release<span class="hidden sm:inline"> <?= esc($releaseLabel) ?></span></span>
                                </button>
                            <?php endif; ?>

                            <button id="btn-approve-target" type="button" 
                                    <?= $isTargetPeriodEnded ? 'disabled' : 'onclick="approveFolderTarget(null)"' ?>
                                    class="<?= $isTargetPeriodEnded ? 'bg-success-500/50 cursor-not-allowed opacity-80' : 'bg-success-500 hover:bg-success-600 shadow-success-500/20 active:scale-[0.98] cursor-pointer' ?> text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg shadow-lg transition-all">
                                Approve<span class="hidden sm:inline"> Target</span>
                            </button>
                        </div>
                    <?php endif; ?>

                <?php elseif ($status === FolderStatus::TARGET_APPROVED->value || $status === FolderStatus::SUBMITTED->value): ?>
                    <?php if (!$isOwner && $status === FolderStatus::TARGET_APPROVED->value): ?>
                        <?php 
                            $ownerDocType = strtolower($doc['doc_type'] ?? 'ipcr');
                            $ownerPos = strtolower($ownerInfo['position'] ?? '');
                            $isOwnerDean = str_contains($ownerPos, 'dean');
                            $isOwnerChair = str_contains($ownerPos, 'chair') || str_contains($ownerPos, 'head');
                            if (($ownerDocType === 'dpcr' || $ownerDocType === 'cdpcr') && $isOwnerDean) {
                                $targetEndCol = (!empty($doc['cdpcr_target_end'])) ? 'cdpcr_target_end' : 'dpcr_target_end';
                            } else {
                                $targetEndCol = $ownerDocType . '_target_end';
                            }
                            $isTargetPeriodEnded = !empty($doc[$targetEndCol]) && date('Y-m-d H:i:s') > $doc[$targetEndCol]; 
                            $isAdmin = session()->get('role') === 'Admin';
                            $userPosLower = strtolower(session()->get('position') ?? '');
                            $isSupervisor = session()->get('role') === 'Supervisor' || str_contains($userPosLower, 'chair') || str_contains($userPosLower, 'head');
                            $docTitleUpper = strtoupper(trim($doc['title'] ?? ''));
                            $isTrueOpcr = str_contains($docTitleUpper, 'OPCR') || str_contains($docTitleUpper, 'OFFICE') || (strtoupper($doc['doc_type'] ?? '') === 'OPCR');
                            $isTrueDpcr = str_contains($docTitleUpper, 'DPCR') || str_contains($docTitleUpper, 'DIVISION') || str_contains($docTitleUpper, 'DEPARTMENT') || (strtoupper($doc['doc_type'] ?? '') === 'DPCR');

                            $canRelease = false;
                            $releaseScope = null;
                            $releaseLabel = '';
                            $releaseTitle = '';

                            if ($isTrueOpcr && $isAdmin) {
                                $canRelease = true;
                                $releaseScope = 'deans';
                                $releaseLabel = 'to Deans';
                                $releaseTitle = 'Distribute this approved OPCR to all College Deans as their target basis';
                            } elseif ($isTrueDpcr && ($isAdmin || $isSupervisor)) {
                                $canRelease = false;
                            }
                        ?>
                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <?php if ($canRelease): ?>
                                <button id="btn-release-subordinates" type="button" 
                                        <?= $isTargetPeriodEnded ? 'disabled' : 'onclick="approveFolderTarget(\'' . $releaseScope . '\')"' ?>
                                        class="<?= $isTargetPeriodEnded ? 'bg-emerald-600/50 cursor-not-allowed opacity-80' : 'bg-gradient-to-r from-emerald-600 to-[#064e3b] hover:from-emerald-700 hover:to-[#085a3a] shadow-emerald-600/30 active:scale-[0.98] cursor-pointer' ?> text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg shadow-lg transition-all flex items-center gap-1.5"
                                        title="<?= esc($releaseTitle) ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    <span>Release<span class="hidden sm:inline"> <?= esc($releaseLabel) ?></span></span>
                                </button>
                            <?php endif; ?>

                            <button id="btn-unapprove-target" type="button" 
                                    <?= $isTargetPeriodEnded ? 'disabled' : 'onclick="unapproveFolderTarget()"' ?>
                                    class="<?= $isTargetPeriodEnded ? 'bg-warning-500/50 cursor-not-allowed opacity-80' : 'bg-warning-500 hover:bg-warning-600 shadow-warning-500/20 active:scale-[0.98] cursor-pointer' ?> text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg shadow-lg transition-all">
                                Remove<span class="hidden sm:inline"> Approval</span>
                            </button>
                        </div>
                    <?php else: ?>
                        <button type="button" disabled class="bg-highlight-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
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

    <!-- Main Workspace Split Container: Form (Left ~3/4) & Rubric (Right ~1/4) -->
    <div class="flex-1 min-h-0 w-full relative flex flex-row overflow-hidden" id="workspace-split-container">
        <div class="flex-1 min-h-0 h-full relative bg-[#031c12] dark:bg-[#031c12] overflow-x-auto transition-all duration-300 ease-out" id="editor-container">
        <?php if (!empty($basisDoc) && !$isGuide): ?>
        <!-- SUPERIOR BASIS STATIC FORM WORKSPACE -->
        <div id="spms-basis-workspace" class="hidden w-full h-full overflow-y-auto p-2 sm:p-6 lg:p-8 flex justify-center items-start custom-scrollbar print:p-0 print:bg-white print:overflow-visible">
            <article id="basis-printable-sheet" class="spms-sheet-container block space-y-5">
                <!-- Top Reference Bar inside Paper -->
                <div class="flex items-center justify-between pb-2.5 border-b-2 border-sky-500 mb-2">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-sky-100 text-sky-800 border border-sky-300">
                            Superior Basis Reference (Read-Only)
                        </span>
                        <span class="text-xs font-bold text-slate-800" id="basis-header-doc-title">
                            <?= esc($basisDoc['title'] ?? '') ?>
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <?php 
                            $basisTitleUpper = strtoupper($basisDoc['title'] ?? '');
                            $basisTypeUpper  = strtoupper($basisDoc['doc_type'] ?? '');
                            $isBasisOpcr = !empty($isParentDocOpcr) || str_contains($basisTitleUpper, 'OPCR') || ($basisTypeUpper === 'OPCR');
                            $isBasisDpcr = !empty($isParentDocDpcr) || str_contains($basisTitleUpper, 'DPCR') || str_contains($basisTitleUpper, 'DEPARTMENT') || str_contains($basisTitleUpper, 'DIVISION') || ($basisTypeUpper === 'DPCR');
                        ?>
                        <?php if ($isParentTargetApproved): ?>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                <?= $isBasisOpcr ? 'Institutional Basis' : 'Approved Targets' ?>
                            </span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-300">
                                Pending Approval
                            </span>
                        <?php endif; ?>
                        <a href="<?= site_url('document/' . $basisDoc['id']) ?>" target="_blank" 
                           class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-[10px] font-bold text-slate-600 hover:text-sky-600 border border-slate-300 hover:border-sky-300 transition-colors print:hidden"
                           title="Open in new window or tab">
                            <span>Open in New Tab ↗</span>
                        </a>
                    </div>
                </div>

                <!-- FORM TITLE -->
                <div style="text-align: center; margin-bottom: 14px;">
                    <h1 style="font-size: 16px; font-weight: bold; margin: 0 0 4px 0; text-transform: uppercase; color: #000000;" id="basis-sheet-title">
                        <?= esc($basisDoc['title'] ?? 'Office Performance Commitment and Review (OPCR)') ?>
                    </h1>
                </div>

                <!-- PREAMBLE (Filled Version of Template) -->
                <div style="margin-bottom: 18px; font-size: 11px; line-height: 1.6; text-align: justify; color: #000000;">
                    I, <input type="text" id="basis-val-ratee-name" readonly value="" style="font-weight: bold; color: #000000; border: none; border-bottom: 1px solid #94a3b8; text-align: center; min-width: 170px; outline: none; padding: 2px 4px; background: transparent; font-family: inherit; font-size: 11px;">, 
                    <input type="text" id="basis-val-ratee-pos" readonly value="" style="color: #000000; border: none; border-bottom: 1px solid #94a3b8; text-align: center; min-width: 200px; outline: none; padding: 2px 4px; background: transparent; font-family: inherit; font-size: 11px;"> of the 
                    <input type="text" id="basis-val-ratee-dept" readonly value="" style="color: #000000; border: none; border-bottom: 1px solid #94a3b8; text-align: center; min-width: 170px; outline: none; padding: 2px 4px; background: transparent; font-family: inherit; font-size: 11px;">, commit to deliver and agree to be rated on the attainment of the following targets in accordance with the indicated measure for the period 
                    <input type="text" id="basis-val-ratee-period" readonly value="" style="font-weight: bold; color: #000000; border: none; border-bottom: 1px solid #94a3b8; text-align: center; min-width: 320px; outline: none; padding: 2px 4px; background: transparent; font-family: inherit; font-size: 11px;">.
                </div>

                <!-- APPROVER, RATEE, AND RATING SCALE MATRIX (Filled Version of Template) -->
                <table class="spms-meta-matrix" style="width: 100%; border-collapse: collapse; border: none; margin-bottom: 18px; font-size: 11px;">
                    <tr>
                        <!-- Left: Approved By (Extreme Left-most) -->
                        <td style="vertical-align: top; border: none; padding: 0 20px 0 0; text-align: left;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                <div style="font-weight: bold; color: #000000;">APPROVED BY:</div>
                            </div>
                            <div id="basis-approvers-container" style="display: flex; flex-direction: column; gap: 8px;">
                                <div class="approver-item" style="position: relative;">
                                    <table style="width: 100%; border-collapse: collapse; border: none; font-size: 11px;">
                                        <tr>
                                            <td style="width: 65px; border: none; padding: 3px 0; font-weight: bold; color: #000000;">Name:</td>
                                            <td style="border: none; padding: 3px 0;">
                                                <input type="text" id="basis-val-approver-name" readonly value="" style="font-weight: bold; color: #000000; border: none; border-bottom: 1px solid #94a3b8; outline: none; font-size: 11px; width: 60%; background: transparent; font-family: inherit;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="border: none; padding: 3px 0; font-weight: bold; color: #000000;">Position:</td>
                                            <td style="border: none; padding: 3px 0;">
                                                <input type="text" id="basis-val-approver-pos" readonly value="" style="color: #000000; border: none; border-bottom: 1px solid #94a3b8; outline: none; font-size: 11px; width: 80%; background: transparent; font-family: inherit;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="border: none; padding: 3px 0; font-weight: bold; color: #000000;">Date:</td>
                                            <td style="border: none; padding: 3px 0;">
                                                <input type="text" id="basis-val-approver-date" readonly value="" style="color: #000000; border: none; border-bottom: 1px solid #94a3b8; outline: none; font-size: 11px; width: 130px; background: transparent; font-family: inherit;">
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </td>

                        <!-- Right: Name of Employee & Rating Scale (Extreme Right-most) -->
                        <td style="vertical-align: top; border: none; padding: 0; text-align: right; width: 1%; white-space: nowrap;">
                            <div style="display: inline-block; text-align: left; min-width: 240px;">
                                <!-- Name of Employee Block -->
                                <div style="margin-bottom: 14px;">
                                    <div>
                                        <input type="text" id="basis-val-ratee-sign" readonly value="" style="color: #000000; font-weight: bold; border: none; border-bottom: 1px solid #94a3b8; text-align: left; width: 240px; outline: none; font-size: 11px; padding: 2px 0; background: transparent; font-family: inherit;">
                                    </div>
                                    <div style="font-size: 11px; color: #000000; margin-top: 3px;" id="basis-val-ratee-sign-pos">Name of Employee</div>
                                    <div style="margin-top: 4px; font-size: 11px; color: #000000;">
                                        Date: <input type="text" id="basis-val-ratee-sign-date" readonly value="" style="border: none; border-bottom: 1px solid #94a3b8; width: 130px; outline: none; font-size: 11px; color: #000000; background: transparent; font-family: inherit;">
                                    </div>
                                </div>

                                <!-- Rating Scale -->
                                <div style="font-size: 10px; color: #000000;">
                                    <div style="font-weight: bold; margin-bottom: 3px;">Rating Scale:</div>
                                    <div style="display: flex; flex-direction: column; gap: 2px; line-height: 1.35;">
                                        <div>5 – Outstanding</div>
                                        <div>4 – Very Satisfactory</div>
                                        <div>3 – Satisfactory</div>
                                        <div>2 – Unsatisfactory</div>
                                        <div>1 – Poor</div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- MAIN TABLE OF DELIVERABLES & RATINGS (Static Read-Only) -->
                <div class="spms-table-responsive-wrapper">
                    <div class="lg:hidden flex items-center justify-between text-[11px] text-slate-500 bg-slate-100 dark:bg-slate-800/40 px-3 py-1.5 rounded border border-slate-200 dark:border-slate-700 mb-2 print-hide">
                        <span class="flex items-center gap-1 font-medium">↔ Swipe matrix horizontally to view all cascaded targets</span>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider" id="basis-col-count-badge"><?= ($isBasisOpcr || $isBasisDpcr) ? '10 Columns' : '8 Columns' ?></span>
                    </div>
                    <table class="spms-table" style="width: 100%; border-collapse: collapse; border: 2px solid #000; font-size: 11px;">
                        <colgroup id="basis-table-colgroup">
                            <?php if ($isBasisOpcr || $isBasisDpcr): ?>
                            <col style="width: 15%;">
                            <col style="width: 17%;">
                            <col style="width: 11%;">
                            <col style="width: 13%;">
                            <col style="width: 18%;">
                            <col style="width: 3.5%;">
                            <col style="width: 3.5%;">
                            <col style="width: 3.5%;">
                            <col style="width: 4.5%;">
                            <col style="width: 11%;">
                            <?php else: ?>
                            <col style="width: 25%;">
                            <col style="width: 25%;">
                            <col style="width: 24%;">
                            <col style="width: 4%;">
                            <col style="width: 4%;">
                            <col style="width: 4%;">
                            <col style="width: 5%;">
                            <col style="width: 9%;">
                            <?php endif; ?>
                        </colgroup>
                        <thead id="basis-table-thead">
                            <?php if ($isBasisOpcr): ?>
                            <tr style="background-color: #fff2cc; text-align: center; font-weight: bold; border-bottom: 1px solid #000; font-size: 10px;">
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">PROJECT / PROGRAM / ACTIVITIES</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">SUCCESS INDICATORS<br><span style="font-weight: normal; font-size: 9px;">(TARGETS + MEASURES)</span></th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ALLOTTED BUDGET</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">DIVISIONS ACCOUNTABLE</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ACTUAL ACCOMPLISHMENTS</th>
                                <th colspan="4" style="border: 1px solid #000; padding: 4px;">RATING</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">REMARKS</th>
                            </tr>
                            <tr style="background-color: #fff2cc; text-align: center; font-weight: bold; border-bottom: 2px solid #000; font-size: 10px;">
                                <th style="border: 1px solid #000; padding: 4px 2px;">Q</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">T</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">E</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">Ave.</th>
                            </tr>
                            <?php elseif ($isBasisDpcr): ?>
                            <tr style="background-color: #cfe2f3; text-align: center; font-weight: bold; border-bottom: 1px solid #000; font-size: 10px;">
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">PROGRAMS, PROJECTS, ACTIVITIES</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">SUCCESS INDICATORS<br><span style="font-weight: normal; font-size: 9px;">(TARGETS + MEASURES)</span></th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ALLOTTED BUDGET</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">INDIVIDUALS / OFFICES ACCOUNTABLE</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ACTUAL ACCOMPLISHMENTS</th>
                                <th colspan="4" style="border: 1px solid #000; padding: 4px;">RATING</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">REMARKS</th>
                            </tr>
                            <tr style="background-color: #cfe2f3; text-align: center; font-weight: bold; border-bottom: 2px solid #000; font-size: 10px;">
                                <th style="border: 1px solid #000; padding: 4px 2px;">Q</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">T</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">E</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">Ave.</th>
                            </tr>
                            <?php else: ?>
                            <tr style="background: #f1f5f9; text-align: center; font-weight: bold; border-bottom: 1px solid #000;">
                                <th rowspan="2" style="padding: 8px; border: 1px solid #000;">ACADEMIC FUNCTION /<br>MAJOR FINAL OUTPUT</th>
                                <th rowspan="2" style="padding: 8px; border: 1px solid #000;">SUCCESS INDICATORS<br><span style="font-size: 9px; font-weight: normal;">(Targets + Measures)</span></th>
                                <th rowspan="2" style="padding: 8px; border: 1px solid #000;">ACTUAL ACCOMPLISHMENTS</th>
                                <th colspan="4" style="padding: 4px; border: 1px solid #000;">RATING</th>
                                <th rowspan="2" style="padding: 8px; border: 1px solid #000;">REMARKS</th>
                            </tr>
                            <tr style="background: #f1f5f9; text-align: center; font-weight: bold; border-bottom: 2px solid #000; font-size: 10px;">
                                <th style="padding: 4px; border: 1px solid #000;">Q</th>
                                <th style="padding: 4px; border: 1px solid #000;">T</th>
                                <th style="padding: 4px; border: 1px solid #000;">E</th>
                                <th style="padding: 4px; border: 1px solid #000;">Ave.</th>
                            </tr>
                            <?php endif; ?>
                        </thead>
                        <tbody id="basis-tbody-core"></tbody>
                        <tbody id="basis-tbody-strategic"></tbody>
                        <tbody id="basis-tbody-support"></tbody>
                    </table>
                </div>

                <!-- Custom HTML Container for older/TinyMCE templates if applicable -->
                <div id="basis-html-fallback" class="hidden text-sm leading-relaxed p-4 bg-white text-slate-800"></div>
            </article>
        </div>
        <?php endif; ?>

        <!-- SPMS Structured Form Builder Container -->
        <div id="spms-form-workspace" class="hidden w-full h-full overflow-y-auto p-2 sm:p-6 lg:p-8 flex flex-col items-center custom-scrollbar print:p-0 print:bg-white print:overflow-visible">
            <div class="w-full max-w-[1280px] flex flex-col gap-4 print:w-full print:block">
                <article id="printable-form" class="spms-sheet-container block space-y-5">
                
                <?php if (!empty($isCycleArchived)): ?>
                    <!-- Archived & Frozen Cycle Warning Banner -->
                    <div class="p-3.5 bg-amber-500/10 border border-amber-500/30 text-amber-900 dark:text-amber-300 rounded-xl text-xs font-bold flex items-center justify-between gap-3 shadow-xs print-hide">
                        <div class="flex items-center gap-2.5">
                            <div class="p-1 rounded-full bg-amber-500/20 text-amber-600 dark:text-amber-400 shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <span>This evaluation cycle has been closed and archived. Ratings, accomplishments, and targets are permanently frozen in read-only mode.</span>
                        </div>
                        <span class="px-2.5 py-1 rounded-md bg-amber-500/20 text-[10px] uppercase tracking-wider font-extrabold shrink-0 border border-amber-500/30">Cycle Closed</span>
                    </div>
                <?php endif; ?>

                <!-- INSTITUTIONAL FORM HEADER -->
                <div style="text-align: center; margin-bottom: 20px;">
                    <h2 style="font-size: 15px; font-weight: 900; margin: 0; text-transform: uppercase; letter-spacing: 0.05em; color: #000000; line-height: 1.4;" id="spms-doc-title">
                        <?= $isDocIperf 
                            ? 'INDIVIDUAL PERFORMANCE EVALUATION RATING FORM FOR CONTRACT OF SERVICE AND JOB ORDER PERSONNEL' 
                            : ($isDocDpcr ? 'DEPARTMENT PERFORMANCE COMMITMENT AND REVIEW (DPCR)' : ($isDocOpcr ? 'OFFICE PERFORMANCE COMMITMENT AND REVIEW (OPCR)' : 'INDIVIDUAL PERFORMANCE COMMITMENT AND REVIEW (IPCR)')) ?>
                    </h2>
                    <?php if ($isDocIperf): ?>
                    <div style="font-size: 11px; font-style: italic; color: #000000; margin-top: 4px;">
                        (attach rubrics for the rating of actual accomplishments vis-à-vis expected outputs)
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($isDocIperf): ?>
                <!-- IPERF 3-ROW METADATA MATRIX (EXACT REPLICA OF PHOTO) -->
                <table class="spms-meta-matrix" style="width: 100%; border-collapse: collapse; border: 1px solid #000000; margin-bottom: 18px; font-size: 11px;">
                    <tr style="border-bottom: 1px solid #000000;">
                        <td style="width: 18%; padding: 6px 8px; font-weight: bold; border-right: 1px solid #000000; background: #fafafa;">Name of Employee:</td>
                        <td style="width: 42%; padding: 6px 8px; border-right: 1px solid #000000;">
                            <input type="text" id="ratee-name" value="" placeholder="indicate full name (First Name Middle Initial Last Name, Extension; e.g., Juan D. Cruz III)" style="width: 100%; border: none; outline: none; font-size: 11px; color: #ba372a; font-weight: bold;" <?= $canEditTargets ? '' : 'disabled' ?>>
                        </td>
                        <td style="width: 15%; padding: 6px 8px; font-weight: bold; border-right: 1px solid #000000; background: #fafafa;">Classification:</td>
                        <td style="width: 25%; padding: 6px 8px;">
                            <select id="ratee-classification" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 3px; padding: 2px 4px; font-size: 11px; color: #000; font-weight: bold; background: #ffffff;" <?= $canEditTargets ? '' : 'disabled' ?>>
                                <option value="Contract of Service (COS)">Contract of Service (COS)</option>
                                <option value="Job Order">Job Order</option>
                            </select>
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid #000000;">
                        <td style="padding: 6px 8px; font-weight: bold; border-right: 1px solid #000000; background: #fafafa;">Position:</td>
                        <td style="padding: 6px 8px; border-right: 1px solid #000000;">
                            <input type="text" id="ratee-position" value="" placeholder="indicate the full position title specified in the contract/job order" style="width: 100%; border: none; outline: none; font-size: 11px; color: #ba372a;" <?= $canEditTargets ? '' : 'disabled' ?>>
                        </td>
                        <td style="padding: 6px 8px; font-weight: bold; border-right: 1px solid #000000; background: #fafafa;">Rating Period:</td>
                        <td style="padding: 6px 8px;">
                            <input type="text" id="ratee-period" value="" placeholder="e.g., July - December 2024" style="width: 100%; border: none; outline: none; font-size: 11px; color: #ba372a; font-weight: bold;" <?= $canEditTargets ? '' : 'disabled' ?>>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 8px; font-weight: bold; border-right: 1px solid #000000; background: #fafafa;">Office:</td>
                        <td colspan="3" style="padding: 6px 8px;">
                            <input type="text" id="ratee-dept" value="" placeholder="indicate in full the specific area of assignment (e.g., CIS - Department of Development Communication)" style="width: 100%; border: none; outline: none; font-size: 11px; color: #ba372a;" <?= $canEditTargets ? '' : 'disabled' ?>>
                        </td>
                    </tr>
                </table>
                <!-- Hidden compatibility elements for IPERF -->
                <input type="hidden" id="ratee-sign-name" value="">
                <input type="hidden" id="ratee-sign-date" value="">
                <div id="approvers-container" style="display: none;">
                    <div class="approver-item">
                        <input type="hidden" class="field-approver-name" id="approver-name" value="">
                        <input type="hidden" class="field-approver-pos" id="approver-pos" value="">
                        <input type="hidden" class="field-approver-date" id="approver-date" value="">
                    </div>
                </div>
                <?php else: ?>
                <!-- Preamble with Red Hint Placeholders -->
                <div style="margin-bottom: 18px; font-size: 11px; line-height: 1.6; text-align: justify; color: #000000;">
                    I, <input type="text" id="ratee-name" value="" placeholder="FULL NAME HERE" style="font-weight: bold; color: #ba372a; border: none; border-bottom: 1px solid #ba372a; text-align: center; min-width: 170px; outline: none; padding: 2px 4px;" <?= $canEditTargets ? '' : 'disabled' ?>>, 
                    <input type="text" id="ratee-position" value="" placeholder="Position and Official Designation" style="color: #ba372a; border: none; border-bottom: 1px solid #ba372a; text-align: center; min-width: 200px; outline: none; padding: 2px 4px;" <?= $canEditTargets ? '' : 'disabled' ?>> of the 
                    <input type="text" id="ratee-dept" value="" placeholder="Office Name" style="color: #ba372a; border: none; border-bottom: 1px solid #ba372a; text-align: center; min-width: 170px; outline: none; padding: 2px 4px;" <?= $canEditTargets ? '' : 'disabled' ?>>, commit to deliver and agree to be rated on the attainment of the following targets in accordance with the indicated measure for the period 
                    <input type="text" id="ratee-period" value="" placeholder="January - June or July - December and Year; e.g., July - December 2024" style="font-weight: bold; color: #ba372a; border: none; border-bottom: 1px solid #ba372a; text-align: center; min-width: 320px; outline: none; padding: 2px 4px;" <?= $canEditTargets ? '' : 'disabled' ?>>.
                </div>

                <!-- Approver, Ratee, and Rating Scale Matrix -->
                <table class="spms-meta-matrix" style="width: 100%; border-collapse: collapse; border: none; margin-bottom: 18px; font-size: 11px;">
                    <tr>
                        <!-- Left: Approved By (Extreme Left-most) -->
                        <td style="vertical-align: top; border: none; padding: 0 20px 0 0; text-align: left;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                <div style="font-weight: bold; color: #000000;">APPROVED BY:</div>
                                <button type="button" id="btn-add-approver" onclick="addApproverBlock()" class="print-hide" style="display: <?= $canEditApprover ? 'inline-flex' : 'none' ?>; align-items: center; gap: 4px; padding: 2px 8px; font-size: 10px; font-weight: 700; color: #0284c7; background: #e0f2fe; border: 1px solid #7dd3fc; border-radius: 4px; cursor: pointer;" title="Add another approving signatory">
                                    + Add Signatory
                                </button>
                            </div>
                            <div id="approvers-container" style="display: flex; flex-direction: column; gap: 8px;">
                                <div class="approver-item" style="position: relative;">
                                    <table style="width: 100%; border-collapse: collapse; border: none; font-size: 11px;">
                                        <tr>
                                            <td style="width: 65px; border: none; padding: 3px 0; font-weight: bold; color: #000000;">Name:</td>
                                            <td style="border: none; padding: 3px 0;">
                                                <input type="text" class="field-approver-name" id="approver-name" value="" placeholder="<?= $isDocOpcr ? '(name of head of office / approving authority)' : ($isDocDpcr ? '(name of office head)' : 'Name of Approving Authority') ?>" style="font-weight: bold; color: #ba372a; border: none; border-bottom: 1px solid #ba372a; outline: none; font-size: 11px; width: 60%;" <?= $canEditApprover ? '' : 'disabled' ?>>
                                                <span style="color: #ba372a; font-style: italic; font-size: 10px; margin-left: 6px;">(may add signatories depending on position)</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="border: none; padding: 3px 0; font-weight: bold; color: #000000;">Position:</td>
                                            <td style="border: none; padding: 3px 0;">
                                                <input type="text" class="field-approver-pos" id="approver-pos" value="" placeholder="<?= $isDocOpcr ? '(position / designation)' : ($isDocDpcr ? '(position of office head)' : 'Official Designation') ?>" style="color: #ba372a; border: none; border-bottom: 1px solid #ba372a; outline: none; font-size: 11px; width: 80%;" <?= $canEditApprover ? '' : 'disabled' ?>>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="border: none; padding: 3px 0; font-weight: bold; color: #000000;">Date:</td>
                                            <td style="border: none; padding: 3px 0;">
                                                <input type="date" class="field-approver-date" id="approver-date" value="" onclick="this.showPicker && this.showPicker()" style="color: #ba372a; border: none; border-bottom: 1px solid #ba372a; outline: none; font-size: 11px; width: 130px; background: transparent; font-family: inherit; cursor: pointer;" <?= $canEditApprover ? '' : 'disabled' ?>>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </td>

                        <!-- Right: Name of Employee & Rating Scale (Extreme Right-most) -->
                        <td style="vertical-align: top; border: none; padding: 0; text-align: right; width: 1%; white-space: nowrap;">
                            <div style="display: inline-block; text-align: left; min-width: 240px;">
                                <!-- Name of Employee Block -->
                                <div style="margin-bottom: 14px;">
                                    <div>
                                        <input type="text" id="ratee-sign-name" value="" placeholder="(full name here)" style="color: #ba372a; font-weight: bold; border: none; border-bottom: 1px solid #ba372a; text-align: left; width: 240px; outline: none; font-size: 11px; padding: 2px 0;" <?= $canEditTargets ? '' : 'disabled' ?>>
                                    </div>
                                    <div style="font-size: 11px; color: #000000; margin-top: 3px;">Name of Employee</div>
                                    <div style="margin-top: 4px; font-size: 11px; color: #000000;">
                                        Date: <input type="date" id="ratee-sign-date" value="" onclick="this.showPicker && this.showPicker()" style="border: none; border-bottom: 1px solid #ba372a; width: 130px; outline: none; font-size: 11px; color: #ba372a; background: transparent; font-family: inherit; cursor: pointer;" <?= $canEditTargets ? '' : 'disabled' ?>>
                                    </div>
                                </div>

                                <!-- Rating Scale -->
                                <div style="font-size: 10px; color: #000000;">
                                    <div style="font-weight: bold; margin-bottom: 3px;">Rating Scale:</div>
                                    <div style="display: flex; flex-direction: column; gap: 2px; line-height: 1.35;">
                                        <div>5 – Outstanding</div>
                                        <div>4 – Very Satisfactory</div>
                                        <div>3 – Satisfactory</div>
                                        <div>2 – Unsatisfactory</div>
                                        <div>1 – Poor</div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
                <?php endif; ?>

                <!-- MAIN TABLE OF DELIVERABLES & RATINGS -->
                <div class="spms-table-responsive-wrapper">
                    <div class="lg:hidden flex items-center justify-between text-[11px] text-slate-500 bg-slate-100 dark:bg-slate-800/40 px-3 py-1.5 rounded border border-slate-200 dark:border-slate-700 mb-2 print-hide">
                        <span class="flex items-center gap-1 font-medium">↔ Swipe matrix horizontally to view ratings & remarks</span>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider"><?= ($isDocDpcr || $isDocOpcr) ? '10 Columns' : ($isDocIperf ? '8 Columns' : '9 Columns') ?></span>
                    </div>
                    <table class="spms-table">
                        <?php if ($isDocDpcr): ?>
                        <!-- Column Widths (10 columns matching standard DPCR Sheet) -->
                        <colgroup>
                            <col style="width: 14%;"> <!-- PROGRAMS, PROJECTS, ACTIVITIES -->
                            <col style="width: 17%;"> <!-- SUCCESS INDICATORS -->
                            <col style="width: 10%;"> <!-- ALLOTTED BUDGET -->
                            <col style="width: 13%;"> <!-- INDIVIDUALS / OFFICES ACCOUNTABLE -->
                            <col style="width: 18%;"> <!-- ACTUAL ACCOMPLISHMENTS -->
                            <col style="width: 3.5%;"> <!-- Q -->
                            <col style="width: 3.5%;"> <!-- T -->
                            <col style="width: 3.5%;"> <!-- E -->
                            <col style="width: 4.5%;"> <!-- Ave. -->
                            <col style="width: 13%;"> <!-- REMARKS -->
                            <col style="width: 3%;">  <!-- ACT -->
                        </colgroup>

                        <!-- Two-Row Header with Light Blue #cfe2f3 Background -->
                        <thead>
                            <tr style="background-color: #cfe2f3; text-align: center; font-weight: bold; border-bottom: 1px solid #000; font-size: 10px;">
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">PROGRAMS, PROJECTS, ACTIVITIES</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">SUCCESS INDICATORS<br><span style="font-weight: normal; font-size: 9px;">(TARGETS + MEASURES)</span></th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">
                                    ALLOTTED BUDGET
                                    <div style="font-weight: normal; font-size: 9px; margin-top: 2px; display: inline-flex; align-items: center; justify-content: center; gap: 2px;">
                                        (<input type="text" id="header-budget-currency" class="header-budget-currency" value="₱" placeholder="₱" title="Currency (editable, defaults to ₱)" style="width: 18px;">)
                                    </div>
                                </th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">INDIVIDUALS /<br>OFFICES ACCOUNTABLE</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ACTUAL ACCOMPLISHMENTS</th>
                                <th colspan="4" style="border: 1px solid #000; padding: 4px;">RATING</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">REMARKS</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 4px;" class="print-hide">ACT</th>
                            </tr>
                            <tr style="background-color: #cfe2f3; text-align: center; font-weight: bold; border-bottom: 2px solid #000; font-size: 10px;">
                                <th style="border: 1px solid #000; padding: 4px 2px;">Q</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">T</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">E</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">Ave.</th>
                            </tr>
                        </thead>
                        <?php elseif ($isDocOpcr): ?>
                        <!-- Column Widths (10 columns matching Vice President OPCR Sheet) -->
                        <colgroup>
                            <col style="width: 14%;"> <!-- PROJECT/ PROGRAM/ ACTIVITIES -->
                            <col style="width: 17%;"> <!-- SUCCESS INDICATORS (TARGETS + MEASURES) PERFORMANCE -->
                            <col style="width: 10%;"> <!-- ALLOTTED BUDGET -->
                            <col style="width: 13%;"> <!-- DIVISIONS ACCOUNTABLE -->
                            <col style="width: 18%;"> <!-- ACTUAL ACCOMPLISHMENT -->
                            <col style="width: 3.5%;"> <!-- Q -->
                            <col style="width: 3.5%;"> <!-- T -->
                            <col style="width: 3.5%;"> <!-- E -->
                            <col style="width: 4.5%;"> <!-- AVE -->
                            <col style="width: 13%;"> <!-- REMARKS -->
                            <col style="width: 3%;">  <!-- ACT -->
                        </colgroup>

                        <!-- Two-Row Header with Soft Yellow/Amber #fff2cc Background -->
                        <thead>
                            <tr style="background-color: #fff2cc; text-align: center; font-weight: bold; border-bottom: 1px solid #000; font-size: 10px;">
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">PROJECT/<br>PROGRAM/<br>ACTIVITIES</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">SUCCESS INDICATORS (TARGETS +<br>MEASURES) PERFORMANCE</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">
                                    ALLOTTED<br>BUDGET
                                    <div style="font-weight: normal; font-size: 9px; margin-top: 2px; display: inline-flex; align-items: center; justify-content: center; gap: 2px;">
                                        (<input type="text" id="header-budget-currency-opcr" class="header-budget-currency" value="₱" placeholder="₱" title="Currency (editable, defaults to ₱)" style="width: 18px;">)
                                    </div>
                                </th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">DIVISIONS<br>ACCOUNTABLE</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ACTUAL ACCOMPLISHMENT</th>
                                <th colspan="4" style="border: 1px solid #000; padding: 4px;">RATINGS</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">REMARKS</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 4px;" class="print-hide">ACT</th>
                            </tr>
                            <tr style="background-color: #fff2cc; text-align: center; font-weight: bold; border-bottom: 2px solid #000; font-size: 10px;">
                                <th style="border: 1px solid #000; padding: 4px 2px;">Q</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">T</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">E</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">AVE</th>
                            </tr>
                        </thead>
                        <?php elseif ($isDocIperf): ?>
                        <!-- IPERF 8-Column Layout (+ Action column in web view) Matching COS & Job Order Sheet -->
                        <colgroup>
                            <col style="width: 24%;"> <!-- OFFICE PPA -->
                            <col style="width: 24%;"> <!-- EXPECTED OUTPUTS -->
                            <col style="width: 24%;"> <!-- ACTUAL ACCOMPLISHMENTS -->
                            <col style="width: 4%;">  <!-- Q -->
                            <col style="width: 4%;">  <!-- T -->
                            <col style="width: 4%;">  <!-- E -->
                            <col style="width: 5%;">  <!-- Ave. -->
                            <col style="width: 12%;"> <!-- REMARKS -->
                            <col style="width: 3%;">  <!-- ACT -->
                        </colgroup>

                        <!-- Two-Row Header with Soft Neutral #f1f5f9 Background -->
                        <thead>
                            <tr style="background-color: #f1f5f9; text-align: center; font-weight: bold; border-bottom: 1px solid #000; font-size: 10px;">
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">
                                    OFFICE PPA<br><span style="font-size: 9px; font-weight: normal;">(PROGRAMS, PROJECTS, ACTIVITIES)</span><br>
                                    <span style="color: #ba372a; font-size: 8.5px; font-style: italic; font-weight: normal;">(aligned with the deliverables of the office)</span>
                                </th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">
                                    EXPECTED OUTPUTS<br>
                                    <span style="color: #ba372a; font-size: 8.5px; font-style: italic; font-weight: normal;">(based on contract or duties and responsibilities in the request to hire personnel)</span>
                                </th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ACTUAL ACCOMPLISHMENTS</th>
                                <th colspan="4" style="border: 1px solid #000; padding: 4px;">RATING</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">REMARKS</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 4px;" class="print-hide">ACT</th>
                            </tr>
                            <tr style="background-color: #f1f5f9; text-align: center; font-weight: bold; border-bottom: 2px solid #000; font-size: 10px;">
                                <th style="border: 1px solid #000; padding: 4px 2px;">Q</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">T</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">E</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">Ave.</th>
                            </tr>
                        </thead>
                        <?php else: ?>
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

                        <!-- Two-Row Header: Q, T, E, Ave side-by-side with Light Blue #cfe2f3 Background -->
                        <thead>
                            <tr style="background-color: #cfe2f3; text-align: center; font-weight: bold; border-bottom: 1px solid #000; font-size: 10px;">
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">MAJOR FINAL OUTPUT</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">SUCCESS INDICATORS<br><span style="font-size: 9px; font-weight: normal; text-transform: none;">(TARGETS + MEASURES)</span></th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ACTUAL ACCOMPLISHMENTS</th>
                                <th colspan="4" style="border: 1px solid #000; padding: 4px;">RATING</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">REMARKS</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 4px;" class="print-hide">ACT</th>
                            </tr>
                            <tr style="background-color: #cfe2f3; text-align: center; font-weight: bold; border-bottom: 2px solid #000; font-size: 10px;">
                                <th style="border: 1px solid #000; padding: 4px 2px;">Q</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">T</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">E</th>
                                <th style="border: 1px solid #000; padding: 4px 2px;">Ave.</th>
                            </tr>
                        </thead>
                        <?php endif; ?>

                        <?php if ($isDocIperf): ?>
                        <!-- IPERF FLAT DELIVERABLES LIST (NO CATEGORIES) -->
                        <tbody id="tbody-core">
                        </tbody>
                        <!-- Add Row Footer for IPERF -->
                        <tbody class="print-hide" id="tfoot-add-core" style="<?= $canEditTargets ? '' : 'display: none;' ?>">
                            <tr>
                                <td colspan="9" style="padding: 6px; background: #fafafa; text-align: center; border: 1px solid #000;">
                                    <button type="button" onclick="addTableRow('core')" class="btn-add-dashed">
                                        + Add Deliverable / Office PPA Row
                                    </button>
                                </td>
                            </tr>
                        </tbody>

                        <!-- OVERALL AVERAGE RATING ROW (EXACT REPLICA OF PHOTO) -->
                        <tfoot>
                            <tr style="background-color: #f8fafc; font-weight: bold; border-top: 2px solid #000; border-bottom: 2px solid #000;">
                                <td colspan="6" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #000000; border: 1px solid #000; text-align: left;">
                                    OVERALL AVERAGE RATING
                                </td>
                                <td style="padding: 8px 4px; text-align: center; font-weight: 900; color: #0284c7; font-size: 12px; border: 1px solid #000;" id="iperf-overall-average">
                                    0.000
                                </td>
                                <td style="padding: 6px 8px; text-align: center; border: 1px solid #000;">
                                    <span id="iperf-adjectival-badge" style="display: inline-block; background: #475569; color: #ffffff; font-weight: 900; font-size: 10px; text-transform: uppercase; padding: 2px 8px; border-radius: 4px;">
                                        PENDING EVALUATION
                                    </span>
                                </td>
                                <td style="border: 1px solid #000;" class="print-hide"></td>
                            </tr>
                        </tfoot>

                        <!-- Hidden strategic and support containers for JS compatibility -->
                        <tbody id="tbody-strategic" style="display: none;"></tbody>
                        <tbody id="tbody-support" style="display: none;"></tbody>

                        <?php else: ?>
                        <!-- 1. CORE FUNCTIONS -->
                        <tbody id="tbody-core">
                            <tr style="background-color: #fce5cd; border-top: 2px solid #000; border-bottom: 1px solid #000; font-weight: bold;">
                                <?php if ($isDocDpcr): ?>
                                <td colspan="5" id="label-cat-core" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #0f172a; border: 1px solid #000;">
                                    CORE FUNCTIONS (60%) <span style="font-weight: normal; font-size: 9px; color: #ba372a;">(depending on position/designation)</span>
                                </td>
                                <td colspan="6" style="padding: 6px 12px; text-align: right; border: 1px solid #000;">
                                    <span style="display: inline-block; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 800; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                        Core Subtotal: <span id="badge-core-subtotal">0.000</span>
                                    </span>
                                </td>
                                <?php elseif ($isDocOpcr): ?>
                                <td colspan="5" id="label-cat-core" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #0f172a; border: 1px solid #000;">
                                    CORE MANDATE (60%) <span style="font-weight: normal; font-size: 9px; color: #ba372a;">(depending on position/designation)</span>
                                </td>
                                <td colspan="6" style="padding: 6px 12px; text-align: right; border: 1px solid #000;">
                                    <span style="display: inline-block; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 800; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                        Core Subtotal: <span id="badge-core-subtotal">0.000</span>
                                    </span>
                                </td>
                                <?php else: ?>
                                <td colspan="5" id="label-cat-core" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #0f172a; border: 1px solid #000;">
                                    CORE FUNCTIONS (70%) <span style="font-weight: normal; font-size: 9px; color: #ba372a;">(depending on position/designation)</span>
                                </td>
                                <td colspan="4" style="padding: 6px 12px; text-align: right; border: 1px solid #000;">
                                    <span style="display: inline-block; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 800; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                        Core Subtotal: <span id="badge-core-subtotal">0.000</span>
                                    </span>
                                </td>
                                <?php endif; ?>
                            </tr>
                        </tbody>
                        <!-- Add Row Footer for Core -->
                        <tbody class="print-hide" id="tfoot-add-core" style="<?= $canEditTargets ? '' : 'display: none;' ?>">
                            <tr>
                                <td colspan="<?= ($isDocDpcr || $isDocOpcr) ? 11 : 9 ?>" style="padding: 6px; background: #fafafa; text-align: center; border: 1px solid #000;">
                                    <button type="button" onclick="addTableRow('core')" class="btn-add-dashed">
                                        + Add Deliverable Row to Core Functions
                                    </button>
                                </td>
                            </tr>
                        </tbody>

                        <!-- 2. STRATEGIC FUNCTIONS -->
                        <tbody id="tbody-strategic">
                            <tr style="background-color: #fce5cd; border-top: 2px solid #000; border-bottom: 1px solid #000; font-weight: bold;">
                                <?php if ($isDocDpcr): ?>
                                <td colspan="5" id="label-cat-strategic" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #0f172a; border: 1px solid #000;">
                                    STRATEGIC FUNCTIONS (30%) <span style="font-weight: normal; font-size: 9px; color: #ba372a;">(depending on position/designation)</span>
                                </td>
                                <td colspan="6" style="padding: 6px 12px; text-align: right; border: 1px solid #000;">
                                    <span style="display: inline-block; background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd; font-weight: 800; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                        Strategic Subtotal: <span id="badge-strategic-subtotal">0.000</span>
                                    </span>
                                </td>
                                <?php elseif ($isDocOpcr): ?>
                                <td colspan="5" id="label-cat-strategic" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #0f172a; border: 1px solid #000;">
                                    STRATEGIC FUNCTIONS (25%) <span style="font-weight: normal; font-size: 9px; color: #ba372a;">(depending on position/designation)</span>
                                </td>
                                <td colspan="6" style="padding: 6px 12px; text-align: right; border: 1px solid #000;">
                                    <span style="display: inline-block; background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd; font-weight: 800; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                        Strategic Subtotal: <span id="badge-strategic-subtotal">0.000</span>
                                    </span>
                                </td>
                                <?php else: ?>
                                <td colspan="5" id="label-cat-strategic" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #0f172a; border: 1px solid #000;">
                                    STRATEGIC FUNCTIONS (20%) <span style="font-weight: normal; font-size: 9px; color: #ba372a;">(depending on position/designation)</span>
                                </td>
                                <td colspan="4" style="padding: 6px 12px; text-align: right; border: 1px solid #000;">
                                    <span style="display: inline-block; background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd; font-weight: 800; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                        Strategic Subtotal: <span id="badge-strategic-subtotal">0.000</span>
                                    </span>
                                </td>
                                <?php endif; ?>
                            </tr>
                        </tbody>
                        <!-- Add Row Footer for Strategic -->
                        <tbody class="print-hide" id="tfoot-add-strategic" style="<?= $canEditTargets ? '' : 'display: none;' ?>">
                            <tr>
                                <td colspan="<?= ($isDocDpcr || $isDocOpcr) ? 11 : 9 ?>" style="padding: 6px; background: #fafafa; text-align: center; border: 1px solid #000;">
                                    <button type="button" onclick="addTableRow('strategic')" class="btn-add-dashed">
                                        + Add Deliverable Row to Strategic Functions
                                    </button>
                                </td>
                            </tr>
                        </tbody>

                        <!-- 3. SUPPORT FUNCTIONS -->
                        <tbody id="tbody-support">
                            <tr style="background-color: #fce5cd; border-top: 2px solid #000; border-bottom: 1px solid #000; font-weight: bold;">
                                <?php if ($isDocDpcr): ?>
                                <td colspan="5" id="label-cat-support" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #0f172a; border: 1px solid #000;">
                                    SUPPORT FUNCTIONS (10%) <span style="font-weight: normal; font-size: 9px; color: #ba372a;">(depending on position/designation)</span>
                                </td>
                                <td colspan="6" style="padding: 6px 12px; text-align: right; border: 1px solid #000;">
                                    <span style="display: inline-block; background: #fffbeb; color: #92400e; border: 1px solid #fde68a; font-weight: 800; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                        Support Subtotal: <span id="badge-support-subtotal">0.000</span>
                                    </span>
                                </td>
                                <?php elseif ($isDocOpcr): ?>
                                <td colspan="5" id="label-cat-support" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #0f172a; border: 1px solid #000;">
                                    SUPPORT FUNCTIONS (15%) <span style="font-weight: normal; font-size: 9px; color: #ba372a;">(depending on position/designation)</span>
                                </td>
                                <td colspan="6" style="padding: 6px 12px; text-align: right; border: 1px solid #000;">
                                    <span style="display: inline-block; background: #fffbeb; color: #92400e; border: 1px solid #fde68a; font-weight: 800; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                        Support Subtotal: <span id="badge-support-subtotal">0.000</span>
                                    </span>
                                </td>
                                <?php else: ?>
                                <td colspan="5" id="label-cat-support" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #0f172a; border: 1px solid #000;">
                                    SUPPORT FUNCTIONS (10%) <span style="font-weight: normal; font-size: 9px; color: #ba372a;">(depending on position/designation)</span>
                                </td>
                                <td colspan="4" style="padding: 6px 12px; text-align: right; border: 1px solid #000;">
                                    <span style="display: inline-block; background: #fffbeb; color: #92400e; border: 1px solid #fde68a; font-weight: 800; font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                        Support Subtotal: <span id="badge-support-subtotal">0.000</span>
                                    </span>
                                </td>
                                <?php endif; ?>
                            </tr>
                        </tbody>
                        <!-- Add Row Footer for Support -->
                        <tbody class="print-hide" id="tfoot-add-support" style="<?= $canEditTargets ? '' : 'display: none;' ?>">
                            <tr>
                                <td colspan="<?= ($isDocDpcr || $isDocOpcr) ? 11 : 9 ?>" style="padding: 6px; background: #fafafa; text-align: center; border: 1px solid #000;">
                                    <button type="button" onclick="addTableRow('support')" class="btn-add-dashed">
                                        + Add Deliverable Row to Support Functions
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <?php endif; ?>
                    </table>
                </div>

                <?php if (!$isDocIperf): ?>
                <!-- GRAND SUMMARY & NAVY RATING BAR (BULLETPROOF TABLE LAYOUT FOR BOTH DPCR AND IPCR) -->
                <table class="spms-summary-matrix" style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 11px;">
                    <tr>
                        <!-- Left: Formula Explanation -->
                        <td style="width: 35%; padding: 12px; border: 1px solid #000; vertical-align: top; background: #fafafa;">
                            <div style="font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Formula Weights:</div>
                            <div id="formula-desc-text" style="font-size: 11px; color: #334155; margin-top: 6px; line-height: 1.5;">
                                <?= $isDocDpcr 
                                    ? 'Core Functions (60%) + Strategic Functions (30%) + Support Functions (10%).' 
                                    : ($isDocOpcr 
                                        ? 'Core Mandate (60%) + Strategic Functions (25%) + Support Functions (15%).'
                                        : 'Core Functions (70%) + Strategic Functions (20%) + Support Functions (10%).') ?>
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
                                        (Average: <span id="sum-core-avg">0.000</span> × <span id="mult-core-val"><?= ($isDocDpcr || $isDocOpcr) ? '0.60' : '0.70' ?></span>)
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
                                        (Average: <span id="sum-strategic-avg">0.000</span> × <span id="mult-strategic-val"><?= $isDocDpcr ? '0.30' : ($isDocOpcr ? '0.25' : '0.20') ?></span>)
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
                                        (Average: <span id="sum-support-avg">0.000</span> × <span id="mult-support-val"><?= $isDocOpcr ? '0.15' : '0.10' ?></span>)
                                    </td>
                                </tr>

                                <!-- Dark Navy Grand Total Banner (Matching Reference Mockup) -->
                                <tr class="spms-navy-bar" style="background: #0a192f; color: #ffffff;">
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
                <?php else: ?>
                <!-- Hidden elements to preserve JS compatibility for IPERF -->
                <div style="display: none;">
                    <span id="badge-core-subtotal">0.000</span>
                    <span id="badge-strategic-subtotal">0.000</span>
                    <span id="badge-support-subtotal">0.000</span>
                    <span id="sum-core-score">0.000</span>
                    <span id="sum-core-avg">0.000</span>
                    <span id="mult-core-val">1.00</span>
                    <span id="sum-strategic-score">0.000</span>
                    <span id="sum-strategic-avg">0.000</span>
                    <span id="mult-strategic-val">0.00</span>
                    <span id="sum-support-score">0.000</span>
                    <span id="sum-support-avg">0.000</span>
                    <span id="mult-support-val">0.00</span>
                    <span id="grand-score">0.000</span>
                    <span id="adjectival-badge">PENDING EVALUATION</span>
                    <span id="grand-formula"></span>
                </div>
                <?php endif; ?>

                <!-- REMARKS BOX (ROW 29 IN EXCEL SPREADSHEET) -->
                <div style="border: 1px solid #000; <?= $isDocIperf ? '' : 'border-top: none;' ?> padding: 8px 10px; box-sizing: border-box;">
                    <div style="font-weight: bold; font-size: 11px; color: #000000; margin-bottom: 4px;">
                        Remarks/Suggestions/Recommendations on Ratee's Performance:
                    </div>
                    <textarea id="pmt-remarks" rows="2" placeholder="Enter remarks/suggestions/recommendations on ratee's performance..." class="spms-textarea" style="width: 100%; border: 1px solid transparent; font-style: italic; font-size: 11px;" <?= $canEditEvaluation ? '' : ($isOwner ? 'disabled' : '') ?>></textarea>
                </div>

                <?php if ($isDocIperf): ?>
                <!-- IPERF TWO-PHASE SIGNATORIES & REFERENCE GUIDES (EXACT REPLICA OF PHOTO) -->
                <table class="spms-signatories-matrix" style="width: 100%; border-collapse: collapse; border: 1px solid #000; margin-top: 18px; font-size: 11px;">
                    <tr>
                        <!-- Phase 1: Start of Period (Columns A-B) -->
                        <td style="width: 36%; vertical-align: top; border-right: 1px solid #000; padding: 10px 12px;">
                            <div style="color: #ba372a; font-style: italic; font-size: 10.5px; font-weight: bold; margin-bottom: 10px;">
                                signed at the start of the rating period
                            </div>

                            <!-- Targets Prepared By -->
                            <div style="margin-bottom: 16px;">
                                <div style="font-weight: bold; margin-bottom: 4px;">Targets prepared by:</div>
                                <input type="text" id="sig-targets-prepared-name" value="" placeholder="(Signature over Printed Name)" style="width: 90%; border: none; border-bottom: 1px solid #ba372a; outline: none; font-size: 11px; font-weight: bold; color: #ba372a;" <?= $canEditTargets ? '' : 'disabled' ?>>
                                <div style="font-size: 10px; color: #334155; margin-top: 2px;">Employee (Ratee)</div>
                                <div style="margin-top: 4px; font-size: 10.5px;">
                                    Date: <input type="date" id="sig-targets-prepared-date" onclick="this.showPicker && this.showPicker()" style="border: none; border-bottom: 1px solid #ba372a; width: 130px; outline: none; font-size: 11px; color: #ba372a; background: transparent; font-family: inherit; cursor: pointer;" <?= $canEditTargets ? '' : 'disabled' ?>>
                                </div>
                            </div>

                            <!-- Approved By -->
                            <div>
                                <div style="font-weight: bold; margin-bottom: 4px;">Approved by:</div>
                                <input type="text" id="sig-targets-approved-name" value="" placeholder="(Signature over Printed Name)" style="width: 90%; border: none; border-bottom: 1px solid #ba372a; outline: none; font-size: 11px; font-weight: bold; color: #ba372a;" <?= $canEditTargets ? '' : 'disabled' ?>>
                                <div style="font-size: 10px; color: #334155; margin-top: 2px;">Immediate Supervisor (Rater)</div>
                                <div style="margin-top: 4px; font-size: 10.5px;">
                                    Date: <input type="date" id="sig-targets-approved-date" onclick="this.showPicker && this.showPicker()" style="border: none; border-bottom: 1px solid #ba372a; width: 130px; outline: none; font-size: 11px; color: #ba372a; background: transparent; font-family: inherit; cursor: pointer;" <?= $canEditTargets ? '' : 'disabled' ?>>
                                </div>
                            </div>
                        </td>

                        <!-- Phase 2: End of Period (Columns C-D) -->
                        <td style="width: 36%; vertical-align: top; border-right: 1px solid #000; padding: 10px 12px;">
                            <div style="color: #ba372a; font-style: italic; font-size: 10.5px; font-weight: bold; margin-bottom: 10px;">
                                signed at the end of the rating period
                            </div>

                            <!-- Rated By -->
                            <div style="margin-bottom: 16px;">
                                <div style="font-weight: bold; margin-bottom: 4px;">Rated by:</div>
                                <input type="text" id="sig-eval-rated-name" value="" placeholder="(Signature over Printed Name)" style="width: 90%; border: none; border-bottom: 1px solid #ba372a; outline: none; font-size: 11px; font-weight: bold; color: #ba372a;" <?= $canEditEvaluation ? '' : 'disabled' ?>>
                                <div style="font-size: 10px; color: #334155; margin-top: 2px;">Immediate Supervisor (Rater)</div>
                                <div style="margin-top: 4px; font-size: 10.5px;">
                                    Date: <input type="date" id="sig-eval-rated-date" onclick="this.showPicker && this.showPicker()" style="border: none; border-bottom: 1px solid #ba372a; width: 130px; outline: none; font-size: 11px; color: #ba372a; background: transparent; font-family: inherit; cursor: pointer;" <?= $canEditEvaluation ? '' : 'disabled' ?>>
                                </div>
                            </div>

                            <!-- Conforme -->
                            <div>
                                <div style="font-weight: bold; margin-bottom: 4px;">Conforme:</div>
                                <input type="text" id="sig-eval-conforme-name" value="" placeholder="(Signature over Printed Name)" style="width: 90%; border: none; border-bottom: 1px solid #ba372a; outline: none; font-size: 11px; font-weight: bold; color: #ba372a;" <?= $canEditEvaluation ? '' : 'disabled' ?>>
                                <div style="font-size: 10px; color: #334155; margin-top: 2px;">Employee (Ratee)</div>
                                <div style="margin-top: 4px; font-size: 10.5px;">
                                    Date: <input type="date" id="sig-eval-conforme-date" onclick="this.showPicker && this.showPicker()" style="border: none; border-bottom: 1px solid #ba372a; width: 130px; outline: none; font-size: 11px; color: #ba372a; background: transparent; font-family: inherit; cursor: pointer;" <?= $canEditEvaluation ? '' : 'disabled' ?>>
                                </div>
                            </div>
                        </td>

                        <!-- Side Reference: Measures & Rating Guide (Columns E-H) -->
                        <td style="width: 28%; vertical-align: top; padding: 10px 12px; background: #fdfdfd;">
                            <!-- Measures -->
                            <div style="margin-bottom: 12px;">
                                <div style="font-weight: bold; margin-bottom: 4px; border-bottom: 1px solid #000; padding-bottom: 2px;">Measures:</div>
                                <table style="width: 100%; font-size: 10.5px; border-collapse: collapse;">
                                    <tr><td style="width: 20px; font-weight: bold; padding: 1px 0;">Q</td><td style="padding: 1px 0;">Quality</td></tr>
                                    <tr><td style="font-weight: bold; padding: 1px 0;">T</td><td style="padding: 1px 0;">Timeliness</td></tr>
                                    <tr><td style="font-weight: bold; padding: 1px 0;">E</td><td style="padding: 1px 0;">Efficiency</td></tr>
                                </table>
                            </div>

                            <!-- Rating Guide -->
                            <div>
                                <div style="font-weight: bold; margin-bottom: 4px; border-bottom: 1px solid #000; padding-bottom: 2px;">Rating Guide:</div>
                                <table style="width: 100%; font-size: 10.5px; border-collapse: collapse;">
                                    <tr><td style="width: 20px; font-weight: bold; padding: 1px 0;">5</td><td style="padding: 1px 0;">Outstanding</td></tr>
                                    <tr><td style="font-weight: bold; padding: 1px 0;">4</td><td style="padding: 1px 0;">Very Satisfactory</td></tr>
                                    <tr><td style="font-weight: bold; padding: 1px 0;">3</td><td style="padding: 1px 0;">Satisfactory</td></tr>
                                    <tr><td style="font-weight: bold; padding: 1px 0;">2</td><td style="padding: 1px 0;">Unsatisfactory</td></tr>
                                    <tr><td style="font-weight: bold; padding: 1px 0;">1</td><td style="padding: 1px 0;">Poor</td></tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
                <!-- Compatibility hidden inputs for standard signatures -->
                <input type="hidden" id="sig-ratee-name" value="">
                <input type="hidden" id="sig-ratee-pos" value="">
                <input type="hidden" id="sig-ratee-date" value="">
                <input type="hidden" id="sig-dean-name" value="">
                <input type="hidden" id="sig-dean-pos" value="">
                <input type="hidden" id="sig-dean-date" value="">
                <input type="hidden" id="sig-vp-name" value="">
                <input type="hidden" id="sig-vp-date" value="">
                <?php else: ?>
                <!-- 2 BOTTOM SIGNATORIES (ROWS 32-35 EXCEL SPREADSHEET EXACT REPLICA) -->
                <table style="width: 100%; border-collapse: collapse; border: none; font-size: 11px; margin-top: 18px;">
                    <tr>
                        <!-- Left: Ratee (Columns A-C) -->
                        <td style="width: 50%; vertical-align: top; border: none; padding: 0 15px 0 0;">
                            <div style="margin-bottom: 6px;">Name and Signature of Ratee: 
                                <input type="text" id="sig-ratee-name" value="" placeholder="(name here)" style="color: #ba372a; font-weight: bold; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px; width: 55%;">
                            </div>
                            <div style="margin-bottom: 6px;">Position: 
                                <input type="text" id="sig-ratee-pos" value="" placeholder="(position here)" style="color: #ba372a; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px; width: 70%;">
                            </div>
                            <div style="margin-bottom: 6px;">
                                Date: <input type="date" id="sig-ratee-date" onclick="this.showPicker && this.showPicker()" style="border: none; border-bottom: 1px solid #ba372a; outline: none; font-size: 11px; width: 130px; color: #ba372a; background: transparent; font-family: inherit; cursor: pointer;">
                            </div>
                        </td>

                        <!-- Right: Office Head (Columns D-H) -->
                        <td style="width: 50%; vertical-align: top; border: none; padding: 0 0 0 15px;">
                            <div style="margin-bottom: 6px;">Final Rating by: 
                                <input type="text" id="sig-dean-name" value="" placeholder="(name of office head)" style="color: #ba372a; font-weight: bold; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px; width: 55%;">
                            </div>
                            <div style="margin-bottom: 6px;">Position: 
                                <input type="text" id="sig-dean-pos" value="" placeholder="(position of office head)" style="color: #ba372a; border: none; border-bottom: 1px solid #cbd5e1; outline: none; font-size: 11px; width: 70%;">
                            </div>
                            <div style="margin-bottom: 6px;">
                                Date: <input type="date" id="sig-dean-date" onclick="this.showPicker && this.showPicker()" style="border: none; border-bottom: 1px solid #ba372a; outline: none; font-size: 11px; width: 130px; color: #ba372a; background: transparent; font-family: inherit; cursor: pointer;">
                            </div>
                            <div style="color: #ba372a; font-size: 10px; font-style: italic;">(may add signatories depending on position/designation)</div>
                        </td>
                    </tr>
                </table>
                <input type="hidden" id="sig-vp-name" value="">
                <input type="hidden" id="sig-vp-date" value="">
                <?php endif; ?>

            </article>
            </div>
        </div>

        <!-- SPMS DIGITAL RUBRICS MATRIX WORKSPACE -->
        <div id="spms-rubrics-workspace" class="hidden w-full h-full overflow-y-auto p-2 sm:p-6 lg:p-8 flex flex-col items-center custom-scrollbar print:p-0 print:bg-white print:overflow-visible">
            <div class="w-full max-w-[1280px] flex flex-col gap-6 print:w-full print:block">
                
                <!-- BSU Institutional Rubrics Banner -->
                <div class="rounded-2xl p-5 sm:p-6 bg-gradient-to-r from-amber-500/15 via-emerald-500/10 to-transparent border border-amber-500/30 dark:border-amber-400/20 shadow-sm">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500/20 text-amber-800 dark:text-amber-300 border border-amber-500/30">
                                    BSU Rubrics Policy
                                </span>
                                <span class="text-xs font-extrabold text-emerald-800 dark:text-emerald-300">
                                    Required Standards &amp; Attachment
                                </span>
                            </div>
                            <p class="text-xs sm:text-sm font-bold text-rose-600 dark:text-rose-400 leading-relaxed">
                                Note: Always attach your rubrics to your DPCR/IPCR/IPERF when submitting. Also, the targets in your rubrics should match the targets in your form.
                            </p>
                            <p class="text-[11px] text-text-muted">
                                Define what qualifies as a rating of 5, 4, 3, 2, or 1 for Quality (Q), Timeliness (T), and Efficiency (E) for each deliverable below.
                            </p>
                        </div>

                        <!-- Action Buttons: Download Template & View Sample Guide -->
                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            <a href="<?= site_url('document/' . $doc['id'] . '/export-rubric') ?>" target="_blank"
                               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-600/20 transition-all cursor-pointer"
                               title="Download official Excel rubric sheet with your current deliverables pre-filled in Column A">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                <span>Download Template (.xlsx)</span>
                            </a>
                            <button type="button" onclick="toggleRubricDrawer(true)"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-amber-500/20 hover:bg-amber-500/30 text-amber-800 dark:text-amber-300 border border-amber-500/30 transition-all cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <span>Sample Guide</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- External File Attachment Section -->
                <div class="p-4 sm:p-5 rounded-2xl bg-surface border border-surface-border shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-surface-border pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <h3 class="text-xs font-black uppercase tracking-wider text-text">Rubric Document Attachment</h3>
                            <span class="text-[10px] text-text-muted font-medium">(Optional external file: XLSX, PDF, DOCX)</span>
                        </div>
                        <div id="rubric-file-status-badge"></div>
                    </div>

                    <!-- Current Attached File Card -->
                    <div id="rubric-attached-file-card" class="hidden p-3.5 rounded-xl bg-surface-border/20 border border-surface-border flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="overflow-hidden">
                                <div class="text-xs font-bold text-text truncate" id="rubric-file-name">—</div>
                                <div class="text-[10px] text-text-muted mt-0.5" id="rubric-file-meta">—</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <a id="rubric-file-download-link" href="#" target="_blank" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-accent/10 hover:bg-accent/20 text-accent transition-colors">
                                Download
                            </a>
                            <button type="button" id="rubric-file-view-btn" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-surface-border/40 hover:bg-surface-border text-text transition-colors">
                                View
                            </button>
                            <?php if ($canEditTargets): ?>
                            <button type="button" onclick="deleteRubricAttachment()" class="px-2.5 py-1.5 rounded-lg text-xs font-bold text-rose-500 hover:bg-rose-500/10 transition-colors" title="Delete attachment">
                                ✕
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Upload Dropzone (if canEditTargets) -->
                    <?php if ($canEditTargets): ?>
                    <div id="rubric-upload-zone" class="space-y-2">
                        <div id="rubric-drop-target"
                             onclick="document.getElementById('rubric-file-input').click()"
                             ondragover="event.preventDefault(); this.classList.add('border-emerald-500', 'bg-emerald-500/5');"
                             ondragleave="this.classList.remove('border-emerald-500', 'bg-emerald-500/5');"
                             ondrop="handleRubricDrop(event)"
                             class="border-2 border-dashed border-surface-border hover:border-emerald-500/60 rounded-xl p-5 text-center cursor-pointer transition-all bg-surface-border/5 hover:bg-emerald-500/5 flex flex-col items-center justify-center gap-2">
                            <div class="w-10 h-10 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-text">Click to attach completed rubric file</span>
                                <span class="text-xs text-text-muted"> or drag &amp; drop</span>
                            </div>
                            <span class="text-[10px] text-text-muted">Accepted formats: .xlsx, .xls, .pdf, .docx, .doc, .csv (Max 20MB)</span>
                            <input type="file" id="rubric-file-input" class="hidden" accept=".xlsx,.xls,.pdf,.docx,.doc,.csv" onchange="handleRubricFileSelect(this)">
                        </div>
                        <div id="rubric-upload-progress" class="hidden p-3 bg-emerald-500/10 border border-emerald-500/30 rounded-xl flex items-center justify-between text-xs text-emerald-700 dark:text-emerald-300 font-bold animate-pulse">
                            <span>Uploading rubric document...</span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Interactive Digital Rubrics Matrix Section -->
                <div class="space-y-5">
                    <!-- Rubric Matrix Header & Controls Card -->
                    <div class="p-4 sm:p-5 rounded-2xl bg-surface border border-surface-border shadow-sm space-y-4">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-surface-border pb-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <h2 class="text-sm sm:text-base font-black uppercase tracking-wider text-text">
                                        Standards or Rating Matrix per Success Indicator
                                    </h2>
                                </div>
                                <p class="text-xs text-text-muted mt-1">
                                    Define measurable scoring criteria (5 to 1) for Quality (Q), Timeliness (T), and Efficiency (E) per output.
                                </p>
                            </div>
                            
                            <div class="flex items-center gap-2.5 shrink-0">
                                <?php if ($canEditTargets): ?>
                                <div class="relative group inline-flex items-center">
                                    <button type="button" 
                                            id="btn-rubrics-save"
                                            onclick="saveDocument(true)" 
                                            title="Save (Ctrl + S)"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm hover:shadow transition-all active:scale-95 cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                        </svg>
                                        <span id="btn-rubrics-save-text">Save</span>
                                    </button>
                                    
                                    <!-- Hover Tooltip showing (Ctrl + S) -->
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-900 text-white shadow-xl pointer-events-none whitespace-nowrap z-50">
                                        <span>Save changes</span>
                                        <span class="text-slate-400 font-mono text-[9px] bg-slate-800 px-1 py-0.5 rounded border border-slate-700">(Ctrl + S)</span>
                                        <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45"></div>
                                    </div>
                                </div>
                                <span id="rubrics-save-status" class="text-[10px] uppercase tracking-widest font-bold transition-all"></span>
                                <?php else: ?>
                                <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-surface-border/40 text-text-muted">
                                    Read-Only
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Deliverables Quick Navigation & Progress -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-0.5">
                            <div class="flex items-center gap-1.5 overflow-x-auto custom-scrollbar pb-1 text-xs" id="rubric-quick-jump-pills">
                                <!-- Populated dynamically by JS -->
                            </div>

                            <div class="flex items-center gap-2 shrink-0 text-xs self-end sm:self-auto flex-wrap">
                                <span id="rubric-overall-progress" class="font-bold text-text-muted text-[11px]"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Full Sheet View Container (Permanent Single View) -->
                    <div id="rubrics-sheet-container" class="overflow-x-auto rounded-2xl border border-surface-border bg-surface shadow-sm p-4">
                        <table id="digital-rubrics-table" class="w-full text-left border-collapse" style="font-size: 11px; min-width: 900px;">
                            <thead>
                                <tr class="bg-surface-border/40 text-text border-b border-surface-border">
                                    <th class="p-3 font-black uppercase tracking-wider text-xs" style="width: 32%;">
                                        OFFICE PPA / MAJOR FINAL OUTPUT / EXPECTED OUTPUTS
                                    </th>
                                    <th class="p-3 font-black uppercase tracking-wider text-xs text-center" style="width: 12%;">
                                        Rating
                                    </th>
                                    <th class="p-3 font-black uppercase tracking-wider text-xs" style="width: 20%;">
                                        Q (Quality)
                                    </th>
                                    <th class="p-3 font-black uppercase tracking-wider text-xs" style="width: 20%;">
                                        T (Timeliness)
                                    </th>
                                    <th class="p-3 font-black uppercase tracking-wider text-xs" style="width: 16%;">
                                        E (Efficiency)
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="digital-rubrics-tbody">
                                <!-- Populated dynamically by JS with Category Groups -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <?php if ($canEditTargets): ?>
            <!-- Floating Action Button (FAB) for Rubrics Quick Add -->
            <div id="rubric-fab-container" class="hidden fixed bottom-6 right-6 z-40">
                <div class="relative">
                    <!-- Dropup Menu for FAB -->
                    <div id="rubric-fab-menu" class="hidden absolute bottom-full right-0 mb-3 w-56 rounded-2xl bg-surface/95 backdrop-blur-md border border-surface-border shadow-2xl p-2 space-y-1 text-xs font-bold transition-all">
                        <div class="px-3 py-1.5 text-[10px] font-black uppercase tracking-wider text-text-muted border-b border-surface-border/60">
                            Add Deliverable
                        </div>
                        <button type="button" onclick="addDeliverableFromRubrics('core'); toggleRubricFabMenu(false);"
                                class="w-full text-left px-3 py-2.5 rounded-xl hover:bg-emerald-500/10 text-text hover:text-emerald-700 dark:hover:text-emerald-300 flex items-center gap-2.5 transition-colors cursor-pointer">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                            <span>Core Functions</span>
                        </button>
                        <?php if (!$isDocIperf): ?>
                        <button type="button" onclick="addDeliverableFromRubrics('strategic'); toggleRubricFabMenu(false);"
                                class="w-full text-left px-3 py-2.5 rounded-xl hover:bg-sky-500/10 text-text hover:text-sky-700 dark:hover:text-sky-300 flex items-center gap-2.5 transition-colors cursor-pointer">
                            <span class="w-2.5 h-2.5 rounded-full bg-sky-500 shrink-0"></span>
                            <span>Strategic Functions</span>
                        </button>
                        <button type="button" onclick="addDeliverableFromRubrics('support'); toggleRubricFabMenu(false);"
                                class="w-full text-left px-3 py-2.5 rounded-xl hover:bg-amber-500/10 text-text hover:text-amber-700 dark:hover:text-amber-300 flex items-center gap-2.5 transition-colors cursor-pointer">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shrink-0"></span>
                            <span>Support Functions</span>
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Primary FAB Button -->
                    <button type="button" onclick="handleRubricFabClick()"
                            id="rubric-fab-btn"
                            title="Add new deliverable"
                            class="flex items-center gap-2 px-4 py-3 rounded-full bg-emerald-600 hover:bg-emerald-500 text-white shadow-xl hover:shadow-emerald-600/30 hover:scale-105 active:scale-95 transition-all cursor-pointer font-bold text-xs">
                        <span class="text-base font-black leading-none">＋</span>
                        <span>Add Deliverable</span>
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Fallback TinyMCE Editor Container for Free-Form Documents -->
        <div id="tinymce-wrapper" class="w-full h-full bg-white dark:bg-zinc-950">
            <textarea id="editable-doc" name="content"></textarea>
        </div>
    </div>
    <?= view('document/_rubric_drawer') ?>
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
    tabs.forEach((t, i) => {
        if (!t.title || !t.title.trim()) {
            t.title = (i === 0) ? 'Target Form' : `Tab ${i + 1}`;
        }
    });

    let activeTabId = tabs[0].id;
    
    const basisFormData = <?= json_encode($basisFormData ?? null) ?>;
    const basisDocContent = <?= json_encode($basisDocContent ?? '') ?>;
    const superiorUserInfo = <?= json_encode($superiorUser ?? null) ?>;
    const ownerAccountInfo = <?= json_encode($ownerInfo ?? []) ?>;

    const canEditTargets = <?= json_encode($canEditTargets) ?>;
    const canEditApprover = <?= json_encode($canEditApprover) ?>;
    const canEditEvaluation = <?= json_encode($canEditEvaluation) ?>;
    const isTargetPhase = <?= json_encode($isTargetPhase) ?>;
    const isEvaluationPhase = <?= json_encode($isEvaluationPhase) ?>;

    window.canEditTargets = canEditTargets;
    window.canEditApprover = canEditApprover;
    window.canEditEvaluation = canEditEvaluation;

    window.rubricAttachments = <?= json_encode($attachmentsByRow['rubric'] ?? []) ?>;
    window.digitalRubricsData = tabs[0]?.formData?.rubrics || {};
    
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
            span.textContent = (tab.title && tab.title.trim()) ? tab.title : 'Target Form';
            
            <?php if ($isEditable): ?>
            span.contentEditable = "true";
            span.title = 'Click to edit tab name';
            span.className = 'outline-none cursor-text px-1 min-w-[20px] inline-block rounded focus:bg-surface-border/30';
            
            span.onblur = (e) => {
                let newName = e.target.textContent.trim();
                if (!newName) {
                    newName = tab.title || 'Target Form';
                }
                if (newName !== tab.title) {
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

        // Digital Rubrics Matrix Tab
        const isRubricsActive = (activeTabId === 'rubrics-tab');
        const rubricsBtn = document.createElement('div');
        rubricsBtn.className = `group flex items-center gap-1.5 pb-2 border-b-2 transition-colors select-none cursor-pointer ${isRubricsActive ? 'border-amber-500 text-amber-500 dark:text-amber-400 font-bold' : 'border-transparent text-text-muted hover:text-text'}`;
        rubricsBtn.onclick = () => switchEditorTab('rubrics-tab');
        rubricsBtn.title = 'View and Edit Rubrics Matrix';
        rubricsBtn.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ${isRubricsActive ? 'text-amber-500' : 'text-amber-500/70'} shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            <span>Rubrics Matrix</span>
            <span id="tab-rubrics-badge" class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-amber-500/15 text-amber-800 dark:text-amber-300 border border-amber-500/30">
                Standards
            </span>
        `;
        tabBar.appendChild(rubricsBtn);

        <?php if (!empty($basisDoc) && !$isGuide): ?>
        <?php 
            $isBasisOpcr = !empty($isParentDocOpcr) || str_contains(strtoupper($basisDoc['title'] ?? ''), 'OPCR') || (strtoupper($basisDoc['doc_type'] ?? '') === 'OPCR');
            $basisBadgeLabel = $isParentTargetApproved ? ($isBasisOpcr ? 'Institutional' : 'Approved') : 'Pending';
        ?>
        const isBasisActive = (activeTabId === 'basis-tab');
        const basisBtn = document.createElement('div');
        basisBtn.className = `group flex items-center gap-1.5 pb-2 border-b-2 transition-colors select-none cursor-pointer ${isBasisActive ? 'border-sky-500 text-sky-500 dark:text-sky-400 font-bold' : 'border-transparent text-text-muted hover:text-text'}`;
        basisBtn.onclick = () => switchEditorTab('basis-tab');
        basisBtn.title = 'View Superior Basis Document';
        basisBtn.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ${isBasisActive ? 'text-sky-500' : 'text-sky-500/70'} shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Basis: <?= esc($basisDoc['title']) ?></span>
            <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold ${isBasisActive ? 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300 border border-sky-300 dark:border-sky-700' : '<?= $isParentTargetApproved ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400' ?>'}">
                <?= esc($basisBadgeLabel) ?>
            </span>
        `;
        tabBar.appendChild(basisBtn);
        <?php endif; ?>
        

    }

    function switchEditorTab(tabId) {
        if (tabId === activeTabId) return;

        if (activeTabId === 'rubrics-tab') {
            if (typeof window.syncDigitalRubricsData === 'function') {
                window.syncDigitalRubricsData();
            }
        } else if (activeTabId !== 'basis-tab') {
            if (window.isSpmsFormActive && typeof window.syncSpmsActiveTab === 'function') {
                window.syncSpmsActiveTab();
            } else {
                const editor = tinymce.get('editable-doc');
                if (editor) {
                    const activeTab = tabs.find(t => t.id === activeTabId);
                    if (activeTab) activeTab.content = editor.getContent();
                }
            }
        }
        
        activeTabId = tabId;
        renderTabs();
        initActiveTabView();
        if (typeof updateRubricsTabBadge === 'function') {
            updateRubricsTabBadge();
        }
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

    // Helper: sanitize budget input and cap at 12 whole digits (up to 999 Billion) + 2 decimal places
    function sanitizeBudgetInput(raw) {
        let clean = String(raw || '').replace(/[^0-9.]/g, '');
        const parts = clean.split('.');
        let whole = parts[0] || '';
        let decimal = parts.length > 1 ? parts.slice(1).join('') : null;
        if (whole.length > 12) {
            whole = whole.substring(0, 12);
        }
        if (decimal !== null && decimal.length > 2) {
            decimal = decimal.substring(0, 2);
        }
        return decimal !== null ? whole + '.' + decimal : whole;
    }

    // Helper: full exact comma-formatted string (e.g. 10,000,000,000.00)
    function formatBudgetFull(num) {
        if (isNaN(num) || num === null || num === undefined) return '';
        return num.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Auto-shrink font size for large budget values + dynamic hover tooltip
    function adjustBudgetFontSize(input) {
        if (!input) return;
        const len = (input.value || '').length;
        if (len >= 18) {
            input.style.fontSize = '7.5px';
            input.style.letterSpacing = '-0.5px';
        } else if (len >= 15) {
            input.style.fontSize = '8px';
            input.style.letterSpacing = '-0.3px';
        } else if (len >= 12) {
            input.style.fontSize = '9px';
            input.style.letterSpacing = '-0.2px';
        } else if (len >= 9) {
            input.style.fontSize = '10px';
            input.style.letterSpacing = 'normal';
        } else {
            input.style.fontSize = '11px';
            input.style.letterSpacing = 'normal';
        }
        const wrapper = input.closest('.budget-input-wrapper');
        const cur = wrapper ? (wrapper.querySelector('.field-budget-currency')?.value || '₱') : '₱';
        input.title = input.value ? `${cur} ${input.value}` : 'Allotted Budget (Numbers only)';
    }

    function applyBudgetBlur(el) {
        if (!el) return;
        const raw = el.dataset.rawValue || el.value.replace(/,/g, '').trim();
        const clean = sanitizeBudgetInput(raw);
        if (clean !== '') {
            const num = parseFloat(clean);
            if (!isNaN(num) && num >= 0) {
                el.dataset.rawValue = clean;
                const full = formatBudgetFull(num);
                el.dataset.fullValue = full;
                el.value = full;
            } else {
                el.value = '';
                el.dataset.rawValue = '';
                el.dataset.fullValue = '';
            }
        } else {
            el.value = '';
            el.dataset.rawValue = '';
            el.dataset.fullValue = '';
        }
        adjustBudgetFontSize(el);
    }

    function applyBudgetFocus(el) {
        if (!el) return;
        const raw = el.dataset.rawValue || el.value.replace(/,/g, '').trim();
        if (raw !== '') {
            el.value = raw;
            setTimeout(() => { try { el.select(); } catch(e) {} }, 0);
        }
        adjustBudgetFontSize(el);
    }

    function applyBudgetInput(el) {
        if (!el) return;
        const oldVal = el.value;
        const clean = sanitizeBudgetInput(oldVal);
        if (clean !== oldVal) {
            el.value = clean;
        }
        el.dataset.rawValue = clean;
        const num = parseFloat(clean);
        if (!isNaN(num)) {
            el.dataset.fullValue = formatBudgetFull(num);
        } else {
            el.dataset.fullValue = clean;
        }
        adjustBudgetFontSize(el);
    }

    // Global delegation for .field-budget to strictly enforce numbers-only input and capping at billions
    document.addEventListener('keydown', function(e) {
        const el = e.target;
        if (!el || !el.classList || !el.classList.contains('field-budget')) return;
        if (['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'].includes(e.key) ||
            (e.ctrlKey || e.metaKey)) {
            return;
        }
        if (/^[0-9]$/.test(e.key)) {
            const val = el.value || '';
            const selStart = el.selectionStart;
            const selEnd = el.selectionEnd;
            const newVal = val.substring(0, selStart) + e.key + val.substring(selEnd);
            const parts = newVal.split('.');
            if (parts[0].length > 12) {
                e.preventDefault();
                return;
            }
            if (parts.length > 1 && parts[1].length > 2) {
                e.preventDefault();
                return;
            }
            return;
        }
        if (e.key === '.') {
            const selStart = el.selectionStart;
            const selEnd = el.selectionEnd;
            const selectedText = el.value.substring(selStart, selEnd);
            if (!el.value.includes('.') || selectedText.includes('.')) {
                return;
            }
        }
        e.preventDefault();
    });

    document.addEventListener('input', function(e) {
        const el = e.target;
        if (!el || !el.classList || !el.classList.contains('field-budget')) return;
        applyBudgetInput(el);
    });

    document.addEventListener('paste', function(e) {
        const el = e.target;
        if (!el || !el.classList || !el.classList.contains('field-budget')) return;
        e.preventDefault();
        const text = (e.clipboardData || window.clipboardData).getData('text') || '';
        const clean = sanitizeBudgetInput(text);
        document.execCommand('insertText', false, clean);
        applyBudgetInput(el);
    });

    document.addEventListener('focusin', function(e) {
        const el = e.target;
        if (!el || !el.classList || !el.classList.contains('field-budget')) return;
        applyBudgetFocus(el);
    });

    document.addEventListener('focusout', function(e) {
        const el = e.target;
        if (!el || !el.classList || !el.classList.contains('field-budget')) return;
        applyBudgetBlur(el);
        if (typeof window.syncSpmsActiveTab === 'function') window.syncSpmsActiveTab();
        if (typeof AppState !== 'undefined' && typeof AppState.setDirty === 'function') AppState.setDirty(true);
    });

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.field-budget').forEach(adjustBudgetFontSize);
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

        const btn = document.getElementById('btn-submit');
        const origContent = btn ? btn.innerHTML : 'Submit';
        if (btn) {
            btn.innerText = 'Locking...';
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
        }

        const restoreBtn = () => {
            if (btn) {
                btn.innerHTML = origContent;
                btn.disabled = false;
                btn.classList.remove('opacity-75', 'cursor-not-allowed');
            }
        };

        apiPost('<?= site_url('folder/evaluate') ?>', formData, {
            onSuccess: () => window.location.reload(),
            onError: async (errMsg) => {
                restoreBtn();
                await window.appAlert(errMsg || "An error occurred while locking evaluation.");
            }
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

        const ok = await window.appConfirm("Complete and approve this evaluation?", { 
            title: 'Approve Evaluation',
            confirmText: 'Approve',
            cancelText: 'Cancel',
            variant: 'success'
        });
        if (!ok) return;

        // Ensure all reviewer remarks (PMT remarks and row remarks) are saved before approving
        if (typeof saveDocument === 'function') {
            await saveDocument(true);
        }

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');
        formData.append('final_score', finalScore);

        document.getElementById('btn-approve').innerText = 'Approving...';
        apiPost('<?= site_url('folder/approve') ?>', formData, {
            onSuccess: () => window.location.reload()
        });
    }

    async function unapproveFolderEvaluation() {
        const ok = await window.appConfirm("Remove your approval and revert to To Evaluate?", { 
            title: 'Remove Approval',
            confirmText: 'Remove Approval',
            cancelText: 'Cancel',
            variant: 'warning'
        });
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
        const reason = prompt("Return this evaluation to the employee for revision? Please enter your remarks or revision feedback:");
        if (reason === null) return;
        
        // Save evaluator row notes and PMT remarks before returning
        if (typeof saveDocument === 'function') {
            await saveDocument(true);
        }

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');
        formData.append('reason', (reason || '').trim());

        const btn = document.getElementById('btn-return');
        if (btn) btn.innerText = 'Returning...';
        apiPost('<?= site_url('folder/return') ?>', formData, {
            onSuccess: () => window.location.reload()
        });
    }

    async function lockFolderTarget() {
        const hasFile = (window.rubricAttachments && window.rubricAttachments.length > 0);
        const rubricsData = window.digitalRubricsData || tabs[0]?.formData?.rubrics || {};
        let criteriaCount = 0;
        Object.values(rubricsData).forEach(scores => {
            if (typeof scores === 'object') {
                Object.values(scores).forEach(s => {
                    if (s && (s.q || s.t || s.e)) criteriaCount++;
                });
            }
        });

        let rubricNotice = '';
        if (hasFile && criteriaCount > 0) {
            rubricNotice = 'Rubrics Matrix configured & external file attached.';
        } else if (hasFile) {
            rubricNotice = 'External Rubrics file attached.';
        } else if (criteriaCount > 0) {
            rubricNotice = `Rubrics Matrix configured (${criteriaCount} criteria set).`;
        } else {
            rubricNotice = 'Caution: No Rubrics standards configured or attached yet.';
        }

        const confirmMsg = `Submit targets and rubrics for supervisor approval?\n\n` +
            `• Rubrics Status: ${rubricNotice}\n\n` +
            `Note: Always attach your rubrics to your DPCR/IPCR/IPERF when submitting. Also, the targets in your rubrics should match the targets in this form.`;

        const ok = await window.appConfirm(confirmMsg, { 
            title: 'Submit Targets & Rubrics',
            confirmText: 'Submit Now',
            cancelText: 'Keep Editing',
            variant: (hasFile || criteriaCount > 0) ? 'primary' : 'warning'
        });
        if (!ok) return;
        
        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');

        const btn = document.getElementById('btn-submit-target');
        const origContent = btn ? btn.innerHTML : 'Submit Targets';
        if (btn) {
            btn.innerHTML = 'Submitting...';
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
        }

        const restoreBtn = () => {
            if (btn) {
                btn.innerHTML = origContent;
                btn.disabled = false;
                btn.classList.remove('opacity-75', 'cursor-not-allowed');
            }
        };

        apiPost('<?= site_url('folder/submit_target') ?>', formData, {
            onSuccess: () => window.location.reload(),
            onError: async (errMsg) => {
                restoreBtn();
                await window.appAlert(errMsg || "An error occurred while submitting targets.");
            }
        });
    }

    async function unsubmitEvaluationDocument() {
        const ok = await window.appConfirm("Are you sure you want to revoke your self-rating submission? This will return your evaluation to drafting status so you can edit your ratings and accomplishments.", { 
            title: 'Revoke Self-Rating',
            variant: 'undo', 
            confirmText: 'Revoke Self-Rating',
            cancelText: 'Keep Submitted'
        });
        if (!ok) return;

        const btn = document.getElementById('btn-unsubmit-eval');
        if (btn) {
            btn.innerText = 'Revoking...';
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        }

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');

        apiPost('<?= site_url('folder/unsubmit') ?>', formData, {
            onSuccess: () => window.location.reload(),
            onError: async (errMsg) => {
                if (btn) {
                    btn.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                        </svg>
                        <span>Revoke Self-Rating</span>
                    `;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
                await window.appAlert(errMsg || "An error occurred.");
            }
        });
    }

    async function unsubmitTargetDocument() {
        const ok = await window.appConfirm("Are you sure you want to revoke your target submission? This will return your folder to Draft status so you can make edits and fixes to your commitments.", { 
            title: 'Revoke Submission',
            variant: 'undo', 
            confirmText: 'Revoke Submission',
            cancelText: 'Keep Submitted'
        });
        if (!ok) return;

        const btn = document.getElementById('btn-unsubmit-target');
        if (btn) {
            btn.innerText = 'Revoking...';
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        }

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');

        apiPost('<?= site_url('folder/unsubmit_target') ?>', formData, {
            onSuccess: () => window.location.reload(),
            onError: async (errMsg) => {
                if (btn) {
                    btn.innerText = 'Revoke Submission';
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
                await window.appAlert(errMsg || "An error occurred.");
            }
        });
    }

    async function approveFolderTarget(releaseScope = null) {
        let msg = "Approve these targets?";
        let confirmBtnText = 'Approve';

        if (releaseScope === 'deans') {
            msg = "Approve this OPCR and immediately distribute it to all College Deans as their target basis?";
            confirmBtnText = 'Approve & Release to Deans';
        } else if (releaseScope === 'chairs') {
            msg = "Distribute this approved collegiate DPCR to all Department Chairs as their target basis?";
            confirmBtnText = 'Release to Chairs';
        } else if (releaseScope === 'faculty' || releaseScope === 'subordinates') {
            msg = "Approve this DPCR Department Chair and immediately distribute it to all faculty members as their target basis?";
            confirmBtnText = 'Approve & Release to Faculty';
        }

        const ok = await window.appConfirm(msg, { 
            title: 'Approve Targets',
            confirmText: confirmBtnText,
            cancelText: 'Cancel',
            variant: 'success'
        });
        if (!ok) return;

        // Save any reviewer remarks before approving targets
        if (typeof saveDocument === 'function') {
            await saveDocument(true);
        }

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');
        if (releaseScope) {
            formData.append('release_scope', releaseScope);
            if (releaseScope === 'deans') {
                formData.append('release_to_deans', '1');
            }
        }

        const btn = document.getElementById(releaseScope ? 'btn-approve-release-target' : 'btn-approve-target')
                 || document.getElementById('btn-release-subordinates');
        if (btn) btn.innerText = releaseScope ? 'Releasing...' : 'Approving...';

        apiPost('<?= site_url('folder/approve_target') ?>', formData, {
            onSuccess: async (res) => {
                if (res && res.message) {
                    await window.appAlert(res.message);
                }
                window.location.reload();
            },
            onError: async (errMsg) => {
                await window.appAlert(errMsg || "An error occurred.");
                window.location.reload();
            }
        });
    }

    async function unapproveFolderTarget() {
        const ok = await window.appConfirm("Remove approval and revert to pending?", { 
            title: 'Remove Approval',
            confirmText: 'Remove Approval',
            cancelText: 'Cancel',
            variant: 'warning'
        });
        if (!ok) return;

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');

        document.getElementById('btn-unapprove-target').innerText = 'Removing...';
        apiPost('<?= site_url('folder/unapprove_target') ?>', formData, {
            onSuccess: () => window.location.reload()
        });
    }

    async function returnTargetRevision() {
        const reason = prompt("Return targets to the employee for revision? Please enter your remarks or revision feedback:");
        if (reason === null) return;
        
        // Save any reviewer notes in the Remarks column before returning
        if (typeof saveDocument === 'function') {
            await saveDocument(true);
        }

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');
        formData.append('reason', (reason || '').trim());

        const btn = document.getElementById('btn-return-target');
        if (btn) btn.innerText = 'Returning...';
        apiPost('<?= site_url('folder/return_target') ?>', formData, {
            onSuccess: () => window.location.reload()
        });
    }

    function stampRoleTag(btn) {
        const td = btn.closest('td');
        if (!td) return;
        const ta = td.querySelector('.field-remarks');
        if (!ta) return;
        const roleTag = `[${window.currentReviewerRole || 'Reviewer'}]: `;
        if (!ta.value.includes(roleTag)) {
            ta.value = ta.value ? `${ta.value.trim()}\n${roleTag}` : roleTag;
        }
        ta.focus();
        if (typeof window.syncSpmsActiveTab === 'function') {
            window.syncSpmsActiveTab();
        }
        AppState.setDirty(true);
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
    window.currentReviewerRole = <?= json_encode($currentReviewerRole ?? 'Reviewer') ?>;
    window.currentReviewerName = <?= json_encode($currentReviewerName ?? '') ?>;

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
    const IS_DOC_DPCR = <?= $isDocDpcr ? 'true' : 'false' ?>;
    const IS_DOC_OPCR = <?= $isDocOpcr ? 'true' : 'false' ?>;

    // Default Seed Blueprint (Clean empty rows upon creation)
    const DEFAULT_BLUEPRINT = IS_DOC_DPCR ? {
        core: [
            { row_id: "row_core_1", mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "", std_5: "", std_4: "", std_3: "", std_2: "", std_1: "" },
            { row_id: "row_core_2", mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "", std_5: "", std_4: "", std_3: "", std_2: "", std_1: "" }
        ],
        strategic: [
            { row_id: "row_strat_1", mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "", std_5: "", std_4: "", std_3: "", std_2: "", std_1: "" }
        ],
        support: [
            { row_id: "row_supp_1", mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "", std_5: "", std_4: "", std_3: "", std_2: "", std_1: "" }
        ]
    } : (IS_DOC_OPCR ? {
        core: [
            { row_id: "row_core_1", mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "" },
            { row_id: "row_core_2", mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ],
        strategic: [
            { row_id: "row_strat_1", mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ],
        support: [
            { row_id: "row_supp_1", mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ]
    } : {
        core: [
            { row_id: "row_core_1", mfo: "", indicators: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ],
        strategic: [
            { row_id: "row_strat_1", mfo: "", indicators: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ],
        support: [
            { row_id: "row_supp_1", mfo: "", indicators: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ]
    });

    const CATEGORY_WEIGHTS = IS_DOC_DPCR ? {
        core: 0.60,
        strategic: 0.30,
        support: 0.10
    } : (IS_DOC_OPCR ? {
        core: 0.60,
        strategic: 0.25,
        support: 0.15
    } : {
        core: 0.70,
        strategic: 0.20,
        support: 0.10
    });

    function initActiveTabView() {
        const basisWorkspace = document.getElementById('spms-basis-workspace');
        const rubricsWorkspace = document.getElementById('spms-rubrics-workspace');

        if (activeTabId === 'basis-tab') {
            window.isSpmsFormActive = false;
            document.getElementById('spms-form-workspace')?.classList.add('hidden');
            document.getElementById('tinymce-wrapper')?.classList.add('hidden');
            if (rubricsWorkspace) rubricsWorkspace.classList.add('hidden');
            if (basisWorkspace) {
                basisWorkspace.classList.remove('hidden');
                renderBasisStaticSheet();
            }
            return;
        }

        if (activeTabId === 'rubrics-tab') {
            window.isSpmsFormActive = false;
            document.getElementById('spms-form-workspace')?.classList.add('hidden');
            document.getElementById('tinymce-wrapper')?.classList.add('hidden');
            if (basisWorkspace) basisWorkspace.classList.add('hidden');
            if (rubricsWorkspace) {
                rubricsWorkspace.classList.remove('hidden');
                document.getElementById('rubric-fab-container')?.classList.remove('hidden');
                renderDigitalRubricsMatrix();
                renderRubricAttachmentCard();
            }
            return;
        }

        document.getElementById('rubric-fab-container')?.classList.add('hidden');
        if (basisWorkspace) {
            basisWorkspace.classList.add('hidden');
        }
        if (rubricsWorkspace) {
            rubricsWorkspace.classList.add('hidden');
        }

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

    function addApproverBlock(name = '', position = '', date = '', isFirst = null) {
        const container = document.getElementById('approvers-container');
        if (!container) return;
        const items = container.querySelectorAll('.approver-item');
        const index = items.length;
        const isActuallyFirst = (isFirst !== null) ? isFirst : (index === 0);

        const isDpcr = (typeof window.isCurrentDocDpcr !== 'undefined') ? window.isCurrentDocDpcr : <?= $isDocDpcr ? 'true' : 'false' ?>;
        const isOpcr = (typeof window.isCurrentDocOpcr !== 'undefined') ? window.isCurrentDocOpcr : <?= $isDocOpcr ? 'true' : 'false' ?>;
        const isDpcrOrOpcr = isDpcr || isOpcr;

        const div = document.createElement('div');
        div.className = 'approver-item';
        div.style.cssText = (!isActuallyFirst ? 'border-top: 1px dashed #cbd5e1; margin-top: 6px; padding-top: 6px;' : '') + ' position: relative;';

        const namePlaceholder = isOpcr ? '(name of head of office / approving authority)' : (isDpcr ? '(name of office head)' : 'Name of Approving Authority');
        const posPlaceholder = isOpcr ? '(position / designation)' : (isDpcr ? '(position of office head)' : 'Official Designation');
        const disabledAttr = canEditTargets ? '' : 'disabled';

        div.innerHTML = `
            <table style="width: 100%; border-collapse: collapse; border: none; font-size: 11px;">
                <tr>
                    <td style="width: ${isDpcrOrOpcr ? '65px' : '60px'}; border: none; padding: 3px 0; font-weight: bold; color: ${isDpcrOrOpcr ? '#000' : '#64748b'};">Name:</td>
                    <td style="border: none; padding: 3px 0;">
                        <input type="text" class="field-approver-name" ${isActuallyFirst ? 'id="approver-name"' : ''} value="${escapeHtml(name)}" placeholder="${namePlaceholder}" style="font-weight: bold; color: #ba372a; border: none; border-bottom: 1px solid #ba372a; outline: none; font-size: 11px; width: ${isDpcrOrOpcr ? '60%' : '100%'};" ${disabledAttr}>
                        ${!isActuallyFirst ? (canEditTargets ? `
                        <button type="button" onclick="removeApproverBlock(this)" class="print-hide" style="margin-left: 6px; color: #dc2626; background: #fee2e2; border: 1px solid #fca5a5; font-size: 9px; padding: 1px 5px; border-radius: 3px; cursor: pointer; font-weight: bold;" title="Remove this signatory">✕ Remove</button>
                        ` : '') : (isDpcrOrOpcr ? `
                        <span style="color: #ba372a; font-style: italic; font-size: 10px; margin-left: 6px;">(may add signatories depending on position)</span>
                        ` : '')}
                    </td>
                </tr>
                <tr>
                    <td style="border: none; padding: 3px 0; font-weight: bold; color: ${isDpcrOrOpcr ? '#000' : '#64748b'};">Position:</td>
                    <td style="border: none; padding: 3px 0;">
                        <input type="text" class="field-approver-pos" ${isActuallyFirst ? 'id="approver-pos"' : ''} value="${escapeHtml(position)}" placeholder="${posPlaceholder}" style="color: #ba372a; border: none; border-bottom: 1px solid #ba372a; outline: none; font-size: 11px; width: ${isDpcrOrOpcr ? '80%' : '100%'};" ${disabledAttr}>
                    </td>
                </tr>
                <tr>
                    <td style="border: none; padding: 3px 0; font-weight: bold; color: ${isDpcrOrOpcr ? '#000' : '#64748b'};">Date:</td>
                    <td style="border: none; padding: 3px 0;">
                        <input type="date" class="field-approver-date" ${isActuallyFirst ? 'id="approver-date"' : ''} value="${escapeHtml(date)}" onclick="this.showPicker && this.showPicker()" style="color: #ba372a; border: none; border-bottom: 1px solid #ba372a; outline: none; font-size: 11px; width: 130px; background: transparent; font-family: inherit; cursor: pointer;" ${disabledAttr}>
                    </td>
                </tr>
            </table>
        `;
        container.appendChild(div);

        // Attach input listeners for dirty state, dynamic red hint styling, and autosync
        div.querySelectorAll('input').forEach(inp => {
            const updateStyle = () => {
                if (inp.value.trim() !== '') {
                    inp.style.color = '#0f172a';
                    inp.style.borderColor = '#cbd5e1';
                } else {
                    inp.style.color = '#ba372a';
                    inp.style.borderColor = '#ba372a';
                }
            };
            updateStyle();
            const handleUpdate = () => {
                updateStyle();
                window.syncSpmsActiveTab();
                if (typeof AppState !== 'undefined') AppState.setDirty(true);
            };
            inp.addEventListener('input', handleUpdate);
            inp.addEventListener('change', handleUpdate);
        });

        if (isFirst === null) {
            window.syncSpmsActiveTab();
            if (typeof AppState !== 'undefined') AppState.setDirty(true);
        }
    }

    function removeApproverBlock(btn) {
        const item = btn.closest('.approver-item');
        if (item) {
            item.remove();
            window.syncSpmsActiveTab();
            if (typeof AppState !== 'undefined') AppState.setDirty(true);
        }
    }

    function extractApprovers() {
        const items = document.querySelectorAll('#approvers-container .approver-item');
        const approvers = [];
        items.forEach(item => {
            const name = item.querySelector('.field-approver-name')?.value || '';
            const position = item.querySelector('.field-approver-pos')?.value || '';
            const date = item.querySelector('.field-approver-date')?.value || '';
            if (name || position || date || items.length === 1) {
                approvers.push({ name, position, date });
            }
        });
        if (approvers.length === 0) {
            approvers.push({
                name: document.getElementById('approver-name')?.value || '',
                position: document.getElementById('approver-pos')?.value || '',
                date: document.getElementById('approver-date')?.value || ''
            });
        }
        return approvers;
    }

    window.addApproverBlock = addApproverBlock;
    window.removeApproverBlock = removeApproverBlock;

    function populateSpmsForm(formData) {
        const data = formData || {};

        const explicitDocTitle = '<?= strtoupper(trim($doc['title'] ?? '')) ?>';
        const titleText = (data.title || explicitDocTitle).toUpperCase();
        const docType = '<?= strtolower($doc['doc_type'] ?? 'ipcr') ?>';
        
        // Prioritize explicit document title: A DPCR is always a DPCR regardless of user default doc_type
        const isDocExplicitDpcr = <?= $isDocDpcr ? 'true' : 'false' ?>;
        const isDpcr = isDocExplicitDpcr || explicitDocTitle === 'DPCR' || titleText.includes('DPCR') || titleText.includes('DIVISION') || (docType === 'dpcr' && !titleText.includes('OPCR'));
        window.isCurrentDocDpcr = isDpcr;
        const isDocExplicitOpcr = <?= $isDocOpcr ? 'true' : 'false' ?>;
        const isOpcr = !isDpcr && (isDocExplicitOpcr || explicitDocTitle === 'OPCR' || titleText.includes('OPCR') || titleText.includes('OFFICE') || docType === 'opcr');
        window.isCurrentDocOpcr = isOpcr;
        const isDocExplicitIperf = <?= $isDocIperf ? 'true' : 'false' ?>;
        const isIperf = !isDpcr && !isOpcr && (isDocExplicitIperf || explicitDocTitle.includes('IPERF') || titleText.startsWith('IPERF') || titleText.includes('INDIVIDUAL PERFORMANCE EVALUATION RATING FORM') || docType === 'iperf');
        window.isCurrentDocIperf = isIperf;

        let coreW = 0.70, stratW = 0.20, suppW = 0.10;
        let defaultDocTitle = 'INDIVIDUAL PERFORMANCE COMMITMENT AND REVIEW';
        let coreTitle = "CORE FUNCTIONS (70%)";
        let stratTitle = "STRATEGIC FUNCTIONS (20%)";
        let suppTitle = "SUPPORT FUNCTIONS (10%)";
        let rateeRole = "Faculty Member / Professor";
        let deanRole = "College Dean / Unit Head";
        let vpRole = "Vice President for Academic Affairs";

        if (isOpcr) {
            coreW = data.weights?.core ?? 0.60;
            stratW = data.weights?.strategic ?? 0.25;
            suppW = data.weights?.support ?? 0.15;
            defaultDocTitle = "OFFICE PERFORMANCE COMMITMENT AND REVIEW (OPCR)";
            coreTitle = `CORE MANDATE (${Math.round(coreW * 100)}%)`;
            stratTitle = `STRATEGIC FUNCTIONS (${Math.round(stratW * 100)}%)`;
            suppTitle = `SUPPORT FUNCTIONS (${Math.round(suppW * 100)}%)`;
            const posLower = (ownerAccountInfo.position || '').toLowerCase();
            rateeRole = posLower.includes('vice president') ? "Vice President / Sector Head" : "Vice President / College Dean";
            deanRole = "University President / PMT Chair";
            vpRole = "University President";
            if (!data.title || data.title.includes('IPCR') || data.title.includes('DPCR') || data.title.includes('Faculty / Professors')) {
                data.title = defaultDocTitle;
            }
        } else if (isDpcr) {
            coreW = data.weights?.core ?? 0.60;
            stratW = data.weights?.strategic ?? 0.30;
            suppW = data.weights?.support ?? 0.10;
            defaultDocTitle = "DEPARTMENT PERFORMANCE COMMITMENT AND REVIEW (DPCR)";
            coreTitle = `CORE FUNCTIONS (${Math.round(coreW * 100)}%)`;
            stratTitle = `STRATEGIC FUNCTIONS (${Math.round(stratW * 100)}%)`;
            suppTitle = `SUPPORT FUNCTIONS (${Math.round(suppW * 100)}%)`;
            const posLower = (ownerAccountInfo.position || '').toLowerCase();
            rateeRole = posLower.includes('dean') ? "College Dean / Supervisor" : "Department Chairperson / Unit Head";
            deanRole = posLower.includes('dean') ? "Vice President for Academic Affairs" : "College Dean / Supervisor";
            vpRole = "Vice President for Academic Affairs";
            if (!data.title || data.title.includes('IPCR') || data.title.includes('OPCR') || data.title.includes('Division Performance')) {
                data.title = defaultDocTitle;
            }
        } else if (isIperf) {
            coreW = 1.00;
            stratW = 0.00;
            suppW = 0.00;
            defaultDocTitle = "INDIVIDUAL PERFORMANCE EVALUATION RATING FORM FOR CONTRACT OF SERVICE AND JOB ORDER PERSONNEL";
            coreTitle = "OFFICE PPA / EXPECTED OUTPUTS";
            stratTitle = "";
            suppTitle = "";
            rateeRole = "Contract of Service / Job Order Personnel";
            deanRole = "Immediate Supervisor";
            vpRole = "Office Head";
            if (!data.title || data.title.includes('IPCR') || data.title.includes('DPCR') || data.title.includes('OPCR')) {
                data.title = defaultDocTitle;
            }
        }

        CATEGORY_WEIGHTS.core = coreW;
        CATEGORY_WEIGHTS.strategic = stratW;
        CATEGORY_WEIGHTS.support = suppW;

        // Header info
        const titleEl = document.getElementById('spms-doc-title');
        if (titleEl) {
            let titleToSet = data.title || defaultDocTitle;
            if (titleToSet.includes('Faculty / Professors') || titleToSet.includes('IPCR Form')) {
                titleToSet = defaultDocTitle;
            }
            titleEl.innerText = titleToSet;
        }

        const elCore = document.getElementById('label-cat-core');
        if (elCore) {
            const hint = ' <span style="font-weight: normal; font-size: 9px; color: #ba372a;">(depending on position/designation)</span>';
            if (!isDpcr && !isOpcr && !isIperf) {
                elCore.innerHTML = 'CORE FUNCTIONS (70%)' + hint;
            } else {
                elCore.innerHTML = escapeHtml(coreTitle) + hint;
            }
        }
        const elStrat = document.getElementById('label-cat-strategic');
        if (elStrat) {
            const hint = ' <span style="font-weight: normal; font-size: 9px; color: #ba372a;">(depending on position/designation)</span>';
            if (!isDpcr && !isOpcr && !isIperf) {
                elStrat.innerHTML = 'STRATEGIC FUNCTIONS (20%)' + hint;
            } else {
                elStrat.innerHTML = escapeHtml(stratTitle) + hint;
            }
        }
        const elSupp = document.getElementById('label-cat-support');
        if (elSupp) {
            const hint = ' <span style="font-weight: normal; font-size: 9px; color: #ba372a;">(depending on position/designation)</span>';
            if (!isDpcr && !isOpcr && !isIperf) {
                elSupp.innerHTML = 'SUPPORT FUNCTIONS (10%)' + hint;
            } else {
                elSupp.innerHTML = escapeHtml(suppTitle) + hint;
            }
        }

        const elRoleRatee = document.getElementById('sig-role-ratee');
        if (elRoleRatee) elRoleRatee.innerText = rateeRole;
        const elRoleDean = document.getElementById('sig-role-dean');
        if (elRoleDean) elRoleDean.innerText = deanRole;
        const elRoleVp = document.getElementById('sig-role-vp');
        if (elRoleVp) elRoleVp.innerText = vpRole;

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
            if (el) {
                el.value = (val !== undefined && val !== null && val !== '') ? val : '';
                if (el.value.trim() !== '') {
                    el.style.color = '#0f172a';
                    el.style.borderColor = '#94a3b8';
                } else {
                    el.style.color = '#ba372a';
                    el.style.borderColor = '#ba372a';
                }
            }
        };

        const defaultRateeName = '';
        const defaultRateePos  = '';
        const defaultRateeDept = '';
        const defaultPeriod    = ''; // Keep blank for user to fill

        let savedPeriod = data.ratee?.period || '';
        if (savedPeriod.toLowerCase().startsWith('untitled evaluation') || (ownerAccountInfo.folderTitle && savedPeriod === ownerAccountInfo.folderTitle)) {
            savedPeriod = '';
        }

        const defaultSupName = '';
        const defaultSupPos  = '';

        setVal('ratee-name', data.ratee?.name || '');
        setVal('ratee-position', data.ratee?.position || '');
        setVal('ratee-dept', data.ratee?.dept || '');
        setVal('ratee-period', savedPeriod);

        // Populate Approver(s)
        const container = document.getElementById('approvers-container');
        if (container) {
            container.innerHTML = '';
            let approversList = (data.approvers && Array.isArray(data.approvers) && data.approvers.length > 0)
                ? data.approvers
                : (data.approver && (data.approver.name || data.approver.position || data.approver.date)
                    ? [data.approver]
                    : [{ name: '', position: '', date: '' }]);

            approversList.forEach((appr, idx) => {
                addApproverBlock(appr.name || '', appr.position || '', appr.date || '', idx === 0);
            });
        } else {
            setVal('approver-name', data.approver?.name || '');
            setVal('approver-pos', data.approver?.position || '');
            setVal('approver-date', data.approver?.date || '');
        }

        const addApprBtn = document.getElementById('btn-add-approver') || document.getElementById('btn-add-approver-ipcr');
        if (addApprBtn) {
            addApprBtn.style.display = canEditTargets ? 'inline-flex' : 'none';
        }

        setVal('ratee-sign-name', data.rateeSign?.name || '');
        setVal('ratee-sign-date', data.rateeSign?.date || '');

        window.currentDocCurrency = data.budget_currency || data.currency || '₱';
        document.querySelectorAll('.header-budget-currency').forEach(el => {
            el.value = window.currentDocCurrency;
            el.style.width = Math.max(22, (el.value.length + 1) * 7.5) + 'px';
            el.disabled = !canEditTargets;
        });

        setVal('pmt-remarks', data.pmtRemarks || '');

        setVal('sig-ratee-name', data.signatories?.ratee || '');
        setVal('sig-ratee-pos', data.signatories?.rateePos || '');
        setVal('sig-ratee-date', data.signatories?.rateeDate || '');
        setVal('sig-dean-name', data.signatories?.dean || '');
        setVal('sig-dean-date', data.signatories?.deanDate || '');
        setVal('sig-vp-name', data.signatories?.vp || '');
        setVal('sig-vp-date', data.signatories?.vpDate || '');

        if (isIperf) {
            setVal('ratee-classification', data.classification || 'Contract of Service (COS)');
            setVal('sig-targets-prepared-name', data.signatories?.targetsPreparedName || '');
            setVal('sig-targets-prepared-date', data.signatories?.targetsPreparedDate || '');
            setVal('sig-targets-approved-name', data.signatories?.targetsApprovedName || '');
            setVal('sig-targets-approved-date', data.signatories?.targetsApprovedDate || '');
            setVal('sig-eval-rated-name', data.signatories?.evalRatedName || '');
            setVal('sig-eval-rated-date', data.signatories?.evalRatedDate || '');
            setVal('sig-eval-conforme-name', data.signatories?.evalConformeName || '');
            setVal('sig-eval-conforme-date', data.signatories?.evalConformeDate || '');

            const elClass = document.getElementById('ratee-classification');
            if (elClass) elClass.disabled = !canEditTargets;
            ['sig-targets-prepared-name', 'sig-targets-prepared-date',
             'sig-targets-approved-name', 'sig-targets-approved-date'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.disabled = !canEditTargets;
            });
            ['sig-eval-rated-name', 'sig-eval-rated-date',
             'sig-eval-conforme-name', 'sig-eval-conforme-date'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.disabled = !canEditEvaluation;
            });
        }

        // Clear existing rows
        document.querySelectorAll('.table-row-core, .table-row-strategic, .table-row-support').forEach(tr => tr.remove());

        // Populate category rows
        const hasSavedCategories = Boolean(data && data.categories && typeof data.categories === 'object');
        const cats = data.categories || DEFAULT_BLUEPRINT;
        const coreRows = Array.isArray(cats.core) 
            ? (hasSavedCategories ? cats.core : (cats.core.length > 0 ? cats.core : DEFAULT_BLUEPRINT.core))
            : (DEFAULT_BLUEPRINT.core || []);
        coreRows.forEach(row => addTableRow('core', row));

        if (!isIperf) {
            const stratRows = Array.isArray(cats.strategic)
                ? (hasSavedCategories ? cats.strategic : (cats.strategic.length > 0 ? cats.strategic : DEFAULT_BLUEPRINT.strategic))
                : (DEFAULT_BLUEPRINT.strategic || []);
            stratRows.forEach(row => addTableRow('strategic', row));

            const suppRows = Array.isArray(cats.support)
                ? (hasSavedCategories ? cats.support : (cats.support.length > 0 ? cats.support : DEFAULT_BLUEPRINT.support))
                : (DEFAULT_BLUEPRINT.support || []);
            suppRows.forEach(row => addTableRow('support', row));
        }

        // Update add buttons visibility based on phase permissions
        ['core', 'strategic', 'support'].forEach(cat => {
            const tfoot = document.getElementById(`tfoot-add-${cat}`);
            if (tfoot) {
                if (isIperf && (cat === 'strategic' || cat === 'support')) {
                    tfoot.style.display = 'none';
                } else {
                    tfoot.style.display = canEditTargets ? '' : 'none';
                }
            }
        });

        // Update header ratee inputs based on phase permissions and attach live styling listeners
        ['ratee-name', 'ratee-position', 'ratee-dept', 'ratee-period', 'ratee-sign-name', 'ratee-sign-date', 'approver-name', 'approver-pos', 'approver-date', 'sig-ratee-name', 'sig-ratee-pos', 'sig-ratee-date', 'sig-dean-name', 'sig-dean-pos', 'sig-dean-date', 'sig-targets-prepared-date', 'sig-targets-approved-date', 'sig-eval-rated-date', 'sig-eval-conforme-date'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.disabled = !canEditTargets;
                const updateStyle = function() {
                    if (el.value.trim() !== '') {
                        el.style.color = '#0f172a';
                        el.style.borderColor = '#94a3b8';
                    } else {
                        el.style.color = '#ba372a';
                        el.style.borderColor = '#ba372a';
                    }
                };
                if (!el.dataset.hasStyleListener) {
                    el.dataset.hasStyleListener = '1';
                    el.addEventListener('input', () => {
                        updateStyle();
                        AppState.setDirty(true);
                        if (typeof window.syncSpmsActiveTab === 'function') {
                            window.syncSpmsActiveTab();
                        }
                    });
                    el.addEventListener('change', () => {
                        updateStyle();
                        AppState.setDirty(true);
                        if (typeof window.syncSpmsActiveTab === 'function') {
                            window.syncSpmsActiveTab();
                        }
                    });
                }
                updateStyle();
            }
        });

        // Setup live sync for header currency inputs
        document.querySelectorAll('.header-budget-currency').forEach(el => {
            if (!el.dataset.hasCurrencyListener) {
                el.dataset.hasCurrencyListener = '1';
                el.style.width = Math.max(18, (el.value.length + 1) * 6.5) + 'px';
                el.addEventListener('input', function() {
                    const val = this.value || '₱';
                    this.style.width = Math.max(18, (this.value.length + 1) * 6.5) + 'px';
                    window.currentDocCurrency = val;
                    document.querySelectorAll('.header-budget-currency').forEach(other => {
                        if (other !== this) other.value = this.value;
                    });
                    document.querySelectorAll('.field-budget-currency').forEach(rowCur => {
                        rowCur.value = val;
                        rowCur.style.width = Math.max(18, (val.length + 1) * 6.5) + 'px';
                        const bInput = rowCur.closest('.budget-input-wrapper')?.querySelector('.field-budget');
                        if (bInput) adjustBudgetFontSize(bInput);
                    });
                    AppState.setDirty(true);
                    if (typeof window.syncSpmsActiveTab === 'function') {
                        window.syncSpmsActiveTab();
                    }
                });
                el.addEventListener('blur', function() {
                    if (this.value.trim() === '') {
                        this.value = '₱';
                        this.dispatchEvent(new Event('input'));
                    }
                });
            }
        });

        recalculateForm();
    }

    function addTableRow(category, rowData = null) {
        const tbody = document.getElementById(`tbody-${category}`);
        if (!tbody) return;

        const data = rowData || (window.isCurrentDocIperf ? {
            row_id: 'row_' + Date.now() + '_' + Math.random().toString(36).substr(2, 7),
            mfo: "",
            indicators: "",
            accomplishments: "",
            q: "", t: "", e: "",
            remarks: ""
        } : (window.isCurrentDocDpcr ? {
            row_id: 'row_' + Date.now() + '_' + Math.random().toString(36).substr(2, 7),
            mfo: "",
            indicators: "",
            budget: "",
            budget_currency: "₱",
            accountable: "",
            accomplishments: "",
            q: "", t: "", e: "",
            remarks: "",
            std_5: "", std_4: "", std_3: "", std_2: "", std_1: ""
        } : (window.isCurrentDocOpcr ? {
            row_id: 'row_' + Date.now() + '_' + Math.random().toString(36).substr(2, 7),
            mfo: "",
            indicators: "",
            budget: "",
            budget_currency: "₱",
            accountable: "",
            accomplishments: "",
            q: "", t: "", e: "",
            remarks: ""
        } : {
            row_id: 'row_' + Date.now() + '_' + Math.random().toString(36).substr(2, 7),
            mfo: "",
            indicators: "",
            accomplishments: "",
            q: "", t: "", e: "",
            remarks: ""
        })));

        const rowId = data.row_id || ('row_' + Date.now() + '_' + Math.random().toString(36).substr(2, 7));
        const rawBudget = (data.budget !== undefined && data.budget !== null && String(data.budget).trim() !== '')
            ? sanitizeBudgetInput(String(data.budget))
            : '';
        const numBudget = parseFloat(rawBudget);
        const fullBudget = !isNaN(numBudget) ? formatBudgetFull(numBudget) : rawBudget;
        const displayBudget = !isNaN(numBudget) ? formatBudgetDisplay(numBudget) : rawBudget;
        const rowCurrency = data.budget_currency || (typeof window !== 'undefined' && window.currentDocCurrency) || '₱';
        const rowFiles = (window.documentAttachments && window.documentAttachments[rowId]) || [];
        const fileCount = rowFiles.length;
        const hasFiles = fileCount > 0;

        const mfoDisabled = !canEditTargets;
        const evalDisabled = !canEditEvaluation;

        const tr = document.createElement('tr');
        tr.className = `table-row-${category}`;
        tr.dataset.rowId = rowId;
        tr.id = rowId;
        tr.style.borderBottom = '1px solid #000000';

        if (window.isCurrentDocIperf) {
            tr.innerHTML = `
                <!-- 1. Office PPA (Target Phase) -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <textarea class="spms-textarea field-mfo" rows="3" placeholder="Enter office/project/programs/activities aligned with office deliverables..." ${mfoDisabled ? 'disabled title="Locked (Only modifiable during Target Phase)"' : ''}>${escapeHtml(data.mfo)}</textarea>
                </td>

                <!-- 2. Expected Outputs (Target Phase) -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <textarea class="spms-textarea field-indicators" rows="3" placeholder="Enter expected outputs based on contract or duties..." ${mfoDisabled ? 'disabled title="Locked (Only modifiable during Target Phase)"' : ''}>${escapeHtml(data.indicators)}</textarea>
                </td>

                <!-- 3. Actual Accomplishments (Evaluation Phase) -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <textarea class="spms-textarea field-accomplishments" rows="3" placeholder="Enter actual accomplishments..." ${evalDisabled ? 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' : ''}>${escapeHtml(data.accomplishments)}</textarea>
                    
                    <!-- MOV Evidence Toolbar (Only visible during Evaluation Phase) -->
                    ${isEvaluationPhase ? `
                    <div class="mov-toolbar flex items-center justify-between mt-1 pt-1 border-t border-slate-200 dark:border-slate-800 text-[10px] print-hide">
                        ${hasFiles ? `
                        <button type="button" onclick="openMovModal('${rowId}', this)" id="btn-mov-${rowId}" 
                                class="btn-mov-attachment inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md font-bold transition-all border shadow-xs cursor-pointer bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-500/40 hover:bg-emerald-500/25" 
                                title="View Means of Verification (MOV) evidence proof for this accomplishment">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            <span class="mov-btn-text">MOV (${fileCount})</span>
                        </button>
                        ` : (canEditEvaluation ? `
                        <button type="button" onclick="openMovModal('${rowId}', this)" id="btn-mov-${rowId}" 
                                class="btn-mov-attachment inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md font-bold transition-all border shadow-xs cursor-pointer bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 border-slate-300 dark:border-slate-700" 
                                title="Upload Means of Verification (MOV) evidence proof for this accomplishment">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            <span class="mov-btn-text">Attach MOV</span>
                        </button>
                        ` : `
                        <span class="text-[9px] text-slate-400 italic">No MOV</span>
                        `)}
                        <span class="text-[9px] text-slate-400 font-medium italic">Evidence</span>
                    </div>
                    ` : ''}
                </td>

                <!-- 4. Rating Q (Evaluation Phase) -->
                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                    <input type="number" min="1" max="5" step="1" 
                        value="${data.q !== undefined && data.q !== null && data.q !== '' ? data.q : ''}" 
                        placeholder="—" 
                        ${evalDisabled ? 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' : 'title="Enter 1 to 5. Erase or press Esc to clear" oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)"'} 
                        class="spms-score-input field-q">
                </td>

                <!-- 5. Rating T (Evaluation Phase) -->
                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                    <input type="number" min="1" max="5" step="1" 
                        value="${data.t !== undefined && data.t !== null && data.t !== '' ? data.t : ''}" 
                        placeholder="—" 
                        ${evalDisabled ? 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' : 'title="Enter 1 to 5. Erase or press Esc to clear" oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)"'} 
                        class="spms-score-input field-t">
                </td>

                <!-- 6. Rating E (Evaluation Phase) -->
                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                    <input type="number" min="1" max="5" step="1" 
                        value="${data.e !== undefined && data.e !== null && data.e !== '' ? data.e : ''}" 
                        placeholder="—" 
                        ${evalDisabled ? 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' : 'title="Enter 1 to 5. Erase or press Esc to clear" oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)"'} 
                        class="spms-score-input field-e">
                </td>

                <!-- 7. Row Average -->
                <td style="padding: 4px 2px; text-align: center; vertical-align: middle; background: #f0f9ff; border: 1px solid #000;">
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <span class="field-row-avg" style="font-weight: 900; color: #0369a1; font-size: 11px;">—</span>
                        <span style="font-size: 8px; font-weight: 800; color: #0284c7; text-transform: uppercase;">auto</span>
                    </div>
                </td>

                <!-- 8. Remarks -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <div class="flex items-center justify-between mb-1 print-hide">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tight">Remarks</span>
                        ${!isOwner ? `
                            <button type="button" onclick="stampRoleTag(this)" class="text-[9px] font-extrabold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-300 px-1.5 py-0.5 rounded border border-emerald-300 dark:border-emerald-800 transition-colors cursor-pointer" title="Stamp your role tag into remarks">
                                + Tag [${escapeHtml(window.currentReviewerRole || 'Reviewer')}]
                            </button>
                        ` : ''}
                    </div>
                    <textarea class="spms-textarea field-remarks" rows="3" placeholder="Enter remarks..." ${isOwner && (status === FolderStatus.PENDING_TARGET_APPROVAL || status === FolderStatus.SUBMITTED || status === FolderStatus.TARGET_APPROVED || status === FolderStatus.APPROVED) ? 'disabled title="Locked while submitted or approved"' : ''}>${escapeHtml(data.remarks)}</textarea>
                </td>

                <!-- 9. Delete Action (Target Phase) -->
                <td style="padding: 2px; text-align: center; vertical-align: middle; border: 1px solid #000;" class="print-hide">
                    ${canEditTargets ? `
                    <button type="button" onclick="deleteTableRow(this)" title="Delete Row" class="btn-del-row">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>` : ''}
                </td>
            `;
        } else if (window.isCurrentDocDpcr) {
            tr.innerHTML = `
                <!-- 1. Programs, Projects, Activities (Target Phase) -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <textarea class="spms-textarea field-mfo" rows="3" placeholder="Enter programs, projects, activities..." ${mfoDisabled ? 'disabled title="Locked (Only modifiable during Target Phase)"' : ''}>${escapeHtml(data.mfo)}</textarea>
                </td>

                <!-- 2. Success Indicators (Target Phase) -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <textarea class="spms-textarea field-indicators" rows="3" placeholder="Enter success indicators (targets + measures)..." ${mfoDisabled ? 'disabled title="Locked (Only modifiable during Target Phase)"' : ''}>${escapeHtml(data.indicators)}</textarea>
                </td>

                <!-- 3. Allotted Budget (Target Phase) - Decimal with Editable Currency -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <div class="budget-input-wrapper">
                        <input type="text" class="field-budget-currency" value="${escapeHtml(rowCurrency)}" placeholder="₱" title="Currency (editable, defaults to ₱)" style="width: 18px; text-align: center; font-weight: 700; color: #0284c7; border: none; border-right: 1px solid #e2e8f0; outline: none; background: transparent; font-size: 10px; padding: 1px 2px 1px 0; cursor: pointer; flex-shrink: 0;" ${mfoDisabled ? 'disabled' : ''}>
                        <input type="text" inputmode="decimal" spellcheck="false" autocomplete="off" class="spms-input field-budget" maxlength="18" data-raw-value="${escapeHtml(rawBudget)}" data-full-value="${escapeHtml(fullBudget)}" value="${escapeHtml(fullBudget)}" placeholder="0.00" style="flex: 1; min-width: 0; width: 100%; border: none; outline: none; font-size: 11px; text-align: right; background: transparent; font-family: inherit; font-weight: 500; color: #0f172a; padding: 1px 1px; transition: font-size 0.1s ease;" ${mfoDisabled ? 'disabled title="Locked (Only modifiable during Target Phase)"' : ''}>
                    </div>
                </td>

                <!-- 4. Individuals / Offices Accountable (Target Phase) -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <textarea class="spms-textarea field-accountable" rows="3" placeholder="Individuals / offices accountable..." ${mfoDisabled ? 'disabled title="Locked (Only modifiable during Target Phase)"' : ''}>${escapeHtml(data.accountable || '')}</textarea>
                </td>

                <!-- 5. Actual Accomplishments (Evaluation Phase) -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <textarea class="spms-textarea field-accomplishments" rows="3" placeholder="Enter actual accomplishments..." ${evalDisabled ? 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' : ''}>${escapeHtml(data.accomplishments)}</textarea>
                    
                    <!-- MOV Evidence Toolbar (Only visible during Evaluation Phase) -->
                    ${isEvaluationPhase ? `
                    <div class="mov-toolbar flex items-center justify-between mt-1 pt-1 border-t border-slate-200 dark:border-slate-800 text-[10px] print-hide">
                        ${hasFiles ? `
                        <button type="button" onclick="openMovModal('${rowId}', this)" id="btn-mov-${rowId}" 
                                class="btn-mov-attachment inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md font-bold transition-all border shadow-xs cursor-pointer bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-500/40 hover:bg-emerald-500/25" 
                                title="View Means of Verification (MOV) evidence proof for this accomplishment">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            <span class="mov-btn-text">MOV (${fileCount})</span>
                        </button>
                        ` : (canEditEvaluation ? `
                        <button type="button" onclick="openMovModal('${rowId}', this)" id="btn-mov-${rowId}" 
                                class="btn-mov-attachment inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md font-bold transition-all border shadow-xs cursor-pointer bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 border-slate-300 dark:border-slate-700" 
                                title="Upload Means of Verification (MOV) evidence proof for this accomplishment">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            <span class="mov-btn-text">Attach MOV</span>
                        </button>
                        ` : `
                        <span class="text-[9px] text-slate-400 italic">No MOV</span>
                        `)}
                        <span class="text-[9px] text-slate-400 font-medium italic">Evidence</span>
                    </div>
                    ` : ''}
                </td>

                <!-- Rating Q, T, E Inputs (Evaluation Phase) with Amber #ffe599 Background -->
                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                    <input type="number" min="1" max="5" step="1" 
                        value="${data.q !== undefined && data.q !== null && data.q !== '' ? data.q : ''}" 
                        placeholder="—" 
                        ${evalDisabled ? 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' : 'title="Enter 1 to 5. Erase or press Esc to clear" oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)"'} 
                        class="spms-score-input field-q" style="background-color: #ffe599;">
                </td>
                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                    <input type="number" min="1" max="5" step="1" 
                        value="${data.t !== undefined && data.t !== null && data.t !== '' ? data.t : ''}" 
                        placeholder="—" 
                        ${evalDisabled ? 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' : 'title="Enter 1 to 5. Erase or press Esc to clear" oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)"'} 
                        class="spms-score-input field-t" style="background-color: #ffe599;">
                </td>
                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                    <input type="number" min="1" max="5" step="1" 
                        value="${data.e !== undefined && data.e !== null && data.e !== '' ? data.e : ''}" 
                        placeholder="—" 
                        ${evalDisabled ? 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' : 'title="Enter 1 to 5. Erase or press Esc to clear" oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)"'} 
                        class="spms-score-input field-e" style="background-color: #ffe599;">
                </td>

                <!-- Row Average -->
                <td style="padding: 4px 2px; text-align: center; vertical-align: middle; background: #f0f9ff; border: 1px solid #000;">
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <span class="field-row-avg" style="font-weight: 900; color: #0369a1; font-size: 11px;">—</span>
                        <span style="font-size: 8px; font-weight: 800; color: #0284c7; text-transform: uppercase;">auto</span>
                    </div>
                </td>

                <!-- Remarks -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <div class="flex items-center justify-between mb-1 print-hide">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tight">Remarks</span>
                        ${!isOwner ? `
                            <button type="button" onclick="stampRoleTag(this)" class="text-[9px] font-extrabold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-300 px-1.5 py-0.5 rounded border border-emerald-300 dark:border-emerald-800 transition-colors cursor-pointer" title="Stamp your role tag into remarks">
                                + Tag [${escapeHtml(window.currentReviewerRole || 'Reviewer')}]
                            </button>
                        ` : ''}
                    </div>
                    <textarea class="spms-textarea field-remarks" rows="3" placeholder="Enter remarks..." ${isOwner && (status === FolderStatus.PENDING_TARGET_APPROVAL || status === FolderStatus.SUBMITTED || status === FolderStatus.TARGET_APPROVED || status === FolderStatus.APPROVED) ? 'disabled title="Locked while submitted or approved"' : ''}>${escapeHtml(data.remarks)}</textarea>
                </td>

                <!-- Delete Action (Target Phase) -->
                <td style="padding: 2px; text-align: center; vertical-align: middle; border: 1px solid #000;" class="print-hide">
                    ${canEditTargets ? `
                    <button type="button" onclick="deleteTableRow(this)" title="Delete Row" class="btn-del-row">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>` : ''}
                </td>
            `;
        } else if (window.isCurrentDocOpcr) {
            tr.innerHTML = `
                <!-- 1. Programs, Projects, Activities (Target Phase) -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <textarea class="spms-textarea field-mfo" rows="3" placeholder="Enter project, program, activities..." ${mfoDisabled ? 'disabled title="Locked (Only modifiable during Target Phase)"' : ''}>${escapeHtml(data.mfo)}</textarea>
                </td>

                <!-- 2. Success Indicators (Targets + Measures) Performance (Target Phase) -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <textarea class="spms-textarea field-indicators" rows="3" placeholder="Enter success indicators (targets + measures)..." ${mfoDisabled ? 'disabled title="Locked (Only modifiable during Target Phase)"' : ''}>${escapeHtml(data.indicators)}</textarea>
                </td>

                <!-- 3. Allotted Budget (Target Phase) - Decimal with Editable Currency -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <div class="budget-input-wrapper">
                        <input type="text" class="field-budget-currency" value="${escapeHtml(rowCurrency)}" placeholder="₱" title="Currency (editable, defaults to ₱)" style="width: 18px; text-align: center; font-weight: 700; color: #0284c7; border: none; border-right: 1px solid #e2e8f0; outline: none; background: transparent; font-size: 10px; padding: 1px 2px 1px 0; cursor: pointer; flex-shrink: 0;" ${mfoDisabled ? 'disabled' : ''}>
                        <input type="text" inputmode="decimal" spellcheck="false" autocomplete="off" class="spms-input field-budget" maxlength="18" data-raw-value="${escapeHtml(rawBudget)}" data-full-value="${escapeHtml(fullBudget)}" value="${escapeHtml(fullBudget)}" placeholder="0.00" style="flex: 1; min-width: 0; width: 100%; border: none; outline: none; font-size: 11px; text-align: right; background: transparent; font-family: inherit; font-weight: 500; color: #0f172a; padding: 1px 1px; transition: font-size 0.1s ease;" ${mfoDisabled ? 'disabled title="Locked (Only modifiable during Target Phase)"' : ''}>
                    </div>
                </td>

                <!-- 4. Divisions Accountable (Target Phase) -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <textarea class="spms-textarea field-accountable" rows="3" placeholder="Divisions accountable..." ${mfoDisabled ? 'disabled title="Locked (Only modifiable during Target Phase)"' : ''}>${escapeHtml(data.accountable || '')}</textarea>
                </td>

                <!-- 5. Actual Accomplishment (Evaluation Phase) -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <textarea class="spms-textarea field-accomplishments" rows="3" placeholder="Enter actual accomplishment..." ${evalDisabled ? 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' : ''}>${escapeHtml(data.accomplishments)}</textarea>
                    
                    <!-- MOV Evidence Toolbar (Only visible during Evaluation Phase) -->
                    ${isEvaluationPhase ? `
                    <div class="mov-toolbar flex items-center justify-between mt-1 pt-1 border-t border-slate-200 dark:border-slate-800 text-[10px] print-hide">
                        ${hasFiles ? `
                        <button type="button" onclick="openMovModal('${rowId}', this)" id="btn-mov-${rowId}" 
                                class="btn-mov-attachment inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md font-bold transition-all border shadow-xs cursor-pointer bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-500/40 hover:bg-emerald-500/25" 
                                title="View Means of Verification (MOV) evidence proof for this accomplishment">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            <span class="mov-btn-text">MOV (${fileCount})</span>
                        </button>
                        ` : (canEditEvaluation ? `
                        <button type="button" onclick="openMovModal('${rowId}', this)" id="btn-mov-${rowId}" 
                                class="btn-mov-attachment inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md font-bold transition-all border shadow-xs cursor-pointer bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 border-slate-300 dark:border-slate-700" 
                                title="Upload Means of Verification (MOV) evidence proof for this accomplishment">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            <span class="mov-btn-text">Attach MOV</span>
                        </button>
                        ` : `
                        <span class="text-[9px] text-slate-400 italic">No MOV</span>
                        `)}
                        <span class="text-[9px] text-slate-400 font-medium italic">Evidence</span>
                    </div>
                    ` : ''}
                </td>

                <!-- Rating Q, T, E Inputs (Evaluation Phase) with Amber #ffe599 Background -->
                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                    <input type="number" min="1" max="5" step="1" 
                        value="${data.q !== undefined && data.q !== null && data.q !== '' ? data.q : ''}" 
                        placeholder="—" 
                        ${evalDisabled ? 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' : 'title="Enter 1 to 5. Erase or press Esc to clear" oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)"'} 
                        class="spms-score-input field-q" style="background-color: #ffe599;">
                </td>
                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                    <input type="number" min="1" max="5" step="1" 
                        value="${data.t !== undefined && data.t !== null && data.t !== '' ? data.t : ''}" 
                        placeholder="—" 
                        ${evalDisabled ? 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' : 'title="Enter 1 to 5. Erase or press Esc to clear" oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)"'} 
                        class="spms-score-input field-t" style="background-color: #ffe599;">
                </td>
                <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                    <input type="number" min="1" max="5" step="1" 
                        value="${data.e !== undefined && data.e !== null && data.e !== '' ? data.e : ''}" 
                        placeholder="—" 
                        ${evalDisabled ? 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' : 'title="Enter 1 to 5. Erase or press Esc to clear" oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)"'} 
                        class="spms-score-input field-e" style="background-color: #ffe599;">
                </td>

                <!-- Row Average -->
                <td style="padding: 4px 2px; text-align: center; vertical-align: middle; background: #f0f9ff; border: 1px solid #000;">
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <span class="field-row-avg" style="font-weight: 900; color: #0369a1; font-size: 11px;">—</span>
                        <span style="font-size: 8px; font-weight: 800; color: #0284c7; text-transform: uppercase;">auto</span>
                    </div>
                </td>

                <!-- Remarks -->
                <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                    <div class="flex items-center justify-between mb-1 print-hide">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tight">Remarks</span>
                        ${!isOwner ? `
                            <button type="button" onclick="stampRoleTag(this)" class="text-[9px] font-extrabold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-300 px-1.5 py-0.5 rounded border border-emerald-300 dark:border-emerald-800 transition-colors cursor-pointer" title="Stamp your role tag into remarks">
                                + Tag [${escapeHtml(window.currentReviewerRole || 'Reviewer')}]
                            </button>
                        ` : ''}
                    </div>
                    <textarea class="spms-textarea field-remarks" rows="3" placeholder="Enter remarks..." ${isOwner && (status === FolderStatus.PENDING_TARGET_APPROVAL || status === FolderStatus.SUBMITTED || status === FolderStatus.TARGET_APPROVED || status === FolderStatus.APPROVED) ? 'disabled title="Locked while submitted or approved"' : ''}>${escapeHtml(data.remarks)}</textarea>
                </td>

                <!-- Delete Action (Target Phase) -->
                <td style="padding: 2px; text-align: center; vertical-align: middle; border: 1px solid #000;" class="print-hide">
                    ${canEditTargets ? `
                    <button type="button" onclick="deleteTableRow(this)" title="Delete Row" class="btn-del-row">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>` : ''}
                </td>
            `;
        } else {
            tr.innerHTML = `
            <!-- Major Final Output (Only modifiable during Target Phase) -->
            <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                <textarea class="spms-textarea field-mfo" rows="3" placeholder="Enter academic function / major final output..." ${mfoDisabled ? 'disabled title="Locked (Only modifiable during Target Phase)"' : ''}>${escapeHtml(data.mfo)}</textarea>
            </td>

            <!-- Success Indicators (Only modifiable during Target Phase) -->
            <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                <textarea class="spms-textarea field-indicators" rows="3" placeholder="Enter success indicators (targets + measures)..." ${mfoDisabled ? 'disabled title="Locked (Only modifiable during Target Phase)"' : ''}>${escapeHtml(data.indicators)}</textarea>
            </td>

            <!-- Actual Accomplishments (Unlocked during evaluation phase) -->
            <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                <textarea class="spms-textarea field-accomplishments" rows="3" placeholder="Enter actual accomplishments..." ${evalDisabled ? 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' : ''}>${escapeHtml(data.accomplishments)}</textarea>
                
                <!-- MOV Evidence Toolbar (Only visible during Evaluation Phase) -->
                ${isEvaluationPhase ? `
                <div class="mov-toolbar flex items-center justify-between mt-1 pt-1 border-t border-slate-200 dark:border-slate-800 text-[10px] print-hide">
                    ${hasFiles ? `
                    <button type="button" onclick="openMovModal('${rowId}', this)" id="btn-mov-${rowId}" 
                            class="btn-mov-attachment inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md font-bold transition-all border shadow-xs cursor-pointer bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-500/40 hover:bg-emerald-500/25" 
                            title="View Means of Verification (MOV) evidence proof for this accomplishment">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                        <span class="mov-btn-text">MOV (${fileCount})</span>
                    </button>
                    ` : (canEditEvaluation ? `
                    <button type="button" onclick="openMovModal('${rowId}', this)" id="btn-mov-${rowId}" 
                            class="btn-mov-attachment inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md font-bold transition-all border shadow-xs cursor-pointer bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 border-slate-300 dark:border-slate-700" 
                            title="Upload Means of Verification (MOV) evidence proof for this accomplishment">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                        <span class="mov-btn-text">Attach MOV</span>
                    </button>
                    ` : `
                    <span class="text-[9px] text-slate-400 italic">No MOV</span>
                    `)}
                    <span class="text-[9px] text-slate-400 font-medium italic">Evidence</span>
                </div>
                ` : ''}
            </td>

            <!-- Rating Q, T, E Inputs (Unlocked during evaluation phase) -->
            <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                <input type="number" min="1" max="5" step="1" 
                    value="${data.q !== undefined && data.q !== null && data.q !== '' ? data.q : ''}" 
                    placeholder="—" 
                    ${evalDisabled ? 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' : 'title="Enter 1 to 5. Erase or press Esc to clear" oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)"'} 
                    class="spms-score-input field-q">
            </td>
            <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                <input type="number" min="1" max="5" step="1" 
                    value="${data.t !== undefined && data.t !== null && data.t !== '' ? data.t : ''}" 
                    placeholder="—" 
                    ${evalDisabled ? 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' : 'title="Enter 1 to 5. Erase or press Esc to clear" oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)"'} 
                    class="spms-score-input field-t">
            </td>
            <td style="padding: 3px; text-align: center; vertical-align: middle; border: 1px solid #000;">
                <input type="number" min="1" max="5" step="1" 
                    value="${data.e !== undefined && data.e !== null && data.e !== '' ? data.e : ''}" 
                    placeholder="—" 
                    ${evalDisabled ? 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' : 'title="Enter 1 to 5. Erase or press Esc to clear" oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)" ondblclick="clearScore(this)"'} 
                    class="spms-score-input field-e">
            </td>

            <!-- Row Average -->
            <td style="padding: 4px 2px; text-align: center; vertical-align: middle; background: #f0f9ff; border: 1px solid #000;">
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <span class="field-row-avg" style="font-weight: 900; color: #0369a1; font-size: 11px;">—</span>
                    <span style="font-size: 8px; font-weight: 800; color: #0284c7; text-transform: uppercase;">auto</span>
                </div>
            </td>

            <!-- Remarks -->
            <td style="padding: 4px; vertical-align: top; border: 1px solid #000;">
                <div class="flex items-center justify-between mb-1 print-hide">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tight">Remarks</span>
                    ${!isOwner ? `
                        <button type="button" onclick="stampRoleTag(this)" class="text-[9px] font-extrabold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-300 px-1.5 py-0.5 rounded border border-emerald-300 dark:border-emerald-800 transition-colors cursor-pointer" title="Stamp your role tag into remarks">
                            + Tag [${escapeHtml(window.currentReviewerRole || 'Reviewer')}]
                        </button>
                    ` : ''}
                </div>
                <textarea class="spms-textarea field-remarks" rows="3" placeholder="Enter remarks..." ${isOwner && (status === FolderStatus.PENDING_TARGET_APPROVAL || status === FolderStatus.SUBMITTED || status === FolderStatus.TARGET_APPROVED || status === FolderStatus.APPROVED) ? 'disabled title="Locked while submitted or approved"' : ''}>${escapeHtml(data.remarks)}</textarea>
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
        }

        tbody.appendChild(tr);

        // Track changes for autoSave
        tr.querySelectorAll('textarea, input').forEach(el => {
            const sync = () => {
                window.syncSpmsActiveTab();
                if (typeof AppState !== 'undefined' && typeof AppState.setDirty === 'function') AppState.setDirty(true);
            };
            el.addEventListener('input', sync);
            el.addEventListener('change', sync);
        });

        // Decimal formatting and currency sync for Budget inputs
        const budgetInput = tr.querySelector('.field-budget');
        const curInput = tr.querySelector('.field-budget-currency');
        if (budgetInput) {
            budgetInput.setAttribute('spellcheck', 'false');
            budgetInput.setAttribute('autocomplete', 'off');
            budgetInput.setAttribute('maxlength', '16');
            adjustBudgetFontSize(budgetInput);
        }
        if (curInput) {
            const syncW = (el) => {
                el.style.width = Math.max(18, (el.value.length + 1) * 6.5) + 'px';
            };
            syncW(curInput);
            curInput.addEventListener('input', function() {
                syncW(this);
                if (budgetInput) adjustBudgetFontSize(budgetInput);
            });
            curInput.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.value = '₱';
                    syncW(this);
                    window.syncSpmsActiveTab();
                    AppState.setDirty(true);
                }
                if (budgetInput) adjustBudgetFontSize(budgetInput);
            });
        }

        recalculateForm();
    }

    function deleteTableRow(button) {
        const tr = button.closest('tr');
        if (!tr) return;
        window._userExplicitlyDeletedRow = true;
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
        // Disallow non-numeric keys: 'e', 'E', '+', '-', '.', ','
        if (['e', 'E', '+', '-', '.', ','].includes(e.key)) {
            e.preventDefault();
            return;
        }
        // Disallow '0' directly since SPMS ratings are 1 to 5
        if (e.key === '0') {
            e.preventDefault();
            return;
        }
        // ArrowDown at 1 or blank clears back to blank
        if (e.key === 'ArrowDown' && (input.value === '1' || input.value === '0' || input.value === '')) {
            e.preventDefault();
            clearScore(input);
            return;
        }
        // ArrowUp when blank starts at 1
        if (e.key === 'ArrowUp' && (input.value === '' || input.value === null)) {
            e.preventDefault();
            input.value = '1';
            handleScoreInput(input);
            return;
        }
        // ArrowUp at 5 stays at 5
        if (e.key === 'ArrowUp' && input.value === '5') {
            e.preventDefault();
            return;
        }
    }

    function handleScoreInput(input) {
        let raw = input.value.trim();
        if (raw === '') {
            input.value = '';
            recalculateForm();
            window.syncSpmsActiveTab();
            AppState.setDirty(true);
            return;
        }
        // If user typed a new digit while one already existed (e.g. was 5, typed 3 -> "53"),
        // take the newly typed digit if it's 1-5 so replacing digits feels effortless
        if (raw.length > 1) {
            const lastChar = raw.slice(-1);
            if (['1', '2', '3', '4', '5'].includes(lastChar)) {
                raw = lastChar;
            }
        }
        const num = parseInt(raw, 10);
        // Only allow 1 to 5. If 0, > 5, or invalid, clear to blank
        if (isNaN(num) || num < 1 || num > 5) {
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
        if (isNaN(num) || num < 1 || num > 5) return null;
        return num;
    }

    function recalculateForm() {
        if (window.isCurrentDocIperf) {
            const rows = document.querySelectorAll('.table-row-core');
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

            let overallAvg = count > 0 ? (sumOfRowAvgs / count) : 0;
            const iperfAvgEl = document.getElementById('iperf-overall-average');
            if (iperfAvgEl) iperfAvgEl.innerText = count > 0 ? overallAvg.toFixed(3) : '0.000';

            let adjectival = getAdjectivalRating(overallAvg, count);
            const iperfBadgeEl = document.getElementById('iperf-adjectival-badge');
            if (iperfBadgeEl) {
                iperfBadgeEl.innerText = adjectival.text;
                iperfBadgeEl.style.background = adjectival.color;
            }

            // Sync with grand-score and adjectival-badge
            const gScoreEl = document.getElementById('grand-score');
            if (gScoreEl) gScoreEl.innerText = count > 0 ? overallAvg.toFixed(3) : '0.000';
            const badgeEl = document.getElementById('adjectival-badge');
            if (badgeEl) {
                badgeEl.innerText = adjectival.text;
                badgeEl.style.background = adjectival.color;
            }
            return;
        }

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

    function getAdjectivalRating(score, totalRated = 1) {
        let res;
        if (totalRated === 0) res = { text: 'PENDING EVALUATION', color: '#64748b' };
        else if (score >= 4.500) res = { text: 'OUTSTANDING', color: '#059669' };
        else if (score >= 3.500) res = { text: 'VERY SATISFACTORY', color: '#2563eb' };
        else if (score >= 2.500) res = { text: 'SATISFACTORY', color: '#d97706' };
        else if (score >= 1.500) res = { text: 'UNSATISFACTORY', color: '#ea580c' };
        else res = { text: 'POOR', color: '#dc2626' };
        res.toString = function() { return this.text; };
        return res;
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

            const rowData = {
                row_id: row.dataset.rowId || ('row_' + Date.now() + '_' + Math.random().toString(36).substr(2, 7)),
                mfo: row.querySelector('.field-mfo')?.value || '',
                indicators: row.querySelector('.field-indicators')?.value || '',
                accomplishments: row.querySelector('.field-accomplishments')?.value || '',
                q: q !== null ? q : '',
                t: t !== null ? t : '',
                e: e !== null ? e : '',
                remarks: row.querySelector('.field-remarks')?.value || ''
            };

            if (window.isCurrentDocDpcr || window.isCurrentDocOpcr) {
                const bInput = row.querySelector('.field-budget');
                rowData.budget = bInput?.dataset?.rawValue || bInput?.dataset?.fullValue || bInput?.value.trim() || '';
                rowData.budget_currency = row.querySelector('.field-budget-currency')?.value.trim() || '₱';
                rowData.accountable = row.querySelector('.field-accountable')?.value || '';
            }
            if (window.isCurrentDocDpcr) {
                rowData.std_5 = row.querySelector('.field-std-5')?.value || '';
                rowData.std_4 = row.querySelector('.field-std-4')?.value || '';
                rowData.std_3 = row.querySelector('.field-std-3')?.value || '';
                rowData.std_2 = row.querySelector('.field-std-2')?.value || '';
                rowData.std_1 = row.querySelector('.field-std-1')?.value || '';
            }

            result.push(rowData);
        });

        return result;
    }

    window.syncSpmsActiveTab = function() {
        const pf = document.getElementById('printable-form');
        if (!pf) return;
        const activeTab = tabs.find(t => t.id === activeTabId && t.id !== 'rubrics-tab' && t.id !== 'basis-tab') 
                       || tabs.find(t => t.id !== 'rubrics-tab' && t.id !== 'basis-tab') 
                       || tabs[0];
        if (!activeTab) return;

        const getVal = (id) => document.getElementById(id)?.value || '';

        const approversList = extractApprovers();

        const defaultTitle = window.isCurrentDocDpcr 
            ? 'DEPARTMENT PERFORMANCE COMMITMENT AND REVIEW (DPCR)' 
            : (window.isCurrentDocOpcr 
                ? 'OFFICE PERFORMANCE COMMITMENT AND REVIEW (OPCR)' 
                : (window.isCurrentDocIperf 
                    ? 'INDIVIDUAL PERFORMANCE EVALUATION RATING FORM FOR CONTRACT OF SERVICE AND JOB ORDER PERSONNEL' 
                    : 'INDIVIDUAL PERFORMANCE COMMITMENT AND REVIEW'));

        const existingCats = activeTab.formData?.categories || {};
        const isFormWorkspaceVisible = window.isSpmsFormActive && !document.getElementById('spms-form-workspace')?.classList.contains('hidden');

        let extractedCore = extractRowsData('core');
        let extractedStrat = window.isCurrentDocIperf ? [] : extractRowsData('strategic');
        let extractedSupp = window.isCurrentDocIperf ? [] : extractRowsData('support');

        // Safeguard against wiping out existing categories:
        // 1. If form workspace is hidden or inactive, or
        // 2. If extracted rows are empty while existing rows exist (and the user hasn't explicitly clicked delete)
        if (!isFormWorkspaceVisible || (extractedCore.length === 0 && (existingCats.core?.length || 0) > 0 && !window._userExplicitlyDeletedRow)) {
            extractedCore = (extractedCore.length > 0) ? extractedCore : (existingCats.core || []);
            extractedStrat = (extractedStrat.length > 0) ? extractedStrat : (existingCats.strategic || []);
            extractedSupp = (extractedSupp.length > 0) ? extractedSupp : (existingCats.support || []);
        } else if (!canEditTargets && (existingCats.core?.length || 0) > 0) {
            // When user cannot edit targets (e.g. reviewer/evaluator), preserve original target fields
            // while updating reviewer fields (q, t, e, accomplishments, remarks)
            const mergeWithOriginal = (extractedRows, origRows) => {
                return extractedRows.map((row, idx) => {
                    const orig = origRows.find(o => o.row_id === row.row_id) || origRows[idx] || {};
                    return {
                        ...row,
                        row_id: row.row_id || orig.row_id,
                        mfo: orig.mfo || row.mfo,
                        indicators: orig.indicators || row.indicators,
                        budget: (orig.budget !== undefined && orig.budget !== '') ? orig.budget : row.budget,
                        budget_currency: orig.budget_currency || row.budget_currency,
                        accountable: orig.accountable || row.accountable,
                        std_5: orig.std_5 !== undefined ? orig.std_5 : row.std_5,
                        std_4: orig.std_4 !== undefined ? orig.std_4 : row.std_4,
                        std_3: orig.std_3 !== undefined ? orig.std_3 : row.std_3,
                        std_2: orig.std_2 !== undefined ? orig.std_2 : row.std_2,
                        std_1: orig.std_1 !== undefined ? orig.std_1 : row.std_1
                    };
                });
            };
            extractedCore = mergeWithOriginal(extractedCore, existingCats.core || []);
            extractedStrat = mergeWithOriginal(extractedStrat, existingCats.strategic || []);
            extractedSupp = mergeWithOriginal(extractedSupp, existingCats.support || []);
        }

        const formData = {
            revisionHistory: activeTab.formData?.revisionHistory || [],
            title: document.getElementById('spms-doc-title')?.innerText || document.getElementById('doc-title')?.value || defaultTitle,
            doc_type: window.isCurrentDocDpcr ? 'dpcr' : (window.isCurrentDocOpcr ? 'opcr' : (window.isCurrentDocIperf ? 'iperf' : (activeTab.formData?.doc_type || 'ipcr'))),
            currency: document.querySelector('.header-budget-currency')?.value.trim() || '₱',
            budget_currency: document.querySelector('.header-budget-currency')?.value.trim() || '₱',
            classification: window.isCurrentDocIperf ? getVal('ratee-classification') : undefined,
            weights: CATEGORY_WEIGHTS,
            ratee: {
                name: getVal('ratee-name'),
                position: getVal('ratee-position'),
                dept: getVal('ratee-dept'),
                period: getVal('ratee-period')
            },
            approver: approversList[0] || {
                name: getVal('approver-name'),
                position: getVal('approver-pos'),
                date: getVal('approver-date')
            },
            approvers: approversList,
            rateeSign: {
                name: getVal('ratee-sign-name'),
                date: getVal('ratee-sign-date')
            },
            categories: {
                core: extractedCore,
                strategic: extractedStrat,
                support: extractedSupp
            },
            pmtRemarks: getVal('pmt-remarks'),
            signatories: window.isCurrentDocIperf ? {
                targetsPreparedName: getVal('sig-targets-prepared-name'),
                targetsPreparedDate: getVal('sig-targets-prepared-date'),
                targetsApprovedName: getVal('sig-targets-approved-name'),
                targetsApprovedDate: getVal('sig-targets-approved-date'),
                evalRatedName: getVal('sig-eval-rated-name'),
                evalRatedDate: getVal('sig-eval-rated-date'),
                evalConformeName: getVal('sig-eval-conforme-name'),
                evalConformeDate: getVal('sig-eval-conforme-date')
            } : {
                ratee: getVal('sig-ratee-name'),
                rateeDate: getVal('sig-ratee-date'),
                dean: getVal('sig-dean-name'),
                deanDate: getVal('sig-dean-date'),
                vp: getVal('sig-vp-name'),
                vpDate: getVal('sig-vp-date')
            },
            rubrics: (window.digitalRubricsData || activeTab.formData?.rubrics || {})
        };

        activeTab.formData = formData;
        if (isFormWorkspaceVisible && (extractedCore.length > 0 || (existingCats.core?.length || 0) === 0)) {
            activeTab.content = document.getElementById('printable-form')?.innerHTML || activeTab.content || '';
        }
    };

    // -------------------------------------------------------------
    // DIGITAL RUBRICS MATRIX & ATTACHMENTS ENGINE
    // -------------------------------------------------------------
    function getFormDeliverables() {
        const deliverables = [];
        const seenRows = new Set();

        const collectFromTbody = (tbodyId, catName) => {
            const tbody = document.getElementById(tbodyId);
            if (!tbody) return;
            tbody.querySelectorAll(`tr.table-row-${catName}, tr[data-row-id], tr[id^="row-"]`).forEach((tr, idx) => {
                const rowId = tr.dataset.rowId || tr.id || `row-${catName}-${idx}`;
                if (seenRows.has(rowId)) return;
                seenRows.add(rowId);

                const mfoEl = tr.querySelector('.field-mfo') || tr.querySelector('.spms-input-mfo') || tr.querySelector('.spms-input-paps');
                const indEl = tr.querySelector('.field-indicators') || tr.querySelector('.spms-input-indicator') || tr.querySelector('.spms-input-success-indicator');
                const ppaEl = tr.querySelector('.spms-input-office-ppa');
                const expEl = tr.querySelector('.spms-input-expected-outputs');
                
                const ppa = ppaEl ? ppaEl.value.trim() : '';
                const exp = expEl ? expEl.value.trim() : '';
                const mfo = mfoEl ? mfoEl.value.trim() : '';
                const ind = indEl ? indEl.value.trim() : '';
                
                const parts = [mfo, ppa, exp, ind].filter(Boolean);
                let text = parts.join(' — ');
                
                if (!text) {
                    const firstInput = tr.querySelector('textarea, input[type="text"]');
                    if (firstInput && firstInput.value.trim()) text = firstInput.value.trim();
                }
                
                deliverables.push({
                    rowId: rowId,
                    cat: catName,
                    title: text || `Deliverable #${deliverables.length + 1}`
                });
            });
        };

        collectFromTbody('tbody-core', 'core');
        if (!window.isCurrentDocIperf) {
            collectFromTbody('tbody-strategic', 'strategic');
            collectFromTbody('tbody-support', 'support');
        }
        
        // Fallback to activeTab.formData if table DOM is not yet populated
        if (deliverables.length === 0) {
            const activeTab = tabs[0];
            const cats = activeTab?.formData?.categories;
            if (cats) {
                ['core', 'strategic', 'support'].forEach(cat => {
                    if (Array.isArray(cats[cat])) {
                        cats[cat].forEach((r, idx) => {
                            const ppa = r.office_ppa || '';
                            const exp = r.expected_outputs || r.indicators || r.success_indicators || '';
                            const mfo = r.mfo_title || r.paps || '';
                            const text = [mfo, ppa, exp].filter(Boolean).join(' — ');
                            deliverables.push({
                                rowId: r.row_id || `row-${cat}-${idx}`,
                                cat: cat,
                                title: text || `Deliverable #${deliverables.length + 1}`
                            });
                        });
                    }
                });
            }
        }
        return deliverables;
    }

    function autoResizeTextarea(el) {
        if (!el) return;
        el.style.height = 'auto';
        el.style.height = Math.max(el.scrollHeight, 46) + 'px';
    }

    // Single Full Sheet Table View (Cards view removed)
    window.setRubricsViewMode = function() {};
    window.toggleRubricCard = function() {};
    window.toggleAllRubricCards = function() {};

    function filterRubricDeliverable(filterKey) {
        document.querySelectorAll('.rubric-jump-pill').forEach(p => {
            const isSelected = (p.getAttribute('data-target-filter') === filterKey);
            if (isSelected) {
                p.className = 'rubric-jump-pill px-3 py-1.5 rounded-xl font-extrabold bg-emerald-500/15 text-emerald-800 dark:text-emerald-300 border border-emerald-500/30 transition-all cursor-pointer shrink-0 flex items-center gap-1.5 shadow-xs';
            } else {
                p.className = 'rubric-jump-pill px-3 py-1.5 rounded-xl font-bold bg-surface-border/30 hover:bg-surface-border/50 text-text-muted hover:text-text transition-all cursor-pointer shrink-0 flex items-center gap-1.5';
            }
        });

        if (filterKey === 'all') {
            document.querySelectorAll('#digital-rubrics-tbody tr').forEach(tr => tr.classList.remove('hidden'));
        } else if (['core', 'strategic', 'support'].includes(filterKey)) {
            document.querySelectorAll('#digital-rubrics-tbody tr').forEach(tr => {
                const cat = tr.getAttribute('data-category');
                const isHeader = tr.id === `sheet-category-row-${filterKey}`;
                if (cat === filterKey || isHeader) {
                    tr.classList.remove('hidden');
                } else {
                    tr.classList.add('hidden');
                }
            });
            const headerRow = document.getElementById(`sheet-category-row-${filterKey}`);
            if (headerRow) {
                headerRow.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        } else {
            // Specific rowId / deliverable
            document.querySelectorAll('#digital-rubrics-tbody tr').forEach(tr => {
                const delId = tr.getAttribute('data-deliverable-id');
                if (!delId || delId === filterKey) {
                    tr.classList.remove('hidden');
                } else {
                    tr.classList.add('hidden');
                }
            });
            const delRow = document.querySelector(`#digital-rubrics-tbody tr[data-deliverable-id="${filterKey}"]`);
            if (delRow) {
                delRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }

    function handleRubricFabClick() {
        if (window.isCurrentDocIperf) {
            addDeliverableFromRubrics('core');
            return;
        }
        const menu = document.getElementById('rubric-fab-menu');
        if (menu) menu.classList.toggle('hidden');
    }

    function toggleRubricFabMenu(show = false) {
        const menu = document.getElementById('rubric-fab-menu');
        if (!menu) return;
        if (show) menu.classList.remove('hidden');
        else menu.classList.add('hidden');
    }

    document.addEventListener('click', (e) => {
        const fabContainer = document.getElementById('rubric-fab-container');
        if (fabContainer && !fabContainer.contains(e.target)) {
            toggleRubricFabMenu(false);
        }
    });

    function addDeliverableFromRubrics(category = 'core') {
        toggleRubricFabMenu(false);

        const newRowId = 'row_' + Date.now() + '_' + Math.random().toString(36).substr(2, 7);
        const rowData = {
            row_id: newRowId,
            mfo: "",
            indicators: "",
            accomplishments: "",
            q: "", t: "", e: "",
            remarks: ""
        };

        if (typeof addTableRow === 'function') {
            addTableRow(category, rowData);
        }

        if (tabs[0]) {
            if (!tabs[0].formData) tabs[0].formData = {};
            if (!tabs[0].formData.categories) tabs[0].formData.categories = { core: [], strategic: [], support: [] };
            if (!tabs[0].formData.categories[category]) tabs[0].formData.categories[category] = [];
            tabs[0].formData.categories[category].push(rowData);
        }

        AppState.setDirty(true);
        renderDigitalRubricsMatrix();

        setTimeout(() => {
            const rowEl = document.querySelector(`#digital-rubrics-tbody tr[data-deliverable-id="${newRowId}"]`);
            if (rowEl) {
                rowEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                rowEl.classList.add('bg-emerald-500/10');
                setTimeout(() => rowEl.classList.remove('bg-emerald-500/10'), 2500);

                const titleInput = rowEl.querySelector('.rubric-title-input');
                if (titleInput) {
                    titleInput.focus();
                    titleInput.select();
                }
            }
        }, 80);
    }

    function syncDeliverableTitleToForm(rowId, newTitle) {
        const rowEl = document.querySelector(`tr[data-row-id="${rowId}"]`) || document.getElementById(rowId);
        if (rowEl) {
            const targetInput = rowEl.querySelector('.field-mfo') || rowEl.querySelector('.spms-input-office-ppa') || rowEl.querySelector('.field-indicators') || rowEl.querySelector('textarea, input[type="text"]');
            if (targetInput) {
                targetInput.value = newTitle;
            }
        }
        if (tabs[0]?.formData?.categories) {
            Object.values(tabs[0].formData.categories).forEach(catRows => {
                if (Array.isArray(catRows)) {
                    const found = catRows.find(r => (r.row_id === rowId || r.id === rowId));
                    if (found) {
                        if ('mfo' in found) found.mfo = newTitle;
                        else if ('office_ppa' in found) found.office_ppa = newTitle;
                        else if ('mfo_title' in found) found.mfo_title = newTitle;
                    }
                }
            });
        }
        AppState.setDirty(true);
    }

    async function deleteDeliverableFromRubrics(rowId) {
        const ok = await window.appConfirm("Delete this deliverable from both the Target Form and Rubrics Matrix?", {
            title: 'Delete Deliverable',
            confirmText: 'Delete',
            isDanger: true
        });
        if (!ok) return;

        const rowEl = document.querySelector(`tr[data-row-id="${rowId}"]`) || document.getElementById(rowId);
        if (rowEl) rowEl.remove();

        if (window.digitalRubricsData && window.digitalRubricsData[rowId]) {
            delete window.digitalRubricsData[rowId];
        }
        if (tabs[0]?.formData?.rubrics?.[rowId]) {
            delete tabs[0].formData.rubrics[rowId];
        }

        if (tabs[0]?.formData?.categories) {
            ['core', 'strategic', 'support'].forEach(cat => {
                if (Array.isArray(tabs[0].formData.categories[cat])) {
                    tabs[0].formData.categories[cat] = tabs[0].formData.categories[cat].filter(r => r.row_id !== rowId && r.id !== rowId);
                }
            });
        }

        AppState.setDirty(true);
        if (typeof recalculateForm === 'function') recalculateForm();
        renderDigitalRubricsMatrix();
    }

    async function clearRubricRow(rowId) {
        const ok = await window.appConfirm("Clear all scoring criteria for this deliverable?", {
            title: 'Clear Rubrics',
            confirmText: 'Clear',
            isDanger: true
        });
        if (!ok) return;

        if (!window.digitalRubricsData) window.digitalRubricsData = tabs[0]?.formData?.rubrics || {};
        window.digitalRubricsData[rowId] = {
            5: { q: '', t: '', e: '' },
            4: { q: '', t: '', e: '' },
            3: { q: '', t: '', e: '' },
            2: { q: '', t: '', e: '' },
            1: { q: '', t: '', e: '' }
        };

        if (tabs[0]) {
            if (!tabs[0].formData) tabs[0].formData = {};
            tabs[0].formData.rubrics = window.digitalRubricsData;
        }

        AppState.setDirty(true);
        renderDigitalRubricsMatrix();
    }

    function updateSingleDeliverableBadge(rowId) {
        const badge = document.getElementById(`rubric-count-badge-${rowId}`);
        if (!badge) return;
        const rowData = (window.digitalRubricsData && window.digitalRubricsData[rowId]) || {};
        let filled = 0;
        [5, 4, 3, 2, 1].forEach(sc => {
            const d = rowData[sc];
            if (d && (d.q || d.t || d.e)) {
                if (d.q) filled++;
                if (d.t) filled++;
                if (d.e) filled++;
            }
        });
        badge.innerText = `${filled}/15 Defined`;
        if (filled === 15) {
            badge.className = 'px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/15 text-emerald-800 dark:text-emerald-300 border border-emerald-500/30';
        } else if (filled > 0) {
            badge.className = 'px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500/15 text-amber-800 dark:text-amber-300 border border-amber-500/30';
        } else {
            badge.className = 'px-2 py-0.5 rounded-full text-[10px] font-medium text-text-muted bg-surface-border/40';
        }
    }
    window.updateSingleCardCriteriaBadge = updateSingleDeliverableBadge;

    function renderDigitalRubricsMatrix() {
        const tbody = document.getElementById('digital-rubrics-tbody');
        const quickPills = document.getElementById('rubric-quick-jump-pills');
        const overallProg = document.getElementById('rubric-overall-progress');
        if (!tbody) return;
        
        const deliverables = getFormDeliverables();
        if (deliverables.length === 0) {
            const emptyHtml = `
                <div class="py-14 text-center rounded-2xl bg-surface border border-surface-border p-8 space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 mx-auto flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="font-black text-base text-text">No deliverables found on the Commitment Form yet</h3>
                    <p class="text-xs text-text-muted max-w-md mx-auto leading-relaxed">
                        Add your committed Major Final Outputs and Expected Outputs under the "Target Form" tab first. They will automatically link here for standard criteria setting.
                    </p>
                    <button type="button" onclick="switchEditorTab(tabs[0].id)" class="mt-2 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-accent text-accent-text hover:bg-accent-hover shadow-sm transition-all cursor-pointer">
                        <span>Open Target Form</span>
                    </button>
                </div>
            `;
            tbody.innerHTML = `<tr><td colspan="5" class="p-6 text-center text-text-muted">${emptyHtml}</td></tr>`;
            if (quickPills) quickPills.innerHTML = '';
            if (overallProg) overallProg.innerText = '';
            return;
        }

        const savedRubrics = window.digitalRubricsData || tabs[0]?.formData?.rubrics || {};

        const isIperf = (typeof window.isCurrentDocIperf !== 'undefined') ? window.isCurrentDocIperf : <?= $isDocIperf ? 'true' : 'false' ?>;
        const isDpcr = (typeof window.isCurrentDocDpcr !== 'undefined') ? window.isCurrentDocDpcr : <?= $isDocDpcr ? 'true' : 'false' ?>;
        const isOpcr = (typeof window.isCurrentDocOpcr !== 'undefined') ? window.isCurrentDocOpcr : <?= $isDocOpcr ? 'true' : 'false' ?>;

        const catWeights = {
            core: isIperf ? '100%' : (isDpcr ? '70%' : (isOpcr ? '70%' : '70%')),
            strategic: isDpcr ? '30%' : (isOpcr ? '25%' : '20%'),
            support: isDpcr ? '10%' : (isOpcr ? '15%' : '10%')
        };

        const catConfigs = {
            core: {
                title: isIperf ? 'Committed Outputs' : 'Core Functions',
                weight: catWeights.core,
                badge: 'bg-emerald-500/15 text-emerald-800 dark:text-emerald-300 border-emerald-500/30',
                dot: 'bg-emerald-500',
                addBtn: 'border-emerald-500/30 hover:border-emerald-500/60 bg-emerald-500/5 hover:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                emptyHint: 'Add your primary operational and mandated deliverables.'
            },
            strategic: {
                title: 'Strategic Functions',
                weight: catWeights.strategic,
                badge: 'bg-sky-500/15 text-sky-800 dark:text-sky-300 border-sky-500/30',
                dot: 'bg-sky-500',
                addBtn: 'border-sky-500/30 hover:border-sky-500/60 bg-sky-500/5 hover:bg-sky-500/10 text-sky-700 dark:text-sky-300',
                emptyHint: 'Add strategic priority projects and institutional development targets.'
            },
            support: {
                title: 'Support Functions',
                weight: catWeights.support,
                badge: 'bg-amber-500/15 text-amber-800 dark:text-amber-300 border-amber-500/30',
                dot: 'bg-amber-500',
                addBtn: 'border-amber-500/30 hover:border-amber-500/60 bg-amber-500/5 hover:bg-amber-500/10 text-amber-700 dark:text-amber-300',
                emptyHint: 'Add administrative, cross-functional, or support committee outputs.'
            }
        };

        const activeCatKeys = isIperf ? ['core'] : ['core', 'strategic', 'support'];

        const scoreMeta = {
            5: { label: '5 • Outstanding', hint: 'Exceptional / Top Quality', pill: 'bg-emerald-500/15 text-emerald-800 dark:text-emerald-300 border-emerald-500/30' },
            4: { label: '4 • Very Satisfactory', hint: 'Exceeds Target', pill: 'bg-sky-500/15 text-sky-800 dark:text-sky-300 border-sky-500/30' },
            3: { label: '3 • Satisfactory', hint: '100% Target Standard', pill: 'bg-amber-500/15 text-amber-800 dark:text-amber-300 border-amber-500/30' },
            2: { label: '2 • Unsatisfactory', hint: 'Below Target Standard', pill: 'bg-orange-500/15 text-orange-800 dark:text-orange-300 border-orange-500/30' },
            1: { label: '1 • Poor', hint: 'Fails Standard / Deficient', pill: 'bg-rose-500/15 text-rose-800 dark:text-rose-300 border-rose-500/30' }
        };

        const readonlyAttr = canEditTargets ? '' : 'readonly disabled';
        const inputBg = canEditTargets 
            ? 'bg-input border border-surface-border text-text placeholder:text-text-muted/50 focus:bg-surface focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 shadow-xs' 
            : 'bg-transparent border border-transparent text-text cursor-default select-text';

        let sheetHtml = '';
        let totalConfiguredCriteria = 0;
        const maxPossibleCriteria = deliverables.length * 15;

        // Quick jump category pills
        if (quickPills) {
            let pillsHtml = `
                <button type="button" onclick="filterRubricDeliverable('all')" data-target-filter="all"
                        class="rubric-jump-pill px-3 py-1.5 rounded-xl font-extrabold bg-emerald-500/15 text-emerald-800 dark:text-emerald-300 border border-emerald-500/30 transition-all cursor-pointer shrink-0 shadow-xs">
                    All Outputs (${deliverables.length})
                </button>
            `;

            activeCatKeys.forEach(catKey => {
                const cfg = catConfigs[catKey];
                const catDelivs = deliverables.filter(d => (d.cat || 'core') === catKey);
                pillsHtml += `
                    <button type="button" onclick="filterRubricDeliverable('${catKey}')" data-target-filter="${catKey}"
                            class="rubric-jump-pill px-3 py-1.5 rounded-xl font-bold bg-surface-border/30 hover:bg-surface-border/50 text-text-muted hover:text-text transition-all cursor-pointer shrink-0 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full ${cfg.dot}"></span>
                        <span>${cfg.title} (${catDelivs.length})</span>
                    </button>
                `;
            });

            quickPills.innerHTML = pillsHtml;
        }

        // Build Table Rows per Category
        activeCatKeys.forEach(catKey => {
            const cfg = catConfigs[catKey];
            const catDelivs = deliverables.filter(d => (d.cat || 'core') === catKey);

            let catFilledCriteria = 0;
            const catMaxCriteria = catDelivs.length * 15;

            catDelivs.forEach(del => {
                const rowId = del.rowId;
                const rubricData = savedRubrics[rowId] || {};
                [5, 4, 3, 2, 1].forEach(sc => {
                    const s = rubricData[sc];
                    if (s?.q) catFilledCriteria++;
                    if (s?.t) catFilledCriteria++;
                    if (s?.e) catFilledCriteria++;
                });
            });
            totalConfiguredCriteria += catFilledCriteria;

            // Category Header Row
            sheetHtml += `
                <tr class="border-t-2 border-b-2 border-surface-border bg-surface-border/25 font-bold" id="sheet-category-row-${catKey}">
                    <td colspan="5" class="p-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full ${cfg.dot}"></span>
                                <span class="text-xs font-black uppercase tracking-wider text-text">${cfg.title} (${cfg.weight})</span>
                                <span class="text-[10px] font-medium text-text-muted">(${catDelivs.length} ${catDelivs.length === 1 ? 'output' : 'outputs'})</span>
                            </div>
                            <span class="text-[10px] font-bold text-text-muted">${catFilledCriteria}/${catMaxCriteria} Criteria Defined</span>
                        </div>
                    </td>
                </tr>
            `;

            if (catDelivs.length === 0) {
                sheetHtml += `
                    <tr class="border-b border-surface-border" data-category="${catKey}">
                        <td colspan="5" class="p-4 text-center text-xs text-text-muted italic bg-surface/20">
                            No deliverables under ${cfg.title} yet.
                        </td>
                    </tr>
                `;
            } else {
                catDelivs.forEach((del, dIdx) => {
                    const rowId = del.rowId;
                    const rubricData = savedRubrics[rowId] || {};
                    const catClass = cfg.badge;

                    let rowFilledCount = 0;
                    [5, 4, 3, 2, 1].forEach(sc => {
                        const s = rubricData[sc];
                        if (s?.q) rowFilledCount++;
                        if (s?.t) rowFilledCount++;
                        if (s?.e) rowFilledCount++;
                    });

                    const badgeCompClass = rowFilledCount === 15 
                        ? 'bg-emerald-500/15 text-emerald-800 dark:text-emerald-300 border-emerald-500/30 font-extrabold'
                        : (rowFilledCount > 0 
                            ? 'bg-amber-500/15 text-amber-800 dark:text-amber-300 border-amber-500/30 font-extrabold'
                            : 'bg-surface-border/40 text-text-muted font-medium');

                    [5, 4, 3, 2, 1].forEach((score, sIdx) => {
                        const scData = rubricData[score] || { q: '', t: '', e: '' };
                        const meta = scoreMeta[score];
                        const isFirst = (sIdx === 0);

                        sheetHtml += `<tr class="border-b border-surface-border hover:bg-surface-border/5 transition-colors" data-category="${catKey}" data-deliverable-id="${escapeHtml(rowId)}">`;

                        if (isFirst) {
                            sheetHtml += `
                                <td rowspan="5" class="p-3.5 align-top bg-surface-border/10 border-r border-surface-border text-text">
                                    <div class="space-y-2.5">
                                        <div class="flex items-center justify-between gap-1 flex-wrap">
                                            <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider border ${catClass}">
                                                Output #${dIdx + 1} &bull; ${escapeHtml(cfg.title.toUpperCase())}
                                            </span>
                                            <span id="rubric-count-badge-${escapeHtml(rowId)}" class="px-2 py-0.5 rounded-full text-[9px] border ${badgeCompClass}">
                                                ${rowFilledCount}/15 Defined
                                            </span>
                                        </div>

                                        ${canEditTargets ? `
                                        <div>
                                            <label class="block text-[9px] font-black uppercase tracking-wider text-text-muted mb-1">Deliverable Title / Description</label>
                                            <textarea rows="2" 
                                                      placeholder="Enter deliverable description..."
                                                      oninput="syncDeliverableTitleToForm('${escapeHtml(rowId)}', this.value); autoResizeTextarea(this);"
                                                      class="rubric-title-input w-full bg-surface-border/20 hover:bg-surface-border/30 focus:bg-surface border border-surface-border/60 focus:border-amber-500 rounded-xl p-2 outline-none text-text font-bold text-xs leading-snug transition-all resize-none">${escapeHtml(del.title)}</textarea>
                                        </div>
                                        <div class="flex items-center gap-1.5 pt-1">
                                            <button type="button" onclick="clearRubricRow('${escapeHtml(rowId)}')"
                                                    class="px-2 py-1 rounded-lg text-[10px] font-bold text-text-muted hover:text-amber-600 hover:bg-amber-500/10 transition-all cursor-pointer"
                                                    title="Clear all scoring criteria for this deliverable">
                                                Clear Criteria
                                            </button>
                                            <button type="button" onclick="deleteDeliverableFromRubrics('${escapeHtml(rowId)}')"
                                                    class="px-2 py-1 rounded-lg text-[10px] font-bold text-rose-500 hover:bg-rose-500/10 transition-all cursor-pointer"
                                                    title="Delete this deliverable from both rubric and target form">
                                                Delete Output
                                            </button>
                                        </div>
                                        ` : `
                                        <div class="font-bold text-xs text-text leading-snug break-words">
                                            ${escapeHtml(del.title)}
                                        </div>
                                        `}
                                    </div>
                                </td>
                            `;
                        }

                        sheetHtml += `
                            <td class="p-2.5 text-center align-middle border-r border-surface-border">
                                <div class="flex flex-col items-center gap-1">
                                    <span class="inline-flex px-2 py-0.5 rounded-lg text-xs font-black border ${meta.pill}">
                                        ${score}
                                    </span>
                                    <span class="text-[9px] text-text-muted font-medium leading-tight">
                                        ${meta.hint}
                                    </span>
                                </div>
                            </td>
                            <td class="p-2 align-top border-r border-surface-border">
                                <textarea data-row-id="${escapeHtml(rowId)}" data-score="${score}" data-dim="q" ${readonlyAttr}
                                          placeholder="${canEditTargets ? 'e.g. No revisions / zero defect...' : '—'}"
                                          rows="2"
                                          oninput="autoResizeTextarea(this); onDigitalRubricChange(this)"
                                          class="w-full text-xs p-2.5 rounded-xl transition-all resize-none leading-relaxed font-sans ${inputBg}">${escapeHtml(scData.q || '')}</textarea>
                            </td>
                            <td class="p-2 align-top border-r border-surface-border">
                                <textarea data-row-id="${escapeHtml(rowId)}" data-score="${score}" data-dim="t" ${readonlyAttr}
                                          placeholder="${canEditTargets ? 'e.g. within 1–2 working days ahead...' : '—'}"
                                          rows="2"
                                          oninput="autoResizeTextarea(this); onDigitalRubricChange(this)"
                                          class="w-full text-xs p-2.5 rounded-xl transition-all resize-none leading-relaxed font-sans ${inputBg}">${escapeHtml(scData.t || '')}</textarea>
                            </td>
                            <td class="p-2 align-top">
                                <textarea data-row-id="${escapeHtml(rowId)}" data-score="${score}" data-dim="e" ${readonlyAttr}
                                          placeholder="${canEditTargets ? 'e.g. 100% of target accomplished...' : '—'}"
                                          rows="2"
                                          oninput="autoResizeTextarea(this); onDigitalRubricChange(this)"
                                          class="w-full text-xs p-2.5 rounded-xl transition-all resize-none leading-relaxed font-sans ${inputBg}">${escapeHtml(scData.e || '')}</textarea>
                            </td>
                        </tr>`;
                    });
                });
            }

            // Category Add Button
            if (canEditTargets) {
                sheetHtml += `
                    <tr class="border-b border-surface-border" data-category="${catKey}">
                        <td colspan="5" class="p-2.5 text-center bg-surface-border/10">
                            <button type="button" onclick="addDeliverableFromRubrics('${catKey}')"
                                    class="w-full py-2 px-3 rounded-xl border border-dashed ${cfg.addBtn} text-xs font-bold transition-all cursor-pointer">
                                ＋ Add Deliverable to ${cfg.title}
                            </button>
                        </td>
                    </tr>
                `;
            }
        });

        tbody.innerHTML = sheetHtml;

        if (overallProg) {
            const pct = Math.round((totalConfiguredCriteria / maxPossibleCriteria) * 100);
            overallProg.innerText = `${totalConfiguredCriteria} / ${maxPossibleCriteria} Criteria Configured (${pct}%)`;
        }

        setTimeout(() => {
            document.querySelectorAll('#spms-rubrics-workspace textarea').forEach(ta => autoResizeTextarea(ta));
        }, 50);

        updateRubricsTabBadge();
    }

    function onDigitalRubricChange(el) {
        const rowId = el.getAttribute('data-row-id');
        const score = el.getAttribute('data-score');
        const dim = el.getAttribute('data-dim');
        const val = el.value;

        if (!window.digitalRubricsData) {
            window.digitalRubricsData = tabs[0]?.formData?.rubrics || {};
        }
        if (!window.digitalRubricsData[rowId]) {
            window.digitalRubricsData[rowId] = {};
        }
        if (!window.digitalRubricsData[rowId][score]) {
            window.digitalRubricsData[rowId][score] = { q: '', t: '', e: '' };
        }
        window.digitalRubricsData[rowId][score][dim] = val;

        // Keep sibling textarea in sync if both card view and sheet view exist in DOM
        document.querySelectorAll(`textarea[data-row-id="${rowId}"][data-score="${score}"][data-dim="${dim}"]`).forEach(sibling => {
            if (sibling !== el && sibling.value !== val) {
                sibling.value = val;
                autoResizeTextarea(sibling);
            }
        });

        if (tabs[0]) {
            if (!tabs[0].formData) tabs[0].formData = {};
            tabs[0].formData.rubrics = window.digitalRubricsData;
        }

        updateSingleCardCriteriaBadge(rowId);

        AppState.setDirty(true);
        updateRubricsTabBadge();
    }

    window.syncDigitalRubricsData = function() {
        const container = document.getElementById('spms-rubrics-workspace');
        if (!container) return;

        if (!window.digitalRubricsData) {
            window.digitalRubricsData = tabs[0]?.formData?.rubrics || {};
        }

        container.querySelectorAll('textarea[data-row-id]').forEach(ta => {
            const rowId = ta.getAttribute('data-row-id');
            const score = ta.getAttribute('data-score');
            const dim = ta.getAttribute('data-dim');
            const val = ta.value;

            if (!window.digitalRubricsData[rowId]) {
                window.digitalRubricsData[rowId] = {};
            }
            if (!window.digitalRubricsData[rowId][score]) {
                window.digitalRubricsData[rowId][score] = { q: '', t: '', e: '' };
            }
            window.digitalRubricsData[rowId][score][dim] = val;
        });

        if (tabs[0]) {
            if (!tabs[0].formData) tabs[0].formData = {};
            tabs[0].formData.rubrics = window.digitalRubricsData;
        }

        updateRubricsTabBadge();
    };

    function renderRubricAttachmentCard() {
        const fileCard = document.getElementById('rubric-attached-file-card');
        const statusBadge = document.getElementById('rubric-file-status-badge');
        const nameEl = document.getElementById('rubric-file-name');
        const metaEl = document.getElementById('rubric-file-meta');
        const dlLink = document.getElementById('rubric-file-download-link');
        const viewBtn = document.getElementById('rubric-file-view-btn');

        const rubrics = window.rubricAttachments || [];
        const active = rubrics.length > 0 ? rubrics[rubrics.length - 1] : null;

        if (active) {
            fileCard?.classList.remove('hidden');
            if (nameEl) nameEl.textContent = active.file_name;
            if (metaEl) metaEl.textContent = `${active.formatted_size || ''} • Uploaded on ${active.created_at || ''}`;
            if (dlLink) dlLink.href = '<?= site_url('attachments/download/') ?>' + active.id;
            if (viewBtn) {
                viewBtn.onclick = () => {
                    window.open('<?= site_url('attachments/view/') ?>' + active.id, '_blank');
                };
            }
            if (statusBadge) {
                statusBadge.innerHTML = `<span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">File Attached</span>`;
            }
        } else {
            fileCard?.classList.add('hidden');
            if (statusBadge) {
                statusBadge.innerHTML = `<span class="px-2 py-0.5 rounded-full text-[10px] font-medium text-text-muted bg-surface-border/30">No File Attached</span>`;
            }
        }
        updateRubricsTabBadge();
    }

    async function handleRubricFileSelect(input) {
        if (input.files && input.files[0]) {
            await uploadRubricAttachment(input.files[0]);
            input.value = '';
        }
    }

    function handleRubricDrop(e) {
        e.preventDefault();
        const dt = document.getElementById('rubric-drop-target');
        dt?.classList.remove('border-emerald-500', 'bg-emerald-500/5');
        if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
            uploadRubricAttachment(e.dataTransfer.files[0]);
        }
    }

    async function uploadRubricAttachment(file) {
        const progressEl = document.getElementById('rubric-upload-progress');
        try {
            progressEl?.classList.remove('hidden');
            const formData = new FormData();
            formData.append('document_id', '<?= $doc['id'] ?>');
            formData.append('row_id', 'rubric');
            formData.append('file', file);

            const res = await axios.post('<?= site_url('attachments/upload') ?>', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            if (res.data?.status === 'success') {
                window.rubricAttachments = [res.data.attachment];
                renderRubricAttachmentCard();
                if (typeof showToast === 'function') {
                    showToast('Rubric attached successfully!', 'success');
                }
            } else {
                const errMsg = res.data?.message || 'Upload failed.';
                if (window.appAlert) {
                    await window.appAlert(errMsg, { title: 'Upload Notice', variant: 'warning' });
                } else {
                    alert(errMsg);
                }
            }
        } catch (err) {
            console.error('Rubric upload error:', err);
            const errMsg = err.response?.data?.message || 'Failed to upload rubric document.';
            if (window.appAlert) {
                await window.appAlert(errMsg, { title: 'Upload Failed', variant: 'danger' });
            } else {
                alert(errMsg);
            }
        } finally {
            progressEl?.classList.add('hidden');
        }
    }

    async function deleteRubricAttachment() {
        const rubrics = window.rubricAttachments || [];
        const active = rubrics.length > 0 ? rubrics[rubrics.length - 1] : null;
        if (!active) return;

        const ok = await window.appConfirm('Are you sure you want to remove this attached rubric file?', {
            title: 'Remove Rubric File',
            confirmText: 'Remove',
            variant: 'danger'
        });
        if (!ok) return;

        try {
            const res = await axios.delete('<?= site_url('attachments/') ?>' + active.id);
            if (res.data?.status === 'success') {
                window.rubricAttachments = [];
                renderRubricAttachmentCard();
                if (typeof showToast === 'function') {
                    showToast('Rubric attachment removed.', 'info');
                }
            }
        } catch (err) {
            console.error('Delete error:', err);
            if (window.appAlert) {
                await window.appAlert('Failed to remove attachment.', { title: 'Delete Failed', variant: 'danger' });
            } else {
                alert('Failed to remove attachment.');
            }
        }
    }

    function updateRubricsTabBadge() {
        const badge = document.getElementById('tab-rubrics-badge');
        if (!badge) return;

        const hasFile = (window.rubricAttachments && window.rubricAttachments.length > 0);
        const rubricsData = window.digitalRubricsData || tabs[0]?.formData?.rubrics || {};
        let criteriaCount = 0;
        Object.values(rubricsData).forEach(scores => {
            if (typeof scores === 'object') {
                Object.values(scores).forEach(s => {
                    if (s && (s.q || s.t || s.e)) criteriaCount++;
                });
            }
        });

        if (hasFile && criteriaCount > 0) {
            badge.innerHTML = 'Matrix &amp; File Configured';
            badge.className = 'px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-emerald-500/15 text-emerald-800 dark:text-emerald-300 border border-emerald-500/30';
        } else if (hasFile) {
            badge.innerHTML = 'File Attached';
            badge.className = 'px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-emerald-500/15 text-emerald-800 dark:text-emerald-300 border border-emerald-500/30';
        } else if (criteriaCount > 0) {
            badge.innerHTML = `${criteriaCount} Criteria Set`;
            badge.className = 'px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-emerald-500/15 text-emerald-800 dark:text-emerald-300 border border-emerald-500/30';
        } else {
            badge.innerHTML = 'Needs Rubrics';
            badge.className = 'px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-amber-500/15 text-amber-800 dark:text-amber-300 border border-amber-500/30';
        }
    }

    // Attach event delegation listeners on printable-form for reliable autosave across all dynamic rows
    document.addEventListener('DOMContentLoaded', () => {
        const pf = document.getElementById('printable-form');
        if (pf) {
            pf.addEventListener('input', (e) => {
                if (e.target && e.target.matches('input, textarea, select')) {
                    window.syncSpmsActiveTab();
                    AppState.setDirty(true);
                }
            });
            pf.addEventListener('change', (e) => {
                if (e.target && e.target.matches('input, textarea, select')) {
                    window.syncSpmsActiveTab();
                    AppState.setDirty(true);
                }
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

    function exportToExcel() {
        const exportUrl = '<?= site_url("document/" . $doc["id"] . "/export-excel") ?>';
        if (window.isSpmsFormActive && AppState.dirty) {
            saveWith({
                after: () => {
                    window.location.href = exportUrl;
                }
            });
        } else {
            window.location.href = exportUrl;
        }
    }

    function exportToPdf() {
        if (activeTabId === 'basis-tab') {
            window.print();
            return;
        }
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

    // Auto-expand textareas before printing
    window.addEventListener('beforeprint', () => {
        document.querySelectorAll('#printable-form textarea, #basis-printable-sheet textarea').forEach(ta => {
            ta.style.height = 'auto';
            ta.style.height = Math.max(ta.scrollHeight, 38) + 'px';
        });
        document.querySelectorAll('.field-budget').forEach(input => {
            if (input.dataset && input.dataset.fullValue) {
                input.value = input.dataset.fullValue;
            }
        });
    });

    window.addEventListener('afterprint', () => {
        document.querySelectorAll('.field-budget').forEach(adjustBudgetFontSize);
    });

    function renderBasisStaticSheet() {
        const sheet = document.getElementById('basis-printable-sheet');
        if (!sheet) return;

        const data = basisFormData || {};
        const fallbackEl = document.getElementById('basis-html-fallback');

        // If no structured categories, but has HTML content:
        if ((!data.categories || Object.keys(data.categories).length === 0) && basisDocContent && basisDocContent.trim() !== '') {
            if (fallbackEl) {
                fallbackEl.innerHTML = basisDocContent;
                fallbackEl.classList.remove('hidden');
            }
            return;
        }

        if (fallbackEl) fallbackEl.classList.add('hidden');

        // Header info
        const titleText = data.title || '<?= esc($basisDoc['title'] ?? '') ?>';
        const sheetTitleEl = document.getElementById('basis-sheet-title');
        if (sheetTitleEl) sheetTitleEl.innerText = titleText;

        const escapeHtml = (str) => {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        };

        const setVal = (id, val, fallback = '') => {
            const el = document.getElementById(id);
            if (!el) return;
            const finalVal = (val !== undefined && val !== null && String(val).trim() !== '' && String(val).trim() !== '—') ? String(val).trim() : fallback;
            if ('value' in el) {
                el.value = finalVal;
                el.removeAttribute('placeholder');
            } else {
                el.innerText = finalVal;
            }
        };

        // In the cascaded basis, if original owner kept fields empty, do not fallback-fill; keep them empty!
        setVal('basis-val-ratee-name', data.ratee?.name || '');
        setVal('basis-val-ratee-pos', data.ratee?.position || '');
        setVal('basis-val-ratee-dept', data.ratee?.dept || '');
        setVal('basis-val-ratee-period', data.ratee?.period || '');

        const approversContainer = document.getElementById('basis-approvers-container');
        if (data.approvers && data.approvers.length > 0 && approversContainer) {
            approversContainer.innerHTML = data.approvers.map((a, idx) => `
                <div class="approver-item" style="${idx > 0 ? 'border-top: 1px dashed #cbd5e1; margin-top: 6px; padding-top: 6px;' : ''} position: relative;">
                    <table style="width: 100%; border-collapse: collapse; border: none; font-size: 11px;">
                        <tr>
                            <td style="width: 65px; border: none; padding: 3px 0; font-weight: bold; color: #000000;">Name:</td>
                            <td style="border: none; padding: 3px 0;">
                                <input type="text" readonly value="${a.name ? escapeHtml(a.name) : ''}" style="font-weight: bold; color: #000000; border: none; border-bottom: 1px solid #94a3b8; outline: none; font-size: 11px; width: 60%; background: transparent; font-family: inherit;">
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 65px; border: none; padding: 3px 0; font-weight: bold; color: #000000;">Position:</td>
                            <td style="border: none; padding: 3px 0;">
                                <input type="text" readonly value="${a.position ? escapeHtml(a.position) : ''}" style="color: #000000; border: none; border-bottom: 1px solid #94a3b8; outline: none; font-size: 11px; width: 80%; background: transparent; font-family: inherit;">
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 65px; border: none; padding: 3px 0; font-weight: bold; color: #000000;">Date:</td>
                            <td style="border: none; padding: 3px 0;">
                                <input type="text" readonly value="${a.date ? escapeHtml(a.date) : ''}" style="color: #000000; border: none; border-bottom: 1px solid #94a3b8; outline: none; font-size: 11px; width: 130px; background: transparent; font-family: inherit;">
                            </td>
                        </tr>
                    </table>
                </div>
            `).join('');
        } else {
            setVal('basis-val-approver-name', data.approver?.name || '');
            setVal('basis-val-approver-pos', data.approver?.position || '');
            setVal('basis-val-approver-date', data.approver?.date || '');
        }

        setVal('basis-val-ratee-sign', data.rateeSign?.name || '');
        const posEl = document.getElementById('basis-val-ratee-sign-pos');
        if (posEl) posEl.innerText = 'Name of Employee';
        setVal('basis-val-ratee-sign-date', data.rateeSign?.date || '');

        const isOpcr = titleText.toUpperCase().includes('OPCR') || titleText.toUpperCase().includes('OFFICE') || (data.doc_type && data.doc_type.toUpperCase() === 'OPCR');
        const isDpcr = titleText.toUpperCase().includes('DPCR') || titleText.toUpperCase().includes('DIVISION') || titleText.toUpperCase().includes('DEPARTMENT') || (data.doc_type && data.doc_type.toUpperCase() === 'DPCR');

        const colCountBadge = document.getElementById('basis-col-count-badge');
        if (colCountBadge) colCountBadge.innerText = (isOpcr || isDpcr) ? '10 Columns' : '8 Columns';

        // Dynamically update thead and colgroup of basis-printable-sheet
        const colgroupEl = document.getElementById('basis-table-colgroup');
        const theadEl = document.getElementById('basis-table-thead');
        if (colgroupEl && theadEl) {
            if (isOpcr) {
                colgroupEl.innerHTML = `
                    <col style="width: 15%;">
                    <col style="width: 17%;">
                    <col style="width: 11%;">
                    <col style="width: 13%;">
                    <col style="width: 18%;">
                    <col style="width: 3.5%;">
                    <col style="width: 3.5%;">
                    <col style="width: 3.5%;">
                    <col style="width: 4.5%;">
                    <col style="width: 11%;">
                `;
                theadEl.innerHTML = `
                    <tr style="background-color: #fff2cc; text-align: center; font-weight: bold; border-bottom: 1px solid #000; font-size: 10px;">
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">PROJECT / PROGRAM / ACTIVITIES</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">SUCCESS INDICATORS<br><span style="font-weight: normal; font-size: 9px;">(TARGETS + MEASURES)</span></th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ALLOTTED BUDGET</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">DIVISIONS ACCOUNTABLE</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ACTUAL ACCOMPLISHMENTS</th>
                        <th colspan="4" style="border: 1px solid #000; padding: 4px;">RATING</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">REMARKS</th>
                    </tr>
                    <tr style="background-color: #fff2cc; text-align: center; font-weight: bold; border-bottom: 2px solid #000; font-size: 10px;">
                        <th style="border: 1px solid #000; padding: 4px 2px;">Q</th>
                        <th style="border: 1px solid #000; padding: 4px 2px;">T</th>
                        <th style="border: 1px solid #000; padding: 4px 2px;">E</th>
                        <th style="border: 1px solid #000; padding: 4px 2px;">Ave.</th>
                    </tr>
                `;
            } else if (isDpcr) {
                colgroupEl.innerHTML = `
                    <col style="width: 15%;">
                    <col style="width: 17%;">
                    <col style="width: 11%;">
                    <col style="width: 13%;">
                    <col style="width: 18%;">
                    <col style="width: 3.5%;">
                    <col style="width: 3.5%;">
                    <col style="width: 3.5%;">
                    <col style="width: 4.5%;">
                    <col style="width: 11%;">
                `;
                theadEl.innerHTML = `
                    <tr style="background-color: #cfe2f3; text-align: center; font-weight: bold; border-bottom: 1px solid #000; font-size: 10px;">
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">PROGRAMS, PROJECTS, ACTIVITIES</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">SUCCESS INDICATORS<br><span style="font-weight: normal; font-size: 9px;">(TARGETS + MEASURES)</span></th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ALLOTTED BUDGET</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">INDIVIDUALS / OFFICES ACCOUNTABLE</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ACTUAL ACCOMPLISHMENTS</th>
                        <th colspan="4" style="border: 1px solid #000; padding: 4px;">RATING</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">REMARKS</th>
                    </tr>
                    <tr style="background-color: #cfe2f3; text-align: center; font-weight: bold; border-bottom: 2px solid #000; font-size: 10px;">
                        <th style="border: 1px solid #000; padding: 4px 2px;">Q</th>
                        <th style="border: 1px solid #000; padding: 4px 2px;">T</th>
                        <th style="border: 1px solid #000; padding: 4px 2px;">E</th>
                        <th style="border: 1px solid #000; padding: 4px 2px;">Ave.</th>
                    </tr>
                `;
            } else {
                colgroupEl.innerHTML = `
                    <col style="width: 25%;">
                    <col style="width: 25%;">
                    <col style="width: 24%;">
                    <col style="width: 4%;">
                    <col style="width: 4%;">
                    <col style="width: 4%;">
                    <col style="width: 5%;">
                    <col style="width: 9%;">
                `;
                theadEl.innerHTML = `
                    <tr style="background: #f1f5f9; text-align: center; font-weight: bold; border-bottom: 1px solid #000;">
                        <th rowspan="2" style="padding: 8px; border: 1px solid #000;">ACADEMIC FUNCTION /<br>MAJOR FINAL OUTPUT</th>
                        <th rowspan="2" style="padding: 8px; border: 1px solid #000;">SUCCESS INDICATORS<br><span style="font-size: 9px; font-weight: normal;">(Targets + Measures)</span></th>
                        <th rowspan="2" style="padding: 8px; border: 1px solid #000;">ACTUAL ACCOMPLISHMENTS</th>
                        <th colspan="4" style="padding: 4px; border: 1px solid #000;">RATING</th>
                        <th rowspan="2" style="padding: 8px; border: 1px solid #000;">REMARKS</th>
                    </tr>
                    <tr style="background: #f1f5f9; text-align: center; font-weight: bold; border-bottom: 2px solid #000; font-size: 10px;">
                        <th style="padding: 4px; border: 1px solid #000;">Q</th>
                        <th style="padding: 4px; border: 1px solid #000;">T</th>
                        <th style="padding: 4px; border: 1px solid #000;">E</th>
                        <th style="padding: 4px; border: 1px solid #000;">Ave.</th>
                    </tr>
                `;
            }
        }

        const catLabels = {
            core: isOpcr ? "1. Core Office Mandate (60%)" : (isDpcr ? "1. Core Division Functions (60%)" : "1. Core Functions (70%)"),
            strategic: isOpcr ? "2. Strategic Functions (25%)" : (isDpcr ? "2. Strategic Functions (25%)" : "2. Strategic Functions (20%)"),
            support: isOpcr ? "3. Support Functions (15%)" : (isDpcr ? "3. Support Functions (15%)" : "3. Support Functions (10%)")
        };

        const colCount = (isOpcr || isDpcr) ? 10 : 8;
        const colTitleSpan = (isOpcr || isDpcr) ? 6 : 5;
        const colBadgeSpan = (isOpcr || isDpcr) ? 4 : 3;



        ['core', 'strategic', 'support'].forEach(cat => {
            const tbody = document.getElementById(`basis-tbody-${cat}`);
            if (!tbody) return;
            tbody.innerHTML = '';

            // Header row
            const trHeader = document.createElement('tr');
            trHeader.style.cssText = 'background: #f8fafc; border-top: 2px solid #000; border-bottom: 1px solid #000;';
            trHeader.innerHTML = `
                <td colspan="${colTitleSpan}" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #0f172a;">
                    ${catLabels[cat]}
                </td>
                <td colspan="${colBadgeSpan}" style="padding: 6px 12px; text-align: right;">
                    <span style="display: inline-block; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 800; font-size: 10px; padding: 2px 6px; border-radius: 4px;">
                        Cascaded Deliverables
                    </span>
                </td>
            `;
            tbody.appendChild(trHeader);

            const rows = data.categories?.[cat] || [];
            if (rows.length === 0 || rows.every(r => !r.mfo && !r.indicators)) {
                const trEmpty = document.createElement('tr');
                trEmpty.style.cssText = 'border-bottom: 1px solid #000;';
                trEmpty.innerHTML = `
                    <td colspan="${colCount}" style="padding: 12px; text-align: center; color: #94a3b8; font-style: italic; font-size: 11px;">
                        No deliverables entered for this category.
                    </td>
                `;
                tbody.appendChild(trEmpty);
            } else {
                rows.forEach(r => {
                    const tr = document.createElement('tr');
                    tr.style.cssText = 'border-bottom: 1px solid #000;';
                    if (isOpcr || isDpcr) {
                        const budgetRaw = (r.budget !== undefined && r.budget !== null && String(r.budget).trim() !== '')
                            ? parseFloat(String(r.budget).replace(/,/g, ''))
                            : null;
                        const budgetFormatted = (budgetRaw !== null && !isNaN(budgetRaw))
                            ? `${escapeHtml(r.budget_currency || '₱')} ${formatBudgetFull(budgetRaw)}`
                            : '—';
                        tr.innerHTML = `
                            <td style="padding: 8px; vertical-align: top; border: 1px solid #000; font-size: 11px; white-space: pre-wrap; color: #0f172a; line-height: 1.5;">
                                ${escapeHtml(r.mfo)}
                            </td>
                            <td style="padding: 8px; vertical-align: top; border: 1px solid #000; font-size: 11px; white-space: pre-wrap; color: #0f172a; font-weight: 600; line-height: 1.5;">
                                ${escapeHtml(r.indicators)}
                            </td>
                            <td style="padding: 8px; vertical-align: top; border: 1px solid #000; font-size: 11px; text-align: right; color: #0f172a; font-weight: 600; line-height: 1.5;">
                                ${budgetFormatted}
                            </td>
                            <td style="padding: 8px; vertical-align: top; border: 1px solid #000; font-size: 11px; white-space: pre-wrap; color: #334155; line-height: 1.5;">
                                ${escapeHtml(r.accountable || '')}
                            </td>
                            <td style="padding: 8px; vertical-align: top; border: 1px solid #000; font-size: 11px; white-space: pre-wrap; color: #334155; line-height: 1.5;">
                                ${escapeHtml(r.accomplishments)}
                            </td>
                            <td style="padding: 4px; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: bold; font-size: 11px;">
                                ${r.q || '—'}
                            </td>
                            <td style="padding: 4px; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: bold; font-size: 11px;">
                                ${r.t || '—'}
                            </td>
                            <td style="padding: 4px; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: bold; font-size: 11px;">
                                ${r.e || '—'}
                            </td>
                            <td style="padding: 4px; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: 900; color: #0369a1; background: #f0f9ff; font-size: 11px;">
                                ${r.ave || '—'}
                            </td>
                            <td style="padding: 8px; vertical-align: top; border: 1px solid #000; font-size: 10px; color: #475569;">
                                ${escapeHtml(r.remarks)}
                            </td>
                        `;
                    } else {
                        tr.innerHTML = `
                            <td style="padding: 8px; vertical-align: top; border: 1px solid #000; font-size: 11px; white-space: pre-wrap; color: #0f172a; line-height: 1.5;">
                                ${escapeHtml(r.mfo)}
                            </td>
                            <td style="padding: 8px; vertical-align: top; border: 1px solid #000; font-size: 11px; white-space: pre-wrap; color: #0f172a; font-weight: 600; line-height: 1.5;">
                                ${escapeHtml(r.indicators)}
                            </td>
                            <td style="padding: 8px; vertical-align: top; border: 1px solid #000; font-size: 11px; white-space: pre-wrap; color: #334155; line-height: 1.5;">
                                ${escapeHtml(r.accomplishments)}
                            </td>
                            <td style="padding: 4px; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: bold; font-size: 11px;">
                                ${r.q || '—'}
                            </td>
                            <td style="padding: 4px; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: bold; font-size: 11px;">
                                ${r.t || '—'}
                            </td>
                            <td style="padding: 4px; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: bold; font-size: 11px;">
                                ${r.e || '—'}
                            </td>
                            <td style="padding: 4px; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: 900; color: #0369a1; background: #f0f9ff; font-size: 11px;">
                                ${r.ave || '—'}
                            </td>
                            <td style="padding: 8px; vertical-align: top; border: 1px solid #000; font-size: 10px; color: #475569;">
                                ${escapeHtml(r.remarks)}
                            </td>
                        `;
                    }
                    tbody.appendChild(tr);
                });
            }
        });
    }

    // Launch active view on page load
    document.addEventListener('DOMContentLoaded', () => {
        initActiveTabView();
        updateRubricsTabBadge();
        if (new URLSearchParams(window.location.search).get('print') === '1') {
            setTimeout(() => exportToPdf(), 700);
        }
    });
</script>

<?= view('document/_mov_modal', [
    'isOwner'           => $isOwner ?? false,
    'isCycleArchived'   => $isCycleArchived ?? false,
    'attachmentsByRow'  => $attachmentsByRow ?? [],
    'doc'               => $doc ?? [],
    'isEvaluationPhase' => $isEvaluationPhase ?? false,
    'canEditEvaluation' => $canEditEvaluation ?? false,
]) ?>

<?= $this->endSection() ?>