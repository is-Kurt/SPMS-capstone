<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Doc-type rules only apply to the roles that fill in forms (Employee, Supervisor). An Admin never gets a
 * paper (Folder only creates one for non-Admin owners), so the "Admin = OPCR" rule carried over from the
 * old code was meaningless: remove the rows for every non-rated role.
 */
class DropAdminDocRule extends Migration
{
    public function up()
    {
        $this->db->query("DELETE FROM role_doc_types WHERE role_id IN (SELECT id FROM roles WHERE name NOT IN ('Employee', 'Supervisor'))");
    }

    public function down()
    {
        $admin = $this->db->table('roles')->where('name', 'Admin')->get()->getRowArray();
        if ($admin) $this->db->table('role_doc_types')->ignore(true)->insert(['role_id' => $admin['id'], 'doc_type' => 'OPCR']);
    }
}
