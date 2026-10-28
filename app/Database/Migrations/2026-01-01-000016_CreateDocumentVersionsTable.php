<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDocumentVersionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'folder_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 11,
                'null'       => false,
            ],
            'version_number' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'phase' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'target',
            ],
            'tabs' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'submitted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'returned_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'returned_by_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'returned_by_role' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'returned_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'return_remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['folder_id', 'version_number']);
        $this->forge->createTable('document_versions', true);
    }

    public function down()
    {
        $this->forge->dropTable('document_versions', true);
    }
}

