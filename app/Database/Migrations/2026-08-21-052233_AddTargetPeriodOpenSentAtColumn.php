<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTargetPeriodOpenSentAtColumn extends Migration
{
    public function up()
    {
        $this->forge->addColumn('document_folders', [
            'target_period_open_sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'target_deadline_reminder_sent_at'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('document_folders', 'target_period_open_sent_at');
    }
}
