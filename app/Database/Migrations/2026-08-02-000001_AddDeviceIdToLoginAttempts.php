<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeviceIdToLoginAttempts extends Migration
{
    public function up()
    {
        $this->forge->addColumn('login_attempts', [
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('login_attempts', 'device_id');
    }
}
