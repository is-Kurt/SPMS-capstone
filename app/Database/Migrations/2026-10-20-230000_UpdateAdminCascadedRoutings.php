<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateAdminCascadedRoutings extends Migration
{
    public function up()
    {
        $now = date('Y-m-d H:i:s');

        // 1. Find Admin user
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

        if (!$adminUser) return;
        $adminUserId = (int)$adminUser['id'];

        // 2. Find President user if any
        $presidentUser = $this->db->table('users')->where('email', 'president@test.com')->get()->getRowArray();
        $presidentUserId = $presidentUser ? (int)$presidentUser['id'] : null;

        // 3. Re-route any evaluation_routings pointing to presidentUserId to adminUserId
        if ($presidentUserId && $presidentUserId !== $adminUserId) {
            $presRoutings = $this->db->table('evaluation_routings')
                ->where('evaluator_id', $presidentUserId)
                ->get()->getResultArray();

            foreach ($presRoutings as $pr) {
                // Check if admin routing already exists for this folder
                $adminRouting = $this->db->table('evaluation_routings')
                    ->where('folder_id', $pr['folder_id'])
                    ->where('evaluator_id', $adminUserId)
                    ->get()->getRowArray();

                if ($adminRouting) {
                    // Delete the redundant president routing
                    $this->db->table('evaluation_routings')->where('id', $pr['id'])->delete();
                } else {
                    // Update evaluator to admin
                    $this->db->table('evaluation_routings')->where('id', $pr['id'])->update([
                        'evaluator_id' => $adminUserId,
                        'updated_at'   => $now
                    ]);
                }
            }

            // Permanently remove the president account and its relations
            $this->db->table('plantillas')->where('user_id', $presidentUserId)->delete();
            $this->db->table('user_roles')->where('user_id', $presidentUserId)->delete();
            $this->db->table('notifications')->where('user_id', $presidentUserId)->orWhere('sender_id', $presidentUserId)->delete();
            $this->db->table('users')->where('id', $presidentUserId)->delete();
        }

        // 4. For any folders directly cascaded by Admin (child folders of Admin's folders),
        // ensure Admin routing exists
        $adminFolders = $this->db->table('document_folders')
            ->where('user_id', $adminUserId)
            ->where('deleted_at IS NULL')
            ->get()->getResultArray();

        $adminFolderIds = array_column($adminFolders, 'id');
        if (!empty($adminFolderIds)) {
            $cascadedFolders = $this->db->table('document_folders')
                ->whereIn('parent_folder_id', $adminFolderIds)
                ->where('deleted_at IS NULL')
                ->get()->getResultArray();

            foreach ($cascadedFolders as $cf) {
                $rExists = $this->db->table('evaluation_routings')
                    ->where('folder_id', $cf['id'])
                    ->where('evaluator_id', $adminUserId)
                    ->get()->getRowArray();

                if (!$rExists) {
                    $this->db->table('evaluation_routings')->insert([
                        'folder_id'           => $cf['id'],
                        'evaluator_id'        => $adminUserId,
                        'evaluator_folder_id' => $cf['parent_folder_id'] ?? $cf['id'],
                        'status'              => $cf['status'] ?? 'Draft',
                        'created_at'          => $now,
                        'updated_at'          => $now
                    ]);
                }
            }
        }
    }

    public function down()
    {
        // Reverting this migration is not needed as it aligns existing data with active Admin ownership
    }
}
