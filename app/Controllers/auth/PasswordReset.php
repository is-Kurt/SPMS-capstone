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
            $code = sprintf("%06d", mt_rand(1, 999999)); 
            $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

            $userModel->update($user['id'], [
                'reset_code' => $code,
                'reset_code_expires_at' => $expiresAt
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
                // LOCAL (Laragon): Blocking - waits for the email to send before redirecting
                \process_email_queue(1);
                return redirect()->to('password/verify')->with('reset_email', $email);
            } else {
                // PRODUCTION (Nginx/PHP-FPM): Non-Blocking - redirects instantly, sends in background
                $response = redirect()->to('password/verify')->with('reset_email', $email);
                $this->response->send();
                
                fastcgi_finish_request(); 
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
