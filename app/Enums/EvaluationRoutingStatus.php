<?php

namespace App\Enums;

enum EvaluationRoutingStatus: string
{
    case PENDING  = 'pending';
    case APPROVED = 'approved';
    case RETURNED = 'returned';

    /**
     * Human-readable label for reviewing authority badge.
     */
    public function label(bool $isTwg = false): string
    {
        return match ($this) {
            self::PENDING  => $isTwg ? 'Pending Verification' : 'Pending Review',
            self::APPROVED => 'Approved',
            self::RETURNED => 'Returned',
        };
    }
}
