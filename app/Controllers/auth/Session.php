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
            'email' => [
                'label' => 'Email',
                'rules' => 'required|valid_email|max_length[254]'
            ],
            'password' => [
                'label' => 'Password',
                'rules' => 'required|max_length[72]'
            ]
        ]);
        
        if (! $validation->run($this->request->getPost())) {
            return redirect()->back()->withInput();
        }

        $email = $this->request->getPost()['email'];
        $password = $this->request->getPost()['password'];

        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('email', $email)->first();

        $rememberMe = (bool) $this->request->getPost('remember-me');

        if ($user && isset($user['is_active']) && $user['is_active'] == 1 && password_verify($password, $user['password'])) {
            
            $db = \Config\Database::connect();

            // 1. Fetch System Role (RBAC)
            $roleData = $db->table('user_roles ur')
                ->select('r.name as role_name')
                ->join('roles r', 'r.id = ur.role_id')
                ->where('ur.user_id', $user['id'])
                ->get()->getRowArray();
            
            $systemRole = $roleData ? $roleData['role_name'] : 'Employee';

            // 2. Fetch HR Department/Unit & Position (Plantilla)
            $plantillaData = $db->table('plantilla p')
                ->select('u.name as department, pos.title as position')
                ->join('units u', 'u.id = p.unit_id')
                ->join('positions pos', 'pos.id = p.position_id')
                ->where('p.user_id', $user['id'])
                ->where('p.ended_at IS NULL') // Ensures we only get their active assignment
                ->get()->getRowArray();

            $department = $plantillaData ? $plantillaData['department'] : null;
            $position   = $plantillaData ? $plantillaData['position'] : null;

            // 3. Set the Session
            session()->set([
                'user_id'    => $user['id'],
                'email'      => $user['email'],
                'role'       => $systemRole,       // From RBAC
                'department' => $department,       // From Plantilla
                'position'   => $position,         // Extra context from Plantilla
                'username'   => $user['first_name'] . ' ' . $user['last_name'],
                'isLoggedIn' => true
            ]);

            if ($rememberMe) {
                $token = bin2hex(random_bytes(32));

                $userModel->update($user['id'], [
                    'remember_token'        => hash('sha256', $token),
                    'remember_token_expiry' => date('Y-m-d H:i:s', strtotime('+30 days'))
                ]);

                setcookie('remember_me', $token, [
                    'expires'  => time() + (30 * 24 * 60 * 60),
                    'path'     => '/',
                    'httponly' => true,
                    'secure'   => false // set true in production (HTTPS)
                ]);
            }

            return redirect()->to(site_url('folders'));
        }

        return redirect()->back()->withInput()->with('errors', ['error' => 'Invalid email or password.']);
    }

    public function destroy() {
        $userId = session()->get('user_id');

        if ($userId) {
            $userModel = new \App\Models\UserModel();
            $userModel->update($userId, [
                'remember_token'        => null,
                'remember_token_expiry' => null
            ]);
        }

        setcookie('remember_me', '', time() - 3600, '/');

        session()->destroy();
        return redirect()->to(site_url('login'));
    }
}