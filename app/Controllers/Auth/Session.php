<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;

/** Login/logout and the CSRF-token refresh endpoint used by the axios retry interceptor. */
class Session extends BaseController
{
    /** GET /login - `?email=` (from the account-mismatch "switch" flow) pre-fills the email field. */
    public function index() {
        $prefillEmail = $this->request->getGet('email');

        return view('auth/login', [
            'prefillEmail' => filter_var($prefillEmail, FILTER_VALIDATE_EMAIL) ? $prefillEmail : null
        ]);
    }

    /**
     * POST /login - Verifies credentials, loads the user's system role + current
     * plantilla (unit/position) into the session, and optionally issues a 30-day
     * "remember me" cookie (a random token, only its SHA-256 hash is stored).
     */
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

        // Trim/lowercase before validating, not after - valid_email (filter_var
        // under the hood) rejects a string with stray leading/trailing whitespace,
        // so a user who accidentally adds a space would otherwise get a confusing
        // "must contain a valid email address" error instead of just logging in.
        $postData = $this->request->getPost();
        $postData['email'] = trim(strtolower($postData['email'] ?? ''));

        if (! $validation->run($postData)) {
            return redirect()->back()->withInput();
        }

        $email    = $postData['email'];
        $password = $postData['password'];

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
                'avatar_color'  => $user['avatar_color'] ?? '#' . substr(md5($user['email']), 0, 6),
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

    /** POST /login (DELETE) - Logs out: clears the remember-me token/cookie and destroys the session. */
    public function destroy() {
        $this->logoutCurrentUser();
        return redirect()->to(site_url('login'));
    }

    /** GET /api/csrf-token - Issues a fresh CSRF hash; called by config.js when a request gets a stale-token 403. */
    public function getCsrfToken() {
        return $this->response->setJSON([
            'status'    => 'success',
            'csrf_hash' => csrf_hash()
        ]);
    }

    /**
     * GET /account-mismatch - Landing page for a link (e.g. "Open Your Folder" in an
     * email) whose target isn't reachable by the currently logged-in account.
     * `mismatch_target_email` is set when there's exactly one possible owner to name
     * (e.g. Folder::index() - a folder only ever has one owner). It's left unset when
     * a route can legitimately belong to more than one account (e.g. Rating::show() -
     * a folder can have several routed evaluators) - flashing just `mismatch_detected`
     * on its own shows a generic version of this screen instead of naming a guess.
     */
    public function accountMismatch() {
        $detected = session()->getFlashdata('mismatch_detected');
        if (!$detected) return redirect()->to('folders');

        $targetEmail = session()->getFlashdata('mismatch_target_email');

        // Re-flash so refreshing this page doesn't lose the context.
        session()->setFlashdata('mismatch_detected', true);
        if ($targetEmail) session()->setFlashdata('mismatch_target_email', $targetEmail);

        return view('auth/account_mismatch', [
            'targetEmail'  => $targetEmail,
            'currentEmail' => session()->get('email'),
        ]);
    }

    /** POST /account-mismatch/switch - Logs out the current user and sends them to login, pre-filled with the intended account's email. */
    public function switchAccount() {
        $email = $this->request->getPost('email');
        $this->logoutCurrentUser();

        return redirect()->to(
            filter_var($email, FILTER_VALIDATE_EMAIL) ? 'login?email=' . urlencode($email) : 'login'
        );
    }

    /** Clears the remember-me token/cookie and destroys the session - shared by destroy() and switchAccount(). */
    private function logoutCurrentUser() {
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
    }
}