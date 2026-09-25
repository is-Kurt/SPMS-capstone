<?php
define('FCPATH', realpath(__DIR__ . '/../public') . DIRECTORY_SEPARATOR);
chdir(FCPATH);
require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/Boot.php';
\CodeIgniter\Boot::bootTest($paths);

$docModel = new \App\Models\DocumentModel();
$doc = $docModel->find('qzbQ3lINhU4');
echo "Initial tabs type: " . gettype($doc['tabs']) . "\n";
echo "Initial categories core count: " . count($doc['tabs'][0]['formData']['categories']['core']) . "\n";

$tabs = $doc['tabs'];
if (is_string($tabs)) $tabs = json_decode($tabs, true);
$tabs[0]['formData']['revisionHistory'][] = [
    'id' => 'test_123',
    'reason' => 'Test reason'
];

$docModel->update($doc['id'], ['tabs' => $tabs]);

$updatedDoc = $docModel->find('qzbQ3lINhU4');
echo "After update tabs type: " . gettype($updatedDoc['tabs']) . "\n";
echo "After update categories core count: " . count($updatedDoc['tabs'][0]['formData']['categories']['core']) . "\n";
echo "After update revisionHistory count: " . count($updatedDoc['tabs'][0]['formData']['revisionHistory']) . "\n";

// Revert test change
array_pop($tabs[0]['formData']['revisionHistory']);
$docModel->update($doc['id'], ['tabs' => $tabs]);
echo "Reverted.\n";
