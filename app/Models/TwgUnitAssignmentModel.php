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

        $allowedUnitIds = $this->getAssignedUnitIdsWithDescendants($userId);
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
            ->orderBy('u.last_name', 'ASC')
            ->get()->getResultArray();
    }
}
