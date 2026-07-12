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
                'null'       => true,  // NULL = position was deleted; the assignment itself lives on
            ],
            'unit_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,  // NULL = unit was deleted; the assignment itself lives on
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
        // addForeignKey($field, $table, $column, $onUpdate, $onDelete) - onDelete is SET
        // NULL here so deleting a position/unit that's in use un-assigns it (the plantilla
        // row survives with a null position_id/unit_id) instead of being blocked or cascaded.
        $this->forge->addForeignKey('user_id',     'users',     'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('position_id', 'positions', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('unit_id',     'units',     'id', 'CASCADE', 'SET NULL');

        $this->forge->createTable('plantillas');
    }

    public function down()
    {
        $this->forge->dropTable('plantillas');
    }
}