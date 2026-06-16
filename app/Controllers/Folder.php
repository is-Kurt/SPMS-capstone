<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Folder extends BaseController
{
    public function index(): string {
        $userId = session()->get('user_id');
        $role   = session()->get('role');
        $dept   = session()->get('department');
        $folderId = $this->request->getGet('folder_id');

        $folderModel = new \App\Models\DocumentFolderModel();
        $documentModel = new \App\Models\DocumentModel();

        $folders = $folderModel->where('user_id', $userId)
                               ->orderBy('created_at', 'DESC')
                               ->findAll();

        if (!$folderId && !empty($folders)) {
            $folderId = $folders[0]['id'];
        }

        $activeFolder = null;
        $myDocs = [];
        $groupedGuides = []; // NEW: Array to hold grouped documents
        $isReadOnly = true; // Default to read-only for visitors

        if ($folderId) {
            $activeFolder = $folderModel->find($folderId);
            
            // SECURITY CHECK
            if (!$activeFolder || $activeFolder['user_id'] != $userId) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Unauthorized access to this folder.');
            }

            // ==========================================
            // HIERARCHICAL SECURITY CHECK
            // ==========================================
            $folderOwnerId = $activeFolder['user_id'];
            $isAuthorized = false;

            if ($folderOwnerId == $userId) {
                $isAuthorized = true;
                $isReadOnly = false; // Owner gets full access
            } elseif ($role === 'Admin') {
                $isAuthorized = true;
            } else {
                // Fetch the folder owner's role to see if they are a subordinate
                $userModel = new \App\Models\UserModel();
                $folderOwner = $userModel->find($folderOwnerId);

                if ($folderOwner) {
                    $ownerRole = $folderOwner['role'];
                    $ownerDept = $folderOwner['department'];

                    $opcrRoles = ['Vice President', 'Campus Administrator'];
                    $dpcrRoles = ['Dean', 'Director', 'Head of Office'];
                    $ipcrRoles = ['Employee'];

                    // VPs can view Deans. Deans can view Employees (in their own dept).
                    if (in_array($role, $opcrRoles) && in_array($ownerRole, $dpcrRoles)) {
                        $isAuthorized = true;
                    } elseif (in_array($role, $dpcrRoles) && in_array($ownerRole, $ipcrRoles) && $ownerDept === $dept) {
                        $isAuthorized = true;
                    }
                }
            }

            if (!$isAuthorized) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Unauthorized access to this folder.');
            }

            // 1. Fetch the user's own working documents
            $myDocs = $documentModel->where('document_folder_id', $folderId)->findAll();

            // 2. Determine the Master Batch ID
            $masterBatchId = $activeFolder['parent_folder_id'] ?? $activeFolder['id'];

            // 3. Determine target Superior Roles
            $superiorRoles = [];
            $requiresSameDept = false;

            $opcrRoles = ['Vice President', 'Campus Administrator'];
            $dpcrRoles = ['Dean', 'Director', 'Head of Office'];
            $ipcrRoles = ['Employee'];

            if (in_array($role, $opcrRoles)) {
                $superiorRoles = ['Admin'];
            } elseif (in_array($role, $dpcrRoles)) {
                $superiorRoles = $opcrRoles;
            } elseif (in_array($role, $ipcrRoles)) {
                $superiorRoles = $dpcrRoles;
                $requiresSameDept = true; 
            }

            // 4. Fetch the Superiors and Group Their Documents
            if (!empty($superiorRoles)) {
                $userModel = new \App\Models\UserModel();
                $superiorQuery = $userModel->whereIn('role', $superiorRoles);
                
                if ($requiresSameDept) {
                    $superiorQuery->where('department', $dept);
                }
                
                $superiors = $superiorQuery->findAll();

                foreach ($superiors as $superior) {
                    $superiorFolder = $folderModel->where('user_id', $superior['id'])
                        ->groupStart()
                            ->where('id', $masterBatchId)
                            ->orWhere('parent_folder_id', $masterBatchId)
                        ->groupEnd()
                        ->first();

                    if ($superiorFolder) {
                        $docs = $documentModel->where('document_folder_id', $superiorFolder['id'])
                                              ->whereIn('status', ['submitted', 'evaluated'])
                                              ->findAll();
                        
                        // Only create a tab if this superior actually has submitted guides
                        if (!empty($docs)) {
                            $groupedGuides[] = [
                                'superior' => $superior,
                                'docs'     => $docs
                            ];
                        }
                    }
                }
            }
        }

        return view('app_shell', [
            'sidebarFolders'   => $this->getSidebarFolders(), 
            'selectedFolderId' => $activeFolder['id'] ?? null,
            'mainView'         => 'document/_doc_rows',
            'mainData'         => [
                'activeFolder'  => $activeFolder,
                'myDocs'        => $myDocs,
                'groupedGuides' => $groupedGuides, // Pass the grouped data
                'isReadOnly'    => $isReadOnly
            ]
        ]);
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
        $folderId = $this->request->getPost('doc_id'); 
        $folderModel = new \App\Models\DocumentFolderModel();
        
        $userId = session()->get('user_id');
        $role   = session()->get('role');

        $folder = $folderModel->find($folderId);

        if (!$folder || $folder['user_id'] != $userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($role === 'Admin') {
            $folderModel->groupStart()
                        ->where('id', $folderId)
                        ->orWhere('parent_folder_id', $folderId)
                        ->groupEnd()
                        ->delete();
        } else {
            $folderModel->delete($folderId);
        }

        return $this->response->setJSON(['status' => 'success']);
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
