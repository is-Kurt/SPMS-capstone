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
        $this->helpers = ['form', 'url', 'functions', 'date', 'email_queue', 'notification', 'audit'];
        
        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);
        // Preload any models, libraries, etc, here.
        // $this->session = service('session');

        // Automatically purge retired president account if present in database
        $this->purgeRetiredPresidentAccount();

        // One-time database purge of all evaluation folders requested by user
        $this->purgeAllEvaluationFoldersOnce();
    }

    /**
     * One-time database purge of all evaluation folders, routings, and attached documents.
     */
    protected function purgeAllEvaluationFoldersOnce(): void
    {
        $flagFile = WRITEPATH . 'eval_folders_purged.flag';
        if (file_exists($flagFile)) {
            return;
        }

        try {
            $db = \Config\Database::connect();
            if ($db->tableExists('document_attachments')) {
                $db->query("DELETE FROM document_attachments");
            }
            if ($db->tableExists('evaluation_routings')) {
                $db->query("DELETE FROM evaluation_routings");
            }
            if ($db->tableExists('documents')) {
                $db->query("DELETE FROM documents");
            }
            if ($db->tableExists('document_folders')) {
                $db->query("DELETE FROM document_folders");
            }
            if ($db->tableExists('notifications')) {
                $db->query("DELETE FROM notifications WHERE link LIKE '%folders%' OR link LIKE '%ratings%'");
            }

            if (session()->get('active_folder_id')) {
                session()->remove('active_folder_id');
            }

            @file_put_contents($flagFile, date('Y-m-d H:i:s'));
        } catch (\Throwable $e) {
            log_message('error', 'Purge evaluation folders failed: ' . $e->getMessage());
        }
    }

    /**
     * Purges the retired separate president account (president@test.com)
     * so that the Admin user directly functions as the executive head.
     */
    protected function purgeRetiredPresidentAccount(): void
    {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        try {
            $db = \Config\Database::connect();
            $pres = $db->table('users')->where('email', 'president@test.com')->get()->getRowArray();
            if ($pres) {
                $presId = (int)$pres['id'];

                // Re-route any evaluation routings pointing to president to the Admin
                $admin = $db->table('users u')
                    ->select('u.id')
                    ->join('user_roles ur', 'ur.user_id = u.id')
                    ->join('roles r', 'r.id = ur.role_id')
                    ->where('r.name', 'Admin')
                    ->where('u.is_active', 1)
                    ->get()->getRowArray();
                $adminId = $admin ? (int)$admin['id'] : null;

                if ($adminId) {
                    $presRoutings = $db->table('evaluation_routings')->where('evaluator_id', $presId)->get()->getResultArray();
                    foreach ($presRoutings as $pr) {
                        $adminR = $db->table('evaluation_routings')
                            ->where('folder_id', $pr['folder_id'])
                            ->where('evaluator_id', $adminId)
                            ->get()->getRowArray();
                        if ($adminR) {
                            $db->table('evaluation_routings')->where('id', $pr['id'])->delete();
                        } else {
                            $db->table('evaluation_routings')->where('id', $pr['id'])->update(['evaluator_id' => $adminId]);
                        }
                    }
                } else {
                    $db->table('evaluation_routings')->where('evaluator_id', $presId)->delete();
                }

                $db->table('plantillas')->where('user_id', $presId)->delete();
                $db->table('user_roles')->where('user_id', $presId)->delete();
                $db->table('notifications')->where('user_id', $presId)->orWhere('sender_id', $presId)->delete();
                $db->table('users')->where('id', $presId)->delete();
            }
        } catch (\Throwable $e) {
            // Silently continue if database is not reachable yet
        }
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
        } catch (\Throwable $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
