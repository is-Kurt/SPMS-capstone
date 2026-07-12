<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;

/**
 * The "Forgot Password" flow: email -> 6-digit code (rate-limited to 5/day,
 * 60s apart) -> code+new-password. Steps are numbered below in the order the
 * user moves through them.
 */
class PasswordReset extends BaseController
{
    // 1. Shows the "Enter Email" page
    public function index() {
        return view('auth/forgot_password');
    }

    // 2. Generates the code and emails the user
    public function sendCode() {
        // Trim/lowercase before validating, not after - valid_email rejects a string
        // with stray leading/trailing whitespace, so this must happen first.
        $email = strtolower(trim($this->request->getPost('email') ?? ''));

        // 1. Validate the email input format (the field-level error is shown inline, no need for a duplicate banner)
        if (!$this->validateData(['email' => $email], ['email' => 'required|valid_email'])) {
            return redirect()->back()->withInput();
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if ($user && $user['is_active'] == 1) {
            $now = time();
            $lastAttempt = strtotime($user['reset_last_attempt_at'] ?? '2000-01-01');
            $attempts = (int)($user['reset_attempts'] ?? 0);

            if (date('Y-m-d', $lastAttempt) !== date('Y-m-d', $now)) {
                $attempts = 0;
            }

            if ($attempts >= 3) {
                return redirect()->back()->with('error', 'You have reached the maximum number of password reset requests (3) for today. Please try again tomorrow.');
            }

            if (($now - $lastAttempt) < 60) {
                $secondsLeft = 60 - ($now - $lastAttempt);
                return redirect()->back()->with('error', "Please wait {$secondsLeft} seconds before requesting another code.");
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
                render_email('password_reset_code', ['code' => $code])
            );

            // Flashdata must be set before dispatch_email_now() can send()/exit()
            // on the production path, so set it here rather than chaining ->with().
            session()->setFlashdata('reset_email', $email);
            return dispatch_email_now(redirect()->to('password/verify'), 1);
        }

        // No active account matches this email. Respond exactly like the success
        // path (redirect to the code-entry page) without generating or sending
        // anything, so this form can't be used to discover which emails are
        // registered or to request a code for a disabled account.
        return redirect()->to('password/verify')->with('reset_email', $email);
    }

    // 3. Shows the "Enter Code & New Password" page
    public function verify() {
        if (!session('reset_email')) return redirect()->to('password/forgot');
        return view('auth/reset_password', ['email' => session('reset_email')]);
    }

    // 4. Validates the code and updates the password
    public function updatePassword() {
        // 1. NEW: Strict Validation Rules
        $rules = [
            'code'             => 'required|exact_length[6]|numeric',
            'password'         => 'required|min_length[8]',
            'confirm-password' => 'required|matches[password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('reset_email', $this->request->getPost('email'));
        }

        $email = strtolower(trim($this->request->getPost('email')));
        $code = trim($this->request->getPost('code'));
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        // 2. Validate Code Accuracy
        if (!$user || empty($user['reset_code']) || $user['reset_code'] !== $code) {
            return redirect()->back()->with('error', 'The reset code you entered is incorrect.')->with('reset_email', $email);
        }

        // 3. Validate Code Expiration
        if (date('Y-m-d H:i:s') > $user['reset_code_expires_at']) {
            $userModel->update($user['id'], ['reset_code' => null, 'reset_code_expires_at' => null]);
            return redirect()->to('password/forgot')->with('error', 'Your reset code has expired. Please request a new one.');
        }

        // 4. Success - Update Password
        $userModel->update($user['id'], [
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'reset_code' => null,
            'reset_code_expires_at' => null
        ]);

        return redirect()->to('login');
    }
}
