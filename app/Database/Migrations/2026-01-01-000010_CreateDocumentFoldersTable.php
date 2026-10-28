<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDocumentFoldersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'       => 'VARCHAR',
                'constraint' => 11,
            ],
            'cycle_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'cycle_folder_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 11,
                'null'       => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'parent_folder_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 11,
                'null'       => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => '30',
                'default'    => 'draft_target',
            ],
            'final_rating' => [
                'type'       => 'DOUBLE',
                'null'       => true,
                'default'    => null,
            ],
            'tabs' => [
                'type'       => 'LONGTEXT',
                'null'       => true,
            ],
            'current_version' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'target_submitted_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'target_approved_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'target_approved_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'target_approver_role' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'submitted_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'rated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'routing_preset_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 11,
                'null'       => true,
            ],
            'rating_period' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'approving_authority_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'approving_authority_position' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'target_deadline_reminder_sent_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'deadline_reminder_sent_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'target_period_open_sent_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'eval_period_open_sent_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'deleted_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('cycle_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('parent_folder_id');
        $this->forge->createTable('document_folders', true);
    }

    public function down()
    {
        $this->forge->dropTable('document_folders', true);
    }
}
