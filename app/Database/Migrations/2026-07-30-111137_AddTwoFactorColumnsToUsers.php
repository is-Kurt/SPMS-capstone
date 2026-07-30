<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTwoFactorColumnsToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'two_factor_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'null'       => true,
            ],
            'two_factor_expires_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'two_factor_code');
        $this->forge->dropColumn('users', 'two_factor_expires_at');
    }
}
