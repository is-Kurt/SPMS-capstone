<?php

namespace App\Models;

use CodeIgniter\Model;

class RoutingPresetModel extends Model
{
    protected $table            = 'routing_presets';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id',
        'owner_id',
        'name',
        'description',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
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
     * The people this owner can pick for a staff list now: active staff with a current position in the owner's units
     * or below (as My Staff lists them). null: no limit (the Admin). A list keeps members outside it (they moved away
     * or were disabled) but hides them; they show again if they come back. Cascade and sync check
     * ReviewHandover::canSupervise as well.
     */
    public static function pickableIds(int $ownerId): ?array
    {
        if (\App\Libraries\OrgStructure::instance()->roleOf($ownerId) === 'Admin') return null;
        $db = \Config\Database::connect();
        $myUnits = array_map('intval', array_column($db->table('plantillas')->select('unit_id')->where('user_id', $ownerId)
            ->where('ended_at IS NULL')->where('unit_id IS NOT NULL')->get()->getResultArray(), 'unit_id'));
        $units = $myUnits ? (new UnitModel())->getDescendantIds(array_unique($myUnits)) : [];
        if (!$units) return [];
        return array_map('intval', array_column((new UserModel())->getEligibleTeamMembers($ownerId, $units), 'user_id'));
    }

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

        // The count is of the members the owner can still pick; the others (moved away, disabled) are kept but hidden
        $pickable = $presets ? self::pickableIds($ownerId) : null;
        $byPreset = [];
        if ($pickable !== null) {
            $rows = $this->db->table('routing_preset_members')->select('preset_id, user_id')
                ->whereIn('preset_id', array_column($presets, 'id'))->get()->getResultArray();
            foreach ($rows as $r) $byPreset[$r['preset_id']][] = (int) $r['user_id'];
        }

        $folderModel = new DocumentFolderModel();
        foreach ($presets as &$p) {
            $p['in_use'] = $folderModel->where('routing_preset_id', $p['id'])->countAllResults() > 0;
            if ($pickable !== null) {
                $ids = $byPreset[$p['id']] ?? [];
                $p['member_count'] = count(array_intersect($ids, $pickable));
                $p['hidden_count'] = count($ids) - $p['member_count'];
            }
        }

        return $presets;
    }
}
