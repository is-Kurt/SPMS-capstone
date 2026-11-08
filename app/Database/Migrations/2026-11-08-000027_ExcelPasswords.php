<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * excel_passwords (2026-11-08): the passwords that protect the exported Excel files, set by the Admin in
 * Accounts > Excel Password. Every one is kept (encrypted with the app key) so an older file can still be unlocked;
 * the newest row is the one in use. With no rows the files use the built-in "spms" (App\Libraries\ExcelPassword).
 */
class ExcelPasswords extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('excel_passwords')) return;
        $this->forge->addField([
            'id'         => ['type' => 'INTEGER', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'secret'     => ['type' => 'TEXT'],
            'set_by'     => ['type' => 'INTEGER', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('excel_passwords');
    }

    public function down()
    {
        $this->forge->dropTable('excel_passwords', true);
    }
}
