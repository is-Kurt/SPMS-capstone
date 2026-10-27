<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRatingCycleFieldsToDocumentFolders extends Migration
{
    public function up()
    {
        $fields = [];
        if (!$this->db->fieldExists('academic_year', 'document_folders')) {
            $fields['academic_year'] = [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ];
        }
        if (!$this->db->fieldExists('semester', 'document_folders')) {
            $fields['semester'] = [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ];
        }
        if (!$this->db->fieldExists('rating_period', 'document_folders')) {
            $fields['rating_period'] = [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ];
        }

        if (!empty($fields)) {
            $this->forge->addColumn('document_folders', $fields);
        }

        // Clean up any legacy cdpcr rows in cycle_deadlines
        if ($this->db->tableExists('cycle_deadlines')) {
            $this->db->table('cycle_deadlines')->where('doc_type', 'cdpcr')->delete();
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('academic_year', 'document_folders')) {
            $this->forge->dropColumn('document_folders', 'academic_year');
        }
        if ($this->db->fieldExists('semester', 'document_folders')) {
            $this->forge->dropColumn('document_folders', 'semester');
        }
        if ($this->db->fieldExists('rating_period', 'document_folders')) {
            $this->forge->dropColumn('document_folders', 'rating_period');
        }
    }
}
