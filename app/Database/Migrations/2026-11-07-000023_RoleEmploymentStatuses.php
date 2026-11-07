<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Which employment statuses each rated role may have (2026-11-07), e.g. a Supervisor only Permanent.
 *
 *   role_employment_statuses (role_id, employment_status_id)   one row per allowed pair
 *
 * Set per status in System Data > Employment Statuses ("Open to: Employee / Supervisor"); every status is open
 * to at least one role. Only Employee and Supervisor use it (Admin, TWG and PMT have no status rules).
 * Every existing status starts open to both roles, so nothing changes until the admin unticks one.
 */
class RoleEmploymentStatuses extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('role_employment_statuses')) {
            $this->forge->addField([
                'id'                   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'role_id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'employment_status_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addUniqueKey(['role_id', 'employment_status_id']);
            $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('employment_status_id', 'employment_statuses', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('role_employment_statuses', true);
        }
        $roles = $this->db->table('roles')->whereIn('name', ['Employee', 'Supervisor'])->get()->getResultArray();
        foreach ($this->db->table('employment_statuses')->get()->getResultArray() as $status) {
            foreach ($roles as $role) {
                $this->db->table('role_employment_statuses')->ignore(true)->insert(['role_id' => $role['id'], 'employment_status_id' => $status['id']]);
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('role_employment_statuses', true);
    }
}
