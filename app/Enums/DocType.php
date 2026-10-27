<?php

namespace App\Enums;

enum DocType: string
{
    case IPCR  = 'IPCR';
    case DPCR  = 'DPCR';
    case OPCR  = 'OPCR';
    case IPERF = 'IPERF';

    /**
     * Human-readable full title of the commitment form.
     */
    public function label(): string
    {
        return match ($this) {
            self::IPCR  => 'Individual Performance Commitment and Review',
            self::DPCR  => 'Division Performance Commitment and Review',
            self::OPCR  => 'Office Performance Commitment and Review',
            self::IPERF => 'Individual Performance Evaluation and Review Form',
        };
    }

    /**
     * Short acronym / code.
     */
    public function code(): string
    {
        return $this->value;
    }

    /**
     * Whether this document serves as an institutional / office-wide basis.
     */
    public function isInstitutional(): bool
    {
        return in_array($this, [self::OPCR, self::DPCR], true);
    }

    /**
     * Whether this document structure includes an Allotted Budget column.
     */
    public function hasBudgetColumn(): bool
    {
        return in_array($this, [self::OPCR, self::DPCR], true);
    }

    /**
     * Whether this document structure includes Divisions / Individuals Accountable column.
     */
    public function hasAccountabilityColumn(): bool
    {
        return in_array($this, [self::OPCR, self::DPCR], true);
    }

    /**
     * Normalizes a raw doc_type or title string into a canonical DocType case.
     */
    public static function canonical(?string $type): self
    {
        if (!$type) return self::IPCR;
        $norm = strtoupper(trim($type));

        if (str_contains($norm, 'OPCR') || str_contains($norm, 'OFFICE')) {
            return self::OPCR;
        }
        if (str_contains($norm, 'DPCR') || str_contains($norm, 'DIVISION') || str_contains($norm, 'DEPARTMENT') || str_contains($norm, 'CDPCR')) {
            return self::DPCR;
        }
        if (str_contains($norm, 'IPERF')) {
            return self::IPERF;
        }
        return self::IPCR;
    }
}
