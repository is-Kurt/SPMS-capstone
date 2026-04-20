<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Document extends BaseController
{
    public function index(): string {
        $userId = session()->get('user_id');
        $documentModel = new \App\Models\DocumentModel();
        $filter = $this->request->getGet('docs') ?? 'all_docs';

        $baseQuery = $documentModel->db->table('documents d')
            ->select('d.*, sd.collaborator_id, u.email, u.first_name, u.last_name')
            ->join('shared_documents sd', 'sd.document_id = d.id AND sd.collaborator_id = ' . $userId, 'left')
            
            ->join('submissions s', 's.document_id = d.id', 'left')
            ->where('s.id IS NULL') 
            
            ->join('users u', 'u.id = d.user_id', 'left')
            ->groupStart()
                ->where('d.user_id', $userId)
                ->orWhere('sd.collaborator_id', $userId)
            ->groupEnd()
            ->orderBy('d.updated_at', 'DESC');

        $allDocs = $baseQuery->get()->getResultArray();

        if ($filter === 'shared') {
            $data['docs'] = array_values(array_filter($allDocs, fn($d) => $d['collaborator_id'] == $userId));
        } elseif ($filter === 'owned') {
            $data['docs'] = array_values(array_filter($allDocs, fn($d) => $d['user_id'] == $userId));
        } elseif ($filter === 'all_docs') {
            $data['docs'] = $allDocs;
        } else {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data['counts'] = $this->_getCounts();
        $data['filter'] = $filter;

        return view('document/index', $data);
    }

    public function show(): string {
        $id      = $this->request->getGet('Id');
        $user_id = session()->get('user_id');

        $documentModel       = new \App\Models\DocumentModel();
        $sharedDocumentModel = new \App\Models\SharedDocumentModel();

        $doc = $documentModel->find($id);

        if (!$doc) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $isOwner = $doc['user_id'] == $user_id;

        $isShared = $sharedDocumentModel
            ->where('collaborator_id', $user_id)
            ->where('document_id', $id)
            ->first();

        if (!$isOwner && !$isShared) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data['doc'] = $doc;
        return view('document/show', $data);
    }

    public function store() {
        $documentModel = new \App\Models\DocumentModel();
        $userId = session()->get('user_id');
        
        $rawTitle = trim($this->request->getPost('title'));
        $baseTitle = $rawTitle ?: 'Untitled Document';
        
        $existingDocs = $documentModel->select('documents.*')
                                    ->join('shared_documents sd', 'sd.document_id = documents.id', 'left')
                                    ->groupStart()
                                        ->where('documents.user_id', $userId)
                                        ->orWhere('sd.collaborator_id', $userId)
                                    ->groupEnd()
                                    ->groupStart()
                                        ->where('documents.title', $baseTitle)
                                        ->orLike('documents.title', $baseTitle . ' (', 'after')
                                    ->groupEnd()
                                    ->findAll();

        $existingTitles = array_column($existingDocs, 'title');
        $finalTitle = resolveUniqueTitle($baseTitle, $existingTitles);

        $newId = null;
        $maxAttempts = 999;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $id = generate_short_id();

            if ($documentModel->find($id) === null) {
                $documentModel->save([
                    'id'      => $id,
                    'title'   => $finalTitle,
                    'content' => '',
                    'user_id' => session()->get('user_id'),
                ]);
                $newId = $id;
                break;
            }
        }

        if (!$newId) {
            return $this->response->setJSON([
                'status'   => 'error',
                'message'  => 'Could not generate a unique ID. Please try again.',
            ]);
        }

        return $this->response->setJSON([
            'status'   => 'success',
            'id'       => $newId,
        ]);
    }

    public function update() {
        $content = $this->request->getPost('content');
        $title   = $this->request->getPost('title');
        $doc_id  = $this->request->getPost('id');
        
        $eval_date_start = $this->request->getPost('doc_date_start');
        $eval_date_end   = $this->request->getPost('doc_date_end');
        
        $documentModel = new \App\Models\DocumentModel();
        
        $dataToSave = [
            'id'      => $doc_id,
            'title'   => $title,
            'content' => $content,
        ];

        if (!empty($eval_date_start) && !empty($eval_date_end)) {
            $dataToSave['eval_date_start'] = date('Y-m-d H:i', strtotime($eval_date_start));
            $dataToSave['eval_date_end']   = date('Y-m-d H:i', strtotime($eval_date_end));
        }

        // 3. Save to the database
        $documentModel->save($dataToSave);

        return $this->response->setJSON([
            'status'  => 'success',
        ]);
    }
        
    public function destroy() {
        $doc_id = $this->request->getPost('doc_id');
        $documentModel = new \App\Models\DocumentModel();

        $doc = $documentModel->find($doc_id);

        if (!$doc || $doc['user_id'] != session()->get('user_id')) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Unauthorized',
            ]);
        }

        $userRatingModel = new \App\Models\UserRatingModel();
        $userRatingModel->where('document_id', $doc_id)->delete();
        
        $documentModel->delete($doc_id);

        return $this->response->setJSON([
            'status'   => 'success',
        ]);
    }

    public function createFolder() {
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
        ]);
    }

    public function share() {
        $documentId     = $this->request->getPost('document_id');
        $collaboratorId = $this->request->getPost('collaborator_id');
        $userId         = session()->get('user_id');

        $documentModel       = new \App\Models\DocumentModel();
        $sharedDocumentModel = new \App\Models\SharedDocumentModel();

        $doc = $documentModel->find($documentId);

        if (!$doc || $doc['user_id'] != $userId) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Unauthorized'
            ]);
        }

        if ($collaboratorId == $userId) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'You cannot share a document with yourself'
            ]);
        }

        $existing = $sharedDocumentModel
            ->where('document_id', $documentId)
            ->where('collaborator_id', $collaboratorId)
            ->first();

        if ($existing) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Document already shared with this user'
            ]);
        }

        $sharedDocumentModel->save([
            'document_id'     => $documentId,
            'collaborator_id' => $collaboratorId,
            'permission'      => 'viewer',
        ]);

        return $this->response->setJSON([
            'status'   => 'success',
        ]);
    }

    public function send() {
        $documentId = $this->request->getPost('document_id');
        $ratingTitle = $this->request->getPost('rating_title'); // 🚨 Catch the new title!
        $adminId    = session()->get('user_id');

        $documentModel   = new \App\Models\DocumentModel();
        $userModel       = new \App\Models\UserModel();
        $ratingModel     = new \App\Models\RatingModel(); // Assumed correct name
        $userRatingModel = new \App\Models\UserRatingModel();

        $templateDoc = $documentModel->find($documentId);

        if (!$templateDoc) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Template not found.']);
        }

        $targetUsers = $userModel->where('id !=', $adminId)->findAll();

        if (empty($targetUsers)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No eligible users found.']);
        }

        $newDocuments = [];
        $generatedIds = [];
        $maxAttempts = 999;

        // 1. Generate all the clones for the users
        foreach ($targetUsers as $user) {
            $newId = null;
            for ($i = 0; $i < $maxAttempts; $i++) {
                $id = generate_short_id();
                if ($documentModel->find($id) === null && !in_array($id, $generatedIds)) {
                    $newId = $id;
                    break;
                }
            }

            if (!$newId) return $this->response->setJSON(['status' => 'error', 'message' => 'ID Generation failed.']);
            
            $generatedIds[] = $newId;
            $newDocuments[] = [
                'id'              => $newId,
                'title'           => $templateDoc['title'], 
                'content'         => $templateDoc['content'],
                'user_id'         => $user['id'],
                'eval_date_start' => $templateDoc['eval_date_start'] ?? null,
                'eval_date_end'   => $templateDoc['eval_date_end'] ?? null,
            ];
        }

        if (!empty($newDocuments)) {
            // 2. Generate a valid Rating ID
            $ratingId = null;
            for ($i = 0; $i < $maxAttempts; $i++) {
                $id = generate_short_id();
                if($ratingModel->find($id) === null) {
                    $ratingId = $id;
                    break;
                }
            }

            // 3. STEP ONE: Insert Documents FIRST (To satisfy Foreign Keys)
            $documentModel->insertBatch($newDocuments);

            // 4. STEP TWO: Insert the main Rating parent row
            $ratingModel->insert([
                'id'      => $ratingId,
                'user_id' => $adminId,
                'title'   => $ratingTitle ?: 'Ratings report' // Fallback just in case
            ]);

            // 5. STEP THREE: Build and insert the junction table rows
            $userRatingsBatch = [];
            foreach ($generatedIds as $docId) {
                $userRatingsBatch[] = [
                    'rating_id'   => $ratingId,
                    'document_id' => $docId
                ];
            }
            $userRatingModel->insertBatch($userRatingsBatch);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Successfully distributed to ' . count($targetUsers) . ' users.'
        ]);
    }

    public function count() {
        return $this->response->setJSON([
            'status' => 'success',
            'counts' => $this->_getCounts()
        ]);
    }

    private function _getCounts() {
        $userId = session()->get('user_id');
        $documentModel = new \App\Models\DocumentModel();

        $allDocs = $documentModel->db->table('documents d')
            ->select('d.user_id, sd.collaborator_id')
            ->join('shared_documents sd', 'sd.document_id = d.id AND sd.collaborator_id = ' . $userId, 'left')
            
            // 🚨 THE FIX: Apply the exact same filter to your math query
            ->join('submissions s', 's.document_id = d.id', 'left')
            ->where('s.id IS NULL')

            ->groupStart()
                ->where('d.user_id', $userId)
                ->orWhere('sd.collaborator_id', $userId)
            ->groupEnd()
            ->get()->getResultArray();

        return [
            'all_docs' => count($allDocs),
            'owned'    => count(array_filter($allDocs, fn($d) => $d['user_id'] == $userId)),
            'shared'   => count(array_filter($allDocs, fn($d) => $d['collaborator_id'] == $userId)),
        ];
    }
}