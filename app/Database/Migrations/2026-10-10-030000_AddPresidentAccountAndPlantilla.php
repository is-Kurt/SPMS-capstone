<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPresidentAccountAndPlantilla extends Migration
{
    public function up()
    {
        $now = date('Y-m-d H:i:s');

        // 1. Ensure "Office of the President" unit exists
        $opUnit = $this->db->table('units')->where('name', 'Office of the President')->get()->getRowArray();
        if (!$opUnit) {
            $this->db->table('units')->insert([
                'name'       => 'Office of the President',
                'parent_id'  => null,
                'created_at' => $now
            ]);
            $opUnitId = (int)$this->db->insertID();
        } else {
            $opUnitId = (int)$opUnit['id'];
        }

        // 2. Ensure "University President" position exists
        $pos = $this->db->table('positions')->where('title', 'University President')->get()->getRowArray();
        if (!$pos) {
            $this->db->table('positions')->insert([
                'title'       => 'University President',
                'is_teaching' => 0
            ]);
            $posId = (int)$this->db->insertID();
        } else {
            $posId = (int)$pos['id'];
        }

        // 3. Resolve Admin user account (who acts as University President / Executive Evaluator)
        $adminUser = $this->db->table('users u')
            ->select('u.id')
            ->join('user_roles ur', 'ur.user_id = u.id')
            ->join('roles r', 'r.id = ur.role_id')
            ->where('r.name', 'Admin')
            ->where('u.is_active', 1)
            ->get()->getRowArray();

        if (!$adminUser) {
            $adminUser = $this->db->table('users')->where('email', 'admin@test.com')->get()->getRowArray();
        }

        $adminUserId = $adminUser ? (int)$adminUser['id'] : null;

        // Clean up any legacy president account if present
        $user = $this->db->table('users')->where('email', 'president@test.com')->get()->getRowArray();
        if ($user) {
            $presId = (int)$user['id'];
            $this->db->table('evaluation_routings')->where('evaluator_id', $presId)->delete();
            $this->db->table('plantillas')->where('user_id', $presId)->delete();
            $this->db->table('user_roles')->where('user_id', $presId)->delete();
            $this->db->table('users')->where('id', $presId)->delete();
        }

        // 4. Route all existing Vice President OPCR folders to the Admin
        if ($adminUserId) {
            $vpUsers = $this->db->table('users u')
                ->select('u.id')
                ->join('plantillas p', 'p.user_id = u.id AND p.ended_at IS NULL', 'inner')
                ->join('positions pos', 'pos.id = p.position_id', 'inner')
                ->where('u.is_active', 1)
                ->groupStart()
                    ->like('pos.title', 'Vice President', 'both')
                    ->orLike('u.email', 'vpaa', 'both')
                    ->orLike('u.email', 'cao', 'both')
                ->groupEnd()
                ->get()->getResultArray();

            $vpUserIds = array_column($vpUsers, 'id');
            if (!empty($vpUserIds)) {
                $vpFolders = $this->db->table('document_folders')
                    ->whereIn('user_id', $vpUserIds)
                    ->where('deleted_at IS NULL')
                    ->get()->getResultArray();

                foreach ($vpFolders as $vf) {
                    $rExists = $this->db->table('evaluation_routings')
                        ->where('folder_id', $vf['id'])
                        ->where('evaluator_id', $adminUserId)
                        ->get()->getRowArray();

                    if (!$rExists) {
                        $this->db->table('evaluation_routings')->insert([
                            'folder_id'           => $vf['id'],
                            'evaluator_id'        => $adminUserId,
                            'evaluator_folder_id' => $vf['parent_folder_id'] ?? $vf['id'],
                            'status'              => $vf['status'] ?? 'Draft',
                            'created_at'          => $now,
                            'updated_at'          => $now
                        ]);
                    }
                }
            }
        }
    }

    public function down()
    {
        $user = $this->db->table('users')->where('email', 'president@test.com')->get()->getRowArray();
        if ($user) {
            $this->db->table('evaluation_routings')->where('evaluator_id', $user['id'])->delete();
            $this->db->table('plantillas')->where('user_id', $user['id'])->delete();
            $this->db->table('user_roles')->where('user_id', $user['id'])->delete();
            $this->db->table('users')->where('id', $user['id'])->delete();
        }
    }
}
