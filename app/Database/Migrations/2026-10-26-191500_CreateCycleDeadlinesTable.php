<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCycleDeadlinesTable extends Migration
{
    public function up()
    {
        // 1. Add cycle_folder_id to document_folders if not exists
        if (!$this->db->fieldExists('cycle_folder_id', 'document_folders')) {
            $this->forge->addColumn('document_folders', [
                'cycle_folder_id' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 11,
                    'null'       => true,
                ],
            ]);
        }

        // 2. Create cycle_deadlines table
        if (!$this->db->tableExists('cycle_deadlines')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'cycle_folder_id' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 11,
                    'null'       => false,
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

            $this->forge->addKey('id', true);
            $this->forge->addKey(['cycle_folder_id', 'doc_type']);
            $this->forge->createTable('cycle_deadlines');
        }

        // 3. Backfill cycle_folder_id for existing folders:
        // Set cycle_folder_id = id for root folders
        $this->db->query("UPDATE document_folders SET cycle_folder_id = id WHERE parent_folder_id IS NULL");
        
        // Propagate cycle_folder_id down descendant levels
        for ($i = 0; $i < 6; $i++) {
            $this->db->query("UPDATE document_folders 
                SET cycle_folder_id = (SELECT p.cycle_folder_id FROM (SELECT id, cycle_folder_id FROM document_folders) p WHERE p.id = document_folders.parent_folder_id)
                WHERE parent_folder_id IS NOT NULL AND (cycle_folder_id IS NULL OR cycle_folder_id = '')");
        }

        // 4. Backfill cycle_deadlines table from existing root folders
        $now = date('Y-m-d H:i:s');
        $rootFolders = $this->db->table('document_folders')
            ->where('parent_folder_id IS NULL')
            ->get()->getResultArray();

        $docTypes = ['opcr', 'cdpcr', 'dpcr', 'ipcr', 'iperf'];

        foreach ($rootFolders as $rf) {
            foreach ($docTypes as $type) {
                $tStart = $rf["{$type}_target_start"] ?? null;
                $tEnd   = $rf["{$type}_target_end"] ?? null;
                $eStart = $rf["{$type}_eval_start"] ?? null;
                $eEnd   = $rf["{$type}_eval_end"] ?? null;

                $existing = $this->db->table('cycle_deadlines')
                    ->where('cycle_folder_id', $rf['id'])
                    ->where('doc_type', $type)
                    ->get()->getRowArray();

                if (!$existing) {
                    $this->db->table('cycle_deadlines')->insert([
                        'cycle_folder_id' => $rf['id'],
                        'doc_type'        => $type,
                        'target_start'    => $tStart,
                        'target_end'      => $tEnd,
                        'eval_start'      => $eStart,
                        'eval_end'        => $eEnd,
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ]);
                }
            }
        }

        // 5. Drop the 20 redundant datetime columns from document_folders
        $dateCols = [
            'ipcr_target_start', 'ipcr_target_end', 'ipcr_eval_start', 'ipcr_eval_end',
            'cdpcr_target_start', 'cdpcr_target_end', 'cdpcr_eval_start', 'cdpcr_eval_end',
            'dpcr_target_start', 'dpcr_target_end', 'dpcr_eval_start', 'dpcr_eval_end',
            'opcr_target_start', 'opcr_target_end', 'opcr_eval_start', 'opcr_eval_end',
            'iperf_target_start', 'iperf_target_end', 'iperf_eval_start', 'iperf_eval_end',
        ];

        foreach ($dateCols as $col) {
            if ($this->db->fieldExists($col, 'document_folders')) {
                $this->forge->dropColumn('document_folders', $col);
            }
        }
    }

    public function down()
    {
        // Re-add 20 columns
        $dateCols = [
            'ipcr_target_start', 'ipcr_target_end', 'ipcr_eval_start', 'ipcr_eval_end',
            'cdpcr_target_start', 'cdpcr_target_end', 'cdpcr_eval_start', 'cdpcr_eval_end',
            'dpcr_target_start', 'dpcr_target_end', 'dpcr_eval_start', 'dpcr_eval_end',
            'opcr_target_start', 'opcr_target_end', 'opcr_eval_start', 'opcr_eval_end',
            'iperf_target_start', 'iperf_target_end', 'iperf_eval_start', 'iperf_eval_end',
        ];

        $fields = [];
        foreach ($dateCols as $col) {
            $fields[$col] = ['type' => 'DATETIME', 'null' => true];
        }
        $this->forge->addColumn('document_folders', $fields);

        // Restore data from cycle_deadlines to document_folders
        $deadlines = $this->db->table('cycle_deadlines')->get()->getResultArray();
        foreach ($deadlines as $d) {
            $type = $d['doc_type'];
            $this->db->table('document_folders')
                ->where('id', $d['cycle_folder_id'])
                ->update([
                    "{$type}_target_start" => $d['target_start'],
                    "{$type}_target_end"   => $d['target_end'],
                    "{$type}_eval_start"   => $d['eval_start'],
                    "{$type}_eval_end"     => $d['eval_end'],
                ]);
        }

        $this->forge->dropTable('cycle_deadlines', true);
        if ($this->db->fieldExists('cycle_folder_id', 'document_folders')) {
            $this->forge->dropColumn('document_folders', 'cycle_folder_id');
        }
    }
}
