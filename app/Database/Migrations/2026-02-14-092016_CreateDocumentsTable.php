<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDocumentsTable extends Migration
{
    public function up() {
        $this->forge->addField([
            'id' => [
                'type'       => 'VARCHAR',
                'constraint' => 11,
                'null'       => false,
            ],
            'document_folder_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 11,
                'null'       => false,
            ],
            'title' => [
                'type'           => 'VARCHAR',
                'constraint'     => 255,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => ['draft', 'submitted', 'toEvaluate', 'evaluated'],
                'default'    => 'draft',
            ],
            'content' => [
                'type'           => 'TEXT',
                'null'           => true,
            ],
            'final_rating' => [
                'type'       => 'DOUBLE',
                'unsigned'   => true,
                'null'       => true,
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
            'parent_doc_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 11,
                'null'       => true,
            ],
            'created_at' => [
                'type'           => 'DATETIME',
                'null'           => true,
            ],
            'updated_at' => [
                'type'           => 'DATETIME',
                'null'           => true,
            ],
            'deleted_at' => [
                'type'           => 'DATETIME',
                'null'           => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('document_folder_id', 'document_folders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('parent_doc_id', 'documents', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('documents');
    }

    public function down()
    {
        $this->forge->dropTable('documents');
    }
}