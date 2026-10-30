<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserSecurityFieldsAndPlantillaSoftDelete extends Migration
{
    public function up()
    {
        // 1. Add email change and password change fields to users table
        $userFields = [
            'email_change_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'null'       => true,
            ],
            'email_change_new_email' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'email_change_expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'email_change_attempts' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
            ],
            'email_change_last_attempt_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'password_change_attempts' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
            ],
            'password_change_last_attempt_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ];

        foreach ($userFields as $fieldName => $fieldDef) {
            if (! $this->db->fieldExists($fieldName, 'users')) {
                $this->forge->addColumn('users', [$fieldName => $fieldDef]);
            }
        }

        // 2. Add deleted_at to plantillas table
        if (! $this->db->fieldExists('deleted_at', 'plantillas')) {
            $this->forge->addColumn('plantillas', [
                'deleted_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
        }
    }

    public function down()
    {
        // Drop columns if supported
        $columnsToDrop = [
            'email_change_code',
            'email_change_new_email',
            'email_change_expires_at',
            'email_change_attempts',
            'email_change_last_attempt_at',
            'password_change_attempts',
            'password_change_last_attempt_at',
        ];

        foreach ($columnsToDrop as $col) {
            if ($this->db->fieldExists($col, 'users')) {
                $this->forge->dropColumn('users', $col);
            }
        }

        if ($this->db->fieldExists('deleted_at', 'plantillas')) {
            $this->forge->dropColumn('plantillas', 'deleted_at');
        }
    }
}

