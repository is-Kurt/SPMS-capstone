<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Load the helper
        helper('functions');

        // 1. Check active session OR attempt to restore from remember_me cookie
        if (!restore_session_from_cookie()) {
            return redirect()->to('login');
        }

        // ==========================================
        // 2. REAL-TIME ACCOUNT STATUS CHECK
        // ==========================================
        $session = session();
        $userId = $session->get('user_id');
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);

        if (!$user || $user['is_active'] == 0) {
            setcookie('remember_me', '', time() - 3600, '/');
            $session->destroy();
            
            return redirect()->to('login')->with('errors', [
                'error' => 'Your account has been disabled by an administrator.'
            ]);
        }

        self::syncRole((int) $userId);
        self::syncProfile($user);
    }

    /**
     * Puts the person's name, email, form type and avatar from the database in the session (2026-11-08). Read on
     * every page like the role, so a backup restore or an admin's edit shows right away, not at the next login.
     * Same values as at login (Auth\Session); the row is already loaded above, so this costs no query.
     */
    public static function syncProfile(array $user): void
    {
        $want = [
            'first_name'    => $user['first_name'],
            'last_name'     => $user['last_name'],
            'email'         => $user['email'],
            'username'      => $user['first_name'] . ' ' . $user['last_name'],
            'doc_type'      => $user['doc_type'] ?? null,
            'avatar_image'  => $user['avatar_image'] ?? $user['avatar'] ?? null,
            'avatar_color'  => $user['avatar_color'] ?? '#' . substr(md5($user['email']), 0, 6),
            'avatar_letter' => $user['avatar_letter'] ?? strtoupper(substr($user['first_name'], 0, 1)),
        ];
        $session = session();
        foreach ($want as $key => $value) {
            if ($session->get($key) !== $value) $session->set($key, $value);
        }
    }

    /**
     * Puts the person's current role in the session. Read on every page (here and in RoleFilter), not only at
     * login, so a role change by the admin applies right away. Returns the role.
     */
    public static function syncRole(int $userId): string
    {
        $row = \Config\Database::connect()->table('user_roles ur')->select('r.name')->join('roles r', 'r.id = ur.role_id')
            ->where('ur.user_id', $userId)->get()->getRowArray();
        $role = $row ? $row['name'] : 'Employee';
        if (session()->get('role') !== $role) session()->set('role', $role);
        return $role;
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Security: Prevent browsers from caching authenticated SPMS pages.
        // Protects against Back-button data leaks after logout and eliminates stale/desynced views.
        $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
        $response->setHeader('Pragma', 'no-cache');
        $response->setHeader('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }
}
