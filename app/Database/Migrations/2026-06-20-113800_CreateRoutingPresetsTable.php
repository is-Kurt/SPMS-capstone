<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRoutingPresetsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => [
                'type' => 'INT', 
                'constraint' => 11, 
                'unsigned' => true, 
                'auto_increment' => true
            ],
            'owner_id' => [
                'type' => 'INT', 
                'constraint' => 11, 
                'unsigned' => true, 
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
            ],
            'description' => [
                'type'           => 'TEXT',
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

        $this->forge->addForeignKey('owner_id', 'users', 'id', 'CASCADE',  'CASCADE');

        $this->forge->createTable('routing_presets');
    }

    public function down()
    {
        $this->forge->dropTable('routing_presets');
    }
}
