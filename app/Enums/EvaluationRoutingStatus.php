<?php

namespace App\Enums;

enum EvaluationRoutingStatus: string
{
    case DRAFT    = 'draft';
    case QUEUED   = 'queued';
    case PENDING  = 'pending';
    case APPROVED = 'approved';
    case RETURNED = 'returned';

    /**
     * Human-readable label for reviewing authority badge.
     */
    public function label(bool $isTwg = false): string
    {
        return match ($this) {
            self::DRAFT    => 'Drafting',
            self::QUEUED   => 'Queued for Verification',
            self::PENDING  => $isTwg ? 'Pending Verification' : 'Pending Review',
            self::APPROVED => 'Approved',
            self::RETURNED => 'Returned',
        };
    }
}
