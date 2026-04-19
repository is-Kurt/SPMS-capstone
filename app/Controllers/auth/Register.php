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
            'email' => [
                'label'  => 'Email Address',
                'rules'  => 'required|valid_email|max_length[254]|is_unique[users.email]',
            ],
            'password' => [
                'label'  => 'Password',
                'rules'  => 'required|min_length[3]|max_length[72]'
            ],
            'confirm-password' => 'required|matches[password]'
        ]);

        if (! $validation->run($this->request->getPost())) {
            return redirect()->back()->withInput();
        }

        $userModel = new \App\Models\UserModel();

        $email = $this->request->getPost()['email'];
        $username = strstr($email, '@', true);

        $userModel->save([
            'email' => $email, 
            'username' => $username,
            'password' => password_hash($this->request->getPost()['password'], PASSWORD_DEFAULT)
        ]);

        return redirect()->to('/login');
    }
}
