<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTargetApproverToFolders extends Migration
{
    public function up()
    {
        $fields = [
            'target_approved_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'target_approver_role' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
        ];
        $this->forge->addColumn('document_folders', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('document_folders', 'target_approved_by');
        $this->forge->dropColumn('document_folders', 'target_approver_role');
    }
}
