<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTabsToDocumentsAndTemplates extends Migration
{
    public function up()
    {
        $this->forge->addColumn('documents', [
            'tabs' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
        ]);

        $this->forge->addColumn('templates', [
            'tabs' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
        ]);

        // Migrate data for documents
        $db = \Config\Database::connect();
        $builder = $db->table('documents');
        $docs = $builder->select('id, content, rubrics_content')->get()->getResultArray();
        foreach ($docs as $doc) {
            $tabs = [
                [
                    'id' => 'tab-target',
                    'title' => 'Target Form',
                    'content' => $doc['content'] ?? ''
                ],
                [
                    'id' => 'tab-rubrics',
                    'title' => 'Rubrics / Guide',
                    'content' => $doc['rubrics_content'] ?? ''
                ]
            ];
            $builder->where('id', $doc['id'])->update(['tabs' => json_encode($tabs)]);
        }

        // Migrate data for templates
        $builder = $db->table('templates');
        $templates = $builder->select('id, content, rubrics_content')->get()->getResultArray();
        foreach ($templates as $tpl) {
            $tabs = [
                [
                    'id' => 'tab-target',
                    'title' => 'Target Form',
                    'content' => $tpl['content'] ?? ''
                ],
                [
                    'id' => 'tab-rubrics',
                    'title' => 'Rubrics / Guide',
                    'content' => $tpl['rubrics_content'] ?? ''
                ]
            ];
            $builder->where('id', $tpl['id'])->update(['tabs' => json_encode($tabs)]);
        }

        // Drop the old columns
        $this->forge->dropColumn('documents', 'content');
        $this->forge->dropColumn('documents', 'rubrics_content');
        $this->forge->dropColumn('templates', 'content');
        $this->forge->dropColumn('templates', 'rubrics_content');
    }

    public function down()
    {
        $this->forge->addColumn('documents', [
            'content' => ['type' => 'LONGTEXT', 'null' => true],
            'rubrics_content' => ['type' => 'LONGTEXT', 'null' => true]
        ]);

        $this->forge->addColumn('templates', [
            'content' => ['type' => 'LONGTEXT', 'null' => true],
            'rubrics_content' => ['type' => 'LONGTEXT', 'null' => true]
        ]);

        // We could attempt to migrate data back here if strictly needed,
        // but for now just dropping the new column is standard.
        $this->forge->dropColumn('documents', 'tabs');
        $this->forge->dropColumn('templates', 'tabs');
    }
}
