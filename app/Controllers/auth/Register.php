<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Register extends BaseController
{
    public function index()
    {        
        return view('auth/signup');
    }

    public function store()
    {
        $validation = \Config\Services::validation();
        
        $validation->setRules([
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'confirm-password' => 'required|matches[password]'
        ]);

        if (! $validation->run($this->request->getPost())) {
            return redirect()->back()->withInput();
        }

        $userModel = new \App\Models\UserModel();
        $userModel->save([
            'email' => $this->request->getPost()['email'],
            'password' => password_hash($this->request->getPost()['password'], PASSWORD_DEFAULT)
        ]);

        return redirect()->to('/login');
    }
}
