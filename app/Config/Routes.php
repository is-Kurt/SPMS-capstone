<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/test', 'Test::index');
$routes->post('/test/importWordTable', 'Test::importWordTable');

$routes->get('/', 'Home::index');

$routes->get('documents', 'Documents\Index::index');
$routes->delete('documents', 'Documents\Index::delete');
$routes->post('documents/share', 'Documents\Index::share');
$routes->get('document', 'Documents\Edit::index');
$routes->patch('document', 'Documents\Edit::patch');
$routes->post('document', 'Documents\Edit::store');

$routes->get('submissions', 'Submissions\Index::index');
$routes->get('submission', 'Submissions\Edit::index');
$routes->post('submission', 'Submissions\Edit::store');
$routes->patch('submission', 'Submissions\Edit::patch');
$routes->patch('submission/rated', 'Submissions\Edit::rated');

$routes->get('signup', 'Auth\Register::index');
$routes->post('signup', 'Auth\Register::store');

$routes->get('login', 'Auth\Session::index');
$routes->post('login', 'Auth\Session::edit');
$routes->delete('login', 'Auth\Session::destroy');

$routes->get('users/search', 'Auth\Users::search');