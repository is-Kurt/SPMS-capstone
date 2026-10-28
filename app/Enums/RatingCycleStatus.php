<?php

namespace App\Enums;

/**
 * Governs the macro institutional operational lifecycle of a Rating Cycle.
 * Stored in `rating_cycles.status`.
 */
enum RatingCycleStatus: string 
{
    case ACTIVE = 'active';
    case CLOSED = 'closed';

    /**
     * Human-readable label for UI badges and displays.
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::CLOSED => 'Closed',
        };
    }

    /**
     * Checks if the rating cycle is currently active and accepting submissions/reviews.
     */
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Checks if the rating cycle is closed/archived (read-only mode across all folders).
     */
    public function isClosed(): bool
    {
        return $this === self::CLOSED;
    }
}
