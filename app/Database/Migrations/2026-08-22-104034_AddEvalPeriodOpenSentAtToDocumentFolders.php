<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEvalPeriodOpenSentAtToDocumentFolders extends Migration
{
    public function up()
    {
        $this->forge->addColumn('document_folders', [
            'eval_period_open_sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('document_folders', 'eval_period_open_sent_at');
    }
}
