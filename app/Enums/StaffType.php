<?php

namespace App\Enums;

enum StaffType: string
{
    case TEACHING     = 'teaching';
    case NON_TEACHING = 'non_teaching';

    public function label(): string
    {
        return match ($this) {
            self::TEACHING     => 'Teaching',
            self::NON_TEACHING => 'Non-Teaching',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::TEACHING     => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300',
            self::NON_TEACHING => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400',
        };
    }

    public function isTeaching(): bool
    {
        return $this === self::TEACHING;
    }

    public static function fromBoolean(bool|int|string|null $isTeaching): self
    {
        if (is_string($isTeaching)) {
            $val = strtolower(trim($isTeaching));
            if (in_array($val, ['1', 'teaching', 'faculty', 'true'], true)) {
                return self::TEACHING;
            }
            return self::NON_TEACHING;
        }

        return (!empty($isTeaching) && (int)$isTeaching === 1)
            ? self::TEACHING
            : self::NON_TEACHING;
    }
}

