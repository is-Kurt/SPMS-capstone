<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Session extends BaseController
{
    public function index() {
        return view('auth/login');
    }

    public function edit() {
        $validation = \Config\Services::validation();

        $validation->setRules([
            'email' => 'required|valid_email',
            'password' => 'required'
        ]);

        if (! $validation->run($this->request->getPost())) {
            return redirect()->back()->withInput();
        }

        $email = $this->request->getPost()['email'];
        $password = $this->request->getPost()['password'];

        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('email', $email)->first();

        if ($user && password_verify($password, $user['password'])) {
            session()->set([
                'user_id' => $user['id'],
                'email' => $user['email'],
                'username' => $user['username'] ?? explode('@', $user['email'])[0],
                'isLoggedIn' => true
            ]);

            return redirect()->to('/');
        }

        return redirect()->back()->withInput()->with('errors', ['password' => 'Invalid email or password.']);
    }

    public function destroy() {
        session()->destroy();
        return redirect()->to(site_url('login'));
    }
}