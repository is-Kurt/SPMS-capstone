<?php

namespace App\Enums;

enum FolderStatus: string 
{
    case DRAFT       = 'draft';
    case SUBMITTED   = 'submitted';
    case TO_EVALUATE = 'to evaluate';
    case EVALUATED   = 'evaluated';
    case APPROVED    = 'approved';
    case REEVALUATE  = 'reevaluate';
    case UNEVALUATED = 'unevaluated';
}