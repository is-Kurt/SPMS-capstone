<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/test', 'Test::index');
$routes->post('/test/importWordTable', 'Test::importWordTable');

$routes->group('', ['filter' => 'auth'], function($routes) {
    //Ratings
    $routes->get('ratings', 'Rating', ['filter' => 'role:admin,supervisor']);
    $routes->get('rating/departments', 'Rating::departments', ['filter' => 'role:admin']);
    $routes->get('rating/show', 'Rating::show', ['filter' => 'role:admin,supervisor']);
    $routes->post('rating/save', 'Rating::save', ['filter' => 'role:admin,supervisor']);

    $routes->delete('rating', 'Rating::destroy', ['filter' => 'role:admin']);
    $routes->post('rating/save', 'Rating::save', ['filter' => 'role:admin']);

    $routes->get('profile', 'Profile');

    // Document
    $routes->get('documents', 'Folder');
    $routes->post('folder', 'Folder::store', ['filter' => 'role:admin']);
    $routes->post('folder/send', 'Folder::send', ['filter' => 'role:admin']);
    $routes->delete('folder', 'Folder::destroy', ['filter' => 'role:admin']);

    $routes->get('document', 'Document');
    $routes->patch('document', 'Document::update');
    $routes->post('document', 'Document::store');
    $routes->delete('document', 'Document::destroy');
    $routes->post('document/share', 'Document::share');
    $routes->get('document/count', 'Document::count');

    // Submission
    $routes->get('submissions', 'Submission');

    $routes->delete('submission', 'Submission::destroy');
    $routes->get('submission', 'Submission::show');
    $routes->post('submission', 'Submission::store');
    $routes->patch('submission', 'Submission::patch');
    $routes->post('submission/rate', 'Submission::rate');
    $routes->get('submission/count', 'Submission::count');

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