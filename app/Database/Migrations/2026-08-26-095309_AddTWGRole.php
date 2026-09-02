<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTWGRole extends Migration
{
    public function up()
    {
        // 1. Insert TWG role if not exists
        $role = $this->db->table('roles')->where('name', 'TWG')->get()->getRowArray();
        if (!$role) {
            $this->db->table('roles')->insert([
                'name' => 'TWG'
            ]);
            $roleId = $this->db->insertID();
        } else {
            $roleId = $role['id'];
        }

        // 2. Create the twg@test.com user account if not exists
        $user = $this->db->table('users')->where('email', 'twg@test.com')->get()->getRowArray();
        if (!$user) {
            $this->db->table('users')->insert([
                'email' => 'twg@test.com',
                'first_name' => 'TWG',
                'last_name' => 'Reviewer',
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
        
        // Assign the TWG role to the user
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
        $user = $this->db->table('users')->where('email', 'twg@test.com')->get()->getRowArray();
        if ($user) {
            $this->db->table('user_roles')->where('user_id', $user['id'])->delete();
            $this->db->table('users')->where('id', $user['id'])->delete();
        }
        $this->db->table('roles')->where('name', 'TWG')->delete();
    }
}
