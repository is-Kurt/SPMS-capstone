<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/test', 'Test::index');
$routes->post('/test/importWordTable', 'Test::importWordTable');

$routes->group('', ['filter' => 'auth'], function($routes) {
    // Ratings
    $routes->get('ratings', 'Rating', ['filter' => 'role:Admin, Vice President, Campus Administrator, Dean, Director, Head of Office']);
    $routes->get('rating/departments', 'Rating::departments', ['filter' => 'role:Admin']);
    $routes->get('rating/show', 'Rating::show', ['filter' => 'role:Admin,supervisor']);
    $routes->post('rating/save', 'Rating::save', ['filter' => 'role:Admin,supervisor']);

    $routes->delete('rating', 'Rating::destroy', ['filter' => 'role:admin']);
    $routes->post('rating/save', 'Rating::save', ['filter' => 'role:admin']);

    $routes->get('profile', 'Profile');

    // Document
    $routes->get('folders', 'Folder');
    $routes->post('folder', 'Folder::store', ['filter' => 'role:Admin']);
    $routes->post('folder/send', 'Folder::send', ['filter' => 'role:Admin']);
    $routes->delete('folder', 'Folder::destroy', ['filter' => 'role:Admin']);

    $routes->get('document', 'Document');
    $routes->post('document/submit', 'Document::submit');
    $routes->post('document/evaluate', 'Document::evaluate');
    $routes->patch('document', 'Document::update');
    $routes->post('document', 'Document::store');
    $routes->delete('document', 'Document::destroy');

    $routes->get('user/find', 'User::find');

    $routes->delete('login', 'Auth\Session::destroy');
});

$routes->group('', ['filter' => 'guest'], function($routes) {
    $routes->get('signup', 'Auth\Register::index');
    $routes->post('signup', 'Auth\Register::store');
    
    $routes->get('/', 'Auth\Session::index');
    $routes->get('login', 'Auth\Session::index');
    $routes->post('login', 'Auth\Session::edit');
});