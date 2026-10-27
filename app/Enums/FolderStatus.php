<?php

namespace App\Enums;

enum FolderStatus: string 
{
    // ==========================================
    // CANONICAL LAYER 1 STATUSES
    // ==========================================

    // --- Phase 1: Target Setting Phase ---
    case DRAFT_TARGET     = 'draft_target';
    case TARGET_SUBMITTED = 'target_submitted';
    case TARGET_RETURNED  = 'target_returned';
    case TARGET_APPROVED  = 'target_approved';
    case TARGET_MISSED    = 'target_missed';

    // --- Phase 2: Accomplishment & Evaluation Phase ---
    case DRAFT_EVALUATION = 'draft_evaluation';
    case EVAL_SUBMITTED   = 'eval_submitted';
    case EVAL_RETURNED    = 'eval_returned';
    case EVAL_APPROVED    = 'eval_approved';
    case UNEVALUATED      = 'unevaluated';

    // ==========================================
    // LEGACY ALIASES (Preserved for safety & backwards compatibility)
    // ==========================================
    case PENDING_TARGET_APPROVAL = 'pending_target_approval';
    case TARGET_UNAPPROVED       = 'target_unapproved';
    case TARGET_CALIBRATING      = 'target_calibrating';
    case TARGET_TWG_DISAPPROVED  = 'target_twg_disapproved';
    case TARGET_UNVERIFIED       = 'target_unverified';

    case TO_EVALUATE             = 'to evaluate';
    case TO_EVALUATE_UNDERSCORE  = 'to_evaluate';
    case DRAFT                   = 'draft';
    case SUBMITTED               = 'submitted';
    case EVALUATED               = 'evaluated';
    case APPROVED                = 'approved';
    case REEVALUATE              = 'reevaluate';
    case EVAL_CALIBRATING        = 'eval_calibrating';
    case TWG_DISAPPROVED         = 'twg_disapproved';
    case TWG_APPROVED            = 'twg_approved';
    case EVAL_MISSED             = 'eval_missed';
    case EVAL_UNVERIFIED         = 'eval_unverified';

    /**
     * Normalizes any raw status string (canonical or legacy) into a canonical FolderStatus case.
     */
    public static function canonical(string $status): self
    {
        return match (strtolower(trim($status))) {
            'pending_target_approval', 'target_submitted' => self::TARGET_SUBMITTED,
            'target_returned', 'target_unapproved', 'target_twg_disapproved' => self::TARGET_RETURNED,
            'target_approved', 'target_calibrating', 'target_unverified' => self::TARGET_APPROVED,
            'target_missed' => self::TARGET_MISSED,

            'to evaluate', 'to_evaluate', 'draft', 'draft_evaluation' => self::DRAFT_EVALUATION,
            'submitted', 'evaluated', 'eval_submitted', 'eval_calibrating', 'eval_unverified' => self::EVAL_SUBMITTED,
            'reevaluate', 'eval_returned', 'twg_disapproved' => self::EVAL_RETURNED,
            'approved', 'twg_approved', 'eval_approved' => self::EVAL_APPROVED,
            'unevaluated', 'eval_missed' => self::UNEVALUATED,

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
            self::TARGET_RETURNED,
            self::TARGET_APPROVED,
            self::TARGET_MISSED,
            self::PENDING_TARGET_APPROVAL,
            self::TARGET_UNAPPROVED,
            self::TARGET_CALIBRATING,
            self::TARGET_TWG_DISAPPROVED,
            self::TARGET_UNVERIFIED,
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
            self::DRAFT_TARGET => 'DRAFT TARGET',
            self::TARGET_SUBMITTED, self::PENDING_TARGET_APPROVAL => 'TARGET SUBMITTED',
            self::TARGET_RETURNED, self::TARGET_UNAPPROVED, self::TARGET_TWG_DISAPPROVED => 'TARGET RETURNED',
            self::TARGET_APPROVED, self::TARGET_CALIBRATING, self::TARGET_UNVERIFIED => 'TARGET APPROVED',
            self::TARGET_MISSED, self::UNEVALUATED, self::EVAL_MISSED => 'MISSED DEADLINE',

            self::DRAFT_EVALUATION, self::TO_EVALUATE, self::TO_EVALUATE_UNDERSCORE, self::DRAFT => 'DRAFT EVALUATION',
            self::EVAL_SUBMITTED, self::SUBMITTED, self::EVALUATED, self::EVAL_CALIBRATING, self::EVAL_UNVERIFIED => 'EVALUATION SUBMITTED',
            self::EVAL_RETURNED, self::REEVALUATE, self::TWG_DISAPPROVED => 'EVALUATION RETURNED',
            self::EVAL_APPROVED, self::APPROVED, self::TWG_APPROVED => 'EVALUATION APPROVED',
        };
    }

    /**
     * Cycle Progress Tracker Stepper Index (1-4).
     */
    public function stepperIndex(): int
    {
        if (in_array($this, [self::EVAL_APPROVED, self::APPROVED, self::TWG_APPROVED, self::TWG_DISAPPROVED], true)) {
            return 4; // Final Rating / Calibrated
        }
        if (in_array($this, [self::DRAFT_EVALUATION, self::EVAL_SUBMITTED, self::EVAL_RETURNED, self::UNEVALUATED, self::DRAFT, self::SUBMITTED, self::TO_EVALUATE, self::EVALUATED, self::REEVALUATE], true)) {
            return 3; // Evaluation Phase
        }
        if (in_array($this, [self::TARGET_APPROVED, self::TARGET_CALIBRATING, self::TARGET_UNVERIFIED], true)) {
            return 2; // Target Approved
        }
        return 1; // Target Setting
    }
}