<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Document extends BaseController
{
    public function index(): string {
        $docId      = $this->request->getGet('Id');
        $userId  = session()->get('user_id');
        
        $doc = $this->getUserDocument($userId, $docId); // Verification via folder join

        if (!$docId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data['doc'] = $doc;
        return view('document/show', $data);
    }

    public function store() {
        return $this->tryOrFail(function() {
            $documentModel = new \App\Models\DocumentModel();
            $userId   = session()->get('user_id');
            $folderId = $this->request->getPost('folder_id');
            $title  = trim($this->request->getPost('title')) ?: 'Untitled Document';

            $docs = $this->getUserDocument($userId);
            $payload = [
                'title'              => resolve_unique_title($title, $docs),
                'user_id'            => $userId,
                'document_folder_id' => $folderId,
                'content'            => '',
            ];
            $newId = create_unique_row($documentModel, $payload);

            if (!$newId) {
                throw new \Exception("Could not generate a unique ID.");
            }

            return $this->respond(['status' => 'success', 'id' => $newId]);
        });
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
        $docId = $this->request->getPost('doc_id');
        $userId = session()->get('user_id');
        
        // Verify ownership via folder join since documents no longer have user_id[cite: 49, 50]
        if (!$this->getUserDocument($userId, $docId)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $userRatingModel = new \App\Models\UserRatingModel();
        $userRatingModel->where('document_id', $docId)->delete();
        
        (new \App\Models\DocumentModel())->delete($docId);

        return $this->response->setJSON(['status' => 'success']);
    }
}