<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddApprovingAuthorityToDocumentFolders extends Migration
{
    public function up()
    {
        $fields = [
            'approving_authority_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'approving_authority_position' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ];
        $this->forge->addColumn('document_folders', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('document_folders', 'approving_authority_name');
        $this->forge->dropColumn('document_folders', 'approving_authority_position');
    }
}
