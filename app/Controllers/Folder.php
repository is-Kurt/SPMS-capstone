<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Folder extends BaseController
{
    public function index(): string {
        $userId = session()->get('user_id');
        $folderId = $this->request->getGet('folder_id');

        $folderModel = new \App\Models\DocumentFolderModel();
        $documentModel = new \App\Models\DocumentModel();

        $data['folders'] = $folderModel->where('user_id', $userId)
                                    ->orderBy('created_at', 'DESC')
                                    ->findAll();

        if (!$folderId && !empty($data['folders'])) {
            $folderId = $data['folders'][0]['id'];
        }

        $data['selectedFolderId'] = $folderId;
        $data['activeFolder'] = null;
        $data['docs'] = [];

        if ($folderId) {
            $data['activeFolder'] = $folderModel->find($folderId);
            
            if ($data['activeFolder'] && $data['activeFolder']['user_id'] == $userId) {
                $data['docs'] = $documentModel->where('document_folder_id', $folderId)->findAll();
            } else {
                $data['activeFolder'] = null;
            }
        }

        return view('document/index', $data);
    }

    public function store() {
        return $this->tryOrFail(function() {
            $documentFolderModel = new \App\Models\DocumentFolderModel();
            $userId = session()->get('user_id');
            $title = trim($this->request->getPost('title')) ?: 'Untitled Evaluation';

            $payload = [
                'title'   => resolve_unique_title($title, ['user_id' => $userId], 'title', $documentFolderModel),
                'user_id' => $userId
            ];
            $newId = create_unique_row($documentFolderModel, $payload);

            if (!$newId) {
                throw new \Exception("Could not generate a unique ID.");
            }

            return $this->respond(['status' => 'success', 'id' => $newId]);
        });
    }

    public function destroy() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('doc_id');
            $folderModel = new \App\Models\DocumentFolderModel();
            $documentModel = new \App\Models\DocumentModel();

            $folder = $folderModel->find($folderId);

            if (!$folder || $folder['user_id'] != session()->get('user_id')) {
                throw new \Exception("Failed to delete.");
            }

            $folderModel->delete($folderId);

            return $this->response->setJSON(['status' => 'success']);
        });
    }

    public function send() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id'); 
            $adminId  = session()->get('user_id');

            $folderModel = new \App\Models\DocumentFolderModel();
            $userModel   = new \App\Models\UserModel();
            $db          = \Config\Database::connect();

            // 1. Fetch original folder shell
            $originalFolder = $folderModel->find($folderId);

            if (!$originalFolder) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Original folder not found.']);
            }

            // Fetch all users who need the folder shell
            $targetUsers = $userModel->where('id !=', $adminId)->findAll();
            $failedDistributions = [];

            foreach ($targetUsers as $user) {
                $db->transStart(); // Start transaction per user

                try {
                    // 2. Clone ONLY the Folder and link to the Master
                    $folderPayload = [
                        'title'            => $originalFolder['title'],
                        'user_id'          => $user['id'],
                        'parent_folder_id' => $originalFolder['id'] // Links to the Admin's Master Folder
                    ];
                    
                    $newFolderId = create_unique_row($folderModel, $folderPayload);

                    if (!$newFolderId) {
                        throw new \Exception("Could not generate folder ID.");
                    }

                    $db->transComplete(); // Commit if folder creation worked

                    if ($db->transStatus() === false) {
                        throw new \Exception("Transaction failed.");
                    }

                } catch (\Exception $e) {
                    // Rollback and log failure if this specific user fails
                    $db->transRollback();
                    $failedDistributions[] = $user['email']; 
                }
            }

            // 3. Return status
            if (count($failedDistributions) > 0) {
                return $this->respond([
                    'status'  => 'warning', 
                    'message' => 'Distributed with errors. Failed for: ' . implode(', ', $failedDistributions)
                ]);
            }

            return $this->respond(['status' => 'success', 'message' => 'Evaluation folders distributed successfully to all users.']);
        });
    }
}
