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
    protected $description = 'Converts every user\'s target papers still in target setting to their form type (submitted ones go back to draft).';

    public function run(array $params)
    {
        helper('functions');
        $db = \Config\Database::connect();
        $userModel = new UserModel();

        CLI::write("=== SPMS PAPER SYNCHRONIZATION ===", 'yellow');

        // Convert everyone's target papers still in target setting to their form type. Submitted, unapproved
        // targets go back to draft (sync_user_papers_mid_target_setting); approved ones are never touched.
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
