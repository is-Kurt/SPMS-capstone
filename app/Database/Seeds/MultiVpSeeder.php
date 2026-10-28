<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Enums\FolderStatus;
use App\Enums\EvaluationRoutingStatus;

/**
 * Seeds a clean SPMS environment where both Vice Presidents (vpaa@test.com & vpaf@test.com)
 * act as co-supervisors for direct subordinates, allowing immediate multi-supervisor testing
 * (Target Approval, Return for Revision, My Staff auto-sync & removal) without cascading down 4 tiers.
 */
class MultiVpSeeder extends Seeder
{
    public function run()
    {
        // 1. Seed base roles, units, positions, users, and cycle via MasterSeeder
        $this->call('MasterSeeder');

        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        echo ">>> [SPMS MultiVpSeeder] Configuring Multi-Supervisor VP Co-Evaluation Setup...\n";

        $vpaa = $db->table('users')->where('email', 'vpaa@test.com')->get()->getRowArray();
        $vpaf = $db->table('users')->where('email', 'vpaf@test.com')->get()->getRowArray();

        if (!$vpaa || !$vpaf) {
            echo "!!! [MultiVpSeeder] VPAA or VPAF user not found.\n";
            return;
        }

        $vpaaId = (int)$vpaa['id'];
        $vpafId = (int)$vpaf['id'];

        // 2. Put both VPs' folders (fld_vpaa & fld_vpaf) in active TARGET_APPROVED phase
        // so both VPs can actively manage/sync their cascaded staff in the Target Phase.
        $db->table('document_folders')
            ->whereIn('id', ['fld_vpaa', 'fld_vpaf'])
            ->update([
                'status'       => FolderStatus::TARGET_APPROVED->value,
                'final_rating' => null,
                'submitted_at' => null,
                'rated_at'     => null,
                'updated_at'   => $now,
            ]);

        // Lookup units & positions for dual-branch eligibility in My Staff
        $cisUnit = $db->table('units')->where('name', 'College of Information Sciences')->get()->getRowArray();
        $cnUnit  = $db->table('units')->where('name', 'College of Nursing')->get()->getRowArray();
        $gsoUnit = $db->table('units')->where('name', 'General Services Office')->get()->getRowArray();
        $hrmoUnit = $db->table('units')->where('name', 'Human Resource Management Office')->get()->getRowArray();

        $deanPos = $db->table('positions')->where('title', 'Dean')->get()->getRowArray();
        $headPos = $db->table('positions')->where('title', 'Unit Head')->get()->getRowArray();

        // 3. Configure 3 co-supervised subordinates directly under BOTH VPAA and VPAF:
        //    - dean@test.com (Roberto Reyes, fld_dean_cn): TARGET_SUBMITTED (Both VPs pending review)
        //    - dean.cis@test.com (Alexander Mercado, fld_dean_cis): TARGET_SUBMITTED (VPAA approved, awaiting VPAF)
        //    - head.gso@test.com (Rodrigo Estrada, fld_head_gso): DRAFT_TARGET (In progress under both VPs)
        $coSupervised = [
            [
                'email'          => 'dean@test.com',
                'folder_id'      => 'fld_dean_cn',
                'folder_status'  => FolderStatus::TARGET_SUBMITTED->value,
                'vpaa_status'    => EvaluationRoutingStatus::DRAFT->value,
                'vpaf_status'    => EvaluationRoutingStatus::DRAFT->value,
                'extra_unit_id'  => $hrmoUnit['id'] ?? null,
                'extra_pos_id'   => $headPos['id'] ?? null,
            ],
            [
                'email'          => 'dean.cis@test.com',
                'folder_id'      => 'fld_dean_cis',
                'folder_status'  => FolderStatus::TARGET_SUBMITTED->value,
                'vpaa_status'    => EvaluationRoutingStatus::APPROVED->value,
                'vpaf_status'    => EvaluationRoutingStatus::DRAFT->value,
                'extra_unit_id'  => $gsoUnit['id'] ?? null,
                'extra_pos_id'   => $headPos['id'] ?? null,
            ],
            [
                'email'          => 'head.gso@test.com',
                'folder_id'      => 'fld_head_gso',
                'folder_status'  => FolderStatus::DRAFT_TARGET->value,
                'vpaa_status'    => EvaluationRoutingStatus::DRAFT->value,
                'vpaf_status'    => EvaluationRoutingStatus::DRAFT->value,
                'extra_unit_id'  => $cisUnit['id'] ?? ($cnUnit['id'] ?? null),
                'extra_pos_id'   => $deanPos['id'] ?? null,
            ],
        ];

        foreach ($coSupervised as $item) {
            $u = $db->table('users')->where('email', $item['email'])->get()->getRowArray();
            if (!$u) {
                continue;
            }
            $uid = (int)$u['id'];

            // Give subordinate a secondary plantilla in the other VP's branch so both VPs see them in My Staff
            if (!empty($item['extra_unit_id']) && !empty($item['extra_pos_id'])) {
                $existingPlantilla = $db->table('plantillas')
                    ->where('user_id', $uid)
                    ->where('unit_id', $item['extra_unit_id'])
                    ->where('ended_at IS NULL')
                    ->get()->getRowArray();

                if (!$existingPlantilla) {
                    $db->table('plantillas')->insert([
                        'user_id'     => $uid,
                        'position_id' => $item['extra_pos_id'],
                        'unit_id'     => $item['extra_unit_id'],
                        'started_at'  => '2023-01-01',
                        'ended_at'    => null,
                    ]);
                }
            }

            // Ensure user is in BOTH VPs' active staff presets (team_vpaa_deans & team_vpaf_heads)
            foreach (['team_vpaa_deans', 'team_vpaf_heads'] as $presetId) {
                $inPreset = $db->table('routing_preset_members')
                    ->where('preset_id', $presetId)
                    ->where('user_id', $uid)
                    ->get()->getRowArray();
                if (!$inPreset) {
                    $db->table('routing_preset_members')->insert([
                        'preset_id'  => $presetId,
                        'user_id'    => $uid,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            // Update subordinate folder status to Target Phase
            $isSubmitted = ($item['folder_status'] === FolderStatus::TARGET_SUBMITTED->value);
            $db->table('document_folders')
                ->where('id', $item['folder_id'])
                ->update([
                    'status'              => $item['folder_status'],
                    'final_rating'        => null,
                    'target_submitted_at' => $isSubmitted ? $now : null,
                    'target_approved_at'  => null,
                    'submitted_at'        => null,
                    'rated_at'            => null,
                    'updated_at'          => $now,
                ]);

            // Clear existing routings on this folder and attach BOTH VPAA and VPAF as co-supervisors
            $db->table('evaluation_routings')->where('folder_id', $item['folder_id'])->delete();

            $db->table('evaluation_routings')->insert([
                'folder_id'           => $item['folder_id'],
                'evaluator_id'        => $vpaaId,
                'evaluator_folder_id' => 'fld_vpaa',
                'status'              => $item['vpaa_status'],
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);

            $db->table('evaluation_routings')->insert([
                'folder_id'           => $item['folder_id'],
                'evaluator_id'        => $vpafId,
                'evaluator_folder_id' => 'fld_vpaf',
                'status'              => $item['vpaf_status'],
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);
        }

        // Also keep remaining direct VP subordinates in Target Phase so both VPs' queues are in Target Phase
        $db->table('document_folders')
            ->whereIn('id', ['fld_dean_ca', 'fld_head_hrmo', 'fld_head_acc'])
            ->update([
                'status'             => FolderStatus::TARGET_APPROVED->value,
                'final_rating'       => null,
                'submitted_at'       => null,
                'rated_at'           => null,
                'updated_at'         => $now,
            ]);

        echo ">>> [SPMS MultiVpSeeder] Complete!\n";
        echo "    - Co-Supervisors: vpaa@test.com (Dr. Ana Santos) & vpaf@test.com (Engr. Roberto Mendoza)\n";
        echo "    - Co-Supervised Subordinates:\n";
        echo "      1. dean@test.com     (Roberto Reyes)     -> TARGET_SUBMITTED (Awaiting both VPAA & VPAF)\n";
        echo "      2. dean.cis@test.com (Alexander Mercado) -> TARGET_SUBMITTED (Approved by VPAA, awaiting VPAF)\n";
        echo "      3. head.gso@test.com (Rodrigo Estrada)   -> DRAFT_TARGET     (In Progress under both VPs)\n";
    }
}

