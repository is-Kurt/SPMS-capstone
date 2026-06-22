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

    protected function respond($data, int $status = 200)
    {
        return $this->response
            ->setStatusCode($status)
            ->setJSON($data);
    }

    protected function respondError(string $message, int $status = 500)
    {
        return $this->response
            ->setStatusCode($status)
            ->setJSON(['status' => 'error', 'message' => $message]);
    }

    protected function tryOrFail(callable $fn)
    {
        try {
            return $fn();
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    protected function getUserDocument($userId, $docId = null) {
        $documentModel = new \App\Models\DocumentModel();

        $builder = $documentModel->db->table('documents d')
            ->select('d.*, df.user_id')
            ->join('document_folders df', 'df.id = d.document_folder_id')
            ->where('df.user_id', $userId);

        if ($docId !== null) {
            $builder->where('d.id', $docId);
        }

        return $docId !== null
            ? $builder->get()->getRowArray()
            : $builder->get()->getResultArray();
    }

    protected function getSidebarFolders() {
        $folderModel = new \App\Models\DocumentFolderModel();
        return $folderModel->where('user_id', session()->get('user_id'))->orderBy('created_at', 'DESC')->findAll();
    }

    protected function restoreSessionFromCookie() {
        if (session()->get('isLoggedIn')) {
            return;
        }

        $token = $_COOKIE['remember_me'] ?? null;

        if ($token) {
            $db = \Config\Database::connect();
            $userModel = new \App\Models\UserModel();
            $user = $userModel->where('remember_token', hash('sha256', $token))
                              ->where('remember_token_expiry >', date('Y-m-d H:i:s'))
                              ->first();

            if ($user) {
                // 1. Fetch System Role
                $roleData = $db->table('user_roles ur')->select('r.name')
                    ->join('roles r', 'r.id = ur.role_id')
                    ->where('ur.user_id', $user['id'])->get()->getRowArray();
                
                // 2. Fetch Plantilla
                $plantilla = $db->table('plantilla p')
                    ->select('un.name as department, pos.title as position')
                    ->join('units un', 'un.id = p.unit_id')
                    ->join('positions pos', 'pos.id = p.position_id')
                    ->where('p.user_id', $user['id'])
                    ->where('p.ended_at IS NULL')->get()->getRowArray();

                session()->set([
                    'user_id'    => $user['id'],
                    'email'      => $user['email'],
                    'role'       => $roleData ? $roleData['name'] : 'Employee',
                    'department' => $plantilla ? $plantilla['department'] : null,
                    'position'   => $plantilla ? $plantilla['position'] : null,
                    'username'   => $user['first_name'] . ' ' . $user['last_name'],
                    'isLoggedIn' => true
                ]);
            }
        }
    }
}
