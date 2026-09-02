<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;

class ProfileEmailChange extends BaseController
{


    public function step1()
    {
        return view('profile/email_change/step1', [
            'email' => session()->get('email')
        ]);
    }

    public function verifyStep1()
    {
        if (!$this->validate(['password' => 'required'])) {
            return redirect()->back()->with('error', 'Please enter your current password.');
        }

        $password = $this->request->getPost('password');
        $userId = session()->get('user_id');
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        // Rate limiting for password attempts
        $now = time();
        $lastAttempt = strtotime($user['email_change_last_attempt_at'] ?? '2000-01-01');
        $attempts = (int)($user['email_change_attempts'] ?? 0);

        if (date('Y-m-d', $lastAttempt) !== date('Y-m-d', $now)) {
            $attempts = 0;
        }

        if ($attempts >= 3) {
            return redirect()->back()->with('error', 'Too many incorrect attempts. Please try again tomorrow.');
        }

        if (!password_verify((string)$password, $user['password'])) {
            $userModel->update($userId, [
                'email_change_attempts'        => $attempts + 1,
                'email_change_last_attempt_at' => date('Y-m-d H:i:s', $now)
            ]);
            return redirect()->back()->with('error', 'The password you entered is incorrect.');
        }

        // Correct password! Reset attempts and authorize them for step 2.
        $userModel->update($userId, [
            'email_change_attempts'        => 0,
            'email_change_last_attempt_at' => null
        ]);
        
        session()->set('email_change_authorized', true);
        return redirect()->to('profile/email/step2');
    }

    public function step2()
    {
        if (!session()->get('email_change_authorized')) {
            return redirect()->to('profile')->with('error', 'Unauthorized access.');
        }
        return view('profile/email_change/step2');
    }

    public function sendStep2Code()
    {
        if (!session()->get('email_change_authorized')) {
            return redirect()->to('profile');
        }

        $newEmail = strtolower(trim($this->request->getPost('new_email') ?? ''));
        $userId = session()->get('user_id');

        if (!$this->validateData(['new_email' => $newEmail], ['new_email' => "required|valid_email|is_unique[users.email,id,{$userId}]"])) {
            return redirect()->back()->withInput()->with('error', $this->validator->getError('new_email'));
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        $now = time();
        $lastAttempt = strtotime($user['email_change_last_attempt_at'] ?? '2000-01-01');
        $attempts = (int)($user['email_change_attempts'] ?? 0);

        if (date('Y-m-d', $lastAttempt) !== date('Y-m-d', $now)) {
            $attempts = 0;
        }

        if ($attempts >= 3) {
            return redirect()->back()->with('error', 'You have reached the maximum number of email change requests (3) for today.');
        }

        if (($now - $lastAttempt) < 60) {
            $secondsLeft = 60 - ($now - $lastAttempt);
            return redirect()->back()->with('error', "Please wait {$secondsLeft} seconds before requesting another code.");
        }

        $code = sprintf("%06d", mt_rand(1, 999999));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        $userModel->update($userId, [
            'email_change_code'            => $code,
            'email_change_new_email'       => $newEmail,
            'email_change_expires_at'      => $expiresAt,
            'email_change_attempts'        => $attempts + 1,
            'email_change_last_attempt_at' => date('Y-m-d H:i:s', $now)
        ]);

        helper('email_queue');
        queue_email(
            $newEmail,
            'SPMS New Email Verification Code',
            render_email('email_change_code', ['code' => $code])
        );

        // Flashdata must be set before dispatch_email_now()
        session()->setFlashdata('email_change_new_email', $newEmail);
        return dispatch_email_now(redirect()->to('profile/email/step3'), 1);
    }

    public function step3()
    {
        if (!session()->get('email_change_authorized')) {
            return redirect()->to('profile')->with('error', 'Unauthorized access.');
        }
        
        // Use flashdata to prefill view, keep session intact
        $newEmail = session()->getFlashdata('email_change_new_email');
        if ($newEmail) session()->setFlashdata('email_change_new_email', $newEmail);
        
        $userId = session()->get('user_id');
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);
        
        $lastAttempt = strtotime($user['email_change_last_attempt_at'] ?? '2000-01-01');
        $timeLeft = max(0, 60 - (time() - $lastAttempt));
        
        return view('profile/email_change/step3', [
            'new_email' => $newEmail,
            'timeLeft'  => $timeLeft
        ]);
    }

    public function verifyStep3()
    {
        if (!session()->get('email_change_authorized')) {
            return redirect()->to('profile');
        }

        if (!$this->validate(['code' => 'required|exact_length[6]|numeric'])) {
            return redirect()->back()->withInput()->with('error', 'Invalid code format.');
        }

        $code = trim($this->request->getPost('code'));
        $userId = session()->get('user_id');
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (empty($user['email_change_code']) || $user['email_change_code'] !== $code) {
            return redirect()->back()->with('error', 'The code you entered is incorrect.');
        }

        if (date('Y-m-d H:i:s') > $user['email_change_expires_at']) {
            $userModel->update($userId, [
                'email_change_code' => null, 
                'email_change_new_email' => null, 
                'email_change_expires_at' => null
            ]);
            session()->remove('email_change_authorized');
            return redirect()->to('profile')->with('error', 'Your verification code has expired. Please start over.');
        }

        // Success! Update the email.
        $userModel->update($userId, [
            'email'                   => $user['email_change_new_email'],
            'email_change_code'       => null,
            'email_change_new_email'  => null,
            'email_change_expires_at' => null
        ]);

        // Clear session flags and update session email
        session()->remove('email_change_authorized');
        session()->set('email', $user['email_change_new_email']);

        return redirect()->to('profile')->with('success', 'Your email address has been successfully updated.');
    }
}
