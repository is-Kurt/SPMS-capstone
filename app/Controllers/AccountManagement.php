<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;

class AccountManagement extends BaseController
{
    public function index()
    {
        // Security check: Only Admins can access this
        if (session()->get('role') !== 'Admin') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $userModel = new UserModel();
        
        $data = [
            'users' => $userModel->orderBy('role', 'ASC')->orderBy('last_name', 'ASC')->findAll()
        ];

        return view('auth/accounts', $data);
    }

    public function store()
    {
        if (session()->get('role') !== 'Admin') return redirect()->to('/');

        $role = $this->request->getPost('role');

        $rules = [
            'first_name' => 'required|min_length[2]',
            'last_name'  => 'required|min_length[2]',
            'email'      => 'required|valid_email|is_unique[users.email]',
            'password'   => 'required|min_length[8]',
            'role'       => 'required'
        ];

        // 1. Conditional Validation Logic
        $requiresDept = !in_array($role, ['Admin', 'Vice President', 'Campus Administrator']);
        $requiresPos  = !in_array($role, ['Admin', 'Vice President', 'Campus Administrator', 'Dean']);

        if ($requiresDept) $rules['department'] = 'required';
        if ($requiresPos)  $rules['position']   = 'required';

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please check the form for errors.');
        }

        // 2. Save User (Insert 'N/A' for fields that aren't required)
        $userModel = new UserModel();
        $userModel->save([
            'first_name' => $this->request->getPost('first_name'),
            'last_name'  => $this->request->getPost('last_name'),
            'email'      => $this->request->getPost('email'),
            'password'   => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'       => $role,
            'department' => $requiresDept ? $this->request->getPost('department') : null,
            'position'   => $requiresPos  ? $this->request->getPost('position')   : null,
            'is_active'  => 1
        ]);

        return redirect()->back()->with('success', 'User account created successfully.');
    }

    public function toggleStatus()
    {
        if (session()->get('role') !== 'Admin') return redirect()->to('/');

        $targetId = $this->request->getPost('user_id');
        
        // Prevent admin from disabling themselves
        if ($targetId == session()->get('user_id')) {
            return redirect()->back()->with('error', 'You cannot disable your own account.');
        }

        $userModel = new UserModel();
        $user = $userModel->find($targetId);

        if ($user) {
            $newStatus = $user['is_active'] == 1 ? 0 : 1;
            $userModel->update($targetId, ['is_active' => $newStatus]);
            $msg = $newStatus == 1 ? 'Account enabled.' : 'Account disabled.';
            return redirect()->back()->with('success', $msg);
        }

        return redirect()->back()->with('error', 'User not found.');
    }
}
