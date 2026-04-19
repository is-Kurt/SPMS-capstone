<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSubmissionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT', 
                'constraint'     => 11, 
                'unsigned'       => true, 
                'auto_increment' => true
            ],
            'document_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 11,
            ],
            'is_rated' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'final_rating' => [
                'type'       => 'DOUBLE',
                'unsigned'   => true,
                'null'       => true,
            ],
            'rated_at' => [
                'type'           => 'DATETIME',
                'null'           => true,
            ],
            'submitted_at' => [
                'type'           => 'DATETIME',
                'null'           => true,
            ],
            'deleted_at' => [
                'type'           => 'DATETIME',
                'null'           => true,
            ],
        ]);

        $this->forge->addKey('id', true);

        $this->forge->addForeignKey('document_id', 'documents', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('submissions');
    }

    public function down()
    {
        $this->forge->dropTable('submissions');
    }
}