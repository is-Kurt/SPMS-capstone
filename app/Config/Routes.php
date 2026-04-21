<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/test', 'Test::index');
$routes->post('/test/importWordTable', 'Test::importWordTable');

$routes->group('', ['filter' => 'auth'], function($routes) {
    //Ratings
    $routes->get('ratings', 'Rating', ['filter' => 'admin']);

    $routes->get('rating/departments', 'Rating::departments', ['filter' => 'admin']);
    $routes->get('rating/show', 'Rating::show', ['filter' => 'admin']);
    $routes->delete('rating', 'Rating::destroy', ['filter' => 'admin']);
    $routes->post('rating/save', 'Rating::save', ['filter' => 'admin']);

    $routes->get('profile', 'Profile');

    // Document
    $routes->get('documents', 'Document');

    $routes->get('document', 'Document::show');
    $routes->patch('document', 'Document::update');
    $routes->post('document', 'Document::store');
    $routes->delete('document', 'Document::destroy');
    $routes->post('document/share', 'Document::share');
    $routes->post('document/send', 'Document::send', ['filter' => 'admin']);
    $routes->post('document/createFolder', 'Document::createFolder', ['filter' => 'admin']);
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