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

        if (!$folder || (int) $folder['user_id'] === $userId) {
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
     * Resolve ALL active TWG reviewers directly assigned to a folder owner's unit(s).
     * Supports shared queues (multiple TWG reviewers covering the same unit/positions)
     * and stays strictly non-cascading to match getRatingDashboardFolders().
     *
     * @param array|string|int|null $folder
     * @return array<int, array>
     */
    public function resolveAssignedTwgsForFolder(array|string|int|null $folder): array
    {
        if (empty($folder)) {
            return [];
        }

        if (!is_array($folder)) {
            $folder = $this->db->table('document_folders')
                ->select('id, user_id, title')
                ->where('id', $folder)
                ->get()->getRowArray();
        }

        if (!$folder || empty($folder['user_id'])) {
            return [];
        }

        $ownerId = (int) $folder['user_id'];
        $plantillaModel = new PlantillaModel();
        $ownerPlantillas = $plantillaModel->where('user_id', $ownerId)->where('ended_at IS NULL')->findAll();
        $ownerUnitIds = array_values(array_unique(array_filter(array_map('intval', array_column($ownerPlantillas, 'unit_id')))));

        if (empty($ownerUnitIds)) {
            return [];
        }

        $rows = $this->db->table($this->table . ' tua')
            ->select('u.id, u.first_name, u.last_name, u.email, un.name as unit_name')
            ->join('users u', 'u.id = tua.user_id')
            ->join('user_roles ur', 'ur.user_id = u.id')
            ->join('roles r', 'r.id = ur.role_id')
            ->join('units un', 'un.id = tua.unit_id')
            ->whereIn('tua.unit_id', $ownerUnitIds)
            ->where('r.name', 'TWG')
            ->where('u.is_active', 1)
            ->where('u.deleted_at IS NULL')
            ->where('u.id !=', $ownerId)
            ->orderBy('u.last_name', 'ASC')
            ->orderBy('un.name', 'ASC')
            ->get()->getResultArray();

        if (empty($rows)) {
            return [];
        }

        $byTwg = [];
        foreach ($rows as $row) {
            $tid = (int) $row['id'];
            if (!isset($byTwg[$tid])) {
                $byTwg[$tid] = [
                    'id'         => $tid,
                    'first_name' => $row['first_name'] ?? '',
                    'last_name'  => $row['last_name'] ?? '',
                    'email'      => $row['email'] ?? '',
                    'units'      => [],
                ];
            }
            $uName = trim((string) ($row['unit_name'] ?? ''));
            if ($uName !== '' && !in_array($uName, $byTwg[$tid]['units'], true)) {
                $byTwg[$tid]['units'][] = $uName;
            }
        }

        $result = [];
        foreach ($byTwg as $twg) {
            $unitSummary = implode(', ', $twg['units']);
            $twgPosition = 'Technical Working Group';
            if ($unitSummary !== '') {
                $twgPosition .= ' (' . $unitSummary . ')';
            }

            $result[] = [
                'id'       => $twg['id'],
                'name'     => trim($twg['first_name'] . ' ' . $twg['last_name']),
                'email'    => $twg['email'],
                'position' => $twgPosition,
                'college'  => $unitSummary,
                'is_twg'   => true,
            ];
        }

        return $result;
    }

    /**
     * Resolve the primary/first assigned TWG reviewer for a folder (or null if none).
     *
     * @param array|string|int|null $folder
     * @return array|null
     */
    public function resolveAssignedTwgForFolder(array|string|int|null $folder): ?array
    {
        $twgs = $this->resolveAssignedTwgsForFolder($folder);
        return $twgs[0] ?? null;
    }

    /**
     * Build per-unit coverage, shared TWG reviewer list, and designated ratee positions/staff
     * so Admins can inspect coverage gaps, multi-TWG shared units, and all positions per TWG.
     *
     * @return array<int, array>
     */
    public function getUnitCoverageMetadata(): array
    {
        $unitModel = new UnitModel();
        $enrichedUnits = $unitModel->getEnrichedHierarchyUnits();

        $meta = [];
        foreach ($enrichedUnits as $eu) {
            $uid = (int) $eu['id'];
            $meta[$uid] = [
                'unit_id'     => $uid,
                'unit_name'   => $eu['name'],
                'parent_id'   => !empty($eu['parent_id']) ? (int) $eu['parent_id'] : null,
                'tier'        => (int) ($eu['tier'] ?? 1),
                'type'        => $eu['type'] ?? 'admin',
                'path_str'    => $eu['path_str'] ?? $eu['name'],
                'staff_count' => 0,
                'positions'   => [],
                'personnel'   => [],
                'reviewers'   => [],
            ];
        }

        if (empty($meta)) {
            return [];
        }

        // 1. Attach active TWG reviewers per unit
        $assignmentRows = $this->db->table($this->table . ' tua')
            ->select('tua.unit_id, u.id as user_id, u.first_name, u.last_name, u.email')
            ->join('users u', 'u.id = tua.user_id')
            ->join('user_roles ur', 'ur.user_id = u.id')
            ->join('roles r', 'r.id = ur.role_id')
            ->where('r.name', 'TWG')
            ->where('u.is_active', 1)
            ->where('u.deleted_at IS NULL')
            ->orderBy('u.last_name', 'ASC')
            ->get()->getResultArray();

        $seenReviewerUnit = [];
        foreach ($assignmentRows as $ar) {
            $unitId = (int) $ar['unit_id'];
            $revId  = (int) $ar['user_id'];
            if (!isset($meta[$unitId]) || isset($seenReviewerUnit[$unitId][$revId])) {
                continue;
            }
            $seenReviewerUnit[$unitId][$revId] = true;
            $fullName = trim(($ar['first_name'] ?? '') . ' ' . ($ar['last_name'] ?? ''));
            $initials = strtoupper(substr($ar['first_name'] ?? 'T', 0, 1) . substr($ar['last_name'] ?? 'W', 0, 1));
            $meta[$unitId]['reviewers'][] = [
                'id'       => $revId,
                'name'     => $fullName,
                'email'    => $ar['email'] ?? '',
                'initials' => $initials,
            ];
        }

        // 2. Attach active ratee personnel & job positions per unit (excluding Admin/TWG/PMT)
        $excludedUserIdsSub = $this->db->table('user_roles ur_ex')
            ->select('ur_ex.user_id')
            ->join('roles r_ex', 'r_ex.id = ur_ex.role_id')
            ->whereIn('r_ex.name', ['Admin', 'TWG', 'PMT'])
            ->getCompiledSelect();

        $plantillaRows = $this->db->table('plantillas p')
            ->select('p.unit_id, u.id as user_id, u.first_name, u.last_name, u.email, pos.id as position_id, pos.title as position_title, pos.is_teaching')
            ->join('users u', 'u.id = p.user_id')
            ->join('positions pos', 'pos.id = p.position_id', 'left')
            ->where('p.ended_at IS NULL')
            ->where('p.unit_id IS NOT NULL')
            ->where('u.is_active', 1)
            ->where('u.deleted_at IS NULL')
            ->where("u.id NOT IN ({$excludedUserIdsSub})", null, false)
            ->orderBy('pos.title', 'ASC')
            ->orderBy('u.last_name', 'ASC')
            ->get()->getResultArray();

        $unitPosMap = [];
        $seenUserInUnit = [];

        foreach ($plantillaRows as $pr) {
            $unitId = (int) $pr['unit_id'];
            if (!isset($meta[$unitId])) {
                continue;
            }

            $uId       = (int) $pr['user_id'];
            $posId     = !empty($pr['position_id']) ? (int) $pr['position_id'] : 0;
            $posTitle  = trim((string) ($pr['position_title'] ?? '')) ?: 'Unassigned Position';
            $teaching  = (int) ($pr['is_teaching'] ?? 0);
            $fullName  = trim(($pr['first_name'] ?? '') . ' ' . ($pr['last_name'] ?? ''));

            // Deduplicate identical user+position rows inside the same unit
            $dedupKey = $uId . ':' . $posId;
            if (isset($seenUserInUnit[$unitId][$dedupKey])) {
                continue;
            }
            $seenUserInUnit[$unitId][$dedupKey] = true;

            $meta[$unitId]['personnel'][] = [
                'user_id'     => $uId,
                'name'        => $fullName,
                'email'       => $pr['email'] ?? '',
                'position_id' => $posId,
                'position'    => $posTitle,
                'is_teaching' => $teaching,
            ];

            $posKey = strtolower($posTitle);
            if (!isset($unitPosMap[$unitId][$posKey])) {
                $unitPosMap[$unitId][$posKey] = [
                    'id'          => $posId,
                    'title'       => $posTitle,
                    'is_teaching' => $teaching,
                    'user_ids'    => [],
                ];
            }
            $unitPosMap[$unitId][$posKey]['user_ids'][$uId] = true;
        }

        foreach ($meta as $unitId => &$m) {
            $uniqueUsers = [];
            foreach ($m['personnel'] as $p) {
                $uniqueUsers[$p['user_id']] = true;
            }
            $m['staff_count'] = count($uniqueUsers);

            if (!empty($unitPosMap[$unitId])) {
                foreach ($unitPosMap[$unitId] as $pInfo) {
                    $m['positions'][] = [
                        'id'          => $pInfo['id'],
                        'title'       => $pInfo['title'],
                        'is_teaching' => $pInfo['is_teaching'],
                        'count'       => count($pInfo['user_ids']),
                    ];
                }
            }
        }
        unset($m);

        return $meta;
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
