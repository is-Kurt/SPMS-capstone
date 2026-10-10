<?php

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
chdir(__DIR__);
require 'vendor/autoload.php';

$app = require_once 'app/Config/Boot/development.php';
$app = \Config\Services::codeigniter();
$app->initialize();

$session = \Config\Services::session();
$db = \Config\Database::connect();

// 1. Test Admin Dashboard
echo "=== Testing Admin Dashboard ===\n";
$session->set([
    'user_id' => 1,
    'role' => 'Admin',
    'logged_in' => true
]);

$controller = new \App\Controllers\Dashboard();
$controller->initController(\Config\Services::request(), \Config\Services::response(), \Config\Services::logger());

ob_start();
$res = $controller->index();
$output = (string)$res;
ob_end_clean();

echo "Admin Output length: " . strlen($output) . "\n";
assert(str_contains($output, 'CSC Rating Distribution'), "Should contain CSC Rating Distribution");
assert(str_contains($output, 'Top Performing Personnel'), "Should contain Top Performing Personnel");
assert(str_contains($output, 'Target Calibration &amp; Per-Office Compliance') || str_contains($output, 'Target Calibration & Per-Office Compliance'), "Should contain Target Calibration");

// 2. Test Supervisor Dashboard
echo "=== Testing Supervisor Dashboard ===\n";
// Find a supervisor user
$supervisor = $db->table('users')->where('role', 'Supervisor')->get()->getRowArray();
if ($supervisor) {
    $session->set([
        'user_id' => $supervisor['id'],
        'role' => 'Supervisor',
        'logged_in' => true
    ]);

    $controller = new \App\Controllers\Dashboard();
    $controller->initController(\Config\Services::request(), \Config\Services::response(), \Config\Services::logger());

    ob_start();
    $res = $controller->index();
    $output = (string)$res;
    ob_end_clean();

    echo "Supervisor Output length: " . strlen($output) . "\n";
    assert(str_contains($output, 'CSC Rating Distribution'), "Supervisor should contain CSC Rating Distribution");
    assert(str_contains($output, 'Top Performing Personnel'), "Supervisor should contain Top Performing Personnel");
}

echo "\nAll Dashboard tests passed successfully!\n";
