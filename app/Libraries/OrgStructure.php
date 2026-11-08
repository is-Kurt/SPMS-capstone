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
 * (junction tables role_doc_types / employment_status_doc_types; no rows means no limit).
 *
 * One instance per request (instance()); call reset() after changing units, positions or rules.
 */
class OrgStructure
{
    private ?bool $statusLinksReady = null;
    private array $statusOpenCache = [];

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

    /**
     * Positions a member (not the head) of the unit can hold: the non-head positions directly under its head
     * position(s), plus General. A position's parent is who it reports to, so deeper positions belong to the
     * units below (e.g. faculty ranks sit under Department Chair, not under Dean).
     */
    public function memberPositionIds(int $unitId): array
    {
        $out = [];
        foreach ($this->headPositionIds($unitId) as $h) {
            foreach ($this->posChildren[$h] ?? [] as $id) {
                if (!$this->isHeadPosition($id)) $out[$id] = $id;
            }
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

    /**
     * Every position a Staff account can hold in this unit: its head position(s) first, then the positions directly
     * under the head and the General ones. Taking a head position makes the person a Supervisor (role follows
     * position, see syncRatedRole); any other makes them an Employee.
     */
    public function positionsForUnit(int $unitId): array
    {
        return array_values(array_unique(array_merge($this->headPositionIds($unitId), $this->memberPositionIds($unitId))));
    }

    /**
     * Role follows position for Staff (rated) accounts: Supervisor when they hold a head position, Employee
     * otherwise. Admin, TWG and PMT are left alone. Returns [old, new] when it changed the role, else null.
     */
    public function syncRatedRole(int $userId): ?array
    {
        $old = $this->roleOf($userId);
        if (!self::isRatedRole($old)) return null;
        $new = $this->isHead($userId) ? 'Supervisor' : 'Employee';
        if ($new === $old) return null;
        $roleId = $this->db->table('roles')->select('id')->where('name', $new)->get()->getRowArray()['id'] ?? null;
        if (!$roleId) return null;
        $this->db->table('user_roles')->where('user_id', $userId)->delete();
        $this->db->table('user_roles')->insert(['user_id' => $userId, 'role_id' => $roleId]);
        return [$old, $new];
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

    /**
     * The people who answer for a unit's members: its own head(s), or, when nobody holds that head position
     * (a vacant department), the nearest head above it. User ids; empty when there is none up to the top.
     */
    public function nearestHeadIdsOf(int $unitId): array
    {
        for ($id = $unitId, $guard = 0; $id && $guard < 50; $guard++) {
            $heads = array_map('intval', array_column($this->headsOf($id), 'id'));
            if ($heads) return $heads;
            $id = (int) ($this->units[$id]['parent_id'] ?? 0);
        }
        return [];
    }

    /**
     * The supervisors' Accounts tab: every Staff assignment in their branch (the units they head and all below),
     * except their own and those of co-heads of their own units. Heads of units below are included: a Dean manages
     * up to the Department Chairs. Rows: plantilla id, user, unit, position, head (holds that unit's head position).
     */
    public function branchAssignments(int $supervisorId): array
    {
        // Editing power comes from the units they head (not every unit they hold a position in) and all below.
        $own = $this->headedUnitIds($supervisorId);
        $reach = $this->unitWithDescendants($own);
        if (!$reach) return [];
        $rows = $this->db->table('plantillas p')
            ->select('p.id AS plantilla_id, p.unit_id, p.position_id, p.employment_status AS assignment_status, p.started_at, u.id AS user_id, u.first_name, u.last_name, u.email, u.doc_type, u.employment_status')
            ->join('users u', 'u.id = p.user_id')
            ->where('p.ended_at IS NULL')->where('u.is_active', 1)->where('u.id !=', $supervisorId)
            ->whereIn('p.unit_id', $reach)->orderBy('u.last_name', 'ASC')->orderBy('u.first_name', 'ASC')
            ->get()->getResultArray();
        $out = [];
        foreach ($rows as $r) {
            $unitId = (int) $r['unit_id'];
            if (!$r['position_id'] || !self::isRatedRole($this->roleOf((int) $r['user_id']))) continue;
            $r['head'] = $this->isHeadOf($unitId, (int) $r['position_id']);
            if ($r['head'] && in_array($unitId, $own, true)) continue;      // a co-head of their own unit: the admin
            $out[] = $r;
        }
        return $out;
    }

    /** The units a supervisor's Accounts tab covers: the units they head and everything below. */
    public function branchUnitIds(int $supervisorId): array
    {
        return $this->unitWithDescendants($this->headedUnitIds($supervisorId));
    }

    /** The branch assignment row a supervisor may edit, or null. */
    public function branchAssignment(int $supervisorId, int $plantillaId): ?array
    {
        foreach ($this->branchAssignments($supervisorId) as $r) {
            if ((int) $r['plantilla_id'] === $plantillaId) return $r;
        }
        return null;
    }

    /**
     * Positions a supervisor may give someone in a unit of their branch: in a unit below them, its head position(s)
     * and the positions under it; in their own unit, only the positions under it (nobody is raised to their level).
     */
    public function positionsForUnitBy(int $supervisorId, int $unitId): array
    {
        $own = $this->headedUnitIds($supervisorId);
        if (!in_array($unitId, $this->unitWithDescendants($own), true)) return [];
        return in_array($unitId, $own, true) ? $this->memberPositionIds($unitId) : $this->positionsForUnit($unitId);
    }

    /** The units a person reaches in My Staff and on the dashboard: their units and everything below (all assignments merged). */
    public function reachUnitIds(int $userId): array
    {
        return $this->unitWithDescendants($this->unitIdsOf($userId));
    }

    // ---------------------------------------------------------------- doc types

    /**
     * Doc types a role / an employment status allows (rows of role_doc_types / employment_status_doc_types),
     * in enum order; null when it has no rows (no limit).
     */
    public function docRule(string $kind, ?string $name): ?array
    {
        if ($name === null || $name === '') return null;
        [$parent, $table, $key] = $kind === 'role'
            ? ['roles', 'role_doc_types', 'role_id']
            : ['employment_statuses', 'employment_status_doc_types', 'employment_status_id'];
        $codes = array_column($this->db->table("{$table} t")->select('t.doc_type')
            ->join("{$parent} p", "p.id = t.{$key}")->where('LOWER(p.name)', strtolower($name))
            ->get()->getResultArray(), 'doc_type');
        if (!$codes) return null;
        return array_values(array_filter(DocType::values(), fn($t) => in_array($t, $codes, true)));
    }

    /** Allowed doc types for a role and an employment status (intersection, enum order). */
    public function allowedDocTypes(?string $role, ?string $status): array
    {
        $byRole = $this->docRule('role', $role);
        $byStatus = $this->docRule('status', $status);
        return array_values(array_filter(DocType::values(), fn($t) => ($byRole === null || in_array($t, $byRole, true))
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
     * Doc type to use when a person has none set: the first one the rules allow. When the role and status
     * rules allow nothing together (the directory flags that person), the role's own first type, so a
     * Supervisor still gets a supervisor form (DPCR / OPCR); IPCR only when the role has no rule either.
     */
    public function defaultDocTypeFor(int $userId): string
    {
        return $this->allowedDocTypesForUser($userId)[0]
            ?? $this->docRule('role', $this->roleOf($userId))[0]
            ?? DocType::IPCR->value;
    }

    // ---------------------------------------------------------------- role / employment status link

    /** Is the role / employment status link stored yet (migration 000023)? Without it every status is open. */
    public function hasStatusLinks(): bool
    {
        return $this->statusLinksReady ??= $this->db->tableExists('role_employment_statuses');
    }

    /** Employment status names open to a rated role (System Data > Employment Statuses); null = every status. */
    public function statusesOpenTo(?string $role): ?array
    {
        if (!self::isRatedRole($role) || !$this->hasStatusLinks()) return null;
        return $this->statusOpenCache[$role] ??= array_column($this->db->table('role_employment_statuses rs')->select('es.name')
            ->join('roles r', 'r.id = rs.role_id')->join('employment_statuses es', 'es.id = rs.employment_status_id')
            ->where('r.name', $role)->get()->getResultArray(), 'name');
    }

    /** May a person with this role have this employment status? (Always true for Admin / TWG / PMT.) */
    public function isStatusOpenTo(?string $role, ?string $status): bool
    {
        $open = $this->statusesOpenTo($role);
        if ($open === null || $status === null || $status === '') return true;
        foreach ($open as $name) if (strcasecmp($name, $status) === 0) return true;
        return false;
    }

    /** Roles that fill in a form of their own (Admin, TWG and PMT review, they are not rated). */
    public static function isRatedRole(?string $role): bool
    {
        return in_array($role, ['Employee', 'Supervisor'], true);
    }
}
