<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UserModel;
use App\Models\DocumentModel;
use App\Models\DocumentFolderModel;

class SyncUserPapers extends BaseCommand
{
    protected $group       = 'SPMS';
    protected $name        = 'spms:sync-papers';
    protected $description = 'Synchronizes papers for users mid target setting to match their profile paper types.';

    public function run(array $params)
    {
        helper('functions');
        $db = \Config\Database::connect();
        $userModel = new UserModel();

        CLI::write("=== SPMS PAPER SYNCHRONIZATION ===", 'yellow');

        // 1. Re-align database executive roles and profile doc_types
        $vpaa = $userModel->where('email', 'vpaa@test.com')->first();
        if ($vpaa && $vpaa['doc_type'] !== 'OPCR') {
            $userModel->update($vpaa['id'], ['doc_type' => 'OPCR']);
            CLI::write("Updated vpaa@test.com doc_type -> OPCR", 'green');
        }

        $cao = $userModel->where('email', 'cao@test.com')->first();
        if ($cao && $cao['doc_type'] !== 'OPCR') {
            $userModel->update($cao['id'], ['doc_type' => 'OPCR']);
            CLI::write("Updated cao@test.com doc_type -> OPCR", 'green');
        }

        $hrdoHead = $userModel->where('email', 'hrdohead@test.com')->first();
        if ($hrdoHead) {
            if ($hrdoHead['doc_type'] !== 'DPCR') {
                $userModel->update($hrdoHead['id'], ['doc_type' => 'DPCR']);
                CLI::write("Updated hrdohead@test.com doc_type -> DPCR", 'green');
            }
            // Ensure Supervisor role in user_roles
            $supervisorRoleId = (int)($db->table('roles')->where('name', 'Supervisor')->get()->getRowArray()['id'] ?? 2);
            $db->table('user_roles')->where('user_id', $hrdoHead['id'])->update(['role_id' => $supervisorRoleId]);
            CLI::write("Updated hrdohead@test.com role -> Supervisor", 'green');
        }

        // 2. Synchronize papers for all users
        $allUsers = $userModel->findAll();
        $totalDocs = 0;
        $totalFolders = 0;

        foreach ($allUsers as $u) {
            $res = sync_user_papers_mid_target_setting((int)$u['id']);
            if ($res['updated_docs'] > 0 || $res['updated_folders'] > 0) {
                CLI::write("User {$u['email']}: updated {$res['updated_docs']} doc(s), {$res['updated_folders']} folder(s)", 'cyan');
                $totalDocs += $res['updated_docs'];
                $totalFolders += $res['updated_folders'];
            }
        }

        CLI::write("Synchronization complete: {$totalDocs} document(s) synchronized across {$totalFolders} folder(s).", 'green');
    }
}
