<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * document_folders.owner_snapshot (2026-11-07): the position(s) and department(s) a paper's owner had when the
 * paper was created, as JSON {"roles": [{"position": ..., "dept": ...}], "at": ...}. Papers print this instead of
 * the owner's current assignment, so a later change no longer rewrites submitted or approved papers.
 *
 * Existing papers are filled once from their owner's current assignments (the best information there is), or the
 * last ones they held when they have none now. NULL = the paper prints the owner's current details.
 */
class PaperOwnerSnapshot extends Migration
{
    private function hasColumn(string $table, string $column): bool
    {
        return (bool) $this->db->query("SELECT 1 FROM pragma_table_info('{$table}') WHERE name = ?", [$column])->getRowArray();
    }

    public function up()
    {
        if (!$this->hasColumn('document_folders', 'owner_snapshot')) {
            $this->forge->addColumn('document_folders', ['owner_snapshot' => ['type' => 'TEXT', 'null' => true]]);
        }
        $now = date('Y-m-d H:i:s');
        $owners = $this->db->query("SELECT DISTINCT user_id FROM document_folders WHERE user_id IS NOT NULL AND owner_snapshot IS NULL")->getResultArray();
        foreach (array_column($owners, 'user_id') as $userId) {
            $rows = $this->db->query("SELECT pos.title AS position, un.name AS dept, p.ended_at FROM plantillas p
                                      JOIN positions pos ON pos.id = p.position_id LEFT JOIN units un ON un.id = p.unit_id
                                      WHERE p.user_id = ? ORDER BY p.id", [$userId])->getResultArray();
            $current = array_values(array_filter($rows, fn($r) => $r['ended_at'] === null));
            if (!$current && $rows) {
                $last = max(array_column($rows, 'ended_at'));
                $current = array_values(array_filter($rows, fn($r) => $r['ended_at'] === $last));
            }
            if (!$current) continue;
            $roles = array_map(fn($r) => ['position' => (string) $r['position'], 'dept' => (string) ($r['dept'] ?? '')], $current);
            $this->db->table('document_folders')->where('user_id', $userId)->where('owner_snapshot IS NULL')
                ->update(['owner_snapshot' => json_encode(['roles' => $roles, 'at' => $now])]);
        }
    }

    public function down()
    {
        if ($this->hasColumn('document_folders', 'owner_snapshot')) {
            $this->db->query('ALTER TABLE document_folders DROP COLUMN owner_snapshot');
        }
    }
}
