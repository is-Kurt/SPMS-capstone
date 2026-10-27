<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropIsTargetFromDocumentsTable extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('is_target', 'documents')) {
            $this->forge->dropColumn('documents', 'is_target');
        }
    }

    public function down()
    {
        if (!$this->db->fieldExists('is_target', 'documents')) {
            $fields = [
                'is_target' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
            ];
            $this->forge->addColumn('documents', $fields);
        }
    }
}
