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
     * Synchronizes all 15 colleges and their undergraduate/graduate programs
     * to match the official degree format requested by the user.
     */
    public function syncHierarchyWithOfficialPrograms(): void
    {
        $bsuHierarchy = [
            'College of Agriculture' => [
                'Bachelor of Science in Agriculture',
                'Bachelor of Science in Agribusiness',
            ],
            'College of Arts and Humanities' => [
                'Bachelor of Arts in Communication',
                'Bachelor of Arts in English Language',
                'Bachelor of Arts in Filipino Language',
            ],
            'College of Engineering' => [
                'Bachelor of Science in Agricultural and Biosystems Engineering',
                'Bachelor of Science in Civil Engineering',
                'Bachelor of Science in Electrical Engineering',
                'Bachelor of Science in Industrial Engineering',
            ],
            'College of Forestry' => [
                'Bachelor of Science in Forestry',
            ],
            'College of Human Ecology' => [
                'Bachelor of Science in Hospitality Management (BSHM)',
                'Bachelor of Science in Nutrition and Dietetics (BSND)',
                'Bachelor of Science in Entrepreneurship (BS Entrep)',
                'Bachelor of Science in Food Technology (BSFT)',
                'Bachelor of Science in Tourism Management (BSTM)',
            ],
            'College of Human Kinetics' => [
                'Bachelor of Science in Physical Education',
                'Bachelor of Science in Exercise and Sports Sciences (BSESS)',
            ],
            'College of Information Sciences' => [
                'Bachelor of Science in Development Communication (BSDC)',
                'Bachelor of Science in Information Technology (BSIT)',
                'Bachelor of Library and Information Science (BLIS)',
            ],
            'College of Medicine' => [
                'Doctor of Medicine',
            ],
            'College of Natural Sciences' => [
                'Bachelor of Science in Biology',
                'Bachelor of Science in Chemistry',
                'Bachelor of Science in Environmental Science',
            ],
            'College of Numeracy and Applied Sciences' => [
                'Bachelor of Science in Statistics',
                'Bachelor of Science in Mathematics',
            ],
            'College of Nursing' => [
                'Bachelor of Science in Nursing (BSN)',
            ],
            'College of Public Administration and Governance' => [
                'Bachelor of Public Administration',
            ],
            'College of Social Sciences' => [
                'Bachelor of Science in Psychology',
                'Bachelor of Arts in History',
            ],
            'College of Teacher Education' => [
                'Bachelor of Early Childhood Education',
                'Bachelor of Elementary Education',
                'Bachelor of Secondary Education',
                'Bachelor of Technology and Livelihood Education',
            ],
            'College of Veterinary Medicine' => [
                'Doctor of Veterinary Medicine',
            ],
        ];

        // Legacy mapping to rename units seamlessly preserving IDs and plantilla links
        $legacyRenames = [
            'Department of Agronomy'                                 => 'Bachelor of Science in Agriculture',
            'Department of Agricultural Economics & Agribusiness'    => 'Bachelor of Science in Agribusiness',
            'Department of Communication'                            => 'Bachelor of Arts in Communication',
            'Department of English Language'                         => 'Bachelor of Arts in English Language',
            'Department of Filipino Language'                        => 'Bachelor of Arts in Filipino Language',
            'Department of Agricultural and Biosystems Engineering'  => 'Bachelor of Science in Agricultural and Biosystems Engineering',
            'Department of Civil Engineering'                        => 'Bachelor of Science in Civil Engineering',
            'Department of Electrical Engineering'                   => 'Bachelor of Science in Electrical Engineering',
            'Department of Industrial Engineering'                   => 'Bachelor of Science in Industrial Engineering',
            'Department of Forest Science'                           => 'Bachelor of Science in Forestry',
            'Department of Hospitality Management'                   => 'Bachelor of Science in Hospitality Management (BSHM)',
            'Department of Nutrition and Dietetics'                  => 'Bachelor of Science in Nutrition and Dietetics (BSND)',
            'Department of Entrepreneurship'                         => 'Bachelor of Science in Entrepreneurship (BS Entrep)',
            'Department of Food Technology'                          => 'Bachelor of Science in Food Technology (BSFT)',
            'Department of Tourism Management'                       => 'Bachelor of Science in Tourism Management (BSTM)',
            'Department of Physical Education'                       => 'Bachelor of Science in Physical Education',
            'Department of Exercise and Sports Sciences'             => 'Bachelor of Science in Exercise and Sports Sciences (BSESS)',
            'Department of Development Communication'                => 'Bachelor of Science in Development Communication (BSDC)',
            'Department of Information Technology'                   => 'Bachelor of Science in Information Technology (BSIT)',
            'Department of Library and Information Science'          => 'Bachelor of Library and Information Science (BLIS)',
            'Department of Basic Medical Sciences'                   => 'Doctor of Medicine',
            'Department of Biology'                                  => 'Bachelor of Science in Biology',
            'Department of Chemistry'                                => 'Bachelor of Science in Chemistry',
            'Department of Environmental Science'                    => 'Bachelor of Science in Environmental Science',
            'Department of Statistics'                               => 'Bachelor of Science in Statistics',
            'Department of Mathematics'                              => 'Bachelor of Science in Mathematics',
            'Department of Nursing'                                  => 'Bachelor of Science in Nursing (BSN)',
            'Department of Public Administration'                    => 'Bachelor of Public Administration',
            'Department of Psychology'                               => 'Bachelor of Science in Psychology',
            'Department of History and Social Studies'               => 'Bachelor of Arts in History',
            'Department of Early Childhood & Elementary Education'   => 'Bachelor of Early Childhood Education',
            'Department of Secondary Education'                      => 'Bachelor of Secondary Education',
            'Department of Technology and Livelihood Education'      => 'Bachelor of Technology and Livelihood Education',
            'Department of Veterinary Medicine'                      => 'Doctor of Veterinary Medicine',
        ];

        // 1. Rename legacy departments
        foreach ($legacyRenames as $oldName => $newName) {
            $existing = $this->where('name', $oldName)->first();
            if ($existing) {
                $target = $this->where('name', $newName)->first();
                if (!$target) {
                    $this->update($existing['id'], ['name' => $newName]);
                }
            }
        }

        // 2. Unnest colleges from OVPAA and ensure all 15 colleges & programs exist
        $now = date('Y-m-d H:i:s');
        foreach ($bsuHierarchy as $collegeName => $programs) {
            $college = $this->where('name', $collegeName)->first();
            if (!$college) {
                $collegeId = (int)$this->insert(['name' => $collegeName, 'parent_id' => null, 'created_at' => $now]);
            } else {
                $collegeId = (int)$college['id'];
                if ($college['parent_id'] !== null) {
                    $this->update($collegeId, ['parent_id' => null]);
                }
            }

            foreach ($programs as $progName) {
                $prog = $this->where('name', $progName)->first();
                if (!$prog) {
                    $this->insert(['name' => $progName, 'parent_id' => $collegeId, 'created_at' => $now]);
                } else {
                    if ((int)($prog['parent_id'] ?? 0) !== $collegeId) {
                        $this->update($prog['id'], ['parent_id' => $collegeId]);
                    }
                }
            }
        }

        // 3. Clean up obsolete units that have no active plantillas assigned
        $validUnitNames = array_merge(
            ['OVPAA', 'OVPAF', 'HRDO', 'Accounting Office', "Registrar's Office", 'General Services Office'],
            array_keys($bsuHierarchy)
        );
        foreach ($bsuHierarchy as $programs) {
            $validUnitNames = array_merge($validUnitNames, $programs);
        }

        $allUnits = $this->findAll();
        foreach ($allUnits as $u) {
            if (!in_array($u['name'], $validUnitNames, true)) {
                $headcount = $this->db->table('plantillas')->where('unit_id', $u['id'])->where('ended_at IS NULL')->countAllResults();
                if ($headcount === 0) {
                    $this->delete($u['id']);
                }
            }
        }
    }

    /**
     * Ensures all colleges are top-level units (parent_id = null) rather than
     * nested under OVPAA, and synchronizes official degree program departments.
     */
    public function unnestCollegesFromOvpaa(): void
    {
        $this->syncHierarchyWithOfficialPrograms();
    }

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
     * Identifies the immediate subordinate targets according to the official SPMS Hierarchy:
     * Tier 0 (System Admin) -> Vice President for Academic Affairs (VPAA)
     * Tier 1 (VPAA / Exec)  -> 15 Academic College Deans
     * Tier 2 (College Dean) -> Department Chairs of their College (plus direct faculty)
     * Tier 3 (Dept Chair)   -> Faculty & Staff of their Department
     */
    public function getOrganizationalCascadeTarget(int $userId, string $sysRole): array
    {
        $userModel = new \App\Models\UserModel();
        $plantilla = $userModel->getActivePlantillaDetails($userId);
        $posTitle = strtolower($plantilla['position'] ?? '');
        $userEmail = strtolower($plantilla['email'] ?? '');
        
        $isVpaa = str_contains($posTitle, 'vpaa') 
               || str_contains($posTitle, 'vice president')
               || str_contains($posTitle, 'president')
               || str_contains($userEmail, 'vpaa');

        $isDean = str_contains($posTitle, 'dean') || str_contains($userEmail, 'dean');
        $isChair = str_contains($posTitle, 'chair') || str_contains($posTitle, 'head') || str_contains($userEmail, 'chair');

        // Tier 0: System Admin -> Vice President for Academic Affairs (VPAA)
        if ($sysRole === 'Admin' && !$isVpaa && !$isDean && !$isChair) {
            $vpaaUsers = $this->db->table('users u')
                ->select('u.id as user_id, u.first_name, u.last_name, u.email, pos.title as position, un.name as department')
                ->join('plantillas p', 'p.user_id = u.id AND p.ended_at IS NULL', 'left')
                ->join('positions pos', 'pos.id = p.position_id', 'left')
                ->join('units un', 'un.id = p.unit_id', 'left')
                ->where('u.is_active', 1)
                ->groupStart()
                    ->like('u.email', 'vpaa', 'both')
                    ->orLike('pos.title', 'VPAA', 'both')
                    ->orGroupStart()
                        ->like('pos.title', 'Vice President', 'both')
                        ->groupStart()
                            ->like('pos.title', 'Academic', 'both')
                            ->orLike('un.name', 'OVPAA', 'both')
                            ->orLike('un.name', 'Academic', 'both')
                        ->groupEnd()
                    ->groupEnd()
                ->groupEnd()
                ->groupBy('u.id')
                ->get()->getResultArray();

            // Fallback if specific VPAA title/unit filter didn't catch anything
            if (empty($vpaaUsers)) {
                $vpaaUsers = $this->db->table('users u')
                    ->select('u.id as user_id, u.first_name, u.last_name, u.email, pos.title as position, un.name as department')
                    ->join('plantillas p', 'p.user_id = u.id AND p.ended_at IS NULL', 'left')
                    ->join('positions pos', 'pos.id = p.position_id', 'left')
                    ->join('units un', 'un.id = p.unit_id', 'left')
                    ->where('u.is_active', 1)
                    ->groupStart()
                        ->like('u.email', 'vpaa', 'both')
                        ->orLike('pos.title', 'Vice President', 'both')
                    ->groupEnd()
                    ->groupBy('u.id')
                    ->get()->getResultArray();
            }

            return [
                'tier_level'       => 0,
                'tier_name'        => 'Apex Executive Delegation',
                'unit_name'        => 'Office of the Vice President for Academic Affairs (OVPAA)',
                'subordinate_role' => 'Vice President for Academic Affairs (VPAA)',
                'button_label'     => 'Cascade to VPAA',
                'member_count'     => count($vpaaUsers),
                'members'          => $vpaaUsers
            ];
        }

        // Tier 1: VPAA / University Executive -> All College Deans
        if (($isVpaa || $sysRole === 'Admin') && !$isDean && !$isChair) {
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
                'tier_name'        => 'Collegiate Division Cascade',
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
