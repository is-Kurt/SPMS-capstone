<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Document extends BaseController
{
    // Verification helper to check if a document belongs to the current user
    private function getOwnedDocument($id, $userId) {
        $documentModel = new \App\Models\DocumentModel();
        return $documentModel->db->table('documents d')
            ->select('d.*, df.user_id')
            ->join('document_folders df', 'df.id = d.document_folder_id')
            ->where('d.id', $id)
            ->where('df.user_id', $userId)
            ->get()->getRowArray();
    }

    public function show(): string {
        $id      = $this->request->getGet('Id');
        $userId  = session()->get('user_id');
        
        $doc = $this->getOwnedDocument($id, $userId); // Verification via folder join

        if (!$doc) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data['doc'] = $doc;
        return view('document/show', $data);
    }

    public function store() {
        $documentModel = new \App\Models\DocumentModel();
        $userId   = session()->get('user_id');
        $folderId = $this->request->getPost('folder_id'); // Captured from folder context[cite: 44]
        
        $rawTitle  = trim($this->request->getPost('title'));
        $baseTitle = $rawTitle ?: 'Untitled Document';
        
        // Resolve unique title by checking all documents in folders owned by the user
        $existingDocs = $documentModel->db->table('documents d')
            ->select('d.title')
            ->join('document_folders df', 'df.id = d.document_folder_id')
            ->where('df.user_id', $userId)
            ->groupStart()
                ->where('d.title', $baseTitle)
                ->orLike('d.title', $baseTitle . ' (', 'after')
            ->groupEnd()
            ->get()->getResultArray();

        $existingTitles = array_column($existingDocs, 'title');
        $finalTitle     = resolveUniqueTitle($baseTitle, $existingTitles);

        $newId = null;
        $maxAttempts = 999;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $id = generate_short_id();

            if ($documentModel->find($id) === null) {
                $documentModel->save([
                    'id'                 => $id,
                    'document_folder_id' => $folderId, // Required non-nullable field
                    'title'              => $finalTitle,
                    'content'            => '',
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
            'id'     => $newId,
        ]);
    }

    public function update() {
        $userId   = session()->get('user_id');
        $role     = session()->get('role');
        $userDept = session()->get('department');
        $doc_id   = $this->request->getPost('id');
        $isRatingMode = $this->request->getPost('is_rating_mode') === 'true';

        $db = \Config\Database::connect();

        // 1. Fetch the owner ID and the owner's department via a Join
        $docOwnerInfo = $db->table('documents d')
            ->select('df.user_id as owner_id, u.department as owner_dept')
            ->join('document_folders df', 'df.id = d.document_folder_id')
            ->join('users u', 'u.id = df.user_id')
            ->where('d.id', $doc_id)
            ->get()->getRowArray();

        if (!$docOwnerInfo) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Document not found']);
        }

        // 2. Multi-Role Authorization Check
        $isAuthorized = false;
        if ($docOwnerInfo['owner_id'] === $userId) {
            $isAuthorized = true; // Owner can always save
        } elseif ($isRatingMode) {
            // Non-owners can ONLY save if they are managers in Rating Mode[cite: 21]
            if ($role === 'admin') {
                $isAuthorized = true;
            } elseif ($role === 'supervisor' && $docOwnerInfo['owner_dept'] === $userDept) {
                $isAuthorized = true;
            }
        }

        if (!$isAuthorized) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        // 3. Save Logic
        $documentModel = new \App\Models\DocumentModel();
        $dataToSave = [
            'id'      => $doc_id,
            'title'   => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
        ];

        $start = $this->request->getPost('doc_date_start');
        $end   = $this->request->getPost('doc_date_end');

        if (!empty($start) && !empty($end)) {
            $dataToSave['eval_date_start'] = date('Y-m-d H:i', strtotime($start));
            $dataToSave['eval_date_end']   = date('Y-m-d H:i', strtotime($end));
        }

        $documentModel->save($dataToSave);
        return $this->response->setJSON(['status' => 'success']);
    }
        
    public function destroy() {
        $doc_id = $this->request->getPost('doc_id');
        $userId = session()->get('user_id');
        
        // Verify ownership via folder join since documents no longer have user_id[cite: 49, 50]
        if (!$this->getOwnedDocument($doc_id, $userId)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $userRatingModel = new \App\Models\UserRatingModel();
        $userRatingModel->where('document_id', $doc_id)->delete();
        
        (new \App\Models\DocumentModel())->delete($doc_id);

        return $this->response->setJSON(['status' => 'success']);
    }
}