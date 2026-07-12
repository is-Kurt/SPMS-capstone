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
    // No role filter here on purpose: Rating::show() enforces Admin-or-routed-evaluator
    // itself, and unlike this filter, it can redirect a wrong-account visitor (e.g.
    // someone clicking a "Pending Review" email link addressed to their evaluator)
    // to the account-mismatch screen instead of a silent, contextless bounce.
    $routes->get('ratings/show/(:segment)', 'Rating::show/$1');

    // Routing Presets (My Teams)
    $routes->get('teams', 'Team::index', ['filter' => 'role:Admin,Supervisor']);
    $routes->post('teams/create-shell', 'Team::createShell', ['filter' => 'role:Admin,Supervisor']);
    $routes->post('teams/store', 'Team::store', ['filter' => 'role:Admin,Supervisor']);
    $routes->delete('teams/delete', 'Team::delete', ['filter' => 'role:Admin,Supervisor']);

    // Accounts
    $routes->get('accounts', 'AccountManagement::index', ['filter' => 'role:Admin']);
    $routes->get('accounts/(:segment)', 'AccountManagement::index/$1', ['filter' => 'role:Admin']);
    $routes->post('account/sendInvites', 'AccountManagement::sendInvites', ['filter' => 'role:Admin']);
    $routes->post('account/invite/delete', 'AccountManagement::deleteInvite', ['filter' => 'role:Admin']);
    $routes->post('account/invite/delete-bulk', 'AccountManagement::deleteInvitesBulk', ['filter' => 'role:Admin']);
    $routes->post('account/toggle', 'AccountManagement::toggleStatus', ['filter' => 'role:Admin']);
    $routes->post('account/update-role', 'AccountManagement::updateRole', ['filter' => 'role:Admin']);
    $routes->post('account/process-queue', 'AccountManagement::processQueueAjax');
    $routes->delete('account', 'AccountManagement::destroy', ['filter' => 'role:Admin']);

    // System Data Management Routes
    // Roles are intentionally not admin-creatable here - role names are hardcoded into
    // access-control checks throughout the app, so a UI-created role would grant nothing.
    $routes->post('account/role/delete', 'AccountManagement::deleteRole', ['filter' => 'role:Admin']);

    $routes->post('account/position/add', 'AccountManagement::addPosition', ['filter' => 'role:Admin']);
    $routes->post('account/position/delete', 'AccountManagement::deletePosition', ['filter' => 'role:Admin']);

    $routes->post('account/unit/add', 'AccountManagement::addUnit', ['filter' => 'role:Admin']);
    $routes->post('account/unit/delete', 'AccountManagement::deleteUnit', ['filter' => 'role:Admin']);

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

    $routes->get('api/csrf-token', 'Auth\Session::getCsrfToken');

    // Shown when a link (e.g. "Open Your Folder" in an email) belongs to a
    // different account than the one currently logged in - lets the user log out
    // and switch instead of silently landing on their own, unrelated data.
    $routes->get('account-mismatch', 'Auth\Session::accountMismatch');
    $routes->post('account-mismatch/switch', 'Auth\Session::switchAccount');
});

// Signup is deliberately outside the guest filter: an invite link must always be
// reachable even if the browser already has an active session (e.g. an admin
// testing the flow, or a shared computer) - Register::index() itself destroys any
// existing session once the token is confirmed valid, rather than blocking access.
$routes->get('signup', 'Auth\Register::index');
$routes->post('signup', 'Auth\Register::store');

$routes->group('', ['filter' => 'guest'], function($routes) {
    $routes->get('/', 'Auth\Session::index');
    $routes->get('login', 'Auth\Session::index');
    $routes->post('login', 'Auth\Session::edit');

    $routes->get('password/forgot', 'Auth\PasswordReset::index');
    $routes->post('password/send', 'Auth\PasswordReset::sendCode');
    $routes->get('password/verify', 'Auth\PasswordReset::verify');
    $routes->post('password/update', 'Auth\PasswordReset::updatePassword');
});