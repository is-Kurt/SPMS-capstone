<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRubricsToDocuments extends Migration
{
    public function up()
    {
        $this->forge->addColumn('documents', [
            'rubrics_content' => [
                'type' => 'LONGTEXT',
                'null' => true,
                // 'after' => 'content',
            ],
        ]);

        $this->forge->addColumn('templates', [
            'rubrics_content' => [
                'type' => 'LONGTEXT',
                'null' => true,
                // 'after' => 'content',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('documents', 'rubrics_content');
        $this->forge->dropColumn('templates', 'rubrics_content');
    }
}
