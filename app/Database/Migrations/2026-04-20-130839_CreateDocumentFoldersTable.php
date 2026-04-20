<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDocumentFoldersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'       => 'VARCHAR',
                'constraint' => 11,
                'null'       => false,
            ],
            'title' => [
                'type'           => 'VARCHAR',
                'constraint'     => 255,
                'null'       => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);


        $this->forge->createTable('document_folders');
    }

    public function down()
    {
        $this->forge->dropTable('document_folders');
    }
}