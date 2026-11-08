<?php

namespace App\Libraries;

/**
 * Adding and ending a person's assignments (unit + position), shared by the admin's Manage (User Directory) and a
 * supervisor's Accounts tab (2026-11-08). The callers check who may give which position where; this does the change
 * and everything that follows it: the role follows the position, draft papers show it, out-of-scope reviews are
 * revoked, and it is written to the audit log. Assignments are history: a row is ended, never edited or deleted.
 */
final class Assignments
{
    /**
     * Adds a current assignment. Returns ['ok' => bool, 'message' => string, 'role' => new role or null,
     * 'co_heads' => names of others who head the unit too].
     */
    public static function add(array $user, int $unitId, int $positionId): array
    {
        $org = OrgStructure::instance();
        $unit = $org->unit($unitId);
        $pos = $org->position($positionId);
        if (!$unit || !$pos) return ['ok' => false, 'message' => 'Select a department / unit and a position.'];
        $db = \Config\Database::connect();
        if ($db->table('plantillas')->where('user_id', $user['id'])->where('unit_id', $unitId)->where('position_id', $positionId)->where('ended_at IS NULL')->countAllResults()) {
            return ['ok' => false, 'message' => 'They already hold this position in this unit.'];
        }
        $coHeads = $org->isHeadOf($unitId, $positionId) ? $org->headsOf($unitId, (int) $user['id']) : [];
        $db->table('plantillas')->insert([
            'user_id' => $user['id'], 'unit_id' => $unitId, 'position_id' => $positionId,
            'employment_status' => $user['employment_status'], 'started_at' => date('Y-m-d'), 'created_at' => date('Y-m-d H:i:s'),
        ]);
        $role = self::after((int) $user['id']);
        $docNote = self::docNote($user);
        audit_log('ASSIGNMENT_ADDED', 'ACCOUNT', 'user', (int) $user['id'], "{$user['email']}: {$pos['title']}, {$unit['name']}");
        $names = array_map(fn($h) => trim($h['first_name'] . ' ' . $h['last_name']), $coHeads);
        $msg = "Added: {$pos['title']}, {$unit['name']}." . ($role ? ' They are now ' . staff_role_label($role) . '.' : '') . $docNote
             . ($names ? ' ' . implode(', ', $names) . ' also heads this unit (co-heads).' : '');
        return ['ok' => true, 'message' => $msg, 'role' => $role, 'co_heads' => $names, 'title' => $pos['title'], 'unit' => $unit['name']];
    }

    /** Ends a current assignment today (kept as history). Same return shape as add(). */
    public static function end(array $user, int $plantillaId): array
    {
        $db = \Config\Database::connect();
        $row = $db->table('plantillas p')->select('p.id, un.name as unit_name, pos.title')
            ->join('units un', 'un.id = p.unit_id', 'left')->join('positions pos', 'pos.id = p.position_id', 'left')
            ->where('p.id', $plantillaId)->where('p.user_id', $user['id'])->where('p.ended_at IS NULL')
            ->get()->getRowArray();
        if (!$row) return ['ok' => false, 'message' => 'That assignment is not current.'];
        $db->table('plantillas')->where('id', $row['id'])->update(['ended_at' => date('Y-m-d'), 'updated_at' => date('Y-m-d H:i:s')]);
        $role = self::after((int) $user['id']);
        $docNote = self::docNote($user);
        audit_log('ASSIGNMENT_ENDED', 'ACCOUNT', 'user', (int) $user['id'], "{$user['email']}: {$row['title']}, {$row['unit_name']} ended");
        return ['ok' => true, 'message' => "Ended: {$row['title']}, {$row['unit_name']}." . ($role ? ' They are now ' . staff_role_label($role) . '.' : '') . $docNote,
                'role' => $role, 'co_heads' => [], 'title' => $row['title'], 'unit' => $row['unit_name']];
    }

    /** " Form type: IPCR -> DPCR." when the change moved their form type (sync_rated_role), with the other choices. */
    private static function docNote(array $before): string
    {
        $now = \Config\Database::connect()->table('users')->select('doc_type')->where('id', $before['id'])->get()->getRowArray();
        $old = strtoupper((string) ($before['doc_type'] ?? '')); $new = strtoupper((string) ($now['doc_type'] ?? ''));
        if ($old === $new) return '';
        $other = array_values(array_diff(OrgStructure::instance()->allowedDocTypesForUser((int) $before['id']), [$new]));
        return " Form type: " . ($old ?: 'none') . " -> {$new}" . ($other ? ' (also allowed: ' . implode(', ', $other) . '; change it in Form type if needed)' : '') . '.';
    }

    /** What follows any assignment change. Returns the new role when it changed. */
    private static function after(int $userId): ?string
    {
        OrgStructure::reset();
        refresh_draft_paper_snapshots([$userId]);   // draft papers show the current assignments
        $role = sync_rated_role($userId);           // a head position makes them a unit head
        reconcile_reviews();                        // reviewers who went out of scope lose the review
        return $role;
    }
}
