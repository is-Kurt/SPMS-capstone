<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCycleDeadlinesTable extends Migration
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
            'cycle_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'cycle_folder_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 11,
                'null'       => true,
            ],
            'doc_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'target_start' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'target_end' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'eval_start' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'eval_end' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['cycle_id', 'doc_type']);
        $this->forge->addKey(['cycle_folder_id', 'doc_type']);
        $this->forge->createTable('cycle_deadlines', true);
    }

    public function down()
    {
        $this->forge->dropTable('cycle_deadlines', true);
    }
}
