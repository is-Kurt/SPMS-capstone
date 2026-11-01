<?php

namespace App\Models;

use CodeIgniter\Model;

class DefaultFunctionWeightModel extends Model
{
    protected $table            = 'default_function_weights';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'doc_type',
        'core_weight',
        'strategic_weight',
        'support_weight',
        'description',
        'created_at',
        'updated_at',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'doc_type'         => 'required|in_list[IPCR,OPCR,DPCR]',
        'core_weight'      => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
        'strategic_weight' => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
        'support_weight'   => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
    ];

    /**
     * Returns all default function weights keyed by doc_type ('OPCR', 'DPCR', 'IPCR').
     *
     * @return array<string, array>
     */
    public function getAllWeightsKeyed(): array
    {
        $rows = $this->orderBy('id', 'ASC')->findAll();
        $keyed = [];

        foreach ($rows as $row) {
            $type = strtoupper((string)$row['doc_type']);
            $keyed[$type] = [
                'id'               => (int)$row['id'],
                'doc_type'         => $type,
                'core_weight'      => (float)$row['core_weight'],
                'strategic_weight' => (float)$row['strategic_weight'],
                'support_weight'   => (float)$row['support_weight'],
                'description'      => $row['description'] ?? '',
                // Convenient decimal fractions for direct calculation in JS/PHP
                'core_fraction'      => round((float)$row['core_weight'] / 100, 4),
                'strategic_fraction' => round((float)$row['strategic_weight'] / 100, 4),
                'support_fraction'   => round((float)$row['support_weight'] / 100, 4),
            ];
        }

        // Fallbacks in case table hasn't been seeded yet
        $defaults = [
            'OPCR' => ['core_weight' => 70.0, 'strategic_weight' => 20.0, 'support_weight' => 10.0, 'description' => 'Office Performance Commitment and Review (Executive / Head of Office)'],
            'DPCR' => ['core_weight' => 70.0, 'strategic_weight' => 15.0, 'support_weight' => 15.0, 'description' => 'Department Performance Commitment and Review (Deans, Chairs, Unit Heads)'],
            'IPCR' => ['core_weight' => 70.0, 'strategic_weight' => 15.0, 'support_weight' => 15.0, 'description' => 'Individual Performance Commitment and Review (Faculty and Staff)'],
        ];

        foreach ($defaults as $type => $fallback) {
            if (!isset($keyed[$type])) {
                $keyed[$type] = array_merge([
                    'id'                 => 0,
                    'doc_type'           => $type,
                    'core_fraction'      => round($fallback['core_weight'] / 100, 4),
                    'strategic_fraction' => round($fallback['strategic_weight'] / 100, 4),
                    'support_fraction'   => round($fallback['support_weight'] / 100, 4),
                ], $fallback);
            }
        }

        return $keyed;
    }
}

