<?php

namespace App\Models;

use CodeIgniter\Model;

class TwgUnitAssignmentModel extends Model
{
    protected $table            = 'twg_unit_assignments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'unit_id',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * Get direct unit IDs assigned to a TWG reviewer.
     *
     * @param int $userId
     * @return int[]
     */
    public function getAssignedUnitIds(int $userId): array
    {
        $rows = $this->where('user_id', $userId)->findAll();
        return array_map('intval', array_column($rows, 'unit_id'));
    }

    /**
     * Get assigned unit IDs including all descendant departments/programs.
     * If a TWG member is assigned "College of Information Sciences", all
     * degree programs/departments nested under it are automatically included.
     *
     * @param int $userId
     * @return int[]
     */
    public function getAssignedUnitIdsWithDescendants(int $userId): array
    {
        $directIds = $this->getAssignedUnitIds($userId);
        if (empty($directIds)) {
            return [];
        }

        $unitModel = new UnitModel();
        $descendants = $unitModel->getDescendantIds($directIds);
        $allIds = array_unique(array_merge($directIds, $descendants));

        return array_map('intval', $allIds);
    }

    /**
     * Get list of directly assigned units for a TWG reviewer with unit details.
     *
     * @param int $userId
     * @return array
     */
    public function getAssignmentsByUser(int $userId): array
    {
        return $this->db->table($this->table . ' tua')
            ->select('un.id as unit_id, un.name as unit_name, un.parent_id')
            ->join('units un', 'un.id = tua.unit_id')
            ->where('tua.user_id', $userId)
            ->orderBy('un.name', 'ASC')
            ->get()->getResultArray();
    }

    /**
     * Get all TWG assignments grouped by user_id:
     * [ userId => [ ['unit_id' => ..., 'unit_name' => ...], ... ] ]
     *
     * @return array<int, array>
     */
    public function getAllAssignmentsGrouped(): array
    {
        $rows = $this->db->table($this->table . ' tua')
            ->select('tua.user_id, un.id as unit_id, un.name as unit_name')
            ->join('units un', 'un.id = tua.unit_id')
            ->orderBy('un.name', 'ASC')
            ->get()->getResultArray();

        $grouped = [];
        foreach ($rows as $row) {
            $uid = (int) $row['user_id'];
            if (!isset($grouped[$uid])) {
                $grouped[$uid] = [];
            }
            $grouped[$uid][] = [
                'unit_id'   => (int) $row['unit_id'],
                'unit_name' => $row['unit_name'],
            ];
        }

        return $grouped;
    }

    /**
     * Sync assignments for a TWG reviewer: removes current ones and inserts new ones.
     *
     * @param int $userId
     * @param int[] $unitIds
     */
    public function assignUnits(int $userId, array $unitIds): void
    {
        $this->db->transStart();

        $this->where('user_id', $userId)->delete();

        $validIds = array_unique(array_filter(array_map('intval', $unitIds)));
        if (!empty($validIds)) {
            $now = date('Y-m-d H:i:s');
            $inserts = [];
            foreach ($validIds as $uid) {
                $inserts[] = [
                    'user_id'    => $userId,
                    'unit_id'    => $uid,
                    'created_at' => $now,
                ];
            }
            $this->insertBatch($inserts);
        }

        $this->db->transComplete();
    }

    /**
     * Check if a TWG user is assigned to evaluate the given folder.
     *
     * @param int $userId
     * @param string|int $folderId
     * @return bool
     */
    public function isTwgAssignedToFolder(int $userId, string|int $folderId): bool
    {
        $folder = $this->db->table('document_folders')
            ->select('id, user_id')
            ->where('id', $folderId)
            ->get()->getRowArray();

        if (!$folder) {
            return false;
        }

        // Strictly non-cascading: Only units directly assigned to this TWG member are authorized
        $allowedUnitIds = $this->getAssignedUnitIds($userId);
        if (empty($allowedUnitIds)) {
            return false;
        }

        // Find the unit(s) of the ratee (folder owner)
        $plantillas = $this->db->table('plantillas')
            ->select('unit_id')
            ->where('user_id', $folder['user_id'])
            ->where('ended_at IS NULL')
            ->where('unit_id IS NOT NULL')
            ->get()->getResultArray();

        if (empty($plantillas)) {
            return false;
        }

        $ownerUnitIds = array_map('intval', array_column($plantillas, 'unit_id'));
        return !empty(array_intersect($ownerUnitIds, $allowedUnitIds));
    }

    /**
     * Resolve the assigned TWG reviewer for a given folder or folder ID.
     * Checks direct unit assignment, and falls back to parent college if in sub-department.
     *
     * @param array|string|int $folder
     * @return array|null
     */
    public function resolveAssignedTwgForFolder(array|string|int $folder): ?array
    {
        if (!is_array($folder)) {
            $folder = $this->db->table('document_folders')
                ->select('id, user_id, title')
                ->where('id', $folder)
                ->get()->getRowArray();
        }

        if (!$folder || empty($folder['user_id'])) {
            return null;
        }

        $ownerId = (int)$folder['user_id'];
        $plantillaModel = new PlantillaModel();
        $ownerPlantillas = $plantillaModel->where('user_id', $ownerId)->where('ended_at IS NULL')->findAll();
        $ownerUnitIds = array_filter(array_map('intval', array_column($ownerPlantillas, 'unit_id')));

        if (empty($ownerUnitIds)) {
            return null;
        }

        // Strictly non-cascading: Match TWG reviewer assigned directly to the owner's unit
        $twgAssignment = $this->db->table($this->table . ' tua')
            ->select('u.id, u.first_name, u.last_name, u.email, un.name as college_name')
            ->join('users u', 'u.id = tua.user_id')
            ->join('units un', 'un.id = tua.unit_id')
            ->whereIn('tua.unit_id', $ownerUnitIds)
            ->where('u.is_active', 1)
            ->get()->getRowArray();

        // If owner is in a sub-program department with no direct assignment, fallback only to their immediate college (never executive OVPAA/OVPAF)
        if (!$twgAssignment) {
            $unitModel = new UnitModel();
            $parentCollegeIds = [];
            foreach ($ownerUnitIds as $uId) {
                $college = $unitModel->getParentCollege($uId);
                if ($college && !empty($college['id']) && !in_array($college['name'], ['OVPAA', 'OVPAF', 'Office of the President'], true)) {
                    $parentCollegeIds[] = (int)$college['id'];
                }
            }
            $parentCollegeIds = array_diff(array_unique($parentCollegeIds), $ownerUnitIds);
            if (!empty($parentCollegeIds)) {
                $twgAssignment = $this->db->table($this->table . ' tua')
                    ->select('u.id, u.first_name, u.last_name, u.email, un.name as college_name')
                    ->join('users u', 'u.id = tua.user_id')
                    ->join('units un', 'un.id = tua.unit_id')
                    ->whereIn('tua.unit_id', $parentCollegeIds)
                    ->where('u.is_active', 1)
                    ->get()->getRowArray();
            }
        }

        if (!$twgAssignment) {
            return null;
        }

        $twgCollege = trim((string)($twgAssignment['college_name'] ?? ''));
        $twgPosition = 'Technical Working Group';
        if (!empty($twgCollege) && stripos($twgCollege, 'OVPAA') === false) {
            $twgPosition .= ' (' . $twgCollege . ')';
        }

        return [
            'id'           => (int)$twgAssignment['id'],
            'name'         => trim(($twgAssignment['first_name'] ?? '') . ' ' . ($twgAssignment['last_name'] ?? '')),
            'email'        => $twgAssignment['email'],
            'position'     => $twgPosition,
            'college'      => $twgAssignment['college_name'],
            'is_twg'       => true
        ];
    }

    /**
     * Get all active TWG reviewers with their full names, email, and active status.
     *
     * @return array
     */
    public function getTwgUsers(): array
    {
        return $this->db->table('users u')
            ->select('u.id, u.first_name, u.last_name, u.email, u.is_active')
            ->join('user_roles ur', 'ur.user_id = u.id')
            ->join('roles r', 'r.id = ur.role_id')
            ->where('r.name', 'TWG')
            ->where('u.deleted_at IS NULL')
            ->groupBy('u.id')
            ->orderBy('u.last_name', 'ASC')
            ->get()->getResultArray();
    }
}
