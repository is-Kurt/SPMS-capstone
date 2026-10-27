<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN      = 'Admin';
    case SUPERVISOR = 'Supervisor';
    case EMPLOYEE   = 'Employee';
    case TWG        = 'TWG';
    case PMT        = 'PMT';
    case HR         = 'HR';

    /**
     * Human-readable label for badges and display.
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN      => 'System Administrator',
            self::SUPERVISOR => 'Supervisor / Division Head',
            self::EMPLOYEE   => 'Faculty / Staff Employee',
            self::TWG        => 'Technical Working Group',
            self::PMT        => 'Performance Management Team',
            self::HR         => 'Human Resources (HRDO)',
        };
    }

    /**
     * Checks if this role has administrative privileges.
     */
    public function isAdministrative(): bool
    {
        return in_array($this, [self::ADMIN, self::SUPERVISOR], true);
    }

    /**
     * Checks if this role can act as an evaluator or reviewing authority.
     */
    public function isReviewer(): bool
    {
        return in_array($this, [self::ADMIN, self::SUPERVISOR, self::TWG, self::PMT], true);
    }

    /**
     * Checks if this role is a compliance/calibration authority (TWG or PMT).
     */
    public function isComplianceAuthority(): bool
    {
        return in_array($this, [self::TWG, self::PMT], true);
    }

    /**
     * Normalizes a role string into a canonical UserRole case.
     */
    public static function canonical(?string $role): ?self
    {
        if (!$role) return null;
        $norm = strtolower(trim($role));
        return match ($norm) {
            'admin', 'administrator' => self::ADMIN,
            'supervisor' => self::SUPERVISOR,
            'employee', 'user', 'staff', 'faculty' => self::EMPLOYEE,
            'twg' => self::TWG,
            'pmt' => self::PMT,
            'hr', 'hrdo' => self::HR,
            default => null,
        };
    }
}
