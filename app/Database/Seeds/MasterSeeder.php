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

        // ==========================================
        // 7. SEED TEMPLATES (IPCR/DPCR/OPCR/IPERF forms)
        // ==========================================
        $templatesData = [
            ['title' => 'IPCR', 'content' => '<table style="border-collapse: collapse; width: 100%; height: 1329.17px; margin-left: auto; margin-right: auto;" border="1" data-score-range="5"><colgroup><col style="width: 13.6857%;"><col style="width: 22.027%;"><col style="width: 17.8577%;"><col style="width: 7.13879%;"><col style="width: 7.13879%;"><col style="width: 7.13879%;"><col style="width: 7.13879%;"><col style="width: 17.8745%;"></colgroup>
<tbody>
<tr style="height: 40.375px;">
<td style="text-align: center;" colspan="8">INDIVIDUAL PERFORMANCE COMMITMENT AND REVIEW</td>
</tr>
<tr style="height: 40.375px;">
<td colspan="8">&nbsp;</td>
</tr>
<tr style="height: 66.375px;">
<td colspan="8">I, <span style="color: #ba372a;">FULL NAME HERE</span>, <span style="color: #ba372a;">Position and Official Designation </span>of the<span style="color: #ba372a;"> Office Name</span>, commit to deliver and agree to be rated on the attainment of the following targets in accordance with the indicated measure for the period (<span style="color: #ba372a;">January - June or July - December and Year; e.g., July - December 2026</span>).</td>
</tr>
<tr style="height: 40.375px;">
<td colspan="8">&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td colspan="3">
<div>
<div>(full name here)</div>
</div>
</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td colspan="3">
<div>
<div>Name of Employee</div>
</div>
</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 66.0417px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td colspan="3">
<div>
<div>Date: ___________________</div>
</div>
</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>APPROVED BY:</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>
<div>
<div>Rating Scale:</div>
</div>
</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>Name:</td>
<td>(name of office head)</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>
<div>
<div>5 &ndash; Outstanding</div>
</div>
</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 66.375px;">
<td>Position:</td>
<td>(position of office head)</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>
<div>
<div>4 &ndash; Very Satisfactory</div>
</div>
</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>Date:</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>
<div>
<div>3 &ndash; Satisfactory</div>
</div>
</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>
<div>
<div>2 &ndash; Unsatisfactory</div>
</div>
</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>
<div>
<div>1 &ndash; Poor</div>
</div>
</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>
<div>
<div>&nbsp;</div>
</div>
</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td rowspan="2">
<div>MAJOR FINAL OUTPUT</div>
</td>
<td>SUCCESS INDICATORS</td>
<td rowspan="2">
<div>ACTUAL ACCOMPLISHMENTS</div>
</td>
<td colspan="4">RATING</td>
<td rowspan="2">
<div>REMARKS</div>
</td>
</tr>
<tr style="height: 40.375px;">
<td>(TARGETS + MEASURES)</td>
<td>Q</td>
<td>T</td>
<td>E</td>
<td>Ave.</td>
</tr>
<tr style="height: 40.375px;">
<td colspan="8">CORE FUNCTIONS (70%)</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-row-avg" style="background-color: rgba(14, 165, 233, 0.25);" data-group-id="1">&nbsp;</td>
<td class="remarks" style="background-color: rgba(235, 49, 49, 0.25);">&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-row-avg" style="background-color: rgba(14, 165, 233, 0.25);" data-group-id="1">&nbsp;</td>
<td class="remarks" style="background-color: rgba(235, 49, 49, 0.25);">&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td colspan="8">STRATEGIC FUNCTIONS (20%)</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-row-avg" style="background-color: rgba(14, 165, 233, 0.25);" data-group-id="2">&nbsp;</td>
<td class="remarks" style="background-color: rgba(235, 49, 49, 0.25);">&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-row-avg" style="background-color: rgba(14, 165, 233, 0.25);" data-group-id="2">&nbsp;</td>
<td class="remarks" style="background-color: rgba(235, 49, 49, 0.25);">&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td colspan="8">SUPPORT FUNCTIONS (10%)</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-row-avg" style="background-color: rgba(14, 165, 233, 0.25);" data-group-id="3">&nbsp;</td>
<td class="remarks" style="background-color: rgba(235, 49, 49, 0.25);">&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-row-avg" style="background-color: rgba(14, 165, 233, 0.25);" data-group-id="3">&nbsp;</td>
<td class="remarks" style="background-color: rgba(235, 49, 49, 0.25);">&nbsp;</td>
</tr>
<tr style="height: 40.25px;">
<td colspan="8">&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td colspan="4">CORE</td>
<td class="calc-total" style="background-color: rgba(245, 158, 11, 0.25);" data-cell-weight="100" data-group-id="1">&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td colspan="4">STRATEGIC</td>
<td class="calc-total" style="background-color: rgba(245, 158, 11, 0.25);" data-cell-weight="100" data-group-id="2">&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td colspan="4">SUPPORT</td>
<td class="calc-total" style="background-color: rgba(245, 158, 11, 0.25);" data-cell-weight="100" data-group-id="3">&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td colspan="4">FINAL AVERAGE RATING</td>
<td class="calc-final-total" style="background-color: rgba(139, 92, 246, 0.25);">&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>Other Accomplishments</td>
<td colspan="7">&nbsp;</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>'],
            ['title' => 'DPCR', 'content' => '<table style="border-collapse: collapse; width: 100%; height: 1329.17px; margin-left: auto; margin-right: auto;" border="1" data-score-range="5"><colgroup><col style="width: 13.6857%;"><col style="width: 22.027%;"><col style="width: 17.8577%;"><col style="width: 7.13879%;"><col style="width: 7.13879%;"><col style="width: 7.13879%;"><col style="width: 7.13879%;"><col style="width: 17.8745%;"></colgroup>
<tbody>
<tr style="height: 40.375px;">
<td style="text-align: center;" colspan="8">INDIVIDUAL PERFORMANCE COMMITMENT AND REVIEW</td>
</tr>
<tr style="height: 40.375px;">
<td colspan="8">&nbsp;</td>
</tr>
<tr style="height: 66.375px;">
<td colspan="8">I, <span style="color: #ba372a;">FULL NAME HERE</span>, <span style="color: #ba372a;">Position and Official Designation </span>of the<span style="color: #ba372a;"> Office Name</span>, commit to deliver and agree to be rated on the attainment of the following targets in accordance with the indicated measure for the period (<span style="color: #ba372a;">January - June or July - December and Year; e.g., July - December 2026</span>).</td>
</tr>
<tr style="height: 40.375px;">
<td colspan="8">&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td colspan="3">
<div>
<div>(full name here)</div>
</div>
</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td colspan="3">
<div>
<div>Name of Employee</div>
</div>
</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 66.0417px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td colspan="3">
<div>
<div>Date: ___________________</div>
</div>
</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>APPROVED BY:</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>
<div>
<div>Rating Scale:</div>
</div>
</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>Name:</td>
<td>(name of office head)</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>
<div>
<div>5 &ndash; Outstanding</div>
</div>
</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 66.375px;">
<td>Position:</td>
<td>(position of office head)</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>
<div>
<div>4 &ndash; Very Satisfactory</div>
</div>
</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>Date:</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>
<div>
<div>3 &ndash; Satisfactory</div>
</div>
</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>
<div>
<div>2 &ndash; Unsatisfactory</div>
</div>
</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>
<div>
<div>1 &ndash; Poor</div>
</div>
</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>
<div>
<div>&nbsp;</div>
</div>
</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td rowspan="2">
<div>MAJOR FINAL OUTPUT</div>
</td>
<td>SUCCESS INDICATORS</td>
<td rowspan="2">
<div>ACTUAL ACCOMPLISHMENTS</div>
</td>
<td colspan="4">RATING</td>
<td rowspan="2">
<div>REMARKS</div>
</td>
</tr>
<tr style="height: 40.375px;">
<td>(TARGETS + MEASURES)</td>
<td>Q</td>
<td>T</td>
<td>E</td>
<td>Ave.</td>
</tr>
<tr style="height: 40.375px;">
<td colspan="8">CORE FUNCTIONS (70%)</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-row-avg" style="background-color: rgba(14, 165, 233, 0.25);" data-group-id="1">&nbsp;</td>
<td class="remarks" style="background-color: rgba(235, 49, 49, 0.25);">&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-row-avg" style="background-color: rgba(14, 165, 233, 0.25);" data-group-id="1">&nbsp;</td>
<td class="remarks" style="background-color: rgba(235, 49, 49, 0.25);">&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td colspan="8">STRATEGIC FUNCTIONS (20%)</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-row-avg" style="background-color: rgba(14, 165, 233, 0.25);" data-group-id="2">&nbsp;</td>
<td class="remarks" style="background-color: rgba(235, 49, 49, 0.25);">&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-row-avg" style="background-color: rgba(14, 165, 233, 0.25);" data-group-id="2">&nbsp;</td>
<td class="remarks" style="background-color: rgba(235, 49, 49, 0.25);">&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td colspan="8">SUPPORT FUNCTIONS (10%)</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-row-avg" style="background-color: rgba(14, 165, 233, 0.25);" data-group-id="3">&nbsp;</td>
<td class="remarks" style="background-color: rgba(235, 49, 49, 0.25);">&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-row-avg" style="background-color: rgba(14, 165, 233, 0.25);" data-group-id="3">&nbsp;</td>
<td class="remarks" style="background-color: rgba(235, 49, 49, 0.25);">&nbsp;</td>
</tr>
<tr style="height: 40.25px;">
<td colspan="8">&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td colspan="4">CORE</td>
<td class="calc-total" style="background-color: rgba(245, 158, 11, 0.25);" data-cell-weight="100" data-group-id="1">&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td colspan="4">STRATEGIC</td>
<td class="calc-total" style="background-color: rgba(245, 158, 11, 0.25);" data-cell-weight="100" data-group-id="2">&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td colspan="4">SUPPORT</td>
<td class="calc-total" style="background-color: rgba(245, 158, 11, 0.25);" data-cell-weight="100" data-group-id="3">&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td colspan="4">FINAL AVERAGE RATING</td>
<td class="calc-final-total" style="background-color: rgba(139, 92, 246, 0.25);">&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>Other Accomplishments</td>
<td colspan="7">&nbsp;</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>'],
            ['title' => 'OPCR', 'content' => '<table style="border-collapse: collapse; width: 100%; height: 1329.17px; margin-left: auto; margin-right: auto;" border="1" data-score-range="5"><colgroup><col style="width: 13.6857%;"><col style="width: 22.027%;"><col style="width: 17.8577%;"><col style="width: 7.13879%;"><col style="width: 7.13879%;"><col style="width: 7.13879%;"><col style="width: 7.13879%;"><col style="width: 17.8745%;"></colgroup>
<tbody>
<tr style="height: 40.375px;">
<td style="text-align: center;" colspan="8">INDIVIDUAL PERFORMANCE COMMITMENT AND REVIEW</td>
</tr>
<tr style="height: 40.375px;">
<td colspan="8">&nbsp;</td>
</tr>
<tr style="height: 66.375px;">
<td colspan="8">I, <span style="color: #ba372a;">FULL NAME HERE</span>, <span style="color: #ba372a;">Position and Official Designation </span>of the<span style="color: #ba372a;"> Office Name</span>, commit to deliver and agree to be rated on the attainment of the following targets in accordance with the indicated measure for the period (<span style="color: #ba372a;">January - June or July - December and Year; e.g., July - December 2026</span>).</td>
</tr>
<tr style="height: 40.375px;">
<td colspan="8">&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td colspan="3">
<div>
<div>(full name here)</div>
</div>
</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td colspan="3">
<div>
<div>Name of Employee</div>
</div>
</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 66.0417px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td colspan="3">
<div>
<div>Date: ___________________</div>
</div>
</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>APPROVED BY:</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>
<div>
<div>Rating Scale:</div>
</div>
</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>Name:</td>
<td>(name of office head)</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>
<div>
<div>5 &ndash; Outstanding</div>
</div>
</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 66.375px;">
<td>Position:</td>
<td>(position of office head)</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>
<div>
<div>4 &ndash; Very Satisfactory</div>
</div>
</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>Date:</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>
<div>
<div>3 &ndash; Satisfactory</div>
</div>
</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>
<div>
<div>2 &ndash; Unsatisfactory</div>
</div>
</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>
<div>
<div>1 &ndash; Poor</div>
</div>
</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>
<div>
<div>&nbsp;</div>
</div>
</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td rowspan="2">
<div>MAJOR FINAL OUTPUT</div>
</td>
<td>SUCCESS INDICATORS</td>
<td rowspan="2">
<div>ACTUAL ACCOMPLISHMENTS</div>
</td>
<td colspan="4">RATING</td>
<td rowspan="2">
<div>REMARKS</div>
</td>
</tr>
<tr style="height: 40.375px;">
<td>(TARGETS + MEASURES)</td>
<td>Q</td>
<td>T</td>
<td>E</td>
<td>Ave.</td>
</tr>
<tr style="height: 40.375px;">
<td colspan="8">CORE FUNCTIONS (70%)</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-row-avg" style="background-color: rgba(14, 165, 233, 0.25);" data-group-id="1">&nbsp;</td>
<td class="remarks" style="background-color: rgba(235, 49, 49, 0.25);">&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-row-avg" style="background-color: rgba(14, 165, 233, 0.25);" data-group-id="1">&nbsp;</td>
<td class="remarks" style="background-color: rgba(235, 49, 49, 0.25);">&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td colspan="8">STRATEGIC FUNCTIONS (20%)</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-row-avg" style="background-color: rgba(14, 165, 233, 0.25);" data-group-id="2">&nbsp;</td>
<td class="remarks" style="background-color: rgba(235, 49, 49, 0.25);">&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-row-avg" style="background-color: rgba(14, 165, 233, 0.25);" data-group-id="2">&nbsp;</td>
<td class="remarks" style="background-color: rgba(235, 49, 49, 0.25);">&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td colspan="8">SUPPORT FUNCTIONS (10%)</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-row-avg" style="background-color: rgba(14, 165, 233, 0.25);" data-group-id="3">&nbsp;</td>
<td class="remarks" style="background-color: rgba(235, 49, 49, 0.25);">&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-row-avg" style="background-color: rgba(14, 165, 233, 0.25);" data-group-id="3">&nbsp;</td>
<td class="remarks" style="background-color: rgba(235, 49, 49, 0.25);">&nbsp;</td>
</tr>
<tr style="height: 40.25px;">
<td colspan="8">&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td colspan="4">CORE</td>
<td class="calc-total" style="background-color: rgba(245, 158, 11, 0.25);" data-cell-weight="100" data-group-id="1">&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td colspan="4">STRATEGIC</td>
<td class="calc-total" style="background-color: rgba(245, 158, 11, 0.25);" data-cell-weight="100" data-group-id="2">&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td colspan="4">SUPPORT</td>
<td class="calc-total" style="background-color: rgba(245, 158, 11, 0.25);" data-cell-weight="100" data-group-id="3">&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td colspan="4">FINAL AVERAGE RATING</td>
<td class="calc-final-total" style="background-color: rgba(139, 92, 246, 0.25);">&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.375px;">
<td>Other Accomplishments</td>
<td colspan="7">&nbsp;</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>'],
            ['title' => 'IPERF', 'content' => '<table style="border-collapse: collapse; width: 100%; margin-left: auto; margin-right: auto; height: 1098.75px;" border="1" data-score-range="5"><colgroup><col style="width: 12.5%;"><col style="width: 12.5%;"><col style="width: 12.5%;"><col style="width: 5%;"><col style="width: 5%;"><col style="width: 5%;"><col style="width: 5%;"><col style="width: 12.5%;"></colgroup>
<tbody>
<tr style="height: 40.25px;">
<td style="text-align: center;" colspan="8">INDIVIDUAL PERFORMANCE EVALUATION RATING FORM FOR CONTRACT OF SERVICE AND JOB ORDER PERSONNEL</td>
</tr>
<tr style="height: 40.25px;">
<td colspan="8">(attach rubrics for the rating of actual accomplishments vis-&agrave;-vis expected outputs)</td>
</tr>
<tr style="height: 40.25px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.25px;">
<td>Name of Employee:</td>
<td colspan="2"><span style="color: #ba372a;">indicate full name (First Name Middle Initial Last Name, Extension; e.g., Juan D. Cruz III)</span></td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.25px;">
<td>Position:</td>
<td colspan="2"><span style="color: #ba372a;">indicate the full position title specified in the contract/job order</span></td>
<td>&nbsp;</td>
<td colspan="3">Classification:</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 66.375px;">
<td>Office:</td>
<td colspan="2"><span style="color: #ba372a;">indicate in full the specific area of assignment (e.g., CIS - Department of Development Communication)</span></td>
<td>&nbsp;</td>
<td colspan="3">Rating Period:</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.25px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.25px;">
<td rowspan="2">
<div>OFFICE PPA<br>(PROGRAMS, PROJECTS, ACTIVITIES)</div>
</td>
<td rowspan="2">
<div>EXPECTED OUTPUTS</div>
</td>
<td rowspan="2">
<div>ACTUAL ACCOMPLISHMENTS</div>
</td>
<td colspan="4">RATING</td>
<td>REMARKS</td>
</tr>
<tr style="height: 40.25px;">
<td>Q</td>
<td>T</td>
<td>E</td>
<td>Ave.</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 66.375px;">
<td><span style="color: #ba372a;">aligned with the deliverables of the office</span></td>
<td><span style="color: #ba372a;">based on contract or duties and responsibilities in the request to hire personnel</span></td>
<td>&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-row-avg" style="background-color: rgba(14, 165, 233, 0.25);">&nbsp;</td>
<td class="calc-final-total" style="background-color: rgba(139, 92, 246, 0.25);">&nbsp;</td>
</tr>
<tr style="height: 40.25px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-row-avg" style="background-color: rgba(14, 165, 233, 0.25);">&nbsp;</td>
<td class="calc-final-total" style="background-color: rgba(139, 92, 246, 0.25);">&nbsp;</td>
</tr>
<tr style="height: 40.25px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-rating" style="background-color: rgba(16, 185, 129, 0.25);">&nbsp;</td>
<td class="calc-row-avg" style="background-color: rgba(14, 165, 233, 0.25);">&nbsp;</td>
<td class="calc-final-total" style="background-color: rgba(139, 92, 246, 0.25);">&nbsp;</td>
</tr>
<tr style="height: 40.25px;">
<td colspan="6">OVERALL AVERAGE RATING</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.25px;">
<td colspan="8">Remarks/Suggestions/Recommendations on Ratee\'s Performance:</td>
</tr>
<tr style="height: 40.25px;">
<td><span style="color: #ba372a;">signed at the start of the rating period</span></td>
<td><span style="color: #ba372a;">&nbsp;</span></td>
<td><span style="color: #ba372a;">signed at the end of the rating period</span></td>
<td>&nbsp;</td>
<td colspan="4">&nbsp;</td>
</tr>
<tr style="height: 40.25px;">
<td>Targets prepared by:</td>
<td>&nbsp;</td>
<td>Rated by:</td>
<td>&nbsp;</td>
<td colspan="4">Measures:</td>
</tr>
<tr style="height: 40.25px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>Q</td>
<td colspan="2">Quality</td>
</tr>
<tr style="height: 40.25px;">
<td><span style="color: #ba372a;">(Signature over Printed Name)</span></td>
<td>&nbsp;</td>
<td><span style="color: #ba372a;">(Signature over Printed Name)</span></td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>T</td>
<td colspan="2">Timeliness</td>
</tr>
<tr style="height: 40.25px;">
<td>Employee (Ratee)</td>
<td>&nbsp;</td>
<td>Immediate Supervisor (Rater)</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>E</td>
<td colspan="2">Efficiency</td>
</tr>
<tr style="height: 40.25px;">
<td>Date:</td>
<td>&nbsp;</td>
<td>Date:</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 40.25px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td colspan="4">Rating Guide:</td>
</tr>
<tr style="height: 40.25px;">
<td>Approved by:</td>
<td>&nbsp;</td>
<td>Conforme:</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>5</td>
<td colspan="2">Outstanding</td>
</tr>
<tr style="height: 40.25px;">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>4</td>
<td colspan="2">Very Satisfactory</td>
</tr>
<tr style="height: 40.25px;">
<td><span style="color: #ba372a;">(Signature over Printed Name)</span></td>
<td>&nbsp;</td>
<td><span style="color: #ba372a;">(Signature over Printed Name)</span></td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>3</td>
<td colspan="2">Satisfactory</td>
</tr>
<tr style="height: 40.25px;">
<td>Immediate Supervisor (Rater)</td>
<td>&nbsp;</td>
<td>Employee (Ratee)</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>2</td>
<td colspan="2">Unsatisfactory</td>
</tr>
<tr style="height: 40.25px;">
<td>Date:</td>
<td>&nbsp;</td>
<td>Date:</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>1</td>
<td colspan="2">Poor</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>'],
        ];

        $db->table('templates')->insertBatch($templatesData);

        echo "Database successfully seeded with RBAC roles and full Plantilla Hierarchy!\n";
    }
}