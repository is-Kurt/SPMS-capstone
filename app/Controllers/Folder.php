<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Folder extends BaseController
{
    public function index(): string {
        $userId = session()->get('user_id');
        $db = \Config\Database::connect();
        $filter = $this->request->getGet('docs') ?? 'all_folders';

        $builder = $db->table('document_folders df')
            ->select('df.*, u.email, u.first_name, u.last_name')
            ->join('users u', 'u.id = df.user_id', 'left')
            ->orderBy('df.created_at', 'DESC');

        // FIX: Apply user restriction to both 'owned' and 'all_folders' 
        // if "all" is intended to be "all of mine"
        if ($filter === 'owned' || $filter === 'all_folders') {
            $builder->where('df.user_id', $userId);
        } 
        // If 'shared' is added later, you would handle that logic here
        elseif ($filter === 'shared') {
            $builder->where('df.user_id', $userId);
        }

        $data['folders'] = $builder->get()->getResultArray();
        $data['filter']  = $filter;
        $data['counts']  = $this->_getCounts(); 

        return view('document/index', $data);
    }

    public function show(): string {
        $folderId = $this->request->getGet('Id');

        $folderModel   = new \App\Models\DocumentFolderModel();
        $documentModel = new \App\Models\DocumentModel();

        $data['folder'] = $folderModel->find($folderId);
        if (!$data['folder']) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data['docs'] = $documentModel->where('document_folder_id', $folderId)
                                    ->findAll();

        return view('document/folder_view', $data);
    }

    public function store() {
        $adminId = session()->get('user_id');
        $title = trim($this->request->getPost('title'));
        
        $DocumentFolderModel = new \App\Models\DocumentFolderModel();

        $newId = null;
        $maxAttempts = 999;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $id = generate_short_id();
            if ($DocumentFolderModel->find($id) === null) {
                $DocumentFolderModel->save([
                    'id'      => $id,
                    'title'   => $title ?: 'New Evaluation Batch',
                    'user_id' => session()->get('user_id')
                ]);
                $newId = $id;
                break;
            }
        }

        if (!$newId) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Could not generate a unique ID. Please try again.',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'id' => $newId,
        ]);
    }

    public function destroy() {
        $folderId = $this->request->getPost('doc_id'); // Modal uses doc_id for consistency[cite: 14]
        $folderModel = new \App\Models\DocumentFolderModel();
        $documentModel = new \App\Models\DocumentModel();

        $folder = $folderModel->find($folderId);

        // Security: Only owner can delete[cite: 46]
        if (!$folder || $folder['user_id'] != session()->get('user_id')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        // Deleting the folder cascades to documents based on your migration[cite: 31]
        $folderModel->delete($folderId);

        return $this->response->setJSON(['status' => 'success']);
    }

    public function send() {
        $folderId    = $this->request->getPost('folder_id'); 
        $ratingTitle = $this->request->getPost('rating_title');
        $adminId     = session()->get('user_id');

        $folderModel     = new \App\Models\DocumentFolderModel();
        $documentModel   = new \App\Models\DocumentModel();
        $userModel       = new \App\Models\UserModel();
        $ratingModel     = new \App\Models\RatingModel();
        $userRatingModel = new \App\Models\UserRatingModel();

        // 1. Get the original folder details and its templates
        $originalFolder = $folderModel->find($folderId);
        $templates = $documentModel->where('document_folder_id', $folderId)->findAll();

        if (!$originalFolder || empty($templates)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No documents found in folder to send.']);
        }

        $targetUsers = $userModel->where('id !=', $adminId)->findAll();
        
        // 2. Create a single Rating Batch entry for the admin to track this distribution[cite: 42]
        $ratingBatchId = generate_short_id();
        $ratingModel->insert([
            'id'      => $ratingBatchId,
            'user_id' => $adminId,
            'title'   => $ratingTitle ?: $originalFolder['title']
        ]);

        // 3. Distribute to each user
        foreach ($targetUsers as $user) {
            // Step A: Create a NEW folder clone for this specific user[cite: 44]
            $newUserFolderId = generate_short_id();
            $folderModel->insert([
                'id'      => $newUserFolderId,
                'user_id' => $user['id'], // Ownership is set here[cite: 44]
                'title'   => $originalFolder['title'],
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Step B: Clone every document into the new folder
            foreach ($templates as $tpl) {
                $newDocId = generate_short_id();
                $documentModel->insert([
                    'id'                 => $newDocId,
                    'document_folder_id' => $newUserFolderId, // Link to the user's folder
                    'title'              => $tpl['title'],
                    'content'            => $tpl['content'],
                    'eval_date_start'    => $tpl['eval_date_start'],
                    'eval_date_end'      => $tpl['eval_date_end'],
                    'created_at'         => date('Y-m-d H:i:s')
                ]);

                // Step C: Link the user's document clone to the Rating Batch[cite: 42]
                $userRatingModel->insert([
                    'rating_id'   => $ratingBatchId,
                    'document_id' => $newDocId
                ]);
            }
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Folder and contents distributed successfully.']);
    }

    public function count() {
        return $this->response->setJSON([
            'status' => 'success',
            'counts' => $this->_getCounts()
        ]);
    }

    private function _getCounts() {
        $userId = session()->get('user_id');
        $folderModel = new \App\Models\DocumentFolderModel();

        // Counts are now strictly for Folder entities[cite: 55]
        $owned = $folderModel->where('user_id', $userId)->countAllResults();

        return [
            'all_folders' => $owned, // Adjust if "All" includes shared folders later
            'owned'       => $owned,
            'shared'      => 0
        ];
    }
}
