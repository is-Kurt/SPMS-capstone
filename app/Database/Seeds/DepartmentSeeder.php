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

        echo ">>> [SPMS] Starting Department & Multi-Account Seeder (With Returns & Form Submissions)...\n";

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
            $db->table('units')->insert(['name' => 'OVPAA', 'parent_id' => null]);
            $ovpaaId = $db->insertID();
        } else {
            $ovpaaId = (int)$ovpaa['id'];
        }

        $ovpaf = $db->table('units')->where('name', 'OVPAF')->get()->getRowArray();
        if (!$ovpaf) {
            $db->table('units')->insert(['name' => 'OVPAF', 'parent_id' => null]);
            $ovpafId = $db->insertID();
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
        // 4. DEFINE UNITS & REALISTIC ROSTER
        // ==========================================
        $password = password_hash('123', PASSWORD_DEFAULT);

        $roster = [
            // --- College of Agriculture ---
            [
                'unit' => ['name' => 'College of Agriculture', 'parent_id' => $ovpaaId],
                'users' => [
                    [
                        'email'      => 'dean.agri@test.com',
                        'first_name' => 'Julian',
                        'last_name'  => 'Ramos',
                        'role'       => 'Supervisor',
                        'position'   => 'Dean',
                        'doc_type'   => 'OPCR',
                        'tier'       => 'dean',
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
                        'rating'     => null,
                        'status'     => FolderStatus::PENDING_TARGET_APPROVAL->value,
                        'remark'     => 'Targets submitted; pending immediate supervisor approval.'
                    ],
                ]
            ],

            // --- College of Teacher Education ---
            [
                'unit' => ['name' => 'College of Teacher Education', 'parent_id' => $ovpaaId],
                'users' => [
                    [
                        'email'      => 'dean.cte@test.com',
                        'first_name' => 'Victoria',
                        'last_name'  => 'Salazar',
                        'role'       => 'Supervisor',
                        'position'   => 'Dean',
                        'doc_type'   => 'OPCR',
                        'tier'       => 'dean',
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
                        'rating'     => null,
                        'status'     => FolderStatus::TARGET_RETURNED->value,
                        'remark'     => '[Chair Arthur Perez]: Module production target needs to be at least 3 instructional modules per academic guidelines.'
                    ],
                ]
            ],

            // --- College of Arts and Sciences ---
            [
                'unit' => ['name' => 'College of Arts and Sciences', 'parent_id' => $ovpaaId],
                'users' => [
                    [
                        'email'      => 'dean.cas@test.com',
                        'first_name' => 'Gabriel',
                        'last_name'  => 'Morales',
                        'role'       => 'Supervisor',
                        'position'   => 'Dean',
                        'doc_type'   => 'OPCR',
                        'tier'       => 'dean',
                        'rating'     => 4.80,
                        'status'     => FolderStatus::APPROVED->value,
                        'remark'     => null
                    ],
                    [
                        'email'      => 'chair.cas@test.com',
                        'first_name' => 'Corazon',
                        'last_name'  => 'Villanueva',
                        'role'       => 'Supervisor',
                        'position'   => 'Department Chair',
                        'doc_type'   => 'DPCR',
                        'tier'       => 'chair',
                        'rating'     => 4.65,
                        'status'     => FolderStatus::APPROVED->value,
                        'remark'     => null
                    ],
                    [
                        'email'      => 'faculty.cas1@test.com',
                        'first_name' => 'Ferdinand',
                        'last_name'  => 'Aquino',
                        'role'       => 'Employee',
                        'position'   => 'Professor',
                        'doc_type'   => 'IPCR',
                        'tier'       => 'faculty',
                        'rating'     => 4.70,
                        'status'     => FolderStatus::APPROVED->value,
                        'remark'     => null
                    ],
                    [
                        'email'      => 'faculty.cas2@test.com',
                        'first_name' => 'Lilibeth',
                        'last_name'  => 'Torres',
                        'role'       => 'Employee',
                        'position'   => 'Instructor I',
                        'doc_type'   => 'IPCR',
                        'tier'       => 'faculty',
                        'rating'     => null,
                        'status'     => FolderStatus::TARGET_RETURNED->value,
                        'remark'     => '[Chair Corazon Villanueva]: Please revise research commitments and specify target submission dates for indexed journal publication.'
                    ],
                ]
            ],

            // --- College of Information Sciences ---
            [
                'unit' => ['name' => 'College of Information Sciences', 'parent_id' => $ovpaaId],
                'users' => [
                    [
                        'email'      => 'dean.cis@test.com',
                        'first_name' => 'Alexander',
                        'last_name'  => 'Mercado',
                        'role'       => 'Supervisor',
                        'position'   => 'Dean',
                        'doc_type'   => 'OPCR',
                        'tier'       => 'dean',
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
                        'rating'     => null,
                        'status'     => FolderStatus::TO_EVALUATE->value,
                        'remark'     => 'Targets approved; currently self-rating accomplishments.'
                    ],
                ]
            ],

            // --- General Services Office (Non-Teaching Admin Unit) ---
            [
                'unit' => ['name' => 'General Services Office', 'parent_id' => $ovpafId],
                'users' => [
                    [
                        'email'      => 'head.gso@test.com',
                        'first_name' => 'Rodrigo',
                        'last_name'  => 'Estrada',
                        'role'       => 'Supervisor',
                        'position'   => 'Unit Head',
                        'doc_type'   => 'DPCR',
                        'tier'       => 'chair',
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

        $now = date('Y-m-d H:i:s');
        $createdUserCount = 0;
        $createdDocCount = 0;

        foreach ($roster as $group) {
            // A. Ensure Unit exists
            $unitName = $group['unit']['name'];
            $existingUnit = $db->table('units')->where('name', $unitName)->get()->getRowArray();
            if ($existingUnit) {
                $unitId = (int)$existingUnit['id'];
            } else {
                $db->table('units')->insert([
                    'name'      => $unitName,
                    'parent_id' => $group['unit']['parent_id']
                ]);
                $unitId = (int)$db->insertID();
                echo "  + Created Unit: {$unitName} (ID: {$unitId})\n";
            }

            $deanFolderId = null;
            $chairFolderId = null;
            $deanUserId = null;
            $chairUserId = null;

            foreach ($group['users'] as $u) {
                // B. Ensure User exists
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
                    echo "  + Created User: {$u['first_name']} {$u['last_name']} ({$u['email']})\n";
                }

                // C. Assign Role
                $roleId = $roleMap[$u['role']] ?? 4; // default Employee
                $existingUserRole = $db->table('user_roles')->where('user_id', $userId)->get()->getRowArray();
                if ($existingUserRole) {
                    $db->table('user_roles')->where('user_id', $userId)->update(['role_id' => $roleId]);
                } else {
                    $db->table('user_roles')->insert([
                        'user_id' => $userId,
                        'role_id' => $roleId
                    ]);
                }

                // D. Assign Plantilla
                $posId = $posMap[$u['position']] ?? 4;
                $existingPlantilla = $db->table('plantillas')
                    ->where('user_id', $userId)
                    ->where('ended_at IS NULL')
                    ->get()->getRowArray();
                if ($existingPlantilla) {
                    $db->table('plantillas')->where('id', $existingPlantilla['id'])->update([
                        'position_id' => $posId,
                        'unit_id'     => $unitId,
                    ]);
                } else {
                    $db->table('plantillas')->insert([
                        'user_id'     => $userId,
                        'position_id' => $posId,
                        'unit_id'     => $unitId,
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

                // E. Hook into Active Evaluation Cycle if present
                if ($activeCycle) {
                    $parentFolderId = null;
                    if ($u['tier'] === 'dean') {
                        $parentFolderId = $vpaaFolder ? $vpaaFolder['id'] : $activeCycle['id'];
                    } elseif ($u['tier'] === 'chair') {
                        $parentFolderId = $deanFolderId ?? ($vpaaFolder ? $vpaaFolder['id'] : $activeCycle['id']);
                    } else {
                        $parentFolderId = $chairFolderId ?? $deanFolderId ?? ($vpaaFolder ? $vpaaFolder['id'] : $activeCycle['id']);
                    }

                    // Check if folder already exists in this cycle
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

                    // F. Ensure Form Document exists in this folder
                    $existingDoc = $db->table('documents')->where('document_folder_id', $folderId)->get()->getRowArray();
                    if (!$existingDoc) {
                        $tpl = $templatesByTitle[$u['doc_type']] ?? null;
                        $tabsData = $tpl ? json_decode($tpl['tabs'] ?? '[]', true) : [];

                        // If user has remarks / return review notes, inject into first category
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
                    } else {
                        // Update doc title and remarks if returned
                        if (!empty($u['remark'])) {
                            $tabsData = json_decode($existingDoc['tabs'] ?? '[]', true);
                            if (!empty($tabsData[0]['formData']['categories']['core'][0])) {
                                $tabsData[0]['formData']['categories']['core'][0]['remarks'] = $u['remark'];
                                $db->table('documents')->where('id', $existingDoc['id'])->update([
                                    'tabs'       => json_encode($tabsData),
                                    'is_target'  => $isTargetPhase ? 1 : 0,
                                    'updated_at' => $now
                                ]);
                            }
                        }
                    }
                }
            }
        }

        // --- SEED SAMPLE IN-APP NOTIFICATIONS ---
        $notifTable = $db->table('notifications');
        $notifTable->emptyTable();

        $usersByEmail = [];
        $allUsers = $db->table('users')->get()->getResultArray();
        foreach ($allUsers as $usr) {
            $usersByEmail[$usr['email']] = $usr;
        }

        $sampleNotifications = [
            // Admin Notifications
            [
                'to'         => 'admin@test.com',
                'from'       => 'faculty.agri2@test.com', // Maricel Flores
                'type'       => 'target_submitted',
                'title'      => 'Targets Submitted for Approval',
                'message'    => 'Maricel Flores submitted DPCR target commitments for approval.',
                'link'       => 'ratings',
                'icon'       => 'file',
                'read_at'    => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-15 minutes'))
            ],
            [
                'to'         => 'admin@test.com',
                'from'       => 'faculty.cis2@test.com', // Stephanie Reyes
                'type'       => 'eval_submitted',
                'title'      => 'Accomplishments Submitted',
                'message'    => 'Stephanie Reyes submitted accomplishments and MOVs for evaluation.',
                'link'       => 'ratings',
                'icon'       => 'file',
                'read_at'    => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))
            ],
            [
                'to'         => 'admin@test.com',
                'from'       => 'dean.agri@test.com', // Julian Ramos
                'type'       => 'target_submitted',
                'title'      => 'College Target Submission',
                'message'    => 'College of Agriculture submitted updated targets for 1st Semester 2026.',
                'link'       => 'ratings',
                'icon'       => 'file',
                'read_at'    => date('Y-m-d H:i:s', strtotime('-2 days')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
            ],

            // Danilo Castillo (Faculty CTE) Notifications
            [
                'to'         => 'faculty.cte2@test.com', // Danilo Castillo
                'from'       => 'chair.cte@test.com',   // Arthur Perez
                'type'       => 'target_returned',
                'title'      => 'Action Required: Targets Returned',
                'message'    => 'Your IPCR targets were returned for revision: [Chair Arthur Perez]: Please specify concrete expected outputs for curriculum modules.',
                'link'       => 'folders',
                'icon'       => 'alert',
                'read_at'    => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-25 minutes'))
            ],
            [
                'to'         => 'faculty.cte2@test.com',
                'from'       => 'dean.cte@test.com',
                'type'       => 'target_approved',
                'title'      => 'Department Target Basis Released',
                'message'    => 'College of Teacher Education DPCR basis targets have been approved and released.',
                'link'       => 'folders',
                'icon'       => 'check',
                'read_at'    => date('Y-m-d H:i:s', strtotime('-2 days')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
            ],

            // Lilibeth Torres (CAS Faculty) Notifications
            [
                'to'         => 'faculty.cas2@test.com', // Lilibeth Torres
                'from'       => 'dean.cas@test.com',    // Gabriel Morales
                'type'       => 'target_returned',
                'title'      => 'Action Required: Targets Returned',
                'message'    => 'Your IPCR targets were returned for revision: [Dean Gabriel Morales]: Target indicators for extension deliverables need measurable proof.',
                'link'       => 'folders',
                'icon'       => 'alert',
                'read_at'    => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-40 minutes'))
            ],

            // Leonora Villar (GSO Staff) Notifications
            [
                'to'         => 'staff.gso2@test.com', // Leonora Villar
                'from'       => 'head.gso@test.com',   // Rodrigo Estrada
                'type'       => 'target_returned',
                'title'      => 'Action Required: Targets Returned',
                'message'    => 'Your IPERF targets were returned: [Unit Head Rodrigo Estrada]: Please clarify preventive maintenance schedule targets for university campus facilities.',
                'link'       => 'folders',
                'icon'       => 'alert',
                'read_at'    => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-50 minutes'))
            ],

            // Julian Ramos (Dean Agri) Notifications
            [
                'to'         => 'dean.agri@test.com',
                'from'       => 'chair.agri@test.com',
                'type'       => 'target_submitted',
                'title'      => 'Department Targets Submitted',
                'message'    => 'Lorna Mendoza submitted DPCR targets for Agronomy & Soil Science.',
                'link'       => 'ratings',
                'icon'       => 'file',
                'read_at'    => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))
            ],

            // Clarissa Diaz (CIS Chair) Notifications
            [
                'to'         => 'chair.cis@test.com',
                'from'       => 'dean.cis@test.com',
                'type'       => 'target_approved',
                'title'      => 'Target Commitments Approved',
                'message'    => 'Your target commitments for 1st Semester 2026 have been approved by the Dean.',
                'link'       => 'folders',
                'icon'       => 'check',
                'read_at'    => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 hours'))
            ],
            [
                'to'         => 'chair.cis@test.com',
                'from'       => 'admin@test.com',
                'type'       => 'twg_approved',
                'title'      => 'SPMS Rating Verified by TWG',
                'message'    => 'Official SPMS performance ratings have been verified and finalized by the Technical Working Group.',
                'link'       => 'folders',
                'icon'       => 'award',
                'read_at'    => date('Y-m-d H:i:s', strtotime('-3 days')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days'))
            ],
        ];

        $seededNotifCount = 0;
        foreach ($sampleNotifications as $sn) {
            $toUser = $usersByEmail[$sn['to']] ?? null;
            $fromUser = $usersByEmail[$sn['from']] ?? null;
            if ($toUser) {
                $notifTable->insert([
                    'user_id'    => $toUser['id'],
                    'sender_id'  => $fromUser['id'] ?? null,
                    'type'       => $sn['type'],
                    'title'      => $sn['title'],
                    'message'    => $sn['message'],
                    'link'       => $sn['link'],
                    'icon'       => $sn['icon'],
                    'read_at'    => $sn['read_at'],
                    'created_at' => $sn['created_at'],
                    'updated_at' => $sn['created_at'],
                ]);
                $seededNotifCount++;
            }
        }

        echo ">>> [SPMS] Seeder complete!\n";
        echo "    - Total Users Configured: 19\n";
        echo "    - Total Documents Created/Verified: {$createdDocCount}\n";
        echo "    - In-App Notifications Seeded: {$seededNotifCount}\n";
        echo "    - Revisions Injected: 3 (Danilo Castillo, Lilibeth Torres, Leonora Villar)\n";
        echo "    - Pending Targets Injected: 1 (Maricel Flores)\n";
        echo "    - In-Evaluation / Submitted: 2 (Stephanie Reyes, Nestor Pascual)\n";
        echo "    - Default Password: 123\n";
    }
}
