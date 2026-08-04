<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;

class ProfilePasswordChange extends BaseController
{
    public function step1()
    {
        return view('profile/password_change/step1');
    }

    public function verifyStep1()
    {
        if (!$this->validate(['current_password' => 'required'])) {
            return redirect()->back()->with('error', 'Please enter your current password.');
        }

        $userId = session()->get('user_id');
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        $now = time();
        $lastAttempt = strtotime($user['password_change_last_attempt_at'] ?? '2000-01-01');
        $attempts = (int)($user['password_change_attempts'] ?? 0);

        if (date('Y-m-d', $lastAttempt) !== date('Y-m-d', $now)) {
            $attempts = 0;
        }

        if ($attempts >= 3) {
            return redirect()->back()->with('error', 'Too many incorrect attempts. Please try again tomorrow.');
        }

        if (!password_verify((string)$this->request->getPost('current_password'), $user['password'])) {
            $userModel->update($userId, [
                'password_change_attempts'        => $attempts + 1,
                'password_change_last_attempt_at' => date('Y-m-d H:i:s', $now)
            ]);
            return redirect()->back()->with('error', 'The current password you entered is incorrect.');
        }

        // Correct password! Authorize them for step 2.
        $userModel->update($userId, [
            'password_change_attempts'        => 0,
            'password_change_last_attempt_at' => null
        ]);
        session()->set('password_change_authorized', true);
        return redirect()->to('profile/password/step2');
    }

    public function step2()
    {
        if (!session()->get('password_change_authorized')) {
            return redirect()->to('profile')->with('error', 'Unauthorized access.');
        }
        return view('profile/password_change/step2');
    }

    public function updatePassword()
    {
        if (!session()->get('password_change_authorized')) {
            return redirect()->to('profile');
        }

        $rules = [
            'new_password'     => 'required|min_length[8]',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('error', $this->validator->getError('new_password') ?: $this->validator->getError('confirm_password'));
        }

        $userId = session()->get('user_id');
        $userModel = new UserModel();
        
        // Ensure new password isn't the same as the old one
        $user = $userModel->find($userId);
        if (password_verify((string)$this->request->getPost('new_password'), $user['password'])) {
            return redirect()->back()->with('error', 'New password cannot be the same as your current password.');
        }

        $userModel->update($userId, [
            'password' => password_hash((string)$this->request->getPost('new_password'), PASSWORD_DEFAULT)
        ]);

        session()->remove('password_change_authorized');
        return redirect()->to('profile')->with('success', 'Your password has been successfully updated.');
    }
}
