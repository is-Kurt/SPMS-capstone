<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * directory_flag_ignores (2026-11-08): User Directory flags the Admin chose to ignore (for example a co-head who is
 * meant to be one). One row per person and flag text; the flag shows again when its text changes, and a row whose
 * flag no longer applies is removed when the directory loads.
 */
class DirectoryFlagIgnores extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('directory_flag_ignores')) return;
        $this->forge->addField([
            'id'         => ['type' => 'INTEGER', 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'null' => false],
            'flag'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'ignored_by' => ['type' => 'INT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'flag']);
        $this->forge->createTable('directory_flag_ignores', true);
    }

    public function down()
    {
        $this->forge->dropTable('directory_flag_ignores', true);
    }
}
