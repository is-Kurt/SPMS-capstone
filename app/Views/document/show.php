<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php 
    use App\Enums\FolderStatus;

    $status = $doc['folder_status']; 
    $isOwner = ($doc['owner_id'] == session()->get('user_id'));
    
    $rawTabsForType = !empty($doc['tabs']) ? (is_string($doc['tabs']) ? json_decode($doc['tabs'], true) : $doc['tabs']) : [];
    $tabFormDocType = strtolower(trim($rawTabsForType[0]['formData']['doc_type'] ?? ''));
    $tabFormTitleUpper = strtoupper(trim($rawTabsForType[0]['formData']['title'] ?? ''));

    $ownerDocType = strtolower($doc['doc_type'] ?? ($tabFormDocType ?: 'ipcr'));
    $docTitleUpper = strtoupper(trim($doc['title'] ?? '')) . ' ' . $tabFormTitleUpper;
    $ownerPos = strtolower($ownerInfo['position'] ?? '');
    $isOwnerDean = str_contains($ownerPos, 'dean');
    $isOwnerChairOrHead = str_contains($ownerPos, 'chair') || str_contains($ownerPos, 'head');

    $isTrueOpcr = $tabFormDocType === 'opcr' || str_contains($docTitleUpper, 'OPCR') || str_contains($docTitleUpper, 'OFFICE') || (strtoupper($doc['doc_type'] ?? '') === 'OPCR');
    $isTrueDpcr = $tabFormDocType === 'dpcr' || str_contains($docTitleUpper, 'DPCR') || str_contains($docTitleUpper, 'DIVISION') || str_contains($docTitleUpper, 'DEPARTMENT') || (strtoupper($doc['doc_type'] ?? '') === 'DPCR');
    $isDocDpcr = ($ownerDocType === 'dpcr') || $isTrueDpcr || (empty($doc['doc_type']) && empty($tabFormDocType) && ($isOwnerDean || $isOwnerChairOrHead));
    $isDocOpcr = (!$isDocDpcr) && ($isTrueOpcr || str_contains($ownerPos, 'vice president') || str_contains($ownerPos, 'president'));
    $isDocIperf = (!$isDocDpcr && !$isDocOpcr) && ($ownerDocType === 'iperf' || $tabFormDocType === 'iperf' || stripos($docTitleUpper, 'iperf') !== false || (empty($doc['doc_type']) && empty($tabFormDocType) && isset($ownerInfo['is_teaching']) && (int)$ownerInfo['is_teaching'] === 0));
    $activeDocType = $isDocDpcr ? 'dpcr' : ($isDocOpcr ? 'opcr' : ($isDocIperf ? 'iperf' : 'ipcr'));
    $targetEndCol = $activeDocType . '_target_end';
    $now = date('Y-m-d H:i:s');
    $tEnd = $doc[$targetEndCol] ?? null;
    $isPastTargetDate = (!empty($tEnd) && $now > $tEnd);

    $cTopStatus = FolderStatus::canonical($status ?? '');
    $isEvaluationPhase = $cTopStatus->isEvaluationPhase() || $isPastTargetDate;
    $isTargetPhase = !$isEvaluationPhase;

    $canEditTargets = ($isOwner && in_array($cTopStatus, [
        FolderStatus::DRAFT_TARGET,
    ], true) && !$isPastTargetDate && !$isGuide);

    $canEditApprover = ($canEditTargets || (!$isOwner && in_array($cTopStatus, [
        FolderStatus::TARGET_SUBMITTED,
        FolderStatus::TARGET_APPROVED,
    ], true) && !$isGuide && empty($isCycleArchived)));

    $sysRole = session()->get('role');
    $isTwg = in_array($sysRole, ['TWG', 'PMT']);
    $isAdmin = ($sysRole === 'Admin');
    $canEditTwg = ($isTwg || $isAdmin) && empty($isCycleArchived) && !$isGuide;

    $canEditEvaluation = ($isEvaluationPhase && (
        ($isOwner && in_array($cTopStatus, [FolderStatus::DRAFT_EVALUATION, FolderStatus::TARGET_APPROVED], true)) ||
        (!$isOwner && (!empty($isCascadedByMe) || isset($routingStatus)) && in_array($cTopStatus, [FolderStatus::EVAL_SUBMITTED], true))
    ) && !$isGuide);

    $editableStatuses = [
        FolderStatus::DRAFT_TARGET->value,
        FolderStatus::DRAFT_EVALUATION->value,
    ];
    $isEditable = ($canEditTargets || $canEditEvaluation || $canEditTwg);

    if (!empty($isCycleArchived) || !empty($isHistoricalVersion)) {
        $canEditTargets = false;
        $canEditEvaluation = false;
        $canEditApprover = false;
        $canEditTwg = false;
        $isEditable = false;
    }

    // Share all variables with modular partials
    $viewData = get_defined_vars();
?>

<?= view('document/partials/assets/_styles', $viewData) ?>

<div class="h-full flex flex-col bg-zinc-200 dark:bg-bg">
    <?= view('components/govph_masthead') ?>
    
    <?= view('document/partials/ui/_navbar', $viewData) ?>

    <!-- Main Workspace Split Container: Form (Left ~3/4) & Rubric (Right ~1/4) -->
    <div class="flex-1 min-h-0 w-full relative flex flex-row overflow-hidden" id="workspace-split-container">
        <div class="flex-1 min-h-0 h-full relative bg-zinc-200 dark:bg-zinc-950 overflow-auto transition-all duration-300 ease-out custom-scrollbar" id="editor-container">
            <?= view('document/partials/workspaces/_basis_workspace', $viewData) ?>

            <!-- SPMS Structured Form Builder Container -->
            <div id="spms-form-workspace" class="hidden w-fit min-w-full min-h-full p-2 sm:p-6 lg:p-8 flex flex-col items-center print:p-0 print:bg-white print:overflow-visible">
                <div class="w-full max-w-[1560px] flex flex-col items-center gap-4 print:w-full print:block">
                    <article id="printable-form" class="spms-sheet-container block space-y-5">
                        
                        <?= view('document/partials/layout/_letterhead', $viewData) ?>
                        <?= view('document/partials/layout/_title', $viewData) ?>

                        <!-- Metadata Matrix Table -->
                        <?php if ($isDocIperf): ?>
                            <?= view('document/partials/meta/_meta_iperf', $viewData) ?>
                        <?php else: ?>
                            <?= view('document/partials/meta/_meta_standard', $viewData) ?>
                        <?php endif; ?>

                        <!-- Main Table of Deliverables & Ratings -->
                        <div class="spms-table-responsive-wrapper">
                            <table class="spms-table">
                                <!-- Document Form Headers by Doc Type -->
                                <?php if ($isDocDpcr): ?>
                                    <?= view('document/templates/dpcr', $viewData) ?>
                                <?php elseif ($isDocOpcr): ?>
                                    <?= view('document/templates/opcr', $viewData) ?>
                                <?php elseif ($isDocIperf): ?>
                                    <?= view('document/templates/iperf', $viewData) ?>
                                <?php else: ?>
                                    <?= view('document/templates/ipcr', $viewData) ?>
                                <?php endif; ?>

                                <!-- Document Form Body Deliverables -->
                                <?php if ($isDocIperf): ?>
                                    <?= view('document/partials/forms/_body_iperf', $viewData) ?>
                                <?php else: ?>
                                    <?= view('document/partials/forms/_body_standard', $viewData) ?>
                                <?php endif; ?>
                            </table>
                        </div>

                        <!-- Grand Summary Matrix Table (Standard) -->
                        <?php if (!$isDocIperf): ?>
                            <?= view('document/partials/forms/_summary_table', $viewData) ?>
                        <?php endif; ?>

                        <?= view('document/partials/layout/_remarks_box', $viewData) ?>

                        <!-- Signatories -->
                        <?php if ($isDocIperf): ?>
                            <?= view('document/partials/signatures/_signatures_iperf', $viewData) ?>
                        <?php else: ?>
                            <?= view('document/partials/signatures/_signatures_standard', $viewData) ?>
                        <?php endif; ?>

                    </article>
                </div>
            </div>

            <!-- SPMS Digital Rubrics Matrix Workspace -->
            <?= view('document/partials/workspaces/_rubrics_workspace', $viewData) ?>

        </div>
    </div>
</div>

<?= view('document/partials/assets/_scripts', $viewData) ?>
<?= view('document/partials/ui/_comments_drawer', $viewData) ?>
<?= view('document/partials/ui/_unsaved_modal', $viewData) ?>

<?= view('document/_mov_modal', [
    'isOwner'           => $isOwner ?? false,
    'isCycleArchived'   => $isCycleArchived ?? false,
    'attachmentsByRow'  => $attachmentsByRow ?? [],
    'doc'               => $doc ?? [],
    'isEvaluationPhase' => $isEvaluationPhase ?? false,
    'canEditEvaluation' => $canEditEvaluation ?? false,
]) ?>

<?= $this->endSection() ?>