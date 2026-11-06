<?php

namespace App\Libraries;

use App\Enums\DocType;

/**
 * The organisation structure in one place (2026-11-07). Nothing here looks at job titles or emails.
 *
 * Positions form a tree. A position with child positions is a HEAD position; one special root marked
 * is_general_root (shown as "General") is not a position anyone holds: its children are the general
 * positions anyone below a head can pick, in any unit.
 *
 * Units form a tree too. A unit may be LINKED to a position (units.head_position_id): that position heads
 * the unit. A unit without a link takes the nearest linked ancestor's position and steps one level down
 * the position tree per unit level ("OVPAA -> VPAA" gives "College -> Dean", "Department -> Dept Chair").
 * When the position tree runs out of head levels, the deepest one is reused (the nearest). A level can
 * hold several head positions (e.g. Dean and Director); then a Supervisor picks which one is theirs.
 *
 * Doc types allowed for a person = allowed for their role AND for their employment status
 * (roles.allowed_doc_types / employment_statuses.allowed_doc_types; NULL means no limit).
 *
 * One instance per request (instance()); call reset() after changing units, positions or rules.
 */
class OrgStructure
{
    private static ?OrgStructure $instance = null;

    private $db;
    /** @var array<int,array> */ private array $units = [];
    /** @var array<int,array> */ private array $positions = [];
    /** @var array<int,int[]> */ private array $unitChildren = [];
    /** @var array<int,int[]> */ private array $posChildren = [];
    private array $headCache = [];

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    public static function reset(): void
    {
        self::$instance = null;
    }

    private function __construct()
    {
        $this->db = \Config\Database::connect();
        foreach ($this->db->table('units')->get()->getResultArray() as $u) {
            $u['id'] = (int) $u['id'];
            $u['parent_id'] = $u['parent_id'] ? (int) $u['parent_id'] : null;
            $u['head_position_id'] = !empty($u['head_position_id']) ? (int) $u['head_position_id'] : null;
            $this->units[$u['id']] = $u;
        }
        foreach ($this->units as $u) {
            if ($u['parent_id'] !== null && isset($this->units[$u['parent_id']])) {
                $this->unitChildren[$u['parent_id']][] = $u['id'];
            }
        }
        foreach ($this->db->table('positions')->orderBy('title', 'ASC')->get()->getResultArray() as $p) {
            $p['id'] = (int) $p['id'];
            $p['parent_id'] = !empty($p['parent_id']) ? (int) $p['parent_id'] : null;
            $p['is_general_root'] = (int) ($p['is_general_root'] ?? 0);
            $this->positions[$p['id']] = $p;
        }
        foreach ($this->positions as $p) {
            if ($p['parent_id'] !== null && isset($this->positions[$p['parent_id']])) {
                $this->posChildren[$p['parent_id']][] = $p['id'];
            }
        }
    }

    // ---------------------------------------------------------------- positions

    public function positions(): array { return $this->positions; }
    public function position(?int $id): ?array { return $id !== null ? ($this->positions[$id] ?? null) : null; }
    public function positionChildren(int $id): array { return $this->posChildren[$id] ?? []; }

    /** The "General" root(s): containers, never held or linked. */
    public function generalRootIds(): array
    {
        return array_keys(array_filter($this->positions, fn($p) => $p['is_general_root'] === 1));
    }

    /** True for a General root or anything under one. */
    public function isGeneral(int $posId): bool
    {
        for ($id = $posId, $guard = 0; $id !== null && isset($this->positions[$id]) && $guard < 50; $guard++) {
            if ($this->positions[$id]['is_general_root'] === 1) return true;
            $id = $this->positions[$id]['parent_id'];
        }
        return false;
    }

    /** A head position has child positions (and is not General). */
    public function isHeadPosition(int $posId): bool
    {
        return isset($this->positions[$posId]) && !$this->isGeneral($posId) && !empty($this->posChildren[$posId]);
    }

    /** Positions under $posId (any depth) that have no children: what members of that branch hold. */
    public function leafPositionsUnder(int $posId): array
    {
        $out = [];
        $stack = $this->posChildren[$posId] ?? [];
        while ($stack) {
            $id = array_pop($stack);
            if (empty($this->posChildren[$id])) $out[$id] = $id;
            else array_push($stack, ...$this->posChildren[$id]);
        }
        return array_values($out);
    }

    /** General positions anyone below a head can pick (the leaves under the General root). */
    public function generalPositionIds(): array
    {
        $out = [];
        foreach ($this->generalRootIds() as $root) {
            foreach ($this->leafPositionsUnder($root) as $id) $out[$id] = $id;
        }
        return array_values($out);
    }

    /** Would making $parentId the parent of $posId create a loop? */
    public function wouldLoop(int $posId, ?int $parentId): bool
    {
        for ($id = $parentId, $guard = 0; $id !== null && $guard < 200; $guard++) {
            if ($id === $posId) return true;
            $id = $this->positions[$id]['parent_id'] ?? null;
        }
        return false;
    }

    // ---------------------------------------------------------------- units

    public function units(): array { return $this->units; }
    public function unit(?int $id): ?array { return $id !== null ? ($this->units[$id] ?? null) : null; }

    /** The unit and all units below it. */
    public function unitWithDescendants(array $unitIds): array
    {
        $out = [];
        $stack = array_map('intval', array_filter($unitIds));
        while ($stack) {
            $id = array_pop($stack);
            if (isset($out[$id]) || !isset($this->units[$id])) continue;
            $out[$id] = $id;
            array_push($stack, ...($this->unitChildren[$id] ?? []));
        }
        return array_values($out);
    }

    /** [linked unit id, its position id, how many levels up] for the nearest unit (itself first) with a link. */
    public function nearestLink(int $unitId): ?array
    {
        for ($id = $unitId, $d = 0; $id !== null && isset($this->units[$id]) && $d < 50; $d++) {
            $pos = $this->units[$id]['head_position_id'];
            if ($pos !== null && isset($this->positions[$pos]) && !$this->isGeneral($pos)) {
                return [$id, $pos, $d];
            }
            $id = $this->units[$id]['parent_id'];
        }
        return null;
    }

    /**
     * Head position(s) of a unit. A linked unit is headed by its linked position; otherwise start at the
     * nearest linked ancestor's position and step down one head level per unit level (keeping the last
     * level when the position tree has no deeper head level). Empty when no ancestor is linked.
     */
    public function headPositionIds(int $unitId): array
    {
        if (array_key_exists($unitId, $this->headCache)) return $this->headCache[$unitId];
        $link = $this->nearestLink($unitId);
        if ($link === null) return $this->headCache[$unitId] = [];
        [, $pos, $distance] = $link;
        $level = [$pos];
        for ($i = 0; $i < $distance; $i++) {
            $next = [];
            foreach ($level as $p) {
                foreach ($this->posChildren[$p] ?? [] as $c) {
                    if ($this->isHeadPosition($c)) $next[$c] = $c;
                }
            }
            if (!$next) break;                                       // no deeper head level: keep the nearest
            $level = array_values($next);
        }
        return $this->headCache[$unitId] = array_values($level);
    }

    /** Positions a member (not the head) of the unit can hold: the leaves under its head position(s), plus General. */
    public function memberPositionIds(int $unitId): array
    {
        $out = [];
        foreach ($this->headPositionIds($unitId) as $h) {
            foreach ($this->leafPositionsUnder($h) as $id) $out[$id] = $id;
        }
        foreach ($this->generalPositionIds() as $id) $out[$id] = $id;
        return array_values($out);
    }

    /**
     * Positions a person can pick for themselves in this unit (sign-up): a Supervisor is the unit's head,
     * an Employee holds a member position. Admin / TWG / PMT have no department assignment.
     */
    public function positionChoicesFor(?string $role, int $unitId): array
    {
        $ids = match ($role) {
            'Supervisor' => $this->headPositionIds($unitId),
            'Employee'   => $this->memberPositionIds($unitId),
            default      => [],
        };
        usort($ids, fn($a, $b) => strcasecmp($this->positions[$a]['title'] ?? '', $this->positions[$b]['title'] ?? ''));
        return $ids;
    }

    /** Is $posId one of the unit's head positions? */
    public function isHeadOf(int $unitId, int $posId): bool
    {
        return in_array($posId, $this->headPositionIds($unitId), true);
    }

    // ---------------------------------------------------------------- people

    /** Current assignments: [{id, unit_id, position_id}] */
    public function assignmentsOf(int $userId): array
    {
        return $this->db->table('plantillas')->select('id, unit_id, position_id')
            ->where('user_id', $userId)->where('ended_at IS NULL')->orderBy('id', 'ASC')
            ->get()->getResultArray();
    }

    public function unitIdsOf(int $userId): array
    {
        return array_values(array_unique(array_map('intval', array_filter(array_column($this->assignmentsOf($userId), 'unit_id')))));
    }

    /** Units this person heads (their assignment there holds one of the unit's head positions). */
    public function headedUnitIds(int $userId): array
    {
        $out = [];
        foreach ($this->assignmentsOf($userId) as $a) {
            if ($a['unit_id'] && $a['position_id'] && $this->isHeadOf((int) $a['unit_id'], (int) $a['position_id'])) {
                $out[(int) $a['unit_id']] = (int) $a['unit_id'];
            }
        }
        return array_values($out);
    }

    /** Titles of the head positions this person holds (e.g. ['Dean']), for labels. */
    public function headTitlesOf(int $userId): array
    {
        $out = [];
        foreach ($this->assignmentsOf($userId) as $a) {
            if ($a['unit_id'] && $a['position_id'] && $this->isHeadOf((int) $a['unit_id'], (int) $a['position_id'])) {
                $out[] = $this->positions[(int) $a['position_id']]['title'] ?? '';
            }
        }
        return array_values(array_unique(array_filter($out)));
    }

    /** Heads a unit that has sub-units (e.g. a college): its DPCR is collegiate / divisional. */
    public function headsUnitWithSubunits(int $userId): bool
    {
        foreach ($this->headedUnitIds($userId) as $u) {
            if (!empty($this->unitChildren[$u])) return true;
        }
        return false;
    }

    public function isHead(int $userId): bool
    {
        return $this->headedUnitIds($userId) !== [];
    }

    /** Heads a top-level unit (e.g. a Vice President): their forms report to the Admin. */
    public function isTopHead(int $userId): bool
    {
        foreach ($this->headedUnitIds($userId) as $u) {
            if (($this->units[$u]['parent_id'] ?? null) === null) return true;
        }
        return false;
    }

    /** Active people currently holding a head position of the unit (co-heads possible). */
    public function headsOf(int $unitId, ?int $excludeUserId = null): array
    {
        $heads = $this->headPositionIds($unitId);
        if (!$heads) return [];
        $b = $this->db->table('plantillas p')->select('u.id, u.first_name, u.last_name, u.email, p.position_id')
            ->join('users u', 'u.id = p.user_id')
            ->where('p.unit_id', $unitId)->where('p.ended_at IS NULL')->where('u.is_active', 1)
            ->whereIn('p.position_id', $heads);
        if ($excludeUserId !== null) $b->where('u.id !=', $excludeUserId);
        return $b->groupBy('u.id')->get()->getResultArray();
    }

    /** The units a person reaches in My Staff and on the dashboard: their units and everything below (all assignments merged). */
    public function reachUnitIds(int $userId): array
    {
        return $this->unitWithDescendants($this->unitIdsOf($userId));
    }

    // ---------------------------------------------------------------- doc types

    /** Allowed doc types for a role and an employment status (intersection, enum order). */
    public function allowedDocTypes(?string $role, ?string $status): array
    {
        $byRole = $role !== null
            ? DocType::fromCsv($this->db->table('roles')->select('allowed_doc_types')->where('name', $role)->get()->getRowArray()['allowed_doc_types'] ?? null)
            : null;
        $byStatus = $status !== null && $status !== ''
            ? DocType::fromCsv($this->db->table('employment_statuses')->select('allowed_doc_types')->where('LOWER(name)', strtolower($status))->get()->getRowArray()['allowed_doc_types'] ?? null)
            : null;
        $all = DocType::values();
        return array_values(array_filter($all, fn($t) => ($byRole === null || in_array($t, $byRole, true))
                                                    && ($byStatus === null || in_array($t, $byStatus, true))));
    }

    public function roleOf(int $userId): ?string
    {
        return $this->db->table('user_roles ur')->select('r.name')->join('roles r', 'r.id = ur.role_id')
            ->where('ur.user_id', $userId)->get()->getRowArray()['name'] ?? null;
    }

    public function allowedDocTypesForUser(int $userId): array
    {
        $user = $this->db->table('users')->select('employment_status')->where('id', $userId)->get()->getRowArray();
        return $this->allowedDocTypes($this->roleOf($userId), $user['employment_status'] ?? null);
    }

    /**
     * Doc type to use when a person has none set: the only allowed one, or the first allowed one,
     * or IPCR when the rules allow nothing (the directory flags that person).
     */
    public function defaultDocTypeFor(int $userId): string
    {
        return $this->allowedDocTypesForUser($userId)[0] ?? DocType::IPCR->value;
    }

    /** Roles that fill in a form of their own (Admin, TWG and PMT review, they are not rated). */
    public static function isRatedRole(?string $role): bool
    {
        return in_array($role, ['Employee', 'Supervisor'], true);
    }
}
