<?php
define('FCPATH', realpath(__DIR__ . '/../public') . DIRECTORY_SEPARATOR);
chdir(FCPATH);
require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/Boot.php';
require $paths->appDirectory . '/Config/Constants.php';

\CodeIgniter\Boot::bootTest($paths);

$session = service('session');
$session->set([
    'user_id' => 3,
    'role' => 'Admin',
    'position' => 'Secondary Admin',
    'logged_in' => true
]);

$docModel = new \App\Models\DocumentModel();
$doc = $docModel->find('qzbQ3lINhU4');
echo "Doc ID: " . $doc['id'] . "\n";
echo "Doc Title: " . $doc['title'] . "\n";

$controller = new \App\Controllers\Document();
$controller->initController(service('request'), service('response'), service('logger'));
$response = $controller->index('qzbQ3lINhU4');
if (is_string($response)) {
    echo "Response length: " . strlen($response) . "\n";
    if (preg_match('/let tabs = (.*?);\s*if \(!tabs/s', $response, $m)) {
        echo "tabs in JS (first 200 chars): " . substr($m[1], 0, 200) . "...\n";
        $tabsDecoded = json_decode($m[1], true);
        echo "Decoded tabs count: " . count($tabsDecoded) . "\n";
        echo "formData categories: " . json_encode($tabsDecoded[0]['formData']['categories']) . "\n";
    }
}
