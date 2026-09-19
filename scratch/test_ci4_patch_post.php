<?php
define('FCPATH', __DIR__ . '/../public/');
require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/Boot.php';

$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['_method'] = 'PATCH';
$_POST['id'] = 'test123';
$_POST['tabs'] = '{"hello":"world"}';

\CodeIgniter\Boot::definePathConstants($paths);
\CodeIgniter\Boot::bootTest($paths);
$request = service('request');

echo "Request getMethod(): " . $request->getMethod() . "\n";
echo "getPost('id'): " . var_export($request->getPost('id'), true) . "\n";
echo "getPost('tabs'): " . var_export($request->getPost('tabs'), true) . "\n";
echo "getVar('tabs'): " . var_export($request->getVar('tabs'), true) . "\n";
