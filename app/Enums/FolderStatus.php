<?php

namespace App\Enums;

enum FolderStatus: string 
{
    // --- Phase 1: Target Setting Phase ---
    case DRAFT_TARGET            = 'draft_target';
    case TARGET_SUBMITTED        = 'target_submitted';
    case TARGET_RETURNED         = 'target_returned';
    case TARGET_CALIBRATING      = 'target_calibrating';
    case TARGET_TWG_DISAPPROVED  = 'target_twg_disapproved';
    case TARGET_APPROVED         = 'target_approved';
    case TARGET_MISSED           = 'target_missed';
    case TARGET_UNVERIFIED       = 'target_unverified';

    // Legacy Phase 1 aliases
    case PENDING_TARGET_APPROVAL = 'pending_target_approval';
    case TARGET_UNAPPROVED       = 'target_unapproved';

    // --- Phase 2: Accomplishment & Evaluation Phase ---
    case TO_EVALUATE             = 'to evaluate';
    case TO_EVALUATE_UNDERSCORE  = 'to_evaluate';
    case EVAL_SUBMITTED          = 'eval_submitted';
    case EVAL_RETURNED           = 'eval_returned';
    case EVAL_CALIBRATING        = 'eval_calibrating';
    case TWG_DISAPPROVED         = 'twg_disapproved';
    case TWG_APPROVED            = 'twg_approved';
    case EVAL_MISSED             = 'eval_missed';
    case EVAL_UNVERIFIED         = 'eval_unverified';

    // Legacy Phase 2 aliases
    case DRAFT                   = 'draft';
    case SUBMITTED               = 'submitted';
    case EVALUATED               = 'evaluated';
    case APPROVED                = 'approved';
    case REEVALUATE              = 'reevaluate';
    case UNEVALUATED             = 'unevaluated';
}