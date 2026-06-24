<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePlantillaTable extends Migration
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
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'position_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'unit_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'started_at' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'ended_at' => [
                'type' => 'DATE',
                'null' => true,  // NULL = currently active
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
        $this->forge->addForeignKey('user_id',     'users',     'id', 'CASCADE',  'CASCADE');
        $this->forge->addForeignKey('position_id', 'positions', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('unit_id',     'units',     'id', 'RESTRICT', 'CASCADE');

        $this->forge->createTable('plantillas');
    }

    public function down()
    {
        $this->forge->dropTable('plantilla');
    }
}