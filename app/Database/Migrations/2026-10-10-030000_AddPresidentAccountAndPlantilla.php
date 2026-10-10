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

        // 3. Ensure "president@test.com" user account exists
        $user = $this->db->table('users')->where('email', 'president@test.com')->get()->getRowArray();
        if (!$user) {
            $this->db->table('users')->insert([
                'email'      => 'president@test.com',
                'first_name' => 'Felipe',
                'last_name'  => 'Comila',
                'password'   => password_hash('123', PASSWORD_BCRYPT),
                'is_active'  => 1,
                'doc_type'   => null,
                'created_at' => $now,
                'updated_at' => $now
            ]);
            $presidentUserId = (int)$this->db->insertID();
        } else {
            $presidentUserId = (int)$user['id'];
            $this->db->table('users')->where('id', $presidentUserId)->update([
                'first_name' => 'Felipe',
                'last_name'  => 'Comila',
                'is_active'  => 1
            ]);
        }

        // 4. Assign "Supervisor" role to the President
        $supervisorRole = $this->db->table('roles')->where('name', 'Supervisor')->get()->getRowArray();
        if ($supervisorRole) {
            $existingUserRole = $this->db->table('user_roles')
                ->where('user_id', $presidentUserId)
                ->where('role_id', $supervisorRole['id'])
                ->get()->getRowArray();
            if (!$existingUserRole) {
                $this->db->table('user_roles')->insert([
                    'user_id' => $presidentUserId,
                    'role_id' => $supervisorRole['id']
                ]);
            }
        }

        // 5. Assign Plantilla
        $plantilla = $this->db->table('plantillas')
            ->where('user_id', $presidentUserId)
            ->where('ended_at IS NULL')
            ->get()->getRowArray();
        if (!$plantilla) {
            $this->db->table('plantillas')->insert([
                'user_id'     => $presidentUserId,
                'position_id' => $posId,
                'unit_id'     => $opUnitId,
                'started_at'  => '2020-01-01',
                'ended_at'    => null
            ]);
        } else {
            $this->db->table('plantillas')->where('id', $plantilla['id'])->update([
                'position_id' => $posId,
                'unit_id'     => $opUnitId
            ]);
        }

        // 6. Route all existing Vice President OPCR folders to the President in evaluation_routings
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
                    ->where('evaluator_id', $presidentUserId)
                    ->get()->getRowArray();

                if (!$rExists) {
                    $this->db->table('evaluation_routings')->insert([
                        'folder_id'           => $vf['id'],
                        'evaluator_id'        => $presidentUserId,
                        'evaluator_folder_id' => $vf['parent_folder_id'] ?? $vf['id'],
                        'status'              => $vf['status'] ?? 'Draft',
                        'created_at'          => $now,
                        'updated_at'          => $now
                    ]);
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
