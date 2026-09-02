<?php

namespace App\Controllers;

class Landing extends BaseController
{
    public function index()
    {
        $isLoggedIn = (bool) session()->get('user_id');
        $userRole = session()->get('role');
        $username = session()->get('username') ?? 'User';

        return view('landing/index', [
            'isLoggedIn' => $isLoggedIn,
            'userRole'   => $userRole,
            'username'   => $username,
        ]);
    }
}
