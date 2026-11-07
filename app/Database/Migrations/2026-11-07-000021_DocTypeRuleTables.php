<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Doc-type rules as junction tables (2026-11-07), replacing the comma-separated
 * roles.allowed_doc_types / employment_statuses.allowed_doc_types columns (one value per field).
 *
 *   role_doc_types               (role_id, doc_type)               one row per allowed form type
 *   employment_status_doc_types  (employment_status_id, doc_type)
 *
 * No rows for a role / status = no limit. doc_type holds a DocType code (IPCR, DPCR, OPCR, IPERF): the form
 * types themselves stay in code (App\Enums\DocType) because each has its own template and behaviour.
 * Deleting a role or status deletes its rule rows (ON DELETE CASCADE).
 */
class DocTypeRuleTables extends Migration
{
    /** Asks SQLite directly: CodeIgniter caches field lists per connection, so a column added by an earlier
     *  migration in the same run may be missing from fieldExists(). */
    private function hasColumn(string $table, string $column): bool
    {
        return (bool) $this->db->query("SELECT 1 FROM pragma_table_info('{$table}') WHERE name = ?", [$column])->getRowArray();
    }

    public function up()
    {
        foreach ([['role_doc_types', 'role_id', 'roles'], ['employment_status_doc_types', 'employment_status_id', 'employment_statuses']] as [$table, $key, $parent]) {
            if ($this->db->tableExists($table)) continue;
            $this->forge->addField([
                'id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                $key       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'doc_type' => ['type' => 'VARCHAR', 'constraint' => 10],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addUniqueKey([$key, 'doc_type']);
            $this->forge->addForeignKey($key, $parent, 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable($table, true);
        }

        // Move the existing rules over (an empty list meant "nothing allowed", which nobody uses: it becomes no rows).
        foreach ([['roles', 'role_doc_types', 'role_id'], ['employment_statuses', 'employment_status_doc_types', 'employment_status_id']] as [$parent, $table, $key]) {
            if (!$this->hasColumn($parent, 'allowed_doc_types')) continue;
            foreach ($this->db->table($parent)->select('id, allowed_doc_types')->get()->getResultArray() as $row) {
                foreach (array_filter(array_map(fn($v) => strtoupper(trim($v)), explode(',', (string) $row['allowed_doc_types']))) as $type) {
                    if (in_array($type, ['IPCR', 'DPCR', 'OPCR', 'IPERF'], true)) {
                        $this->db->table($table)->ignore(true)->insert([$key => $row['id'], 'doc_type' => $type]);
                    }
                }
            }
            // SQLite 3.35+ drops a plain column in place (no table rebuild, so no foreign key side effects).
            $this->db->query("ALTER TABLE {$parent} DROP COLUMN allowed_doc_types");
        }
    }

    public function down()
    {
        foreach ([['roles', 'role_doc_types', 'role_id'], ['employment_statuses', 'employment_status_doc_types', 'employment_status_id']] as [$parent, $table, $key]) {
            if (!$this->hasColumn($parent, 'allowed_doc_types')) {
                $this->forge->addColumn($parent, ['allowed_doc_types' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true]]);
            }
            if ($this->db->tableExists($table)) {
                foreach ($this->db->table($table)->select("{$key} as id, GROUP_CONCAT(doc_type) as csv")->groupBy($key)->get()->getResultArray() as $r) {
                    $this->db->table($parent)->where('id', $r['id'])->update(['allowed_doc_types' => $r['csv']]);
                }
                $this->forge->dropTable($table, true);
            }
        }
    }
}
