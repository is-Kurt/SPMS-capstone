<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCdpcrDatesToDocumentFolders extends Migration
{
    public function up()
    {
        $fields = [
            'cdpcr_target_start' => ['type' => 'DATETIME', 'null' => true],
            'cdpcr_target_end'   => ['type' => 'DATETIME', 'null' => true],
            'cdpcr_eval_start'   => ['type' => 'DATETIME', 'null' => true],
            'cdpcr_eval_end'     => ['type' => 'DATETIME', 'null' => true],
        ];
        $this->forge->addColumn('document_folders', $fields);

        // Backfill existing rows so existing cycles immediately have collegiate DPCR matching department DPCR
        $this->db->query("UPDATE document_folders 
            SET cdpcr_target_start = dpcr_target_start, 
                cdpcr_target_end   = dpcr_target_end, 
                cdpcr_eval_start   = dpcr_eval_start, 
                cdpcr_eval_end     = dpcr_eval_end 
            WHERE cdpcr_target_start IS NULL AND dpcr_target_start IS NOT NULL");
    }

    public function down()
    {
        $this->forge->dropColumn('document_folders', 'cdpcr_target_start');
        $this->forge->dropColumn('document_folders', 'cdpcr_target_end');
        $this->forge->dropColumn('document_folders', 'cdpcr_eval_start');
        $this->forge->dropColumn('document_folders', 'cdpcr_eval_end');
    }
}
