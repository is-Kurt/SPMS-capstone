<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTargetDatesToFolders extends Migration
{
    public function up()
    {
        $fields = [
            // target_date_start and target_date_end were moved to CreateDocumentFoldersTable as 16 distinct dates
            'target_submitted_at' => [
                'type' => 'DATETIME',
                'null' => true,
                // 'after' => 'eval_date_end',
            ],
            'target_approved_at' => [
                'type' => 'DATETIME',
                'null' => true,
                // 'after' => 'target_submitted_at',
            ],
        ];
        $this->forge->addColumn('document_folders', $fields);
    }

    public function down()
    {
        // $this->forge->dropColumn('document_folders', 'target_date_start');
        // $this->forge->dropColumn('document_folders', 'target_date_end');
        $this->forge->dropColumn('document_folders', 'target_submitted_at');
        $this->forge->dropColumn('document_folders', 'target_approved_at');
    }
}
