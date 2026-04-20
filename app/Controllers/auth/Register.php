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
            'first_name' => [
                'label'  => 'First Name',
                'rules'  => 'required|min_length[2]|max_length[100]'
            ],
            'last_name' => [
                'label'  => 'Last Name',
                'rules'  => 'required|min_length[2]|max_length[100]'
            ],
            'email' => [
                'label'  => 'Email Address',
                'rules'  => 'required|valid_email|max_length[254]|is_unique[users.email]',
            ],
            'password' => [
                'label'  => 'Password',
                'rules'  => 'required|min_length[3]|max_length[72]'
            ],
            'confirm-password' => 'required|matches[password]',
            'department' => [
                'label'  => 'Department',
                'rules'  => 'required'
            ],
            'position' => [
                'label'  => 'Position',
                'rules'  => 'required'
            ]
        ]);

        if (! $validation->run($this->request->getPost())) {
            return redirect()->back()->withInput();
        }

        $userModel = new \App\Models\UserModel();

        $userModel->save([
            'first_name' => $this->request->getPost()['first_name'],
            'last_name'  => $this->request->getPost()['last_name'],
            'email'      => $this->request->getPost()['email'], 
            'password'   => password_hash($this->request->getPost()['password'], PASSWORD_DEFAULT),
            'department' => $this->request->getPost()['department'],
            'position'   => $this->request->getPost()['position']
        ]);

        return redirect()->to('/login');
    }
}