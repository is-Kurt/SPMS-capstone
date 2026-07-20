<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

use App\Models\UserModel;
use App\Models\DocumentModel;
use App\Models\DocumentFolderModel;

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
     * The creation oThis team is stillf dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        $this->helpers = ['form', 'url', 'functions', 'date', 'email_queue'];
        
        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);
        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    /** Shortcut for a JSON success/data response, used by AJAX endpoints. */
    protected function respond($data, int $status = 200) {
        return $this->response
            ->setStatusCode($status)
            ->setJSON($data);
    }

    /**
     * Shortcut for a JSON {status: 'error', message} response. Pass $errors
     * (field => message) when the caller wants the frontend to display errors
     * inline next to each field instead of a single generic message.
     */
    protected function respondError(string $message, int $status = 500, array $errors = []) {
        $data = ['status' => 'error', 'message' => $message];
        if (!empty($errors)) $data['errors'] = $errors;

        return $this->response
            ->setStatusCode($status)
            ->setJSON($data);
    }

    /**
     * Runs an AJAX action and converts any \Exception into a JSON error response
     * instead of a raw PHP error page - lets controller actions just `throw` on
     * invalid state instead of manually building error responses everywhere.
     */
    protected function tryOrFail(callable $fn) {
        try {
            return $fn();
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
