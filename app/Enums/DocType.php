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

    /** All doc type codes in their display order (IPCR, DPCR, OPCR, IPERF). */
    public static function values(): array
    {
        return array_map(fn(self $t) => $t->value, self::cases());
    }

    /**
     * Doc types stored as a comma-separated list (roles / employment statuses allowed_doc_types).
     * NULL means "no limit" and returns null; unknown codes are dropped; the result keeps enum order.
     */
    public static function fromCsv(?string $csv): ?array
    {
        if ($csv === null) {
            return null;
        }
        $given = array_map(fn($v) => strtoupper(trim($v)), explode(',', $csv));
        return array_values(array_filter(self::values(), fn($v) => in_array($v, $given, true)));
    }

    /** The opposite of fromCsv(): null (no limit) stays null. */
    public static function toCsv(?array $types): ?string
    {
        if ($types === null) {
            return null;
        }
        $types = array_map(fn($v) => strtoupper(trim((string) $v)), $types);
        return implode(',', array_values(array_filter(self::values(), fn($v) => in_array($v, $types, true))));
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
