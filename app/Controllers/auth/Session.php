<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;

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

        $email    = trim(strtolower($this->request->getPost('email')));
        $password = $this->request->getPost()['password'];

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        $rememberMe = (bool) $this->request->getPost('remember-me');

        if ($user && isset($user['is_active']) && $user['is_active'] == 1 && password_verify($password, $user['password'])) {
            
            $roleData = $userModel->db->table('user_roles ur')
                ->select('r.name as role_name')
                ->join('roles r', 'r.id = ur.role_id')
                ->where('ur.user_id', $user['id'])
                ->get()->getRowArray();
            
            $systemRole = $roleData ? $roleData['role_name'] : 'Employee';

            $plantillaData = $userModel->db->table('plantillas p')
                ->select('u.name as department, pos.title as position')
                ->join('units u', 'u.id = p.unit_id')
                ->join('positions pos', 'pos.id = p.position_id')
                ->where('p.user_id', $user['id'])
                ->where('p.ended_at IS NULL')
                ->get()->getRowArray();

            $department = $plantillaData ? $plantillaData['department'] : null;
            $position   = $plantillaData ? $plantillaData['position'] : null;

            session()->set([
                'user_id'    => $user['id'],
                'email'      => $user['email'],
                'role'       => $systemRole, 
                'department' => $department,
                'position'   => $position,
                'username'   => $user['first_name'] . ' ' . $user['last_name'],
                'isLoggedIn' => true,
                'avatar_image'  => $user['avatar_image'],
                'avatar_color'  => $user['avatar_color'] ?? sprintf('#%06X', mt_rand(0, 0xFFFFFF)),
                'avatar_letter' => $user['avatar_letter'] ?? strtoupper(substr($user['first_name'], 0, 1)),
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
                    'secure'   => false
                ]);
            }

            return redirect()->to(site_url('folders'));
        }

        return redirect()->back()->withInput()->with('errors', ['error' => 'Invalid email or password.']);
    }

    public function destroy() {
        $userId = session()->get('user_id');

        if ($userId) {
            $userModel = new UserModel();
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