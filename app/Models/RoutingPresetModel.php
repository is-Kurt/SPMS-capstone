<?php

namespace App\Models;

use CodeIgniter\Model;

class RoutingPresetModel extends Model
{
    protected $table            = 'routing_presets';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'owner_id',
        'name',
        'description',
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
     * For Team.php: Gets all teams, their member count, and active usage status.
     */
    public function getPresetsWithDetails(int $ownerId): array
    {
        $presets = $this->select('routing_presets.*, COUNT(rpm.id) as member_count')
            ->join('routing_preset_members rpm', 'rpm.preset_id = routing_presets.id', 'left')
            ->where('routing_presets.owner_id', $ownerId)
            ->groupBy('routing_presets.id')
            ->orderBy('routing_presets.created_at', 'DESC')
            ->findAll();

        $folderModel = new DocumentFolderModel();
        foreach ($presets as &$p) {
            $p['in_use'] = $folderModel->where('routing_preset_id', $p['id'])->countAllResults() > 0;
        }

        return $presets;
    }
}
