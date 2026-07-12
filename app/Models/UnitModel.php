<?php

namespace App\Models;

use CodeIgniter\Model;

class UnitModel extends Model
{
    protected $table            = 'units';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'parent_id',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * For Team.php: Given one or more unit ids, returns those ids plus every
     * descendant unit underneath them (recursively) - e.g. a VP office plus
     * every college/department nested under it, at any depth.
     */
    public function getDescendantIds(array $rootUnitIds): array
    {
        $rootUnitIds = array_filter($rootUnitIds);
        if (empty($rootUnitIds)) return [];

        $childrenByParent = [];
        foreach ($this->select('id, parent_id')->findAll() as $unit) {
            if ($unit['parent_id'] !== null) {
                $childrenByParent[$unit['parent_id']][] = $unit['id'];
            }
        }

        $result = [];
        $stack = $rootUnitIds;
        while (!empty($stack)) {
            $current = array_pop($stack);
            if (in_array($current, $result)) continue;
            $result[] = $current;
            foreach ($childrenByParent[$current] ?? [] as $childId) {
                $stack[] = $childId;
            }
        }

        return $result;
    }
}
