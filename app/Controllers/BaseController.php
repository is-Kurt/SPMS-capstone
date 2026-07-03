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
        $this->helpers = ['form', 'url', 'functions', 'date', 'email_queue'];
        
        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);
        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    protected function respond($data, int $status = 200) {
        return $this->response
            ->setStatusCode($status)
            ->setJSON($data);
    }

    protected function respondError(string $message, int $status = 500) {
        return $this->response
            ->setStatusCode($status)
            ->setJSON(['status' => 'error', 'message' => $message]);
    }

    protected function tryOrFail(callable $fn) {
        try {
            return $fn();
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    protected function getUserDocument($userId, $docId = null) {
        $documentModel = new DocumentModel();

        $builder = $documentModel->db->table('documents d')
            ->select('d.*, df.user_id')
            ->join('document_folders df', 'df.id = d.document_folder_id')
            ->where('df.user_id', $userId);

        if ($docId !== null) $builder->where('d.id', $docId);

        return $docId !== null ? $builder->get()->getRowArray() : $builder->get()->getResultArray();
    }

    protected function getSidebarFolders() {
        $folderModel = new DocumentFolderModel();
        return $folderModel->where('user_id', session()->get('user_id'))->orderBy('created_at', 'DESC')->findAll();
    }
}
