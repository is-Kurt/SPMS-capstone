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
            // Which document
            'document_id' => [
                'type'       => 'VARCHAR', // (Note: I changed this to VARCHAR to match your previous submissions table ID!)
                'constraint' => 11,
            ],
            // Their access level
            'permission' => [
                'type'       => 'ENUM',
                'constraint' => ['viewer', 'commenter', 'editor'],
                'default'    => 'viewer', // Always good to have a safe default
            ],
            // Optional but recommended: track when it was shared
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);

        // 1. Define the Primary Key
        $this->forge->addKey('id', true); 

        // 2. Define your Unique Key (for preventing duplicates)
        $this->forge->addUniqueKey(['document_id', 'collaborator_id']);

        // 3. Define Foreign Keys
        $this->forge->addForeignKey('collaborator_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('document_id', 'submissions', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('shared_documents');
    }

    public function down()
    {
        $this->forge->dropTable('shared_documents');
    }
}