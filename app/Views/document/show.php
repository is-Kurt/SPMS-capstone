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
    // When neither the document nor its form says which form it is, use the owner's form type (or the one the
    // doc-type rules give them), never their job title (2026-11-07).
    $noStoredType = empty($doc['doc_type']) && empty($tabFormDocType);
    $ownerFormType = '';
    if ($noStoredType && !empty($doc['owner_id'])) {
        $ownerRow = (new \App\Models\UserModel())->find($doc['owner_id']);
        $ownerFormType = strtoupper((string) ($ownerRow['doc_type'] ?? '')) ?: \App\Libraries\OrgStructure::instance()->defaultDocTypeFor((int) $doc['owner_id']);
    }

    $isTrueOpcr = $tabFormDocType === 'opcr' || str_contains($docTitleUpper, 'OPCR') || str_contains($docTitleUpper, 'OFFICE') || (strtoupper($doc['doc_type'] ?? '') === 'OPCR');
    $isTrueDpcr = $tabFormDocType === 'dpcr' || str_contains($docTitleUpper, 'DPCR') || str_contains($docTitleUpper, 'DIVISION') || str_contains($docTitleUpper, 'DEPARTMENT') || (strtoupper($doc['doc_type'] ?? '') === 'DPCR');
    $isDocDpcr = ($ownerDocType === 'dpcr') || $isTrueDpcr || ($noStoredType && $ownerFormType === 'DPCR');
    $isDocOpcr = (!$isDocDpcr) && ($isTrueOpcr || ($noStoredType && $ownerFormType === 'OPCR'));
    $isDocIperf = (!$isDocDpcr && !$isDocOpcr) && ($ownerDocType === 'iperf' || $tabFormDocType === 'iperf' || stripos($docTitleUpper, 'iperf') !== false || ($noStoredType && $ownerFormType === 'IPERF'));
    $activeDocType = $isDocDpcr ? 'dpcr' : ($isDocOpcr ? 'opcr' : ($isDocIperf ? 'iperf' : 'ipcr'));
    $targetEndCol = $activeDocType . '_target_end';
    $evalEndCol = $activeDocType . '_eval_end';
    $now = date('Y-m-d H:i:s');
    $tEnd = $doc[$targetEndCol] ?? $doc['target_end'] ?? null;
    $eEnd = $doc[$evalEndCol] ?? $doc['eval_end'] ?? null;
    $isPastTargetDate = (!empty($tEnd) && $now > $tEnd);
    $isPastEvalDate = (!empty($eEnd) && $now > $eEnd);

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

    $isSupervisorRole = in_array($sysRole, ['Supervisor', 'Admin']);
    $isSupervisorUser = (!$isOwner && !$isTwg && ($isSupervisorRole || !empty($isCascadedByMe) || isset($routingStatus)));

    $isTargetSubmitted = in_array($cTopStatus, [
        FolderStatus::TARGET_SUBMITTED,
        FolderStatus::TARGET_UNAPPROVED,
        FolderStatus::TARGET_APPROVED,
    ], true);

    $isEvalSubmitted = in_array($cTopStatus, [
        FolderStatus::EVAL_SUBMITTED,
        FolderStatus::EVAL_UNRATED,
        FolderStatus::EVAL_APPROVED,
    ], true);

    // Active review resolution for Supervisors vs TWG/PMT
    $isCurrentSupervisorApproved = (!empty($currentEvaluatorRoute) && in_array($currentEvaluatorRoute['status'], ['target_approved', 'approved', 'eval_approved']))
        || ($isTargetPhase && $cTopStatus === FolderStatus::TARGET_APPROVED && ($isAdmin || (int)($doc['target_approved_by'] ?? 0) === (int)session()->get('user_id')))
        || (!$isTargetPhase && $cTopStatus === FolderStatus::EVAL_APPROVED && ($isAdmin || (int)($doc['eval_approved_by'] ?? 0) === (int)session()->get('user_id')));

    $isTwgApproved = ($isTwg && (
        ($isTargetPhase && $cTopStatus === FolderStatus::TARGET_APPROVED && in_array($doc['target_approver_role'] ?? '', ['TWG', 'PMT'], true)) ||
        (!$isTargetPhase && $cTopStatus === FolderStatus::EVAL_APPROVED)
    ));

    // TWG review turn: only when ALL supervisors have approved and document is in submitted review state, and not yet finalized by TWG
    $canTwgReview = ($isTwg && !empty($allSupervisorsApproved) && !$isTwgApproved && (
        ($isTargetPhase && in_array($cTopStatus, [FolderStatus::TARGET_SUBMITTED, FolderStatus::TARGET_UNAPPROVED], true)) ||
        (!$isTargetPhase && in_array($cTopStatus, [FolderStatus::EVAL_SUBMITTED, FolderStatus::EVAL_UNRATED], true))
    ));

    // Supervisor review turn: only when document is submitted to them and not yet approved by this supervisor
    $canSupervisorReview = ($isSupervisorUser && !$isCurrentSupervisorApproved && (
        ($isTargetPhase && in_array($cTopStatus, [FolderStatus::TARGET_SUBMITTED, FolderStatus::TARGET_UNAPPROVED], true)) ||
        (!$isTargetPhase && in_array($cTopStatus, [FolderStatus::EVAL_SUBMITTED, FolderStatus::EVAL_UNRATED], true))
    ));

    $canEditEvaluation = ($isEvaluationPhase && (
        ($isOwner && in_array($cTopStatus, [FolderStatus::DRAFT_EVALUATION, FolderStatus::TARGET_APPROVED], true)) ||
        (!$isOwner && ($canSupervisorReview || $canTwgReview || ($isAdmin && in_array($cTopStatus, [FolderStatus::EVAL_SUBMITTED], true))))
    ) && !$isGuide);

    // Option B: Allow progressive drafting of accomplishments and attaching MOVs once targets are approved
    $canEditAccomplishments = empty($isCycleArchived) && empty($isHistoricalVersion) && !$isGuide && (
        $canEditEvaluation || 
        ($isOwner && $cTopStatus === FolderStatus::TARGET_APPROVED)
    );

    // Reviewer Remarks Permissions: Open for active reviewer (Supervisor or TWG during their turn, or Admin during submitted states)
    $canReviewerEditRemarks = ($canSupervisorReview || $canTwgReview || ($isAdmin && ($isTargetSubmitted || $isEvalSubmitted)));
    $canEditRemarks = $canReviewerEditRemarks;

    // Ratee signatory permissions: editable ONLY to that user (the owner/ratee) while their document is active/editable
    $canEditRateeSignatory = ($isOwner && ($canEditTargets || $canEditEvaluation || !empty($canEditAccomplishments)) && empty($isCycleArchived) && empty($isHistoricalVersion) && !$isGuide);

    // Supervisor signatory date permissions: editable when the supervisor is actively reviewing/rating a submitted document
    $canEditSupervisorDate = ($canSupervisorReview || ($isAdmin && ($isTargetSubmitted || $isEvalSubmitted))) && empty($isCycleArchived) && empty($isHistoricalVersion) && !$isGuide;

    // TWG comment editing: only during active TWG verification turn or Admin submitted reviews
    $canEditTwg = ($canTwgReview || ($isAdmin && ($isTargetSubmitted || $isEvalSubmitted))) && empty($isCycleArchived) && !$isGuide;

    $editableStatuses = [
        FolderStatus::DRAFT_TARGET->value,
        FolderStatus::DRAFT_EVALUATION->value,
    ];
    $isEditable = ($canEditTargets || $canEditEvaluation || $canEditAccomplishments || $canEditTwg || $canReviewerEditRemarks || $canEditSupervisorDate || $canEditRateeSignatory);

    if (!empty($isCycleArchived) || !empty($isHistoricalVersion)) {
        $canEditTargets = false;
        $canEditEvaluation = false;
        $canEditAccomplishments = false;
        $canEditApprover = false;
        $canEditRateeSignatory = false;
        $canEditSupervisorDate = false;
        $canEditTwg = false;
        $canReviewerEditRemarks = false;
        $canEditRemarks = false;
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
    <div class="flex-1 min-h-0 w-full relative flex flex-row overflow-hidden" id="workspace-split-container" role="main">
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
    'isOwner'                => $isOwner ?? false,
    'isCycleArchived'        => $isCycleArchived ?? false,
    'attachmentsByRow'       => $attachmentsByRow ?? [],
    'doc'                    => $doc ?? [],
    'isEvaluationPhase'      => $isEvaluationPhase ?? false,
    'canEditEvaluation'      => $canEditEvaluation ?? false,
    'canEditAccomplishments' => $canEditAccomplishments ?? false,
]) ?>

<?= $this->endSection() ?>