<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Organisation structure (2026-11-07):
 * - positions form a tree (parent_id); one special root marked is_general_root holds the positions
 *   anyone can pick (e.g. Administrative Aide). A position with child positions is a head position.
 * - a unit can be linked to the head position at its level (head_position_id); units without a link
 *   inherit it from the nearest linked ancestor (App\Libraries\OrgStructure works out the rest).
 * - employment status belongs to the person (users.employment_status), not to each assignment.
 * - which doc types a role / an employment status allows (CSV of DocType values; NULL = no limit).
 * - an invitation carries the department the admin picked (invitations.unit_id).
 * Columns are added without database foreign keys (SQLite cannot add them to an existing table);
 * the code checks the references.
 */
class OrgStructure extends Migration
{
    private function add(string $table, string $field, array $def): void
    {
        if (! $this->db->fieldExists($field, $table)) {
            $this->forge->addColumn($table, [$field => $def]);
        }
    }

    public function up()
    {
        $intNull = ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true];

        $this->add('positions', 'parent_id', $intNull);
        $this->add('positions', 'is_general_root', ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => false]);
        $this->add('units', 'head_position_id', $intNull);
        $this->add('users', 'employment_status', ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true]);
        $this->add('roles', 'allowed_doc_types', ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true]);
        $this->add('employment_statuses', 'allowed_doc_types', ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true]);
        $this->add('invitations', 'unit_id', $intNull);

        // Employment status moves to the person: take it from their current assignment (nobody holds
        // assignments with different statuses today), else from any assignment, else Permanent.
        $this->db->query("
            UPDATE users SET employment_status = COALESCE(
                (SELECT p.employment_status FROM plantillas p WHERE p.user_id = users.id AND p.ended_at IS NULL
                   AND p.employment_status IS NOT NULL AND p.employment_status <> '' ORDER BY p.id LIMIT 1),
                (SELECT p.employment_status FROM plantillas p WHERE p.user_id = users.id
                   AND p.employment_status IS NOT NULL AND p.employment_status <> '' ORDER BY p.id DESC LIMIT 1),
                'Permanent')
            WHERE employment_status IS NULL OR employment_status = ''");

        // Assignments recorded without a start date start when the account was created.
        $this->db->query("
            UPDATE plantillas SET started_at = (SELECT DATE(u.created_at) FROM users u WHERE u.id = plantillas.user_id)
            WHERE started_at IS NULL");
    }

    public function down()
    {
        foreach ([['positions', 'parent_id'], ['positions', 'is_general_root'], ['units', 'head_position_id'],
                  ['users', 'employment_status'], ['roles', 'allowed_doc_types'],
                  ['employment_statuses', 'allowed_doc_types'], ['invitations', 'unit_id']] as [$t, $f]) {
            if ($this->db->fieldExists($f, $t)) {
                $this->forge->dropColumn($t, $f);
            }
        }
    }
}
