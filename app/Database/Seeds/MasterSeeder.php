<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MasterSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        echo ">>> [SPMS MasterSeeder] Resetting database tables...\n";

        // Temporarily disable foreign key checks for clean truncation in SQLite/MySQL
        $db->query('PRAGMA foreign_keys = OFF');

        $tablesToClear = [
            'evaluation_routings',
            'document_comments',
            'document_attachments',
            'twg_unit_assignments',
            'notifications',
            'activity_logs',
            'invitations',
            'email_queue',
            'login_attempts',
            'routing_preset_members',
            'routing_presets',
            'cycle_deadlines',
            'document_folders',
            'rating_cycles',
            'plantillas',
            'user_roles',
            'positions',
            'units',
            'users',
            'roles',
        ];

        foreach ($tablesToClear as $table) {
            if ($db->tableExists($table)) {
                $db->table($table)->emptyTable();
            }
        }

        // Reset SQLite auto-increment counter so IDs are deterministic
        try {
            $db->query("DELETE FROM sqlite_sequence");
        } catch (\Throwable $e) {
            // Non-SQLite database or sqlite_sequence not found
        }

        $db->query('PRAGMA foreign_keys = ON');

        // ==========================================
        // 1. SEED SYSTEM ROLES (RBAC)
        // ==========================================
        echo ">>> Seeding System Roles...\n";
        $roles = ['Admin', 'Supervisor', 'Employee', 'TWG', 'PMT'];
        foreach ($roles as $r) {
            $db->table('roles')->insert(['name' => $r]);
        }

        // ==========================================
        // 2. SEED POSITIONS
        // ==========================================
        echo ">>> Seeding Positions...\n";
        $positions = [
            ['title' => 'University President',                         'is_teaching' => 0],
            ['title' => 'Vice President for Academic Affairs',          'is_teaching' => 0],
            ['title' => 'Vice President for Administration and Finance', 'is_teaching' => 0],
            ['title' => 'Vice President',                               'is_teaching' => 0],
            ['title' => 'Dean',                                         'is_teaching' => 1],
            ['title' => 'Department Chair',                             'is_teaching' => 1],
            ['title' => 'Professor',                                    'is_teaching' => 1],
            ['title' => 'Associate Professor',                          'is_teaching' => 1],
            ['title' => 'Assistant Professor',                          'is_teaching' => 1],
            ['title' => 'Instructor II',                                'is_teaching' => 1],
            ['title' => 'Instructor I',                                 'is_teaching' => 1],
            ['title' => 'Unit Head',                                    'is_teaching' => 0],
            ['title' => 'Chief Accountant',                             'is_teaching' => 0],
            ['title' => 'Bookkeeper',                                   'is_teaching' => 0],
            ['title' => 'HR Management Officer',                        'is_teaching' => 0],
            ['title' => 'Administrative Officer',                       'is_teaching' => 0],
            ['title' => 'Administrative Assistant',                     'is_teaching' => 0],
            ['title' => 'Administrative Aide',                          'is_teaching' => 0],
            ['title' => 'Planning Officer',                             'is_teaching' => 0],
            ['title' => 'Quality Assurance Officer',                    'is_teaching' => 0],
            ['title' => 'Security Officer',                             'is_teaching' => 0],
        ];
        $db->table('positions')->insertBatch($positions);

        // ==========================================
        // 3. SEED UNITS (Org Hierarchy)
        // ==========================================
        echo ">>> Seeding Units Hierarchy...\n";
        $now = date('Y-m-d H:i:s');

        // Top Executive Offices
        $db->table('units')->insert(['name' => 'Office of the President', 'parent_id' => null, 'created_at' => $now]);
        $opId = $db->insertID();

        $db->table('units')->insert(['name' => 'Office of the Vice President for Academic Affairs', 'parent_id' => null, 'created_at' => $now]);
        $ovpaaId = $db->insertID();

        $db->table('units')->insert(['name' => 'Office of the Vice President for Administration and Finance', 'parent_id' => null, 'created_at' => $now]);
        $ovpafId = $db->insertID();

        // Administrative Units under OVPAF / OP
        $adminUnits = [
            ['name' => 'General Services Office',           'parent_id' => $ovpafId, 'created_at' => $now],
            ['name' => 'Human Resource Management Office',   'parent_id' => $ovpafId, 'created_at' => $now],
            ['name' => 'Accounting Office',                  'parent_id' => $ovpafId, 'created_at' => $now],
            ['name' => 'Planning and Development Office',    'parent_id' => $opId,    'created_at' => $now],
            ['name' => 'Quality Assurance Office',           'parent_id' => $opId,    'created_at' => $now],
        ];
        $db->table('units')->insertBatch($adminUnits);

        // Academic Colleges under OVPAA
        $colleges = [
            'College of Information Sciences' => [
                'BS Information Technology',
                'BS Development Communication',
            ],
            'College of Nursing' => [
                'BS Nursing',
            ],
            'College of Agriculture' => [
                'BS Agriculture',
                'BS Agribusiness',
            ],
            'College of Teacher Education' => [
                'Bachelor of Secondary Education',
                'Bachelor of Elementary Education',
            ],
            'College of Engineering' => [
                'BS Civil Engineering',
                'BS Agricultural and Biosystems Engineering',
            ],
            'College of Arts and Humanities' => [],
            'College of Forestry' => [],
            'College of Human Ecology' => [],
            'College of Human Kinetics' => [],
            'College of Medicine' => [],
            'College of Natural Sciences' => [],
            'College of Numeracy and Applied Sciences' => [],
            'College of Public Administration and Governance' => [],
            'College of Social Sciences' => [],
            'College of Veterinary Medicine' => [],
        ];

        foreach ($colleges as $collegeName => $depts) {
            $db->table('units')->insert(['name' => $collegeName, 'parent_id' => $ovpaaId, 'created_at' => $now]);
            $collegeId = $db->insertID();
            foreach ($depts as $deptName) {
                $db->table('units')->insert(['name' => $deptName, 'parent_id' => $collegeId, 'created_at' => $now]);
            }
        }

        // ==========================================
        // 4. CALL DEPARTMENT SEEDER FOR USERS & FOLDERS
        // ==========================================
        $this->call('DepartmentSeeder');

        echo ">>> [SPMS MasterSeeder] Complete! Fresh SPMS database ready.\n";
    }
}
