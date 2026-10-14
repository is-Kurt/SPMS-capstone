<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPMTRole extends Migration
{
    public function up()
    {
        // 1. Insert PMT role if not exists
        $role = $this->db->table('roles')->where('name', 'PMT')->get()->getRowArray();
        if (!$role) {
            $this->db->table('roles')->insert([
                'name' => 'PMT'
            ]);
            $roleId = $this->db->insertID();
        } else {
            $roleId = $role['id'];
        }

        // 2. Create the pmt@test.com user account if not exists
        $user = $this->db->table('users')->where('email', 'pmt@test.com')->get()->getRowArray();
        if (!$user) {
            $this->db->table('users')->insert([
                'email' => 'pmt@test.com',
                'first_name' => 'PMT',
                'last_name' => 'Chair / Reviewer',
                'password' => password_hash('123', PASSWORD_BCRYPT),
                'is_active' => 1,
                'doc_type' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $userId = $this->db->insertID();
        } else {
            $userId = $user['id'];
        }

        // 3. Assign the PMT role to the user
        $userRole = $this->db->table('user_roles')->where('user_id', $userId)->where('role_id', $roleId)->get()->getRowArray();
        if (!$userRole) {
            $this->db->table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => $roleId
            ]);
        }
    }

    public function down()
    {
        // Cleanup if rollback is needed
        $user = $this->db->table('users')->where('email', 'pmt@test.com')->get()->getRowArray();
        if ($user) {
            $this->db->table('user_roles')->where('user_id', $user['id'])->delete();
            $this->db->table('users')->where('id', $user['id'])->delete();
        }
        $this->db->table('roles')->where('name', 'PMT')->delete();
    }
}
