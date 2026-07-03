<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MasterSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        $password = password_hash('123', PASSWORD_DEFAULT);

        // ==========================================
        // 1. SEED SYSTEM ROLES (RBAC)
        // ==========================================
        $db->table('roles')->insert(['name' => 'Admin']);
        $adminRoleId = $db->insertID();

        $db->table('roles')->insert(['name' => 'Supervisor']);
        $supervisorRoleId = $db->insertID();

        $db->table('roles')->insert(['name' => 'Employee']);
        $employeeRoleId = $db->insertID();

        // ==========================================
        // 2. SEED SYSTEM USERS (Identity Only)
        // ==========================================
        // Keyed by positional slug (matches email prefix) instead of last_name,
        // so there are no collisions and plantillaData below can't reference a
        // user that doesn't exist.
        $supervisorSlugs = ['vpaa', 'dean', 'cao'];

        $usersData = [
            'admin'     => ['email' => 'admin@test.com',     'first_name' => 'System',    'last_name' => 'Admin',    'password' => $password, 'is_active' => 1],
            'vpaa'      => ['email' => 'vpaa@test.com',      'first_name' => 'Ana',       'last_name' => 'Santos',   'password' => $password, 'is_active' => 1],
            'dean'      => ['email' => 'dean@test.com',      'first_name' => 'Roberto',   'last_name' => 'Reyes',    'password' => $password, 'is_active' => 1],
            'deptchair' => ['email' => 'deptchair@test.com', 'first_name' => 'Miguel',    'last_name' => 'Cruz',     'password' => $password, 'is_active' => 1],
            'inst1'     => ['email' => 'inst1@test.com',     'first_name' => 'Carlos',    'last_name' => 'Lim',      'password' => $password, 'is_active' => 1],
            'instg2a'   => ['email' => 'instg2a@test.com',   'first_name' => 'Teresa',    'last_name' => 'Garcia',   'password' => $password, 'is_active' => 1],
            'vpadfin'   => ['email' => 'vpadfin@test.com',   'first_name' => 'Michael',   'last_name' => 'Tan',      'password' => $password, 'is_active' => 1],
            'cao'       => ['email' => 'cao@test.com',       'first_name' => 'Elena',     'last_name' => 'Navarro', 'password' => $password, 'is_active' => 1],
            'hrdohead'  => ['email' => 'hrdohead@test.com',  'first_name' => 'Patricia',  'last_name' => 'Aquino',   'password' => $password, 'is_active' => 1],
            'hrdo1'     => ['email' => 'hrdo1@test.com',     'first_name' => 'Grace',     'last_name' => 'Bautista', 'password' => $password, 'is_active' => 1],
            'hrdo2'     => ['email' => 'hrdo2@test.com',     'first_name' => 'Ramon',     'last_name' => 'Delgado',  'password' => $password, 'is_active' => 1],
        ];

        $userMap = [];
        foreach ($usersData as $slug => $u) {
            $db->table('users')->insert($u);
            $userId = $db->insertID();
            $userMap[$slug] = $userId;

            // ==========================================
            // 3. SEED USER ROLES PIVOT
            // ==========================================
            if ($slug === 'admin') {
                $roleId = $adminRoleId;
            } elseif (in_array($slug, $supervisorSlugs)) {
                $roleId = $supervisorRoleId;
            } else {
                $roleId = $employeeRoleId;
            }

            $db->table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => $roleId,
            ]);
        }

        // ==========================================
        // 4. SEED POSITIONS (HR Titles)
        // ==========================================
        $positionsData = [
            ['title' => 'Vice President',           'is_teaching' => 0],
            ['title' => 'Dean',                     'is_teaching' => 1],
            ['title' => 'Department Chair',         'is_teaching' => 1],
            ['title' => 'Instructor I',             'is_teaching' => 1],
            ['title' => 'Instructor II',            'is_teaching' => 1],
            ['title' => 'Registrar',                'is_teaching' => 0],
            ['title' => 'Administrative Aide',      'is_teaching' => 0],
            ['title' => 'Accountant III',           'is_teaching' => 0],
            ['title' => 'Administrative Assistant', 'is_teaching' => 0],
            ['title' => 'HRDO Head',                'is_teaching' => 0],
        ];

        $posMap = [];
        foreach ($positionsData as $p) {
            $db->table('positions')->insert($p);
            $posMap[$p['title']] = $db->insertID();
        }

        // ==========================================
        // 5. SEED UNITS (The Org Chart)
        // ==========================================
        $db->table('units')->insert(['name' => 'OVPAA', 'parent_id' => null]);
        $ovpaaId = $db->insertID();

        $db->table('units')->insert(['name' => 'OVPAF', 'parent_id' => null]);
        $ovpafId = $db->insertID();

        $db->table('units')->insert(['name' => 'HRDO', 'parent_id' => null]);
        $hrdoId = $db->insertID();

        $db->table('units')->insert(['name' => 'College of Engineering', 'parent_id' => $ovpaaId]);
        $coeId = $db->insertID();

        $db->table('units')->insert(['name' => "Registrar's Office", 'parent_id' => $ovpaaId]);
        $regId = $db->insertID();

        $db->table('units')->insert(['name' => 'Accounting Office', 'parent_id' => $ovpafId]);
        $accId = $db->insertID();

        // ==========================================
        // 6. SEED PLANTILLA (The Connective Tissue)
        // ==========================================
        $plantillaData = [
            ['user_id' => $userMap['vpaa'],      'position_id' => $posMap['Vice President'],           'unit_id' => $ovpaaId, 'started_at' => '2020-01-01', 'ended_at' => null],
            ['user_id' => $userMap['dean'],      'position_id' => $posMap['Dean'],                     'unit_id' => $coeId,   'started_at' => '2021-06-01', 'ended_at' => null],
            ['user_id' => $userMap['deptchair'], 'position_id' => $posMap['Department Chair'],         'unit_id' => $coeId,   'started_at' => '2022-08-15', 'ended_at' => null],
            ['user_id' => $userMap['inst1'],     'position_id' => $posMap['Instructor I'],             'unit_id' => $coeId,   'started_at' => '2019-06-01', 'ended_at' => null],
            ['user_id' => $userMap['instg2a'],   'position_id' => $posMap['Instructor II'],            'unit_id' => $coeId,   'started_at' => '2020-04-01', 'ended_at' => null],
            ['user_id' => $userMap['vpadfin'],   'position_id' => $posMap['Administrative Aide'],      'unit_id' => $accId,   'started_at' => '2023-11-01', 'ended_at' => null],
            ['user_id' => $userMap['cao'],       'position_id' => $posMap['Vice President'],           'unit_id' => $ovpafId, 'started_at' => '2015-05-01', 'ended_at' => null],
            ['user_id' => $userMap['hrdohead'],  'position_id' => $posMap['HRDO Head'],                'unit_id' => $hrdoId,  'started_at' => '2016-01-01', 'ended_at' => null],
            ['user_id' => $userMap['hrdo1'],     'position_id' => $posMap['Administrative Assistant'], 'unit_id' => $hrdoId,  'started_at' => '2021-09-01', 'ended_at' => null],
            ['user_id' => $userMap['hrdo2'],     'position_id' => $posMap['Administrative Assistant'], 'unit_id' => $hrdoId,  'started_at' => '2022-01-01', 'ended_at' => null],
        ];

        $db->table('plantillas')->insertBatch($plantillaData);

        echo "Database successfully seeded with RBAC roles and full Plantilla Hierarchy!\n";
    }
}