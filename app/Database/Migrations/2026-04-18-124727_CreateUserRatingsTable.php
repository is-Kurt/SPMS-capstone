<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserRatingsTable extends Migration
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
            'rating_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 11,
            ],
            'document_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 11,
            ],
            'remarks' => [
                'type'           => 'VARCHAR',
                'constraint'     => 255,
                'null'           => true,
            ],
            'created_at' => [
                'type'           => 'DATETIME',
                'null'           => true,
            ],
            'deleted_at' => [
                'type'           => 'DATETIME',
                'null'           => true,
            ],
        ]);

        $this->forge->addKey('id', true); 

        $this->forge->addUniqueKey(['document_id', 'rating_id']);

        $this->forge->addForeignKey('rating_id', 'ratings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('document_id', 'documents', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('user_ratings');
    }

    public function down()
    {
        $this->forge->dropTable('user_ratings');
    }
}
