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
        $session = session();

        // 1. Check if they have an active session at all
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('login');
        }

        // ==========================================
        // 2. NEW: REAL-TIME ACCOUNT STATUS CHECK
        // ==========================================
        $userId = $session->get('user_id');
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);

        // If the user no longer exists, or the Admin disabled them (is_active == 0)
        if (!$user || $user['is_active'] == 0) {
            
            // Destroy their cookie and session completely
            setcookie('remember_me', '', time() - 3600, '/');
            $session->destroy();
            
            // Boot them back to the login screen with a specific error message
            return redirect()->to('login')->with('errors', [
                'error' => 'Your account has been disabled by an administrator.'
            ]);
        }
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
        //
    }
}
