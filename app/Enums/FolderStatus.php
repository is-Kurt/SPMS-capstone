<?php

namespace App\Enums;

enum FolderStatus: string 
{
    case DRAFT_TARGET            = 'draft_target';
    case PENDING_TARGET_APPROVAL = 'pending_target_approval';
    case TARGET_APPROVED         = 'target_approved';

    case DRAFT       = 'draft';
    case SUBMITTED   = 'submitted';
    case TO_EVALUATE = 'to evaluate';
    case EVALUATED   = 'evaluated';
    case APPROVED    = 'approved';
    case REEVALUATE  = 'reevaluate';
    case UNEVALUATED = 'unevaluated';
}