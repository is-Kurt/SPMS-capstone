<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use App\Models\DocumentFolderModel;
use App\Enums\FolderStatus;

class AddIperfSampleAccount extends Migration
{
    public function up()
    {
        $now = date('Y-m-d H:i:s');

        // 1. Ensure position exists
        $pos = $this->db->table('positions')->where('title', 'Administrative Aide')->get()->getRowArray();
        if (!$pos) {
            $this->db->table('positions')->insert([
                'title'       => 'Administrative Aide',
                'is_teaching' => 0
            ]);
            $posId = (int)$this->db->insertID();
        } else {
            $posId = (int)$pos['id'];
        }

        // 2. Ensure HRDO unit exists
        $hrdo = $this->db->table('units')->where('name', 'HRDO')->get()->getRowArray();
        if (!$hrdo) {
            $this->db->table('units')->insert([
                'name'       => 'HRDO',
                'parent_id'  => null,
                'created_at' => $now
            ]);
            $hrdoId = (int)$this->db->insertID();
        } else {
            $hrdoId = (int)$hrdo['id'];
        }

        // 3. Ensure "iperf@test.com" user account exists
        $user = $this->db->table('users')->where('email', 'iperf@test.com')->get()->getRowArray();
        if (!$user) {
            $this->db->table('users')->insert([
                'email'      => 'iperf@test.com',
                'first_name' => 'Danilo',
                'last_name'  => 'Ocampo',
                'password'   => password_hash('123', PASSWORD_BCRYPT),
                'is_active'  => 1,
                'doc_type'   => 'IPERF',
                'created_at' => $now,
                'updated_at' => $now
            ]);
            $userId = (int)$this->db->insertID();
        } else {
            $userId = (int)$user['id'];
            $this->db->table('users')->where('id', $userId)->update([
                'first_name' => 'Danilo',
                'last_name'  => 'Ocampo',
                'doc_type'   => 'IPERF',
                'is_active'  => 1
            ]);
        }

        // 4. Assign "Employee" role
        $empRole = $this->db->table('roles')->where('name', 'Employee')->get()->getRowArray();
        if ($empRole) {
            $existingUserRole = $this->db->table('user_roles')
                ->where('user_id', $userId)
                ->where('role_id', $empRole['id'])
                ->get()->getRowArray();
            if (!$existingUserRole) {
                $this->db->table('user_roles')->insert([
                    'user_id' => $userId,
                    'role_id' => $empRole['id']
                ]);
            }
        }

        // 5. Assign Plantilla
        $plantilla = $this->db->table('plantillas')
            ->where('user_id', $userId)
            ->where('ended_at IS NULL')
            ->get()->getRowArray();
        if (!$plantilla) {
            $this->db->table('plantillas')->insert([
                'user_id'     => $userId,
                'position_id' => $posId,
                'unit_id'     => $hrdoId,
                'started_at'  => '2022-01-01',
                'ended_at'    => null
            ]);
        } else {
            $this->db->table('plantillas')->where('id', $plantilla['id'])->update([
                'position_id' => $posId,
                'unit_id'     => $hrdoId
            ]);
        }

        // 6. Hook into active evaluation cycle (if present) so Danilo has an immediate IPERF folder
        $activeCycle = $this->db->table('document_folders')
            ->where('parent_folder_id IS NULL')
            ->where('deleted_at IS NULL')
            ->orderBy('created_at', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        if ($activeCycle) {
            $hrdoHeadUser = $this->db->table('users')->where('email', 'hrdohead@test.com')->get()->getRowArray();
            $hrdoHeadId = $hrdoHeadUser ? (int)$hrdoHeadUser['id'] : null;

            $existingFolder = $this->db->table('document_folders')
                ->where('user_id', $userId)
                ->where('title', $activeCycle['title'])
                ->where('deleted_at IS NULL')
                ->get()->getRowArray();

            if (!$existingFolder) {
                helper('functions');
                $folderId = generate_short_id();
                $this->db->table('document_folders')->insert([
                    'id'                  => $folderId,
                    'title'               => $activeCycle['title'],
                    'user_id'             => $userId,
                    'parent_folder_id'    => $activeCycle['id'],
                    'status'              => FolderStatus::DRAFT_TARGET->value,
                    'ipcr_target_start'   => $activeCycle['ipcr_target_start'] ?? null,
                    'ipcr_target_end'     => $activeCycle['ipcr_target_end'] ?? null,
                    'ipcr_eval_start'     => $activeCycle['ipcr_eval_start'] ?? null,
                    'ipcr_eval_end'       => $activeCycle['ipcr_eval_end'] ?? null,
                    'iperf_target_start'  => $activeCycle['iperf_target_start'] ?? date('Y-m-d', strtotime('-15 days')),
                    'iperf_target_end'    => $activeCycle['iperf_target_end'] ?? date('Y-m-d', strtotime('+15 days')),
                    'iperf_eval_start'    => $activeCycle['iperf_eval_start'] ?? date('Y-m-d', strtotime('+16 days')),
                    'iperf_eval_end'      => $activeCycle['iperf_eval_end'] ?? date('Y-m-d', strtotime('+45 days')),
                    'created_at'          => $now,
                    'updated_at'          => $now
                ]);

                // Create IPERF Document
                $iperfTpl = $this->db->table('templates')->where('title', 'IPERF')->get()->getRowArray();
                $tabs = $iperfTpl ? json_decode($iperfTpl['tabs'] ?? '[]', true) : [];

                $docId = generate_short_id();
                $this->db->table('documents')->insert([
                    'id'                 => $docId,
                    'document_folder_id' => $folderId,
                    'title'              => 'IPERF — Danilo Ocampo',
                    'is_target'          => 1,
                    'tabs'               => json_encode($tabs),
                    'created_at'         => $now,
                    'updated_at'         => $now
                ]);

                // Register routing to supervisor
                if ($hrdoHeadId) {
                    $this->db->table('evaluation_routings')->insert([
                        'folder_id'           => $folderId,
                        'evaluator_id'        => $hrdoHeadId,
                        'evaluator_folder_id' => $activeCycle['id'],
                        'status'              => FolderStatus::DRAFT->value,
                        'created_at'          => $now,
                        'updated_at'          => $now
                    ]);
                }
            }
        }
    }

    public function down()
    {
        $user = $this->db->table('users')->where('email', 'iperf@test.com')->get()->getRowArray();
        if ($user) {
            $this->db->table('evaluation_routings')->where('evaluator_id', $user['id'])->delete();
            $this->db->table('plantillas')->where('user_id', $user['id'])->delete();
            $this->db->table('user_roles')->where('user_id', $user['id'])->delete();
            $this->db->table('users')->where('id', $user['id'])->delete();
        }
    }
}
