<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DefaultFunctionWeightsSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('default_function_weights');

        echo ">>> Seeding Default Function Weights...\n";

        $now = date('Y-m-d H:i:s');
        $weights = [
            [
                'doc_type'         => 'OPCR',
                'core_weight'      => 70.00,
                'strategic_weight' => 20.00,
                'support_weight'   => 10.00,
                'description'      => 'Office Performance Commitment and Review for Executive and Head of Office units',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'doc_type'         => 'DPCR',
                'core_weight'      => 70.00,
                'strategic_weight' => 15.00,
                'support_weight'   => 15.00,
                'description'      => 'Department Performance Commitment and Review for Academic Deans, Department Chairs, and Service Divisions',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'doc_type'         => 'IPCR',
                'core_weight'      => 70.00,
                'strategic_weight' => 15.00,
                'support_weight'   => 15.00,
                'description'      => 'Individual Performance Commitment and Review for Faculty Members and Regular Staff',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ];

        foreach ($weights as $w) {
            $existing = $builder->where('doc_type', $w['doc_type'])->get()->getRow();
            if ($existing) {
                $builder->where('doc_type', $w['doc_type'])->update($w);
            } else {
                $builder->insert($w);
            }
        }

        echo ">>> Default Function Weights seeded successfully.\n";
    }
}

