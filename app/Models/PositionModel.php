<?php

namespace App\Models;

use CodeIgniter\Model;

class PositionModel extends Model
{
    protected $table            = 'positions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'title',
        'is_teaching',
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
     * Get distinct positions held by active users assigned to the specified unit IDs.
     * Can be reused across Team, AccountManagement, and API controllers.
     *
     * @param array $unitIds List of unit IDs
     * @param int|null $excludeUserId Optional user ID to exclude
     * @return array List of positions (id, title, is_teaching)
     */
    public function getPositionsByUnitIds(array $unitIds, ?int $excludeUserId = null): array
    {
        if (empty($unitIds)) {
            return [];
        }

        $builder = $this->db->table('positions pos')
            ->select('pos.id, pos.title, pos.is_teaching')
            ->distinct()
            ->join('plantillas p', 'p.position_id = pos.id AND p.ended_at IS NULL')
            ->join('users u', 'u.id = p.user_id AND u.is_active = 1')
            ->whereIn('p.unit_id', $unitIds)
            ->orderBy('pos.title', 'ASC');

        if ($excludeUserId !== null) {
            $builder->where('u.id !=', $excludeUserId);
        }

        return $builder->get()->getResultArray();
    }
}
