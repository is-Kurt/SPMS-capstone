<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\DocumentModel;

class TestDocUpdate extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:doc-update';
    protected $description = 'Test document update';

    public function run(array $params)
    {
        $docModel = new DocumentModel();
        $doc = $docModel->find('qzbQ3lINhU4');
        CLI::write("Doc ID: " . $doc['id']);
        CLI::write("Tabs type: " . gettype($doc['tabs']));
        $tabs = $doc['tabs'];
        CLI::write("Core rows: " . count($tabs[0]['formData']['categories']['core'] ?? []));

        // Test updating tabs array
        $tabs[0]['formData']['revisionHistory'][] = [
            'id' => 'test_spark_rev',
            'reason' => 'Spark test revision'
        ];
        $docModel->update($doc['id'], ['tabs' => $tabs]);

        $doc2 = $docModel->find('qzbQ3lINhU4');
        CLI::write("After update core rows: " . count($doc2['tabs'][0]['formData']['categories']['core'] ?? []));
        CLI::write("After update revHistory: " . count($doc2['tabs'][0]['formData']['revisionHistory'] ?? []));

        // Revert
        array_pop($tabs[0]['formData']['revisionHistory']);
        $docModel->update($doc['id'], ['tabs' => $tabs]);
        CLI::write("Reverted test.");
    }
}
