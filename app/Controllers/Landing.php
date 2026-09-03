<?php

namespace App\Controllers;

class Landing extends BaseController
{
    public function index()
    {
        $session = session();
        if ($session->get('user_id') && !$this->request->getGet('logged_out')) {
            $dest = ($session->get('role') === 'TWG') ? 'ratings' : 'folders';
            return redirect()->to(site_url($dest));
        }

        $isLoggedIn = (bool) $session->get('user_id');
        $userRole = $session->get('role');
        $username = $session->get('username') ?? 'User';

        return view('landing/index', [
            'isLoggedIn' => $isLoggedIn,
            'userRole'   => $userRole,
            'username'   => $username,
        ]);
    }
}
