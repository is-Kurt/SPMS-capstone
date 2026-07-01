<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;

class PasswordReset extends BaseController
{
    // 1. Shows the "Enter Email" page
    public function index() {
        return view('auth/forgot_password');
    }

    // 2. Generates the code and emails the user
    public function sendCode() {
        $email = strtolower(trim($this->request->getPost('email')));
        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if ($user && $user['is_active'] == 1) {
            $now = time();
            $lastAttempt = strtotime($user['reset_last_attempt_at'] ?? '2000-01-01');
            $attempts = (int)($user['reset_attempts'] ?? 0);

            if (date('Y-m-d', $lastAttempt) !== date('Y-m-d', $now)) {
                $attempts = 0;
            }

            if ($attempts >= 5) {
                return redirect()->back()->with('error', 'Security Lock: You have reached the maximum number of password reset requests (5) for today. Please try again tomorrow.');
            }

            if (($now - $lastAttempt) < 60) {
                $secondsLeft = 60 - ($now - $lastAttempt);
                return redirect()->back()->with('error', "Anti-Spam: Please wait {$secondsLeft} seconds before requesting another code.");
            }

            $code = sprintf("%06d", mt_rand(1, 999999)); 
            $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

            $userModel->update($user['id'], [
                'reset_code'            => $code,
                'reset_code_expires_at' => $expiresAt,
                'reset_attempts'        => $attempts + 1,
                'reset_last_attempt_at' => date('Y-m-d H:i:s', $now)
            ]);

            helper('email_queue');
            queue_email(
                $email, 
                'SPMS Password Reset Code', 
                "Your password reset code is: <b style='font-size:24px;'>{$code}</b><br><br>This code will expire in exactly 5 minutes."
            );

            // ==========================================
            // DYNAMIC EXECUTION BASED ON ENVIRONMENT
            // ==========================================
            if (ENVIRONMENT === 'development' || !function_exists('fastcgi_finish_request')) {
                // LOCAL: Standard blocking redirect
                \process_email_queue(1);
                return redirect()->to('password/verify')->with('reset_email', $email);
            } else {
                // PRODUCTION (Nginx): Non-Blocking
                
                // 1. Manually set flashdata so it isn't lost when we force-exit
                session()->setFlashdata('reset_email', $email);
                
                // 2. Create the redirect response
                $response = redirect()->to('password/verify');
                
                // 3. Send the redirect headers to the browser (Fixes the white screen!)
                $response->send();
                
                // 4. Force PHP to write the session data to the server immediately
                session_write_close();
                
                // 5. Detach the browser (User instantly goes to the next page)
                fastcgi_finish_request(); 
                
                // 6. Send the email quietly in the background
                \process_email_queue(1); 
                exit();
            }
        }

        return redirect()->to('password/verify')->with('reset_email', $email);
    }

    // 3. Shows the "Enter Code & New Password" page
    public function verify() {
        return view('auth/reset_password', ['email' => session('reset_email')]);
    }

    // 4. Validates the code and updates the password
    public function updatePassword() {
        $email = strtolower(trim($this->request->getPost('email')));
        $code = trim($this->request->getPost('code'));
        $password = $this->request->getPost('password');
        $confirm = $this->request->getPost('confirm-password');

        if ($password !== $confirm) {
            return redirect()->back()->with('error', 'Passwords do not match.');
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        // Check if code is wrong or missing
        if (!$user || empty($user['reset_code']) || $user['reset_code'] !== $code) {
            return redirect()->back()->with('error', 'Invalid reset code.')->with('reset_email', $email);
        }

        // Check if code is expired
        if (date('Y-m-d H:i:s') > $user['reset_code_expires_at']) {
            $userModel->update($user['id'], ['reset_code' => null, 'reset_code_expires_at' => null]);
            return redirect()->to('password/forgot')->with('error', 'Your reset code has expired. Please request a new one.');
        }

        // Success! Update password and destroy the code
        $userModel->update($user['id'], [
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'reset_code' => null,
            'reset_code_expires_at' => null
        ]);

        return redirect()->to('login')->with('success', 'Password reset successfully! You may now log in.');
    }
}
