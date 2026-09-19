<?php
require 'vendor/autoload.php';
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['_method'] = 'PATCH';

// Bootstrap CI4
define('FCPATH', __DIR__ . '/../public/');
require 'app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/bootstrap.php';

$request = service('request');
echo "Request method: " . $request->getMethod() . PHP_EOL;

$routes = service('routes');
$router = service('router', $routes, $request);
try {
    $match = $router->handle('document');
    echo "Matched controller: " . $router->controllerName() . "::" . $router->methodName() . PHP_EOL;
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
