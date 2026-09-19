<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmploymentStatusToPlantillasTable extends Migration
{
    public function up()
    {
        $fields = [
            'employment_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'Permanent',
                'null'       => false,
            ],
        ];
        $this->forge->addColumn('plantillas', $fields);

        // Backfill with realistic institutional employment statuses based on position titles
        $this->db->query("UPDATE plantillas SET employment_status = 'Permanent' WHERE employment_status IS NULL OR employment_status = ''");

        // Temporary faculty: Instructor I and Assistant Professor who may be probationary
        $this->db->query("UPDATE plantillas SET employment_status = 'Temporary' 
            WHERE position_id IN (SELECT id FROM positions WHERE title IN ('Instructor I', 'Assistant Professor'))
            AND id % 2 = 0");

        // Casual staff: Administrative Aide and Assistant
        $this->db->query("UPDATE plantillas SET employment_status = 'Casual' 
            WHERE position_id IN (SELECT id FROM positions WHERE title IN ('Administrative Aide', 'Administrative Assistant'))
            AND id % 3 = 1");

        // Contractual staff
        $this->db->query("UPDATE plantillas SET employment_status = 'Contractual' 
            WHERE position_id IN (SELECT id FROM positions WHERE title IN ('Administrative Assistant', 'Security Officer'))
            AND id % 3 = 2");
    }

    public function down()
    {
        $this->forge->dropColumn('plantillas', 'employment_status');
    }
}
