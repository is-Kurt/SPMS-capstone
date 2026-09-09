<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\DocumentFolderModel;
use App\Enums\FolderStatus;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        helper('functions');
        $db = \Config\Database::connect();
        $folderModel = new DocumentFolderModel();

        echo ">>> [SPMS] Seeding BSU La Trinidad 4-Tier College & Department Hierarchy...\n";

        // ==========================================
        // 1. ROLES LOOKUP
        // ==========================================
        $roleMap = [];
        $roles = $db->table('roles')->get()->getResultArray();
        foreach ($roles as $r) {
            $roleMap[$r['name']] = (int)$r['id'];
        }

        // ==========================================
        // 2. ENSURE PARENT EXECUTIVE UNITS EXIST
        // ==========================================
        $ovpaa = $db->table('units')->where('name', 'OVPAA')->get()->getRowArray();
        if (!$ovpaa) {
            $db->table('units')->insert(['name' => 'OVPAA', 'parent_id' => null, 'created_at' => date('Y-m-d H:i:s')]);
            $ovpaaId = (int)$db->insertID();
        } else {
            $ovpaaId = (int)$ovpaa['id'];
        }

        $ovpaf = $db->table('units')->where('name', 'OVPAF')->get()->getRowArray();
        if (!$ovpaf) {
            $db->table('units')->insert(['name' => 'OVPAF', 'parent_id' => null, 'created_at' => date('Y-m-d H:i:s')]);
            $ovpafId = (int)$db->insertID();
        } else {
            $ovpafId = (int)$ovpaf['id'];
        }

        // ==========================================
        // 3. SEED / RESOLVE POSITIONS
        // ==========================================
        $positionsToEnsure = [
            ['title' => 'Vice President',           'is_teaching' => 0],
            ['title' => 'Dean',                     'is_teaching' => 1],
            ['title' => 'Department Chair',         'is_teaching' => 1],
            ['title' => 'Instructor I',             'is_teaching' => 1],
            ['title' => 'Instructor II',            'is_teaching' => 1],
            ['title' => 'Assistant Professor',      'is_teaching' => 1],
            ['title' => 'Associate Professor',      'is_teaching' => 1],
            ['title' => 'Professor',                'is_teaching' => 1],
            ['title' => 'Administrative Aide',      'is_teaching' => 0],
            ['title' => 'Administrative Assistant', 'is_teaching' => 0],
            ['title' => 'Unit Head',                'is_teaching' => 0],
            ['title' => 'Security Officer',         'is_teaching' => 0],
        ];

        $posMap = [];
        foreach ($positionsToEnsure as $p) {
            $existing = $db->table('positions')->where('title', $p['title'])->get()->getRowArray();
            if ($existing) {
                $posMap[$p['title']] = (int)$existing['id'];
            } else {
                $db->table('positions')->insert($p);
                $posMap[$p['title']] = (int)$db->insertID();
            }
        }

        // Cache templates for form cloning
        $templatesByTitle = [];
        $templates = $db->table('templates')->get()->getResultArray();
        foreach ($templates as $tpl) {
            $templatesByTitle[$tpl['title']] = $tpl;
        }

        // ==========================================
        // 4. BSU LA TRINIDAD COLLEGES & DEPARTMENTS
        // ==========================================
        $bsuHierarchy = [
            'College of Agriculture' => [
                'Department of Agricultural Economics & Agribusiness',
                'Department of Agronomy',
                'Department of Horticulture',
                'Department of Animal Science',
                'Department of Entomology',
                'Department of Plant Pathology',
                'Department of Soil Science',
                'Department of Extension Education',
            ],
            'College of Arts and Humanities' => [
                'Department of Communication',
                'Department of English Language',
                'Department of Filipino Language',
            ],
            'College of Engineering' => [
                'Department of Agricultural and Biosystems Engineering',
                'Department of Civil Engineering',
                'Department of Electrical Engineering',
                'Department of Industrial Engineering',
            ],
            'College of Forestry' => [
                'Department of Forest Science',
                'Department of Agroforestry',
            ],
            'College of Human Ecology' => [
                'Department of Hospitality Management',
                'Department of Nutrition and Dietetics',
                'Department of Entrepreneurship',
                'Department of Food Technology',
                'Department of Tourism Management',
            ],
            'College of Human Kinetics' => [
                'Department of Physical Education',
                'Department of Exercise and Sports Sciences',
            ],
            'College of Information Sciences' => [
                'Department of Information Technology',
                'Department of Development Communication',
                'Department of Library and Information Science',
            ],
            'College of Medicine' => [
                'Department of Basic Medical Sciences',
                'Department of Clinical Practice',
            ],
            'College of Natural Sciences' => [
                'Department of Biology',
                'Department of Chemistry',
                'Department of Environmental Science',
            ],
            'College of Numeracy and Applied Sciences' => [
                'Department of Mathematics',
                'Department of Statistics',
                'Department of Physics',
            ],
            'College of Nursing' => [
                'Department of Nursing',
            ],
            'College of Public Administration and Governance' => [
                'Department of Public Administration',
            ],
            'College of Social Sciences' => [
                'Department of Psychology',
                'Department of History and Social Studies',
            ],
            'College of Teacher Education' => [
                'Department of Early Childhood & Elementary Education',
                'Department of Secondary Education',
                'Department of Technology and Livelihood Education',
            ],
            'College of Veterinary Medicine' => [
                'Department of Veterinary Medicine',
            ],
        ];

        $unitIdMap = [];
        $now = date('Y-m-d H:i:s');

        // Seed 15 Colleges under OVPAA
        foreach ($bsuHierarchy as $collegeName => $departments) {
            $existingCollege = $db->table('units')->where('name', $collegeName)->get()->getRowArray();
            if ($existingCollege) {
                $collegeId = (int)$existingCollege['id'];
                if ($existingCollege['parent_id'] != $ovpaaId) {
                    $db->table('units')->where('id', $collegeId)->update(['parent_id' => $ovpaaId]);
                }
            } else {
                $db->table('units')->insert([
                    'name'       => $collegeName,
                    'parent_id'  => $ovpaaId,
                    'created_at' => $now
                ]);
                $collegeId = (int)$db->insertID();
            }
            $unitIdMap[$collegeName] = $collegeId;

            // Seed Constituent Departments under this College
            foreach ($departments as $deptName) {
                $existingDept = $db->table('units')->where('name', $deptName)->get()->getRowArray();
                if ($existingDept) {
                    $deptId = (int)$existingDept['id'];
                    // Ensure linked to this college if not assigned
                    if (empty($existingDept['parent_id'])) {
                        $db->table('units')->where('id', $deptId)->update(['parent_id' => $collegeId]);
                    }
                } else {
                    $db->table('units')->insert([
                        'name'       => $deptName,
                        'parent_id'  => $collegeId,
                        'created_at' => $now
                    ]);
                    $deptId = (int)$db->insertID();
                }
                $unitIdMap[$deptName] = $deptId;
            }
        }

        // Seed Non-Teaching Administrative Offices under OVPAF
        $gso = $db->table('units')->where('name', 'General Services Office')->get()->getRowArray();
        if (!$gso) {
            $db->table('units')->insert([
                'name'       => 'General Services Office',
                'parent_id'  => $ovpafId,
                'created_at' => $now
            ]);
            $gsoId = (int)$db->insertID();
        } else {
            $gsoId = (int)$gso['id'];
            if ($gso['parent_id'] != $ovpafId) {
                $db->table('units')->where('id', $gsoId)->update(['parent_id' => $ovpafId]);
            }
        }
        $unitIdMap['General Services Office'] = $gsoId;

        // ==========================================
        // 5. DEFINE REALISTIC 4-TIER ROSTERS
        // ==========================================
        $password = password_hash('123', PASSWORD_DEFAULT);

        $rosterGroups = [
            // --- College of Information Sciences (Showcase) ---
            [
                'college_name' => 'College of Information Sciences',
                'dept_name'    => 'Department of Information Technology',
                'users' => [
                    [
                        'email'      => 'dean.cis@test.com',
                        'first_name' => 'Alexander',
                        'last_name'  => 'Mercado',
                        'role'       => 'Supervisor',
                        'position'   => 'Dean',
                        'doc_type'   => 'DPCR',
                        'tier'       => 'dean',
                        'unit_level' => 'college',
                        'rating'     => 4.95,
                        'status'     => FolderStatus::APPROVED->value,
                        'remark'     => null
                    ],
                    [
                        'email'      => 'chair.cis@test.com',
                        'first_name' => 'Clarissa',
                        'last_name'  => 'Diaz',
                        'role'       => 'Supervisor',
                        'position'   => 'Department Chair',
                        'doc_type'   => 'DPCR',
                        'tier'       => 'chair',
                        'unit_level' => 'department',
                        'rating'     => 4.83,
                        'status'     => FolderStatus::APPROVED->value,
                        'remark'     => null
                    ],
                    [
                        'email'      => 'faculty.cis1@test.com',
                        'first_name' => 'Kenji',
                        'last_name'  => 'Tanaka',
                        'role'       => 'Employee',
                        'position'   => 'Assistant Professor',
                        'doc_type'   => 'IPCR',
                        'tier'       => 'faculty',
                        'unit_level' => 'department',
                        'rating'     => 4.90,
                        'status'     => FolderStatus::APPROVED->value,
                        'remark'     => null
                    ],
                    [
                        'email'      => 'faculty.cis2@test.com',
                        'first_name' => 'Stephanie',
                        'last_name'  => 'Reyes',
                        'role'       => 'Employee',
                        'position'   => 'Instructor II',
                        'doc_type'   => 'IPCR',
                        'tier'       => 'faculty',
                        'unit_level' => 'department',
                        'rating'     => null,
                        'status'     => FolderStatus::TO_EVALUATE->value,
                        'remark'     => 'Targets approved; currently self-rating accomplishments.'
                    ],
                ]
            ],

            // --- College of Nursing (Used in End-to-End Automated Test Cycle) ---
            [
                'college_name' => 'College of Nursing',
                'dept_name'    => 'Department of Nursing',
                'users' => [
                    [
                        'email'      => 'dean@test.com',
                        'first_name' => 'Roberto',
                        'last_name'  => 'Reyes',
                        'role'       => 'Supervisor',
                        'position'   => 'Dean',
                        'doc_type'   => 'DPCR',
                        'tier'       => 'dean',
                        'unit_level' => 'college',
                        'rating'     => 4.85,
                        'status'     => FolderStatus::TARGET_APPROVED->value,
                        'remark'     => null
                    ],
                    [
                        'email'      => 'chair@test.com',
                        'first_name' => 'Miguel',
                        'last_name'  => 'Cruz',
                        'role'       => 'Supervisor',
                        'position'   => 'Department Chair',
                        'doc_type'   => 'DPCR',
                        'tier'       => 'chair',
                        'unit_level' => 'department',
                        'rating'     => 4.75,
                        'status'     => FolderStatus::TARGET_APPROVED->value,
                        'remark'     => null
                    ],
                    [
                        'email'      => 'faculty@test.com',
                        'first_name' => 'Carlos',
                        'last_name'  => 'Lim',
                        'role'       => 'Employee',
                        'position'   => 'Assistant Professor',
                        'doc_type'   => 'IPCR',
                        'tier'       => 'faculty',
                        'unit_level' => 'department',
                        'rating'     => 4.80,
                        'status'     => FolderStatus::TARGET_APPROVED->value,
                        'remark'     => null
                    ],
                ]
            ],

            // --- College of Agriculture ---
            [
                'college_name' => 'College of Agriculture',
                'dept_name'    => 'Department of Agronomy',
                'users' => [
                    [
                        'email'      => 'dean.agri@test.com',
                        'first_name' => 'Julian',
                        'last_name'  => 'Ramos',
                        'role'       => 'Supervisor',
                        'position'   => 'Dean',
                        'doc_type'   => 'DPCR',
                        'tier'       => 'dean',
                        'unit_level' => 'college',
                        'rating'     => 4.88,
                        'status'     => FolderStatus::APPROVED->value,
                        'remark'     => null
                    ],
                    [
                        'email'      => 'chair.agri@test.com',
                        'first_name' => 'Lorna',
                        'last_name'  => 'Mendoza',
                        'role'       => 'Supervisor',
                        'position'   => 'Department Chair',
                        'doc_type'   => 'DPCR',
                        'tier'       => 'chair',
                        'unit_level' => 'department',
                        'rating'     => 4.75,
                        'status'     => FolderStatus::APPROVED->value,
                        'remark'     => null
                    ],
                    [
                        'email'      => 'faculty.agri1@test.com',
                        'first_name' => 'Edgar',
                        'last_name'  => 'Santos',
                        'role'       => 'Employee',
                        'position'   => 'Assistant Professor',
                        'doc_type'   => 'IPCR',
                        'tier'       => 'faculty',
                        'unit_level' => 'department',
                        'rating'     => 4.82,
                        'status'     => FolderStatus::APPROVED->value,
                        'remark'     => null
                    ],
                    [
                        'email'      => 'faculty.agri2@test.com',
                        'first_name' => 'Maricel',
                        'last_name'  => 'Flores',
                        'role'       => 'Employee',
                        'position'   => 'Instructor II',
                        'doc_type'   => 'IPCR',
                        'tier'       => 'faculty',
                        'unit_level' => 'department',
                        'rating'     => null,
                        'status'     => FolderStatus::PENDING_TARGET_APPROVAL->value,
                        'remark'     => 'Targets submitted; pending immediate supervisor approval.'
                    ],
                ]
            ],

            // --- College of Teacher Education ---
            [
                'college_name' => 'College of Teacher Education',
                'dept_name'    => 'Department of Secondary Education',
                'users' => [
                    [
                        'email'      => 'dean.cte@test.com',
                        'first_name' => 'Victoria',
                        'last_name'  => 'Salazar',
                        'role'       => 'Supervisor',
                        'position'   => 'Dean',
                        'doc_type'   => 'DPCR',
                        'tier'       => 'dean',
                        'unit_level' => 'college',
                        'rating'     => 4.92,
                        'status'     => FolderStatus::APPROVED->value,
                        'remark'     => null
                    ],
                    [
                        'email'      => 'chair.cte@test.com',
                        'first_name' => 'Arthur',
                        'last_name'  => 'Perez',
                        'role'       => 'Supervisor',
                        'position'   => 'Department Chair',
                        'doc_type'   => 'DPCR',
                        'tier'       => 'chair',
                        'unit_level' => 'department',
                        'rating'     => 4.79,
                        'status'     => FolderStatus::APPROVED->value,
                        'remark'     => null
                    ],
                    [
                        'email'      => 'faculty.cte1@test.com',
                        'first_name' => 'Rowena',
                        'last_name'  => 'Gomez',
                        'role'       => 'Employee',
                        'position'   => 'Associate Professor',
                        'doc_type'   => 'IPCR',
                        'tier'       => 'faculty',
                        'unit_level' => 'department',
                        'rating'     => 4.86,
                        'status'     => FolderStatus::APPROVED->value,
                        'remark'     => null
                    ],
                    [
                        'email'      => 'faculty.cte2@test.com',
                        'first_name' => 'Danilo',
                        'last_name'  => 'Castillo',
                        'role'       => 'Employee',
                        'position'   => 'Instructor I',
                        'doc_type'   => 'IPCR',
                        'tier'       => 'faculty',
                        'unit_level' => 'department',
                        'rating'     => null,
                        'status'     => FolderStatus::TARGET_RETURNED->value,
                        'remark'     => '[Chair Arthur Perez]: Module production target needs to be at least 3 instructional modules per academic guidelines.'
                    ],
                ]
            ],

            // --- College of Natural Sciences ---
            [
                'college_name' => 'College of Natural Sciences',
                'dept_name'    => 'Department of Biology',
                'users' => [
                    [
                        'email'      => 'dean.cns@test.com',
                        'first_name' => 'Gabriel',
                        'last_name'  => 'Morales',
                        'role'       => 'Supervisor',
                        'position'   => 'Dean',
                        'doc_type'   => 'DPCR',
                        'tier'       => 'dean',
                        'unit_level' => 'college',
                        'rating'     => 4.80,
                        'status'     => FolderStatus::APPROVED->value,
                        'remark'     => null
                    ],
                    [
                        'email'      => 'chair.cns@test.com',
                        'first_name' => 'Corazon',
                        'last_name'  => 'Villanueva',
                        'role'       => 'Supervisor',
                        'position'   => 'Department Chair',
                        'doc_type'   => 'DPCR',
                        'tier'       => 'chair',
                        'unit_level' => 'department',
                        'rating'     => 4.65,
                        'status'     => FolderStatus::APPROVED->value,
                        'remark'     => null
                    ],
                    [
                        'email'      => 'faculty.cns1@test.com',
                        'first_name' => 'Ferdinand',
                        'last_name'  => 'Aquino',
                        'role'       => 'Employee',
                        'position'   => 'Professor',
                        'doc_type'   => 'IPCR',
                        'tier'       => 'faculty',
                        'unit_level' => 'department',
                        'rating'     => 4.70,
                        'status'     => FolderStatus::APPROVED->value,
                        'remark'     => null
                    ],
                    [
                        'email'      => 'faculty.cns2@test.com',
                        'first_name' => 'Lilibeth',
                        'last_name'  => 'Torres',
                        'role'       => 'Employee',
                        'position'   => 'Instructor I',
                        'doc_type'   => 'IPCR',
                        'tier'       => 'faculty',
                        'unit_level' => 'department',
                        'rating'     => null,
                        'status'     => FolderStatus::TARGET_RETURNED->value,
                        'remark'     => '[Chair Corazon Villanueva]: Please revise research commitments and specify target submission dates for indexed journal publication.'
                    ],
                ]
            ],

            // --- General Services Office (Non-Teaching Admin Unit) ---
            [
                'college_name' => 'General Services Office',
                'dept_name'    => 'General Services Office',
                'users' => [
                    [
                        'email'      => 'head.gso@test.com',
                        'first_name' => 'Rodrigo',
                        'last_name'  => 'Estrada',
                        'role'       => 'Supervisor',
                        'position'   => 'Unit Head',
                        'doc_type'   => 'DPCR',
                        'tier'       => 'chair',
                        'unit_level' => 'college',
                        'rating'     => 4.60,
                        'status'     => FolderStatus::APPROVED->value,
                        'remark'     => null
                    ],
                    [
                        'email'      => 'staff.gso1@test.com',
                        'first_name' => 'Nestor',
                        'last_name'  => 'Pascual',
                        'role'       => 'Employee',
                        'position'   => 'Administrative Aide',
                        'doc_type'   => 'IPERF',
                        'tier'       => 'faculty',
                        'unit_level' => 'college',
                        'rating'     => null,
                        'status'     => FolderStatus::SUBMITTED->value,
                        'remark'     => 'Accomplishments and MOVs submitted for head rating.'
                    ],
                    [
                        'email'      => 'staff.gso2@test.com',
                        'first_name' => 'Leonora',
                        'last_name'  => 'Villar',
                        'role'       => 'Employee',
                        'position'   => 'Administrative Assistant',
                        'doc_type'   => 'IPERF',
                        'tier'       => 'faculty',
                        'unit_level' => 'college',
                        'rating'     => null,
                        'status'     => FolderStatus::TARGET_RETURNED->value,
                        'remark'     => '[Unit Head Rodrigo Estrada]: Please clarify preventive maintenance schedule targets for university campus facilities.'
                    ],
                ]
            ],
        ];

        // Find active cycle folder (root folder) to attach folders if available
        $activeCycle = $db->table('document_folders')
            ->where('parent_folder_id IS NULL')
            ->where('deleted_at IS NULL')
            ->orderBy('created_at', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        // Find VPAA folder in that cycle (if available)
        $vpaaFolder = null;
        if ($activeCycle) {
            $vpaaUser = $db->table('users')->where('email', 'vpaa@test.com')->get()->getRowArray();
            if ($vpaaUser) {
                $vpaaFolder = $db->table('document_folders')
                    ->where('parent_folder_id', $activeCycle['id'])
                    ->where('user_id', $vpaaUser['id'])
                    ->where('deleted_at IS NULL')
                    ->get()->getRowArray();
            }
        }

        $createdUserCount = 0;
        $createdDocCount = 0;

        foreach ($rosterGroups as $group) {
            $collegeUnitId = $unitIdMap[$group['college_name']] ?? $ovpaaId;
            $deptUnitId    = $unitIdMap[$group['dept_name']] ?? $collegeUnitId;

            $deanFolderId  = null;
            $chairFolderId = null;
            $deanUserId    = null;
            $chairUserId   = null;

            foreach ($group['users'] as $u) {
                // A. Ensure User exists
                $existingUser = $db->table('users')->where('email', $u['email'])->get()->getRowArray();
                if ($existingUser) {
                    $userId = (int)$existingUser['id'];
                    $db->table('users')->where('id', $userId)->update([
                        'password'   => $password,
                        'is_active'  => 1,
                        'first_name' => $u['first_name'],
                        'last_name'  => $u['last_name'],
                        'doc_type'   => $u['doc_type'],
                    ]);
                } else {
                    $db->table('users')->insert([
                        'email'      => $u['email'],
                        'first_name' => $u['first_name'],
                        'last_name'  => $u['last_name'],
                        'password'   => $password,
                        'is_active'  => 1,
                        'doc_type'   => $u['doc_type'],
                    ]);
                    $userId = (int)$db->insertID();
                    $createdUserCount++;
                }

                // B. Assign Role
                $roleId = $roleMap[$u['role']] ?? 4;
                $existingUserRole = $db->table('user_roles')->where('user_id', $userId)->get()->getRowArray();
                if ($existingUserRole) {
                    $db->table('user_roles')->where('user_id', $userId)->update(['role_id' => $roleId]);
                } else {
                    $db->table('user_roles')->insert([
                        'user_id' => $userId,
                        'role_id' => $roleId
                    ]);
                }

                // C. Assign Plantilla with authentic Unit level
                // Deans belong to the College unit; Chairs and Faculty belong to their constituent Department unit!
                $userUnitId = ($u['unit_level'] === 'college') ? $collegeUnitId : $deptUnitId;

                $posId = $posMap[$u['position']] ?? 4;
                $existingPlantilla = $db->table('plantillas')
                    ->where('user_id', $userId)
                    ->where('ended_at IS NULL')
                    ->get()->getRowArray();
                if ($existingPlantilla) {
                    $db->table('plantillas')->where('id', $existingPlantilla['id'])->update([
                        'position_id' => $posId,
                        'unit_id'     => $userUnitId,
                    ]);
                } else {
                    $db->table('plantillas')->insert([
                        'user_id'     => $userId,
                        'position_id' => $posId,
                        'unit_id'     => $userUnitId,
                        'started_at'  => '2023-01-01',
                        'ended_at'    => null,
                    ]);
                }

                // Track hierarchy for folders
                if ($u['tier'] === 'dean') {
                    $deanUserId = $userId;
                } elseif ($u['tier'] === 'chair') {
                    $chairUserId = $userId;
                }

                // D. Hook into Active Evaluation Cycle if present
                if ($activeCycle) {
                    $parentFolderId = null;
                    if ($u['tier'] === 'dean') {
                        $parentFolderId = $vpaaFolder ? $vpaaFolder['id'] : $activeCycle['id'];
                    } elseif ($u['tier'] === 'chair') {
                        $parentFolderId = $deanFolderId ?? ($vpaaFolder ? $vpaaFolder['id'] : $activeCycle['id']);
                    } else {
                        $parentFolderId = $chairFolderId ?? $deanFolderId ?? ($vpaaFolder ? $vpaaFolder['id'] : $activeCycle['id']);
                    }

                    $existingFolder = $db->table('document_folders')
                        ->where('user_id', $userId)
                        ->where('title', $activeCycle['title'])
                        ->where('deleted_at IS NULL')
                        ->get()->getRowArray();

                    $isTargetPhase = in_array($u['status'], [
                        FolderStatus::DRAFT_TARGET->value,
                        FolderStatus::PENDING_TARGET_APPROVAL->value,
                        FolderStatus::TARGET_RETURNED->value
                    ]);

                    if (!$existingFolder) {
                        $folderId = generate_short_id();
                        $folderData = [
                            'id'                  => $folderId,
                            'title'               => $activeCycle['title'],
                            'user_id'             => $userId,
                            'parent_folder_id'    => $parentFolderId,
                            'final_rating'        => $u['rating'],
                            'status'              => $u['status'],
                            'target_submitted_at' => $now,
                            'target_approved_at'  => $isTargetPhase ? null : $now,
                            'submitted_at'        => in_array($u['status'], [FolderStatus::SUBMITTED->value, FolderStatus::APPROVED->value]) ? $now : null,
                            'rated_at'            => $u['rating'] ? $now : null,
                            'ipcr_target_start'   => $activeCycle['ipcr_target_start'] ?? date('Y-m-d', strtotime('-15 days')),
                            'ipcr_target_end'     => $activeCycle['ipcr_target_end'] ?? date('Y-m-d', strtotime('+15 days')),
                            'ipcr_eval_start'     => $activeCycle['ipcr_eval_start'] ?? date('Y-m-d', strtotime('+16 days')),
                            'ipcr_eval_end'       => $activeCycle['ipcr_eval_end'] ?? date('Y-m-d', strtotime('+45 days')),
                            'dpcr_target_start'   => $activeCycle['dpcr_target_start'] ?? date('Y-m-d', strtotime('-15 days')),
                            'dpcr_target_end'     => $activeCycle['dpcr_target_end'] ?? date('Y-m-d', strtotime('+15 days')),
                            'dpcr_eval_start'     => $activeCycle['dpcr_eval_start'] ?? date('Y-m-d', strtotime('+16 days')),
                            'dpcr_eval_end'       => $activeCycle['dpcr_eval_end'] ?? date('Y-m-d', strtotime('+45 days')),
                            'opcr_target_start'   => $activeCycle['opcr_target_start'] ?? date('Y-m-d', strtotime('-15 days')),
                            'opcr_target_end'     => $activeCycle['opcr_target_end'] ?? date('Y-m-d', strtotime('+15 days')),
                            'opcr_eval_start'     => $activeCycle['opcr_eval_start'] ?? date('Y-m-d', strtotime('+16 days')),
                            'opcr_eval_end'       => $activeCycle['opcr_eval_end'] ?? date('Y-m-d', strtotime('+45 days')),
                            'created_at'          => $now,
                            'updated_at'          => $now
                        ];

                        $db->table('document_folders')->insert($folderData);

                        if ($u['tier'] === 'faculty' && $chairUserId) {
                            $db->table('evaluation_routings')->insert([
                                'folder_id'           => $folderId,
                                'evaluator_id'        => $chairUserId,
                                'status'              => $u['status'],
                                'evaluator_folder_id' => $chairFolderId,
                                'created_at'          => $now,
                                'updated_at'          => $now
                            ]);
                        }
                    } else {
                        $folderId = $existingFolder['id'];
                        $db->table('document_folders')->where('id', $folderId)->update([
                            'status'              => $u['status'],
                            'final_rating'        => $u['rating'],
                            'target_submitted_at' => $now,
                            'target_approved_at'  => $isTargetPhase ? null : ($existingFolder['target_approved_at'] ?: $now),
                            'submitted_at'        => in_array($u['status'], [FolderStatus::SUBMITTED->value, FolderStatus::APPROVED->value]) ? $now : null,
                            'rated_at'            => $u['rating'] ? $now : null,
                            'updated_at'          => $now
                        ]);
                    }

                    if ($u['tier'] === 'dean') {
                        $deanFolderId = $folderId;
                    } elseif ($u['tier'] === 'chair') {
                        $chairFolderId = $folderId;
                    }

                    // E. Form Document in Folder
                    $existingDoc = $db->table('documents')->where('document_folder_id', $folderId)->get()->getRowArray();
                    if (!$existingDoc) {
                        $tpl = $templatesByTitle[$u['doc_type']] ?? null;
                        $tabsData = $tpl ? json_decode($tpl['tabs'] ?? '[]', true) : [];

                        if (!empty($u['remark']) && !empty($tabsData[0]['formData']['categories']['core'][0])) {
                            $tabsData[0]['formData']['categories']['core'][0]['remarks'] = $u['remark'];
                        }

                        $docId = generate_short_id();
                        $db->table('documents')->insert([
                            'id'                 => $docId,
                            'document_folder_id' => $folderId,
                            'title'              => "{$u['doc_type']} — {$u['first_name']} {$u['last_name']}",
                            'is_target'          => $isTargetPhase ? 1 : 0,
                            'tabs'               => json_encode($tabsData),
                            'created_at'         => $now,
                            'updated_at'         => $now
                        ]);
                        $createdDocCount++;
                    }
                }
            }
        }

        // Clean up old CAS if present and transfer any unlinked rows
        $cas = $db->table('units')->where('name', 'College of Arts and Sciences')->get()->getRowArray();
        if ($cas) {
            $cahId = $unitIdMap['College of Arts and Humanities'] ?? null;
            if ($cahId) {
                // Point any sub-units of CAS to CAH
                $db->table('units')->where('parent_id', $cas['id'])->update(['parent_id' => $cahId]);
                $db->table('units')->where('id', $cas['id'])->delete();
            }
        }

        echo ">>> [SPMS] Seeder complete!\n";
        echo "    - 15 BSU Colleges & Constituent Departments Seeded\n";
        echo "    - 4-Tier Rosters Initialized\n";
        echo "    - Default Password: 123\n";
    }
}
