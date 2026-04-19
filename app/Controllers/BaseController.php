<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        $this->helpers = ['form', 'url', 'functions', 'date'];
        
        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);
        // Preload any models, libraries, etc, here.
        // $this->session = service('session');

        $this->restoreSessionFromCookie();
    }

    private function restoreSessionFromCookie() {
        if (session()->get('isLoggedIn')) {
            return;
        }

        $token = $_COOKIE['remember_me'] ?? null;

        if ($token) {
            $userModel = new \App\Models\UserModel();
            $user = $userModel->where('remember_token', hash('sha256', $token))
                              ->where('remember_token_expiry >', date('Y-m-d H:i:s'))
                              ->first();

            if ($user) {
                session()->set([
                    'user_id'    => $user['id'],
                    'email'      => $user['email'],
                    'username'   => $user['username'],
                    'isLoggedIn' => true
                ]);
            }
        }
    }
}
