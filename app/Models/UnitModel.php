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
        'head_position_id',
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
     * Resolves all units enriched with dynamic n-tier hierarchy information:
     * - tier: Integer depth (1 = top-level executive/sectors, 2 = colleges/divisions, 3 = programs/departments, ...)
     * - root_id & root_name: Root sector unit (e.g. OVPAA, OVPAF)
     * - parent_name: Immediate parent name
     * - path: Array of names from root to this unit
     * - path_str: Formatted breadcrumb (e.g. "OVPAA > College of Nursing > BSN")
     * - sub_unit_count: Direct children count
     * - type: 'executive' (tier 1), 'college', 'program', 'admin'
     *
     * @return array
     */
    public function getEnrichedHierarchyUnits(): array
    {
        $units = $this->orderBy('name', 'ASC')->findAll();
        $byId = [];
        $childrenByParent = [];
        foreach ($units as $u) {
            $byId[$u['id']] = $u;
            if (!empty($u['parent_id'])) {
                $childrenByParent[$u['parent_id']][] = $u['id'];
            }
        }

        $memo = [];
        $resolve = function($id) use (&$resolve, &$byId, &$memo) {
            if (isset($memo[$id])) return $memo[$id];
            if (!isset($byId[$id])) {
                return ['tier' => 1, 'path' => [], 'root_id' => $id, 'root_name' => ''];
            }
            $unit = $byId[$id];
            $pId = $unit['parent_id'] ?? null;
            if (empty($pId) || !isset($byId[$pId]) || $pId == $id) {
                return $memo[$id] = [
                    'tier'      => 1,
                    'path'      => [$unit['name']],
                    'root_id'   => $id,
                    'root_name' => $unit['name'],
                ];
            }
            $p = $resolve($pId);
            return $memo[$id] = [
                'tier'      => $p['tier'] + 1,
                'path'      => array_merge($p['path'], [$unit['name']]),
                'root_id'   => $p['root_id'],
                'root_name' => $p['root_name'],
            ];
        };

        $enriched = [];
        foreach ($units as $u) {
            $info = $resolve($u['id']);
            $tier = $info['tier'];

            // From the structure, not the unit's name: a top-level unit is an executive office; a unit headed by a
            // teaching position is a college (one level down) or a program below it; anything else is an office.
            $org = \App\Libraries\OrgStructure::instance();
            $academic = (bool) array_filter($org->headPositionIds((int) $u['id']), fn($pid) => (int) ($org->position($pid)['is_teaching'] ?? 0) === 1);
            if ($tier === 1) {
                $type = 'executive';
            } elseif ($academic) {
                $type = $tier === 2 ? 'college' : 'program';
            } else {
                $type = 'admin';
            }

            $u['tier']           = $tier;
            $u['path']           = $info['path'];
            $u['path_str']       = implode(' > ', $info['path']);
            $u['root_id']        = $info['root_id'];
            $u['root_name']      = $info['root_name'];
            $u['parent_name']    = (!empty($u['parent_id']) && isset($byId[$u['parent_id']])) ? $byId[$u['parent_id']]['name'] : null;
            $u['sub_unit_count'] = count($childrenByParent[$u['id']] ?? []);
            $u['type']           = $type;

            $enriched[] = $u;
        }

        usort($enriched, function($a, $b) {
            if ($a['tier'] !== $b['tier']) {
                return $a['tier'] <=> $b['tier'];
            }
            return strcmp($a['path_str'], $b['path_str']);
        });

        return $enriched;
    }

    /**
     * Resolves an organizational tree where root units (Tier 1) have their children (Tier 2),
     * and those have their children (Tier 3), nested recursively.
     *
     * @return array
     */
    public function getHierarchyTree(): array
    {
        $enriched = $this->getEnrichedHierarchyUnits();
        $byId = [];
        $childrenMap = [];

        foreach ($enriched as $u) {
            $byId[$u['id']] = $u;
            $pId = $u['parent_id'] ?? null;
            if (!empty($pId)) {
                $childrenMap[$pId][] = $u['id'];
            }
        }

        $buildNode = function($uId) use (&$buildNode, &$byId, &$childrenMap) {
            $node = $byId[$uId];
            $childIds = $childrenMap[$uId] ?? [];
            $childNodes = [];
            foreach ($childIds as $cId) {
                if (isset($byId[$cId])) {
                    $childNodes[] = $buildNode($cId);
                }
            }
            usort($childNodes, fn($a, $b) => strcmp($a['name'], $b['name']));
            $node['children'] = $childNodes;
            return $node;
        };

        $tree = [];
        foreach ($enriched as $u) {
            $pId = $u['parent_id'] ?? null;
            if (empty($pId) || !isset($byId[$pId])) {
                $tree[] = $buildNode($u['id']);
            }
        }

        usort($tree, fn($a, $b) => strcmp($a['name'], $b['name']));
        return $tree;
    }





}
