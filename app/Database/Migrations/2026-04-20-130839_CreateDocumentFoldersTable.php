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
            'user_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'title' => [
                'type'           => 'VARCHAR',
                'constraint'     => 255,
                'null'       => false,
            ],
            'parent_folder_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 11,
                'null'       => true,
            ],
            'final_rating' => [
                'type'       => 'DOUBLE',
                'unsigned'   => true,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'routing_preset_id' => [
                'type' => 'INT', 
                'constraint' => 11,
                'unsigned' => true,
                'null'     => true
            ],
            'eval_date_start' => [
                'type'           => 'DATETIME',
                'null'           => true,
            ],
            'eval_date_end' => [
                'type'           => 'DATETIME',
                'null'           => true,
            ],
            'submitted_at' => [
                'type'           => 'DATETIME',
                'null'           => true,
            ],
            'rated_at' => [
                'type'           => 'DATETIME',
                'null'           => true,
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

        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('parent_folder_id', 'document_folders', 'id', 'CASCADE', 'CASCADE'); 
        $this->forge->addForeignKey('routing_preset_id', 'routing_presets', 'id', 'CASCADE', 'CASCADE'); 
        $this->forge->createTable('document_folders');
    }

    public function down()
    {
        $this->forge->dropTable('document_folders');
    }
}