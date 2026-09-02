<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRoutingPresetMembersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'        => [
                'type'           => 'INT', 
                'constraint'     => 11, 
                'unsigned'       => true, 
                'auto_increment' => true
            ],
            'preset_id' => [
                'type' => 'INT', 
                'constraint' => 11, 
                'unsigned' => true, 
            ],
            'user_id'   => [
                'type' => 'INT', 
                'constraint' => 11, 
                'unsigned' => true, 
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

        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE',  'CASCADE');
        $this->forge->addForeignKey('preset_id', 'routing_presets', 'id', 'CASCADE',  'CASCADE');

        $this->forge->createTable('routing_preset_members');
    }

    public function down()
    {
        $this->forge->dropTable('routing_preset_members');
    }
}
