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
            self::DRAFT_TARGET     => 'DRAFT TARGET',
            self::TARGET_SUBMITTED => 'TARGET SUBMITTED',
            self::TARGET_RETURNED  => 'TARGET RETURNED',
            self::TARGET_APPROVED  => 'TARGET APPROVED',
            self::TARGET_MISSED    => 'MISSED DEADLINE',

            self::DRAFT_EVALUATION => 'DRAFT EVALUATION',
            self::EVAL_SUBMITTED   => 'EVALUATION SUBMITTED',
            self::EVAL_RETURNED    => 'EVALUATION RETURNED',
            self::EVAL_APPROVED    => 'EVALUATION APPROVED',
            self::UNEVALUATED      => 'MISSED DEADLINE',
        };
    }

    /**
     * Cycle Progress Tracker Stepper Index (1-4).
     */
    public function stepperIndex(): int
    {
        if ($this === self::EVAL_APPROVED) {
            return 4; // Final Rating / Calibrated
        }
        if (in_array($this, [self::DRAFT_EVALUATION, self::EVAL_SUBMITTED, self::EVAL_RETURNED, self::UNEVALUATED], true)) {
            return 3; // Evaluation Phase
        }
        if ($this === self::TARGET_APPROVED) {
            return 2; // Target Approved
        }
        return 1; // Target Setting
    }
}