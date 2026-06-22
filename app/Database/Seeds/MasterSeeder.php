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
        // Supervisors: Santos (VP OVPAA), Reyes (Dean), Navarro (VP OVPAF)
        $supervisorEmails = ['santos@test.com', 'reyes@test.com', 'navarro@test.com'];

        $usersData = [
            ['email' => 'admin@test.com',      'first_name' => 'System', 'last_name' => 'Admin',    'password' => $password, 'is_active' => 1],
            ['email' => 'santos@test.com',     'first_name' => 'Jose',   'last_name' => 'Santos',   'password' => $password, 'is_active' => 1],
            ['email' => 'reyes@test.com',      'first_name' => 'Maria',  'last_name' => 'Reyes',    'password' => $password, 'is_active' => 1],
            ['email' => 'cruz@test.com',       'first_name' => 'Juan',   'last_name' => 'Cruz',     'password' => $password, 'is_active' => 1],
            ['email' => 'lim@test.com',        'first_name' => 'Ana',    'last_name' => 'Lim',      'password' => $password, 'is_active' => 1],
            ['email' => 'garcia@test.com',     'first_name' => 'Luz',    'last_name' => 'Garcia',   'password' => $password, 'is_active' => 1],
            ['email' => 'tan@test.com',        'first_name' => 'Mark',   'last_name' => 'Tan',      'password' => $password, 'is_active' => 1],
            ['email' => 'navarro@test.com',    'first_name' => 'Luis',   'last_name' => 'Navarro',  'password' => $password, 'is_active' => 1],
            ['email' => 'flores@test.com',     'first_name' => 'Rosa',   'last_name' => 'Flores',   'password' => $password, 'is_active' => 1],
            ['email' => 'bautista@test.com',   'first_name' => 'Paul',   'last_name' => 'Bautista', 'password' => $password, 'is_active' => 1],
        ];

        $userMap = [];
        foreach ($usersData as $u) {
            $db->table('users')->insert($u);
            $userId = $db->insertID();
            $userMap[$u['last_name']] = $userId;

            // ==========================================
            // 3. SEED USER ROLES PIVOT
            // ==========================================
            if ($u['email'] === 'admin@test.com') {
                $roleId = $adminRoleId;
            } elseif (in_array($u['email'], $supervisorEmails)) {
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
            ['title' => 'Instructor I',             'is_teaching' => 1],
            ['title' => 'Instructor II',            'is_teaching' => 1],
            ['title' => 'Registrar',                'is_teaching' => 0],
            ['title' => 'Administrative Aide',      'is_teaching' => 0],
            ['title' => 'Accountant III',           'is_teaching' => 0],
            ['title' => 'Administrative Assistant', 'is_teaching' => 0],
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
            ['user_id' => $userMap['Santos'],  'position_id' => $posMap['Vice President'],           'unit_id' => $ovpaaId, 'started_at' => '2020-01-01', 'ended_at' => null],
            ['user_id' => $userMap['Reyes'],   'position_id' => $posMap['Dean'],                     'unit_id' => $coeId,   'started_at' => '2021-06-01', 'ended_at' => null],
            ['user_id' => $userMap['Cruz'],    'position_id' => $posMap['Instructor I'],             'unit_id' => $coeId,   'started_at' => '2022-08-15', 'ended_at' => null],
            ['user_id' => $userMap['Lim'],     'position_id' => $posMap['Instructor II'],            'unit_id' => $coeId,   'started_at' => '2019-06-01', 'ended_at' => null],
            ['user_id' => $userMap['Garcia'],  'position_id' => $posMap['Registrar'],                'unit_id' => $regId,   'started_at' => '2018-03-01', 'ended_at' => null],
            ['user_id' => $userMap['Tan'],     'position_id' => $posMap['Administrative Aide'],      'unit_id' => $regId,   'started_at' => '2023-11-01', 'ended_at' => null],
            ['user_id' => $userMap['Navarro'], 'position_id' => $posMap['Vice President'],           'unit_id' => $ovpafId, 'started_at' => '2015-05-01', 'ended_at' => null],
            ['user_id' => $userMap['Flores'],  'position_id' => $posMap['Accountant III'],           'unit_id' => $accId,   'started_at' => '2017-02-10', 'ended_at' => null],
            ['user_id' => $userMap['Bautista'],'position_id' => $posMap['Administrative Assistant'], 'unit_id' => $accId,   'started_at' => '2021-09-01', 'ended_at' => null],
        ];

        $db->table('plantilla')->insertBatch($plantillaData);

        echo "Database successfully seeded with RBAC roles and full Plantilla Hierarchy!\n";
    }
}