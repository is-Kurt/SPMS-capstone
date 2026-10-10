<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CleanIperfDefaultDeliverables extends Migration
{
    public function up()
    {
        $now = date('Y-m-d H:i:s');

        // 1. Clean Template IPERF
        $tpls = $this->db->table('templates')->where('title', 'IPERF')->get()->getResultArray();
        foreach ($tpls as $t) {
            $tabs = is_string($t['tabs']) ? json_decode($t['tabs'], true) : $t['tabs'];
            if (!empty($tabs) && is_array($tabs)) {
                if (isset($tabs[0]['formData'])) {
                    $tabs[0]['formData']['categories'] = [
                        'core' => [
                            [
                                'row_id'          => 'row_core_1',
                                'mfo'             => '',
                                'indicators'      => '',
                                'accomplishments' => '',
                                'q'               => '',
                                't'               => '',
                                'e'               => '',
                                'remarks'         => '',
                                'twg_comment'     => ''
                            ]
                        ],
                        'strategic' => [],
                        'support'   => []
                    ];
                }
                $this->db->table('templates')->where('id', $t['id'])->update([
                    'tabs'       => json_encode($tabs),
                    'updated_at' => $now
                ]);
            }
        }

        // 2. Clean Existing IPERF Documents
        $iperfDocs = $this->db->table('documents')
            ->like('title', 'IPERF')
            ->get()->getResultArray();

        foreach ($iperfDocs as $d) {
            $tabs = is_string($d['tabs']) ? json_decode($d['tabs'], true) : $d['tabs'];
            if (!empty($tabs) && is_array($tabs)) {
                if (isset($tabs[0]['formData'])) {
                    $tabs[0]['formData']['categories'] = [
                        'core' => [
                            [
                                'row_id'          => 'row_core_1',
                                'mfo'             => '',
                                'indicators'      => '',
                                'accomplishments' => '',
                                'q'               => '',
                                't'               => '',
                                'e'               => '',
                                'remarks'         => '',
                                'twg_comment'     => ''
                            ]
                        ],
                        'strategic' => [],
                        'support'   => []
                    ];
                }
                $this->db->table('documents')->where('id', $d['id'])->update([
                    'tabs'       => json_encode($tabs),
                    'updated_at' => $now
                ]);
            }
        }
    }

    public function down()
    {
        // Down migration intentionally empty as clearing sample data is permanent
    }
}
