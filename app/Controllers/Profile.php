<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;

class Profile extends BaseController
{
    public function index()
    {
        $userId = session()->get('user_id');
        $userModel = new UserModel();
        
        $data = [
            'user' => $userModel->find($userId)
        ];

        return view('profile', $data);
    }

    public function updateGeneral()
    {
        $userId = session()->get('user_id');
        $userModel = new UserModel();

        // 1. Validation Rules
        $rules = [
            'first_name' => 'required|min_length[2]',
            'last_name'  => 'required|min_length[2]',
            'email'      => "required|valid_email|is_unique[users.email,id,{$userId}]"
        ];

        if (!$this->validate($rules)) {
            // FIXED: Added withInput() to flash the field errors back to the view
            return redirect()->back()->withInput()->with('error', 'Please check your inputs and try again.');
        }

        // 2. Update Database
        $userModel->update($userId, [
            'first_name' => $this->request->getPost('first_name'),
            'last_name'  => $this->request->getPost('last_name'),
            'email'      => $this->request->getPost('email'),
        ]);

        // 3. Update Session variables so the UI updates instantly
        session()->set([
            'first_name' => $this->request->getPost('first_name'),
            'last_name'  => $this->request->getPost('last_name'),
            'email'      => $this->request->getPost('email'),
            'username'   => $this->request->getPost('first_name') . ' ' . $this->request->getPost('last_name')
        ]);

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword()
    {
        $userId = session()->get('user_id');
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        // 1. Validation Rules
        $rules = [
            'current_password' => 'required',
            'new_password'     => 'required|min_length[3]',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            // FIXED: Added withInput() to flash the field errors back to the view
            return redirect()->back()->withInput()->with('error', 'Please check your password inputs.');
        }

        // 2. Verify current password
        $currentPassword = $this->request->getPost('current_password');
        if (!password_verify($currentPassword, $user['password'])) {
            return redirect()->back()->withInput()->with('errors', [
                'current_password' => 'The current password you entered is incorrect.'
            ]);
        }

        // 3. Hash and save new password
        $newPassword = password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT);
        $userModel->update($userId, ['password' => $newPassword]);

        return redirect()->back()->with('success', 'Password updated successfully.');
    }
}