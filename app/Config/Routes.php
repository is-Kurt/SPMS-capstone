<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/test', 'Test::index');
$routes->post('/test/importWordTable', 'Test::importWordTable');

$routes->group('', ['filter' => 'auth'], function($routes) {
    // Ratings
    $routes->get('ratings', 'Rating::index');
    $routes->get('ratings/(:segment)', 'Rating::index/$1');
    $routes->get('ratings/show/(:segment)', 'Rating::show/$1', ['filter' => 'role:Admin, Supervisor']);

    // Routing Presets (My Teams)
    $routes->get('teams', 'Team::index', ['filter' => 'role:Admin,Supervisor']);
    $routes->post('teams/create-shell', 'Team::createShell', ['filter' => 'role:Admin,Supervisor']);
    $routes->post('teams/store', 'Team::store', ['filter' => 'role:Admin,Supervisor']);
    $routes->delete('teams/delete', 'Team::delete', ['filter' => 'role:Admin,Supervisor']);

    // Accounts
    $routes->get('accounts', 'AccountManagement::index', ['filter' => 'role:Admin']);
    $routes->post('account/sendInvites', 'AccountManagement::sendInvites', ['filter' => 'role:Admin']);
    $routes->post('account/toggle', 'AccountManagement::toggleStatus', ['filter' => 'role:Admin']);
    $routes->post('account/process-queue', 'AccountManagement::processQueueAjax');
    $routes->delete('account', 'AccountManagement::destroy', ['filter' => 'role:Admin']);

    // System Data Management Routes
    $routes->post('account/role/add', 'AccountManagement::addRole');
    $routes->post('account/role/delete', 'AccountManagement::deleteRole');

    $routes->post('account/position/add', 'AccountManagement::addPosition');
    $routes->post('account/position/delete', 'AccountManagement::deletePosition');

    $routes->post('account/unit/add', 'AccountManagement::addUnit');
    $routes->post('account/unit/delete', 'AccountManagement::deleteUnit');

    // Template Management
    $routes->get('templates', 'Template::index', ['filter' => 'role:Admin']);
    $routes->get('templates/create', 'Template::create', ['filter' => 'role:Admin']);
    $routes->get('templates/edit/(:num)', 'Template::edit/$1', ['filter' => 'role:Admin']);
    
    $routes->post('templates/store', 'Template::store', ['filter' => 'role:Admin']);
    $routes->post('templates/delete', 'Template::delete', ['filter' => 'role:Admin']);

    // Profile
    $routes->get('profile', 'Profile');
    $routes->post('profile/general', 'Profile::updateGeneral');
    $routes->post('profile/password', 'Profile::updatePassword');
    $routes->post('profile/updateAvatar', 'Profile::updateAvatar');

    // Folder
    $routes->get('folders', 'Folder');
    $routes->get('folders/(:segment)', 'Folder::index/$1');
    $routes->post('folder', 'Folder::store', ['filter' => 'role:Admin']);
    $routes->post('folder/update', 'Folder::update', ['filter' => 'role:Admin']);
    $routes->delete('folder', 'Folder::destroy', ['filter' => 'role:Admin']);
    $routes->post('folder/submit', 'Folder::submit');
    $routes->post('folder/unsubmit', 'Folder::unsubmit');
    $routes->post('folder/evaluate', 'Folder::evaluate');
    $routes->post('folder/approve', 'Folder::approve');
    $routes->post('folder/return', 'Folder::returnRevision');
    $routes->post('folder/cascade-team', 'Folder::cascadeTeam', ['filter' => 'role:Admin,Supervisor']);
    $routes->post('folder/uncascade-team', 'Folder::uncascadeTeam', ['filter' => 'role:Admin,Supervisor']);

    // Document
    $routes->get('document', 'Document');
    $routes->get('document/(:segment)', 'Document::index/$1');
    $routes->post('document', 'Document::store');
    $routes->patch('document', 'Document::update');
    $routes->delete('document', 'Document::destroy');
    $routes->post('document/target', 'Document::setTarget');

    // Auth
    $routes->delete('login', 'Auth\Session::destroy');
});

$routes->group('', ['filter' => 'guest'], function($routes) {
    $routes->get('signup', 'Auth\Register::index');
    $routes->post('signup', 'Auth\Register::store');
    
    $routes->get('/', 'Auth\Session::index');
    $routes->get('login', 'Auth\Session::index');
    $routes->post('login', 'Auth\Session::edit');

    $routes->get('password/forgot', 'Auth\PasswordReset::index');
    $routes->post('password/send', 'Auth\PasswordReset::sendCode');
    $routes->get('password/verify', 'Auth\PasswordReset::verify');
    $routes->post('password/update', 'Auth\PasswordReset::updatePassword');
});