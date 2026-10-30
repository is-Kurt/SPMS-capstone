<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Enums\FolderStatus;
use App\Enums\EvaluationRoutingStatus;
use App\Enums\RatingCycleStatus;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        helper('functions');
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');
        $password = password_hash('123', PASSWORD_DEFAULT);

        echo ">>> [SPMS DepartmentSeeder] Seeding Rating Cycle & 4-Tier Hierarchy...\n";

        // ==========================================
        // 1. LOOKUPS
        // ==========================================
        $roleMap = [];
        foreach ($db->table('roles')->get()->getResultArray() as $r) {
            $roleMap[$r['name']] = (int)$r['id'];
        }

        $posMap = [];
        foreach ($db->table('positions')->get()->getResultArray() as $p) {
            $posMap[$p['title']] = (int)$p['id'];
        }

        $unitMap = [];
        foreach ($db->table('units')->get()->getResultArray() as $u) {
            $unitMap[$u['name']] = (int)$u['id'];
        }

        // ==========================================
        // 2. SEED RATING CYCLE
        // ==========================================
        echo ">>> Seeding Rating Cycle...\n";
        $cycleTitle    = '2nd Semester AY 2025–2026';
        $ratingPeriod  = 'October 01, 2026 - March 31, 2027';
        $authName      = 'President';
        $authPosition  = 'President';

        $db->table('rating_cycles')->insert([
            'title'                        => $cycleTitle,
            'rating_period'                => $ratingPeriod,
            'approving_authority_name'     => $authName,
            'approving_authority_position' => $authPosition,
            'status'                       => RatingCycleStatus::ACTIVE->value,
            'created_at'                   => $now,
            'updated_at'                   => $now,
        ]);
        $cycleId = (int)$db->insertID();

        // ==========================================
        // 3. SEED CYCLE DEADLINES
        // ==========================================
        echo ">>> Seeding Cycle Deadlines...\n";
        $rootCycleFolderId = 'cycle_2026_s2';

        $deadlines = [
            [
                'cycle_id'        => $cycleId,
                'cycle_folder_id' => $rootCycleFolderId,
                'doc_type'        => 'opcr',
                'target_start'    => '2026-10-01 08:00:00',
                'target_end'      => '2026-11-05 17:00:00',
                'eval_start'      => '2026-12-01 08:00:00',
                'eval_end'        => '2026-12-15 17:00:00',
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'cycle_id'        => $cycleId,
                'cycle_folder_id' => $rootCycleFolderId,
                'doc_type'        => 'dpcr',
                'target_start'    => '2026-10-01 08:00:00',
                'target_end'      => '2026-11-15 17:00:00',
                'eval_start'      => '2026-12-01 08:00:00',
                'eval_end'        => '2026-12-22 17:00:00',
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'cycle_id'        => $cycleId,
                'cycle_folder_id' => $rootCycleFolderId,
                'doc_type'        => 'ipcr',
                'target_start'    => '2026-10-01 08:00:00',
                'target_end'      => '2026-11-30 17:00:00',
                'eval_start'      => '2026-12-01 08:00:00',
                'eval_end'        => '2026-12-31 17:00:00',
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'cycle_id'        => $cycleId,
                'cycle_folder_id' => $rootCycleFolderId,
                'doc_type'        => 'iperf',
                'target_start'    => '2026-10-01 08:00:00',
                'target_end'      => '2026-11-30 17:00:00',
                'eval_start'      => '2026-12-01 08:00:00',
                'eval_end'        => '2026-12-31 17:00:00',
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
        ];
        $db->table('cycle_deadlines')->insertBatch($deadlines);

        // ==========================================
        // 4. SEED APEX / SYSTEM USERS
        // ==========================================
        echo ">>> Seeding Apex / System Users...\n";
        $apexUsers = [
            'admin' => [
                'email'      => 'admin@test.com',
                'first_name' => 'Admin',
                'last_name'  => 'Admin',
                'role'       => 'Admin',
                'position'   => null,
                'unit'       => null,
                'doc_type'   => null,
            ],
            'twg' => [
                'email'      => 'twg@test.com',
                'first_name' => 'System',
                'last_name'  => 'TWG Reviewer',
                'role'       => 'TWG',
                'position'   => 'Quality Assurance Officer',
                'unit'       => 'Quality Assurance Office',
                'doc_type'   => null,
            ],
            'pmt' => [
                'email'      => 'pmt@test.com',
                'first_name' => 'PMT',
                'last_name'  => 'Committee Chair',
                'role'       => 'PMT',
                'position'   => 'Planning Officer',
                'unit'       => 'Planning and Development Office',
                'doc_type'   => null,
            ],
        ];

        $userIdMap = [];
        foreach ($apexUsers as $key => $u) {
            $db->table('users')->insert([
                'email'      => $u['email'],
                'first_name' => $u['first_name'],
                'last_name'  => $u['last_name'],
                'password'   => $password,
                'is_active'  => 1,
                'doc_type'   => $u['doc_type'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $uid = (int)$db->insertID();
            $userIdMap[$key] = $uid;

            $db->table('user_roles')->insert([
                'user_id' => $uid,
                'role_id' => $roleMap[$u['role']] ?? 1,
            ]);

            if (!empty($u['position']) && !empty($u['unit'])) {
                $db->table('plantillas')->insert([
                    'user_id'     => $uid,
                    'position_id' => $posMap[$u['position']] ?? 1,
                    'unit_id'     => $unitMap[$u['unit']] ?? 1,
                    'started_at'  => '2020-01-01',
                    'ended_at'    => null,
                ]);
            }
        }

        $adminUserId = $userIdMap['admin'];

        // Create Master Root Cycle Folder
        $db->table('document_folders')->insert([
            'id'                           => $rootCycleFolderId,
            'cycle_id'                     => $cycleId,
            'cycle_folder_id'              => $rootCycleFolderId,
            'user_id'                      => $adminUserId,
            'parent_folder_id'             => null,
            'title'                        => $cycleTitle,
            'status'                       => FolderStatus::TARGET_APPROVED->value,
            'routing_preset_id'            => null,
            'rating_period'                => $ratingPeriod,
            'approving_authority_name'     => $authName,
            'approving_authority_position' => $authPosition,
            'created_at'                   => $now,
            'updated_at'                   => $now,
        ]);

        // Helper closures for generating document folders and routings
        $createFolderRecord = function($folderId, $userId, $parentFolderId, $docType, $status, $rating = null, $remark = null, $routingPresetId = null) use ($db, $cycleId, $rootCycleFolderId, $cycleTitle, $ratingPeriod, $authName, $authPosition, $now) {
            $tabs = get_default_form_template($docType);
            if (!empty($remark) && !empty($tabs[0]['formData']['categories']['core'][0])) {
                $tabs[0]['formData']['categories']['core'][0]['remarks'] = $remark;
            }

            $isTargetPhase = in_array($status, [
                FolderStatus::DRAFT_TARGET->value,
                FolderStatus::TARGET_SUBMITTED->value,
            ]);

            $isEvalPhase = in_array($status, [
                FolderStatus::EVAL_SUBMITTED->value,
                FolderStatus::EVAL_APPROVED->value,
            ]);

            $folderData = [
                'id'                           => $folderId,
                'cycle_id'                     => $cycleId,
                'cycle_folder_id'              => $rootCycleFolderId,
                'user_id'                      => $userId,
                'parent_folder_id'             => $parentFolderId,
                'title'                        => $cycleTitle,
                'status'                       => $status,
                'final_rating'                 => $rating,
                'tabs'                         => json_encode($tabs),
                'approving_authority_name'     => $authName,
                'approving_authority_position' => $authPosition,
                'routing_preset_id'            => $routingPresetId,
                'rating_period'                => $ratingPeriod,
                'target_submitted_at'          => $now,
                'target_approved_at'           => $isTargetPhase ? null : $now,
                'submitted_at'                 => $isEvalPhase ? $now : null,
                'rated_at'                     => $rating ? $now : null,
                'created_at'                   => $now,
                'updated_at'                   => $now,
            ];

            $db->table('document_folders')->insert($folderData);
            return $folderId;
        };

        $createRoutingRecord = function($folderId, $evaluatorId, $evaluatorFolderId, $status) use ($db, $now) {
            $db->table('evaluation_routings')->insert([
                'folder_id'           => $folderId,
                'evaluator_id'        => $evaluatorId,
                'evaluator_folder_id' => $evaluatorFolderId,
                'status'              => $status,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);
        };

        // ==========================================
        // 5. SEED VPAA ACADEMIC HIERARCHY
        // ==========================================
        echo ">>> Seeding Academic Affairs (VPAA) Hierarchy...\n";

        // 5.1 VPAA (Dr. Ana Santos)
        $db->table('users')->insert([
            'email'      => 'vpaa@test.com',
            'first_name' => 'Ana',
            'last_name'  => 'Santos',
            'password'   => $password,
            'is_active'  => 1,
            'doc_type'   => 'OPCR',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $vpaaUserId = (int)$db->insertID();

        $db->table('user_roles')->insert([
            'user_id' => $vpaaUserId,
            'role_id' => $roleMap['Supervisor'] ?? 2,
        ]);

        $db->table('plantillas')->insert([
            'user_id'     => $vpaaUserId,
            'position_id' => $posMap['Vice President for Academic Affairs'] ?? $posMap['Vice President'],
            'unit_id'     => $unitMap['Office of the Vice President for Academic Affairs'] ?? ($unitMap['Office of the Vice President for Academic Affairs (OVPAA)'] ?? 2),
            'started_at'  => '2020-01-01',
            'ended_at'    => null,
        ]);

        $vpaaFolderId = $createFolderRecord('fld_vpaa', $vpaaUserId, $rootCycleFolderId, 'OPCR', FolderStatus::EVAL_APPROVED->value, 4.95, null, 'team_vpaa_deans');
        $createRoutingRecord($vpaaFolderId, $adminUserId, $rootCycleFolderId, EvaluationRoutingStatus::APPROVED->value);

        // 5.2 College of Information Sciences (Showcase College)
        // Dean CIS
        $db->table('users')->insert([
            'email'      => 'dean.cis@test.com',
            'first_name' => 'Alexander',
            'last_name'  => 'Mercado',
            'password'   => $password,
            'is_active'  => 1,
            'doc_type'   => 'DPCR',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $deanCisId = (int)$db->insertID();
        $db->table('user_roles')->insert(['user_id' => $deanCisId, 'role_id' => $roleMap['Supervisor']]);
        $db->table('plantillas')->insert([
            'user_id'     => $deanCisId,
            'position_id' => $posMap['Dean'],
            'unit_id'     => $unitMap['College of Information Sciences'],
            'started_at'  => '2021-06-01',
            'ended_at'    => null,
        ]);
        $deanCisFolderId = $createFolderRecord('fld_dean_cis', $deanCisId, $vpaaFolderId, 'DPCR', FolderStatus::EVAL_APPROVED->value, 4.92, null, 'team_cis_chairs');
        $createRoutingRecord($deanCisFolderId, $vpaaUserId, $vpaaFolderId, EvaluationRoutingStatus::APPROVED->value);

        // Chair BSIT
        $db->table('users')->insert([
            'email'      => 'chair.cis@test.com',
            'first_name' => 'Clarissa',
            'last_name'  => 'Diaz',
            'password'   => $password,
            'is_active'  => 1,
            'doc_type'   => 'DPCR',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $chairCisId = (int)$db->insertID();
        $db->table('user_roles')->insert(['user_id' => $chairCisId, 'role_id' => $roleMap['Supervisor']]);
        $db->table('plantillas')->insert([
            'user_id'     => $chairCisId,
            'position_id' => $posMap['Department Chair'],
            'unit_id'     => $unitMap['BS Information Technology'],
            'started_at'  => '2022-08-01',
            'ended_at'    => null,
        ]);
        $chairCisFolderId = $createFolderRecord('fld_chair_cis', $chairCisId, $deanCisFolderId, 'DPCR', FolderStatus::EVAL_APPROVED->value, 4.85, null, 'team_cis_faculty');
        $createRoutingRecord($chairCisFolderId, $deanCisId, $deanCisFolderId, EvaluationRoutingStatus::APPROVED->value);

        // Faculty BSIT 1 (Kenji Tanaka - Evaluated)
        $db->table('users')->insert([
            'email'      => 'faculty.cis1@test.com',
            'first_name' => 'Kenji',
            'last_name'  => 'Tanaka',
            'password'   => $password,
            'is_active'  => 1,
            'doc_type'   => 'IPCR',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $facCis1Id = (int)$db->insertID();
        $db->table('user_roles')->insert(['user_id' => $facCis1Id, 'role_id' => $roleMap['Employee']]);
        $db->table('plantillas')->insert([
            'user_id'     => $facCis1Id,
            'position_id' => $posMap['Assistant Professor'],
            'unit_id'     => $unitMap['BS Information Technology'],
            'started_at'  => '2022-09-01',
            'ended_at'    => null,
        ]);
        $facCis1FolderId = $createFolderRecord('fld_fac_cis1', $facCis1Id, $chairCisFolderId, 'IPCR', FolderStatus::EVAL_APPROVED->value, 4.90);
        $createRoutingRecord($facCis1FolderId, $chairCisId, $chairCisFolderId, EvaluationRoutingStatus::APPROVED->value);

        // Faculty BSIT 2 (Stephanie Reyes - Draft Evaluation)
        $db->table('users')->insert([
            'email'      => 'faculty.cis2@test.com',
            'first_name' => 'Stephanie',
            'last_name'  => 'Reyes',
            'password'   => $password,
            'is_active'  => 1,
            'doc_type'   => 'IPCR',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $facCis2Id = (int)$db->insertID();
        $db->table('user_roles')->insert(['user_id' => $facCis2Id, 'role_id' => $roleMap['Employee']]);
        $db->table('plantillas')->insert([
            'user_id'     => $facCis2Id,
            'position_id' => $posMap['Instructor II'],
            'unit_id'     => $unitMap['BS Information Technology'],
            'started_at'  => '2023-01-15',
            'ended_at'    => null,
        ]);
        $facCis2FolderId = $createFolderRecord('fld_fac_cis2', $facCis2Id, $chairCisFolderId, 'IPCR', FolderStatus::DRAFT_EVALUATION->value, null);
        $createRoutingRecord($facCis2FolderId, $chairCisId, $chairCisFolderId, EvaluationRoutingStatus::DRAFT->value);

        // 5.3 College of Nursing (CN) - Features test accounts dean@test.com, chair@test.com, faculty@test.com
        // Dean CN (dean@test.com)
        $db->table('users')->insert([
            'email'      => 'dean@test.com',
            'first_name' => 'Roberto',
            'last_name'  => 'Reyes',
            'password'   => $password,
            'is_active'  => 1,
            'doc_type'   => 'DPCR',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $deanCnId = (int)$db->insertID();
        $db->table('user_roles')->insert(['user_id' => $deanCnId, 'role_id' => $roleMap['Supervisor']]);
        $db->table('plantillas')->insert([
            'user_id'     => $deanCnId,
            'position_id' => $posMap['Dean'],
            'unit_id'     => $unitMap['College of Nursing'],
            'started_at'  => '2021-06-01',
            'ended_at'    => null,
        ]);
        $deanCnFolderId = $createFolderRecord('fld_dean_cn', $deanCnId, $vpaaFolderId, 'DPCR', FolderStatus::TARGET_APPROVED->value, 4.85, null, 'team_cn_chairs');
        $createRoutingRecord($deanCnFolderId, $vpaaUserId, $vpaaFolderId, EvaluationRoutingStatus::APPROVED->value);

        // Chair BSN (chair@test.com)
        $db->table('users')->insert([
            'email'      => 'chair@test.com',
            'first_name' => 'Miguel',
            'last_name'  => 'Cruz',
            'password'   => $password,
            'is_active'  => 1,
            'doc_type'   => 'DPCR',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $chairCnId = (int)$db->insertID();
        $db->table('user_roles')->insert(['user_id' => $chairCnId, 'role_id' => $roleMap['Supervisor']]);
        $db->table('plantillas')->insert([
            'user_id'     => $chairCnId,
            'position_id' => $posMap['Department Chair'],
            'unit_id'     => $unitMap['BS Nursing'],
            'started_at'  => '2022-08-15',
            'ended_at'    => null,
        ]);
        $chairCnFolderId = $createFolderRecord('fld_chair_cn', $chairCnId, $deanCnFolderId, 'DPCR', FolderStatus::TARGET_APPROVED->value, 4.75, null, 'team_cn_faculty');
        $createRoutingRecord($chairCnFolderId, $deanCnId, $deanCnFolderId, EvaluationRoutingStatus::APPROVED->value);

        // Faculty BSN 1 (faculty@test.com / Carlos Lim)
        $db->table('users')->insert([
            'email'      => 'faculty@test.com',
            'first_name' => 'Carlos',
            'last_name'  => 'Lim',
            'password'   => $password,
            'is_active'  => 1,
            'doc_type'   => 'IPCR',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $facCn1Id = (int)$db->insertID();
        $db->table('user_roles')->insert(['user_id' => $facCn1Id, 'role_id' => $roleMap['Employee']]);
        $db->table('plantillas')->insert([
            'user_id'     => $facCn1Id,
            'position_id' => $posMap['Assistant Professor'],
            'unit_id'     => $unitMap['BS Nursing'],
            'started_at'  => '2019-06-01',
            'ended_at'    => null,
        ]);
        $facCn1FolderId = $createFolderRecord('fld_fac_cn1', $facCn1Id, $chairCnFolderId, 'IPCR', FolderStatus::TARGET_APPROVED->value, 4.80);
        $createRoutingRecord($facCn1FolderId, $chairCnId, $chairCnFolderId, EvaluationRoutingStatus::APPROVED->value);

        // Faculty BSN 2 (Teresa Garcia - Draft Target)
        $db->table('users')->insert([
            'email'      => 'faculty.cn2@test.com',
            'first_name' => 'Teresa',
            'last_name'  => 'Garcia',
            'password'   => $password,
            'is_active'  => 1,
            'doc_type'   => 'IPCR',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $facCn2Id = (int)$db->insertID();
        $db->table('user_roles')->insert(['user_id' => $facCn2Id, 'role_id' => $roleMap['Employee']]);
        $db->table('plantillas')->insert([
            'user_id'     => $facCn2Id,
            'position_id' => $posMap['Instructor I'],
            'unit_id'     => $unitMap['BS Nursing'],
            'started_at'  => '2023-08-01',
            'ended_at'    => null,
        ]);
        $facCn2FolderId = $createFolderRecord('fld_fac_cn2', $facCn2Id, $chairCnFolderId, 'IPCR', FolderStatus::DRAFT_TARGET->value, null);
        $createRoutingRecord($facCn2FolderId, $chairCnId, $chairCnFolderId, EvaluationRoutingStatus::DRAFT->value);

        // 5.4 College of Agriculture (CA)
        // Dean CA
        $db->table('users')->insert([
            'email'      => 'dean.ca@test.com',
            'first_name' => 'Bernardo',
            'last_name'  => 'Ocampo',
            'password'   => $password,
            'is_active'  => 1,
            'doc_type'   => 'DPCR',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $deanCaId = (int)$db->insertID();
        $db->table('user_roles')->insert(['user_id' => $deanCaId, 'role_id' => $roleMap['Supervisor']]);
        $db->table('plantillas')->insert([
            'user_id'     => $deanCaId,
            'position_id' => $posMap['Dean'],
            'unit_id'     => $unitMap['College of Agriculture'],
            'started_at'  => '2020-03-01',
            'ended_at'    => null,
        ]);
        $deanCaFolderId = $createFolderRecord('fld_dean_ca', $deanCaId, $vpaaFolderId, 'DPCR', FolderStatus::EVAL_APPROVED->value, 4.88, null, 'team_ca_chairs');
        $createRoutingRecord($deanCaFolderId, $vpaaUserId, $vpaaFolderId, EvaluationRoutingStatus::APPROVED->value);

        // Chair BSA
        $db->table('users')->insert([
            'email'      => 'chair.ca@test.com',
            'first_name' => 'Corazon',
            'last_name'  => 'Villanueva',
            'password'   => $password,
            'is_active'  => 1,
            'doc_type'   => 'DPCR',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $chairCaId = (int)$db->insertID();
        $db->table('user_roles')->insert(['user_id' => $chairCaId, 'role_id' => $roleMap['Supervisor']]);
        $db->table('plantillas')->insert([
            'user_id'     => $chairCaId,
            'position_id' => $posMap['Department Chair'],
            'unit_id'     => $unitMap['BS Agriculture'],
            'started_at'  => '2021-08-01',
            'ended_at'    => null,
        ]);
        $chairCaFolderId = $createFolderRecord('fld_chair_ca', $chairCaId, $deanCaFolderId, 'DPCR', FolderStatus::EVAL_APPROVED->value, 4.75, null, 'team_ca_faculty');
        $createRoutingRecord($chairCaFolderId, $deanCaId, $deanCaFolderId, EvaluationRoutingStatus::APPROVED->value);

        // Faculty BSA (Ferdinand Aquino - Target Submitted)
        $db->table('users')->insert([
            'email'      => 'faculty.ca1@test.com',
            'first_name' => 'Ferdinand',
            'last_name'  => 'Aquino',
            'password'   => $password,
            'is_active'  => 1,
            'doc_type'   => 'IPCR',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $facCa1Id = (int)$db->insertID();
        $db->table('user_roles')->insert(['user_id' => $facCa1Id, 'role_id' => $roleMap['Employee']]);
        $db->table('plantillas')->insert([
            'user_id'     => $facCa1Id,
            'position_id' => $posMap['Professor'],
            'unit_id'     => $unitMap['BS Agriculture'],
            'started_at'  => '2018-06-01',
            'ended_at'    => null,
        ]);
        $facCa1FolderId = $createFolderRecord('fld_fac_ca1', $facCa1Id, $chairCaFolderId, 'IPCR', FolderStatus::TARGET_SUBMITTED->value, null);
        $createRoutingRecord($facCa1FolderId, $chairCaId, $chairCaFolderId, EvaluationRoutingStatus::DRAFT->value);

        // ==========================================
        // 6. SEED VPAF ADMINISTRATIVE HIERARCHY
        // ==========================================
        echo ">>> Seeding Administration & Finance (VPAF) Hierarchy...\n";

        // 6.1 VPAF (Engr. Roberto Mendoza)
        $db->table('users')->insert([
            'email'      => 'vpaf@test.com',
            'first_name' => 'Roberto',
            'last_name'  => 'Mendoza',
            'password'   => $password,
            'is_active'  => 1,
            'doc_type'   => 'OPCR',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $vpafUserId = (int)$db->insertID();

        $db->table('user_roles')->insert([
            'user_id' => $vpafUserId,
            'role_id' => $roleMap['Supervisor'] ?? 2,
        ]);

        $db->table('plantillas')->insert([
            'user_id'     => $vpafUserId,
            'position_id' => $posMap['Vice President for Administration and Finance'] ?? $posMap['Vice President'],
            'unit_id'     => $unitMap['Office of the Vice President for Administration and Finance'] ?? ($unitMap['Office of the Vice President for Administration and Finance (OVPAF)'] ?? 3),
            'started_at'  => '2019-01-01',
            'ended_at'    => null,
        ]);

        $vpafFolderId = $createFolderRecord('fld_vpaf', $vpafUserId, $rootCycleFolderId, 'OPCR', FolderStatus::EVAL_APPROVED->value, 4.90, null, 'team_vpaf_heads');
        $createRoutingRecord($vpafFolderId, $adminUserId, $rootCycleFolderId, EvaluationRoutingStatus::APPROVED->value);

        // 6.2 General Services Office (GSO)
        // Head GSO
        $db->table('users')->insert([
            'email'      => 'head.gso@test.com',
            'first_name' => 'Rodrigo',
            'last_name'  => 'Estrada',
            'password'   => $password,
            'is_active'  => 1,
            'doc_type'   => 'DPCR',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $headGsoId = (int)$db->insertID();
        $db->table('user_roles')->insert(['user_id' => $headGsoId, 'role_id' => $roleMap['Supervisor']]);
        $db->table('plantillas')->insert([
            'user_id'     => $headGsoId,
            'position_id' => $posMap['Unit Head'],
            'unit_id'     => $unitMap['General Services Office'],
            'started_at'  => '2020-04-01',
            'ended_at'    => null,
        ]);
        $headGsoFolderId = $createFolderRecord('fld_head_gso', $headGsoId, $vpafFolderId, 'DPCR', FolderStatus::EVAL_APPROVED->value, 4.60, null, 'team_gso_staff');
        $createRoutingRecord($headGsoFolderId, $vpafUserId, $vpafFolderId, EvaluationRoutingStatus::APPROVED->value);

        // Staff GSO 1 (Nestor Pascual - Eval Submitted)
        $db->table('users')->insert([
            'email'      => 'staff.gso1@test.com',
            'first_name' => 'Nestor',
            'last_name'  => 'Pascual',
            'password'   => $password,
            'is_active'  => 1,
            'doc_type'   => 'IPERF',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $staffGso1Id = (int)$db->insertID();
        $db->table('user_roles')->insert(['user_id' => $staffGso1Id, 'role_id' => $roleMap['Employee']]);
        $db->table('plantillas')->insert([
            'user_id'     => $staffGso1Id,
            'position_id' => $posMap['Administrative Aide'],
            'unit_id'     => $unitMap['General Services Office'],
            'started_at'  => '2022-01-01',
            'ended_at'    => null,
        ]);
        $staffGso1FolderId = $createFolderRecord('fld_staff_gso1', $staffGso1Id, $headGsoFolderId, 'IPERF', FolderStatus::EVAL_SUBMITTED->value, null);
        $createRoutingRecord($staffGso1FolderId, $headGsoId, $headGsoFolderId, EvaluationRoutingStatus::DRAFT->value);

        // Staff GSO 2 (Leonora Villar - Target Returned with supervisor remark)
        $db->table('users')->insert([
            'email'      => 'staff.gso2@test.com',
            'first_name' => 'Leonora',
            'last_name'  => 'Villar',
            'password'   => $password,
            'is_active'  => 1,
            'doc_type'   => 'IPERF',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $staffGso2Id = (int)$db->insertID();
        $db->table('user_roles')->insert(['user_id' => $staffGso2Id, 'role_id' => $roleMap['Employee']]);
        $db->table('plantillas')->insert([
            'user_id'     => $staffGso2Id,
            'position_id' => $posMap['Administrative Assistant'],
            'unit_id'     => $unitMap['General Services Office'],
            'started_at'  => '2022-05-01',
            'ended_at'    => null,
        ]);
        $gsoRemark = '[Unit Head Rodrigo Estrada]: Please clarify preventive maintenance schedule targets for university campus facilities.';
        $staffGso2FolderId = $createFolderRecord('fld_staff_gso2', $staffGso2Id, $headGsoFolderId, 'IPERF', FolderStatus::DRAFT_TARGET->value, null, $gsoRemark);
        $createRoutingRecord($staffGso2FolderId, $headGsoId, $headGsoFolderId, EvaluationRoutingStatus::RETURNED->value);

        // Test Alias iperf@test.com (Danilo Ocampo)
        $db->table('users')->insert([
            'email'      => 'iperf@test.com',
            'first_name' => 'Danilo',
            'last_name'  => 'Ocampo',
            'password'   => $password,
            'is_active'  => 1,
            'doc_type'   => 'IPERF',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $iperfId = (int)$db->insertID();
        $db->table('user_roles')->insert(['user_id' => $iperfId, 'role_id' => $roleMap['Employee']]);
        $db->table('plantillas')->insert([
            'user_id'     => $iperfId,
            'position_id' => $posMap['Administrative Aide'],
            'unit_id'     => $unitMap['General Services Office'],
            'started_at'  => '2022-01-01',
            'ended_at'    => null,
        ]);
        $iperfFolderId = $createFolderRecord('fld_iperf', $iperfId, $headGsoFolderId, 'IPERF', FolderStatus::DRAFT_TARGET->value, null);
        $createRoutingRecord($iperfFolderId, $headGsoId, $headGsoFolderId, EvaluationRoutingStatus::DRAFT->value);

        // 6.3 Human Resource Management Office (HRMO)
        // Head HRMO
        $db->table('users')->insert([
            'email'      => 'head.hrmo@test.com',
            'first_name' => 'Maria Clara',
            'last_name'  => 'Cruz',
            'password'   => $password,
            'is_active'  => 1,
            'doc_type'   => 'DPCR',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $headHrmoId = (int)$db->insertID();
        $db->table('user_roles')->insert(['user_id' => $headHrmoId, 'role_id' => $roleMap['Supervisor']]);
        $db->table('plantillas')->insert([
            'user_id'     => $headHrmoId,
            'position_id' => $posMap['HR Management Officer'] ?? $posMap['Unit Head'],
            'unit_id'     => $unitMap['Human Resource Management Office'],
            'started_at'  => '2019-11-01',
            'ended_at'    => null,
        ]);
        $headHrmoFolderId = $createFolderRecord('fld_head_hrmo', $headHrmoId, $vpafFolderId, 'DPCR', FolderStatus::EVAL_APPROVED->value, 4.82, null, 'team_hrmo_staff');
        $createRoutingRecord($headHrmoFolderId, $vpafUserId, $vpafFolderId, EvaluationRoutingStatus::APPROVED->value);

        // Staff HRMO
        $db->table('users')->insert([
            'email'      => 'staff.hrmo1@test.com',
            'first_name' => 'Danilo',
            'last_name'  => 'Santos',
            'password'   => $password,
            'is_active'  => 1,
            'doc_type'   => 'IPERF',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $staffHrmoId = (int)$db->insertID();
        $db->table('user_roles')->insert(['user_id' => $staffHrmoId, 'role_id' => $roleMap['Employee']]);
        $db->table('plantillas')->insert([
            'user_id'     => $staffHrmoId,
            'position_id' => $posMap['Administrative Assistant'],
            'unit_id'     => $unitMap['Human Resource Management Office'],
            'started_at'  => '2021-09-01',
            'ended_at'    => null,
        ]);
        $staffHrmoFolderId = $createFolderRecord('fld_staff_hrmo1', $staffHrmoId, $headHrmoFolderId, 'IPERF', FolderStatus::DRAFT_TARGET->value, null);
        $createRoutingRecord($staffHrmoFolderId, $headHrmoId, $headHrmoFolderId, EvaluationRoutingStatus::DRAFT->value);

        // 6.4 Accounting Office
        // Head Accounting
        $db->table('users')->insert([
            'email'      => 'head.acc@test.com',
            'first_name' => 'Carmen',
            'last_name'  => 'Perez',
            'password'   => $password,
            'is_active'  => 1,
            'doc_type'   => 'DPCR',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $headAccId = (int)$db->insertID();
        $db->table('user_roles')->insert(['user_id' => $headAccId, 'role_id' => $roleMap['Supervisor']]);
        $db->table('plantillas')->insert([
            'user_id'     => $headAccId,
            'position_id' => $posMap['Chief Accountant'] ?? $posMap['Unit Head'],
            'unit_id'     => $unitMap['Accounting Office'],
            'started_at'  => '2018-07-01',
            'ended_at'    => null,
        ]);
        $headAccFolderId = $createFolderRecord('fld_head_acc', $headAccId, $vpafFolderId, 'DPCR', FolderStatus::EVAL_APPROVED->value, 4.88, null, 'team_acc_staff');
        $createRoutingRecord($headAccFolderId, $vpafUserId, $vpafFolderId, EvaluationRoutingStatus::APPROVED->value);

        // Staff Accounting
        $db->table('users')->insert([
            'email'      => 'staff.acc1@test.com',
            'first_name' => 'Mark Joseph',
            'last_name'  => 'Bautista',
            'password'   => $password,
            'is_active'  => 1,
            'doc_type'   => 'IPERF',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $staffAccId = (int)$db->insertID();
        $db->table('user_roles')->insert(['user_id' => $staffAccId, 'role_id' => $roleMap['Employee']]);
        $db->table('plantillas')->insert([
            'user_id'     => $staffAccId,
            'position_id' => $posMap['Bookkeeper'] ?? $posMap['Administrative Aide'],
            'unit_id'     => $unitMap['Accounting Office'],
            'started_at'  => '2023-11-01',
            'ended_at'    => null,
        ]);
        $staffAccFolderId = $createFolderRecord('fld_staff_acc1', $staffAccId, $headAccFolderId, 'IPERF', FolderStatus::DRAFT_TARGET->value, null);
        $createRoutingRecord($staffAccFolderId, $headAccId, $headAccFolderId, EvaluationRoutingStatus::DRAFT->value);

        // ==========================================
        // 7. SEED ROUTING PRESETS (TEAMS FOR CASCADING)
        // ==========================================
        echo ">>> Seeding Routing Presets (Cascading Teams)...\n";

        $presetsToSeed = [
            [
                'id'          => 'team_vpaa_deans',
                'owner_id'    => $vpaaUserId,
                'name'        => 'Academic Deans Council',
                'description' => 'Deans of Academic Colleges reporting to the VPAA',
                'members'     => [$deanCisId, $deanCnId, $deanCaId],
            ],
            [
                'id'          => 'team_cis_chairs',
                'owner_id'    => $deanCisId,
                'name'        => 'CIS Department Chairs',
                'description' => 'Academic Department Chairs under College of Information Sciences',
                'members'     => [$chairCisId],
            ],
            [
                'id'          => 'team_cis_faculty',
                'owner_id'    => $chairCisId,
                'name'        => 'CIS Faculty Members',
                'description' => 'Teaching faculty under the Department of Information Technology',
                'members'     => [$facCis1Id, $facCis2Id],
            ],
            [
                'id'          => 'team_cn_chairs',
                'owner_id'    => $deanCnId,
                'name'        => 'Nursing Department Chairs',
                'description' => 'Department Chairs under College of Nursing',
                'members'     => [$chairCnId],
            ],
            [
                'id'          => 'team_cn_faculty',
                'owner_id'    => $chairCnId,
                'name'        => 'Nursing Faculty Members',
                'description' => 'Teaching faculty under the College of Nursing',
                'members'     => [$facCn1Id, $facCn2Id],
            ],
            [
                'id'          => 'team_ca_chairs',
                'owner_id'    => $deanCaId,
                'name'        => 'Agriculture Department Chairs',
                'description' => 'Department Chairs under College of Agriculture',
                'members'     => [$chairCaId],
            ],
            [
                'id'          => 'team_ca_faculty',
                'owner_id'    => $chairCaId,
                'name'        => 'Agriculture Faculty Members',
                'description' => 'Teaching faculty under the College of Agriculture',
                'members'     => [$facCa1Id],
            ],
            [
                'id'          => 'team_vpaf_heads',
                'owner_id'    => $vpafUserId,
                'name'        => 'Administrative Unit Heads',
                'description' => 'Divisional Unit Heads reporting to the VPAF',
                'members'     => [$headGsoId, $headHrmoId, $headAccId],
            ],
            [
                'id'          => 'team_gso_staff',
                'owner_id'    => $headGsoId,
                'name'        => 'GSO Staff Members',
                'description' => 'General Services personnel and administrative aides',
                'members'     => [$staffGso1Id, $staffGso2Id, $iperfId],
            ],
            [
                'id'          => 'team_hrmo_staff',
                'owner_id'    => $headHrmoId,
                'name'        => 'HRMO Staff Members',
                'description' => 'Human Resources personnel',
                'members'     => [$staffHrmoId],
            ],
            [
                'id'          => 'team_acc_staff',
                'owner_id'    => $headAccId,
                'name'        => 'Accounting Staff Members',
                'description' => 'Accounting Office personnel and bookkeepers',
                'members'     => [$staffAccId],
            ],
        ];

        foreach ($presetsToSeed as $pst) {
            $db->table('routing_presets')->insert([
                'id'          => $pst['id'],
                'owner_id'    => $pst['owner_id'],
                'name'        => $pst['name'],
                'description' => $pst['description'],
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            foreach ($pst['members'] as $mUid) {
                $db->table('routing_preset_members')->insert([
                    'preset_id'  => $pst['id'],
                    'user_id'    => $mUid,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        echo ">>> [SPMS DepartmentSeeder] Successfully seeded 25 users across VPAA & VPAF hierarchies!\n";
        echo "    - All accounts initialized with default password: 123\n";
        echo "    - Pre-seeded 12 routing presets/teams for instant cascading.\n";
        echo "    - Rating cycle, cycle deadlines, folders, and routings attached.\n";
    }
}
