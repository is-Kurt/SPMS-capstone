<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * document_folders.waiting_supervisor (2026-11-07): 1 when the paper's supervisor was revoked (they went out of
 * scope: moved, left, disabled, no longer a head) and no new supervisor has cascaded to it yet. Such a paper cannot
 * be submitted. Cleared when a supervisor is routed to it again (EvaluationRoutingModel).
 */
class PaperWaitingSupervisor extends Migration
{
    private function hasColumn(string $table, string $column): bool
    {
        return (bool) $this->db->query("SELECT 1 FROM pragma_table_info('{$table}') WHERE name = ?", [$column])->getRowArray();
    }

    public function up()
    {
        if (!$this->hasColumn('document_folders', 'waiting_supervisor')) {
            $this->forge->addColumn('document_folders', ['waiting_supervisor' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => false]]);
        }
    }

    public function down()
    {
        if ($this->hasColumn('document_folders', 'waiting_supervisor')) {
            $this->db->query('ALTER TABLE document_folders DROP COLUMN waiting_supervisor');
        }
    }
}
