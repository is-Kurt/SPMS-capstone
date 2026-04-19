<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSharedDocumentsTable extends Migration
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
            'collaborator_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'document_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 11,
            ],
            'permission' => [
                'type'       => 'ENUM',
                'constraint' => ['viewer', 'commenter', 'editor'],
                'default'    => 'viewer',
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);


        $this->forge->addKey('id', true); 

        $this->forge->addUniqueKey(['document_id', 'collaborator_id']);

        $this->forge->addForeignKey('collaborator_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('document_id', 'submissions', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('shared_documents');
    }

    public function down()
    {
        $this->forge->dropTable('shared_documents');
    }
}