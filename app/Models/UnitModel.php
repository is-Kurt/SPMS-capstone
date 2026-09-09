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

    /**
     * Resolves all active Department Chairs for a college (including its sub-departments).
     */
    public function getChairsForCollege(int $collegeUnitId): array
    {
        $childUnitIds = $this->getDescendantIds([$collegeUnitId]);
        $allTargetUnitIds = array_unique(array_merge([$collegeUnitId], $childUnitIds));
        if (empty($allTargetUnitIds)) return [];

        return $this->db->table('users u')
            ->select('u.id as user_id, u.id, u.first_name, u.last_name, u.email, u.doc_type, un.id as unit_id, un.name as department, pos.title as position')
            ->join('plantillas p', 'p.user_id = u.id AND p.ended_at IS NULL', 'inner')
            ->join('positions pos', 'pos.id = p.position_id', 'inner')
            ->join('units un', 'un.id = p.unit_id', 'inner')
            ->where('u.is_active', 1)
            ->whereIn('p.unit_id', $allTargetUnitIds)
            ->groupStart()
                ->like('pos.title', 'Chair', 'both')
                ->orLike('pos.title', 'Head', 'both')
                ->orLike('u.email', 'chair', 'both')
            ->groupEnd()
            ->groupBy('u.id')
            ->get()->getResultArray();
    }

    /**
     * Resolves all active personnel belonging to a specific department or unit.
     */
    public function getPersonnelForDepartment(int $departmentUnitId, ?int $excludeUserId = null): array
    {
        $builder = $this->db->table('users u')
            ->select('u.id as user_id, u.id, u.first_name, u.last_name, u.email, u.doc_type, un.id as unit_id, un.name as department, pos.title as position, pos.is_teaching')
            ->join('plantillas p', 'p.user_id = u.id AND p.ended_at IS NULL', 'inner')
            ->join('positions pos', 'pos.id = p.position_id', 'inner')
            ->join('units un', 'un.id = p.unit_id', 'inner')
            ->where('u.is_active', 1)
            ->where('p.unit_id', $departmentUnitId);

        if ($excludeUserId) {
            $builder->where('u.id !=', $excludeUserId);
        }

        return $builder->groupBy('u.id')->get()->getResultArray();
    }

    /**
     * Resolves the parent College unit for a given sub-department.
     */
    public function getParentCollege(int $unitId): ?array
    {
        $unit = $this->find($unitId);
        if (!$unit) return null;
        if (empty($unit['parent_id'])) {
            return $unit;
        }
        return $this->find($unit['parent_id']);
    }

    /**
     * Resolves the active Department Chair for a specific department.
     */
    public function getDepartmentChair(int $departmentUnitId): ?array
    {
        return $this->db->table('users u')
            ->select('u.id as user_id, u.id, u.first_name, u.last_name, u.email, u.doc_type, un.id as unit_id, un.name as department, pos.title as position')
            ->join('plantillas p', 'p.user_id = u.id AND p.ended_at IS NULL', 'inner')
            ->join('positions pos', 'pos.id = p.position_id', 'inner')
            ->join('units un', 'un.id = p.unit_id', 'inner')
            ->where('u.is_active', 1)
            ->where('p.unit_id', $departmentUnitId)
            ->groupStart()
                ->like('pos.title', 'Chair', 'both')
                ->orLike('pos.title', 'Head', 'both')
                ->orLike('u.email', 'chair', 'both')
            ->groupEnd()
            ->groupBy('u.id')
            ->get()->getRowArray();
    }

    /**
     * Resolves the official organizational cascade target group for a given user.
     * Tier 1 (Admin/VPAA) -> University College Deans
     * Tier 2 (College Dean) -> Department Chairs of their College (plus direct faculty)
     * Tier 3 (Dept Chair)  -> Faculty & Staff of their Department
     */
    public function getOrganizationalCascadeTarget(int $userId, string $sysRole): array
    {
        $userModel = new \App\Models\UserModel();
        $plantilla = $userModel->getActivePlantillaDetails($userId);
        $posTitle = strtolower($plantilla['position'] ?? '');
        $userEmail = strtolower($plantilla['email'] ?? '');
        
        $isExecutive = ($sysRole === 'Admin') 
                    || str_contains($posTitle, 'vpaa') 
                    || str_contains($posTitle, 'vice president')
                    || str_contains($posTitle, 'president')
                    || str_contains($userEmail, 'vpaa');

        $isDean = str_contains($posTitle, 'dean') || str_contains($userEmail, 'dean');
        $isChair = str_contains($posTitle, 'chair') || str_contains($posTitle, 'head') || str_contains($userEmail, 'chair');

        // Tier 1: Admin / Executive -> All College Deans
        if ($isExecutive && !$isDean && !$isChair) {
            $deans = $this->db->table('users u')
                ->select('u.id as user_id, u.first_name, u.last_name, u.email, pos.title as position, un.name as department')
                ->join('plantillas p', 'p.user_id = u.id AND p.ended_at IS NULL', 'left')
                ->join('positions pos', 'pos.id = p.position_id', 'left')
                ->join('units un', 'un.id = p.unit_id', 'left')
                ->where('u.is_active', 1)
                ->groupStart()
                    ->like('pos.title', 'Dean', 'both')
                    ->orLike('u.email', 'dean', 'both')
                ->groupEnd()
                ->groupBy('u.id')
                ->get()->getResultArray();

            return [
                'tier_level'       => 1,
                'tier_name'        => 'Apex Institutional Cascade',
                'unit_name'        => 'University Academic Colleges',
                'subordinate_role' => 'College Deans',
                'button_label'     => 'Cascade to College Deans',
                'member_count'     => count($deans),
                'members'          => $deans
            ];
        }

        // Tier 2: College Dean -> Department Chairs of their College
        if ($isDean) {
            $collegeUnitId = $plantilla['unit_id'] ?? null;
            $chairs = $collegeUnitId ? $this->getChairsForCollege((int) $collegeUnitId) : [];
            
            // Also include direct personnel under the college (if any)
            $directPersonnel = $collegeUnitId ? $this->getPersonnelForDepartment((int) $collegeUnitId, $userId) : [];
            $chairUserIds = array_column($chairs, 'user_id');
            foreach ($directPersonnel as $dp) {
                $dpUid = $dp['user_id'] ?? $dp['id'] ?? null;
                if ($dpUid && !in_array($dpUid, $chairUserIds)) {
                    $chairs[] = $dp;
                }
            }

            $collegeName = $plantilla['department'] ?? 'College';

            return [
                'tier_level'       => 2,
                'tier_name'        => 'Collegiate Cascade',
                'unit_name'        => $collegeName,
                'subordinate_role' => 'Department Chairs',
                'button_label'     => 'Cascade to Department Chairs',
                'member_count'     => count($chairs),
                'members'          => $chairs
            ];
        }

        // Tier 3: Department Chair -> Department Faculty & Staff
        if ($isChair) {
            $deptUnitId = $plantilla['unit_id'] ?? null;
            $personnel = $deptUnitId ? $this->getPersonnelForDepartment((int) $deptUnitId, $userId) : [];
            $deptName = $plantilla['department'] ?? 'Department';

            return [
                'tier_level'       => 3,
                'tier_name'        => 'Departmental Cascade',
                'unit_name'        => $deptName,
                'subordinate_role' => 'Department Faculty',
                'button_label'     => 'Cascade to Department Faculty',
                'member_count'     => count($personnel),
                'members'          => $personnel
            ];
        }

        // General Supervisor fallback
        $ownUnitId = $plantilla['unit_id'] ?? null;
        $personnel = $ownUnitId ? $this->getPersonnelForDepartment((int) $ownUnitId, $userId) : [];
        $unitName = $plantilla['department'] ?? 'Unit';

        return [
            'tier_level'       => 4,
            'tier_name'        => 'Subordinate Cascade',
            'unit_name'        => $unitName,
            'subordinate_role' => 'Unit Personnel',
            'button_label'     => 'Cascade to Personnel',
            'member_count'     => count($personnel),
            'members'          => $personnel
        ];
    }
}
