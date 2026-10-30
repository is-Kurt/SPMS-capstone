<?php

namespace App\Enums;

enum FolderStatus: string 
{
    // ==========================================
    // CANONICAL LAYER 1 STATUSES
    // ==========================================

    // --- Phase 1: Target Setting Phase ---
    case DRAFT_TARGET       = 'draft_target';
    case TARGET_SUBMITTED   = 'target_submitted';
    case TARGET_APPROVED    = 'target_approved';
    case TARGET_MISSED      = 'target_missed';
    case TARGET_UNAPPROVED  = 'target_unapproved';

    // --- Phase 2: Accomplishment & Evaluation Phase ---
    case DRAFT_EVALUATION   = 'draft_evaluation';
    case EVAL_SUBMITTED     = 'eval_submitted';
    case EVAL_APPROVED      = 'eval_approved';
    case UNEVALUATED        = 'unevaluated';
    case EVAL_UNRATED       = 'eval_unrated';

    /**
     * Normalizes any raw status string (canonical or legacy) into a canonical FolderStatus case.
     */
    public static function canonical(string $status): self
    {
        return match (strtolower(trim($status))) {
            'pending_target_approval', 'target_submitted' => self::TARGET_SUBMITTED,
            'target_returned', 'target_twg_disapproved' => self::DRAFT_TARGET,
            'target_approved', 'target_calibrating', 'target_unverified' => self::TARGET_APPROVED,
            'target_missed' => self::TARGET_MISSED,
            'target_unapproved', 'unapproved' => self::TARGET_UNAPPROVED,

            'to evaluate', 'to_evaluate', 'draft', 'draft_evaluation', 'reevaluate', 'eval_returned', 'twg_disapproved' => self::DRAFT_EVALUATION,
            'submitted', 'evaluated', 'eval_submitted', 'eval_calibrating', 'eval_unverified' => self::EVAL_SUBMITTED,
            'approved', 'twg_approved', 'eval_approved' => self::EVAL_APPROVED,
            'unevaluated', 'eval_missed' => self::UNEVALUATED,
            'eval_unrated', 'unrated', 'uncalibrated' => self::EVAL_UNRATED,

            default => self::DRAFT_TARGET,
        };
    }

    /**
     * Checks if this status belongs to Phase 1 (Target Setting).
     */
    public function isTargetPhase(): bool
    {
        return in_array($this, [
            self::DRAFT_TARGET,
            self::TARGET_SUBMITTED,
            self::TARGET_APPROVED,
            self::TARGET_MISSED,
            self::TARGET_UNAPPROVED,
        ], true);
    }

    /**
     * Checks if this status belongs to Phase 2 (Accomplishment & Evaluation).
     */
    public function isEvaluationPhase(): bool
    {
        return !$this->isTargetPhase();
    }

    /**
     * Human-readable label for Layer 1 badges.
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT_TARGET       => 'DRAFT TARGET',
            self::TARGET_SUBMITTED   => 'TARGET SUBMITTED',
            self::TARGET_APPROVED    => 'TARGET APPROVED',
            self::TARGET_MISSED      => 'MISSED DEADLINE',
            self::TARGET_UNAPPROVED  => 'SUBMITTED • UNAPPROVED',

            self::DRAFT_EVALUATION   => 'DRAFT EVALUATION',
            self::EVAL_SUBMITTED     => 'EVALUATION SUBMITTED',
            self::EVAL_APPROVED      => 'EVALUATION APPROVED',
            self::UNEVALUATED        => 'MISSED DEADLINE',
            self::EVAL_UNRATED       => 'SUBMITTED • UNRATED',
        };
    }

    /**
     * Cycle Progress Tracker Stepper Index (1-4).
     *
     * Step 1: Target Setting (Phase 1 drafting window)
     * Step 2: Target Approved (Phase 1 approval status milestone)
     * Step 3: Evaluation (Phase 2 evaluation window / drafting)
     * Step 4: Final Rating (Phase 2 final approval status milestone)
     */
    public function stepperIndex(?string $evalStartDate = null, ?string $targetEndDate = null, bool $isSubmitted = false): int
    {
        if ($this === self::EVAL_APPROVED) {
            return 4; // Final Rating Approved (All complete)
        }
        if ($this === self::EVAL_SUBMITTED || $this === self::EVAL_UNRATED || ($this === self::UNEVALUATED && $isSubmitted)) {
            return 4; // Evaluation submitted and complete (Step 3 complete ✓, Step 4 Final Rating active 🟡)
        }
        if (in_array($this, [self::DRAFT_EVALUATION, self::UNEVALUATED], true)) {
            return 3; // Evaluation Phase (In-progress drafting 🟡)
        }
        if ($this === self::TARGET_APPROVED) {
            // Target is approved: check if calendar dates have advanced into the Evaluation period
            $now = date('Y-m-d H:i:s');
            $evalOpened = (!empty($evalStartDate) && $now >= $evalStartDate)
                       || (!empty($targetEndDate) && $now > $targetEndDate);
            if ($evalOpened) {
                return 3; // Evaluation period has begun (Step 3 active 🟡)
            }
            return 2; // Target Approved milestone reached (waiting for evaluation period 🟡)
        }
        if ($this === self::TARGET_SUBMITTED || $this === self::TARGET_UNAPPROVED || ($this === self::TARGET_MISSED && $isSubmitted)) {
            return 2; // Targets submitted and complete (Step 1 complete ✓, Step 2 Target Approved active 🟡)
        }
        return 1; // Target Setting (Drafting 🟡)
    }
}