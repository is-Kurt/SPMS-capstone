<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDefaultFunctionWeightsTable extends Migration
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
            'doc_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'unique'     => true,
            ],
            'core_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 70.00,
            ],
            'strategic_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 15.00,
            ],
            'support_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 15.00,
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
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
        $this->forge->createTable('default_function_weights', true);
    }

    public function down()
    {
        $this->forge->dropTable('default_function_weights', true);
    }
}

