<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Document extends BaseController
{
    public function index(): string {
        $docId  = $this->request->getGet('Id');
        $userId = session()->get('user_id');
        
        if (!$docId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $db = \Config\Database::connect();
        
        // Fetch the document AND find out who owns the folder it lives in
        $docInfo = $db->table('documents d')
            ->select('d.*, df.user_id as owner_id')
            ->join('document_folders df', 'df.id = d.document_folder_id')
            ->where('d.id', $docId)
            ->get()->getRowArray();

        if (!$docInfo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // If the current user does NOT own this document's folder, it's a Reference Guide!
        $isGuide = ($docInfo['owner_id'] !== $userId);

        $data['doc'] = $docInfo;
        $data['isGuide'] = $isGuide; // Pass the flag to the view
        
        return view('document/show', $data);
    }
    
    public function store() {
        return $this->tryOrFail(function() {
            $documentModel = new \App\Models\DocumentModel();
            $userId   = session()->get('user_id');
            $folderId = $this->request->getPost('folder_id');
            $title  = trim($this->request->getPost('title')) ?: 'Untitled Document';
            $templateId = $this->request->getPost('template');
            $initialContent = '';

            if (!empty($templateId)) {
                $templateModel = new \App\Models\TemplateModel();
                $template = $templateModel->find($templateId);
                if ($template) {
                    $initialContent = $template['content'];
                }
            }

            $docs = $this->getUserDocument($userId);
            $payload = [
                'title'              => resolve_unique_title($title, $docs),
                'user_id'            => $userId,
                'document_folder_id' => $folderId,
                'content'            => $initialContent,
                'status'             => 'draft'
            ];
            $newId = create_unique_row($documentModel, $payload);

            if (!$newId) {
                throw new \Exception("Could not generate a unique ID.");
            }

            return $this->respond(['status' => 'success', 'id' => $folderId]);
        });
    }

    public function submit() {
        return $this->tryOrFail(function() {
            $docId = $this->request->getPost('doc_id');
            $action = $this->request->getPost('action');
            $userId = session()->get('user_id');

            $documentModel = new \App\Models\DocumentModel();
            $folderModel = new \App\Models\DocumentFolderModel();

            $doc = $documentModel->find($docId);
            if (!$doc) throw new \Exception("Document not found.");

            $folder = $folderModel->find($doc['document_folder_id']);
            if (!$folder || $folder['user_id'] != $userId) {
                throw new \Exception("Unauthorized to modify this document.");
            }

            // ==========================================
            // UNSUBMIT LOGIC
            // ==========================================
            if ($action === 'unsubmit') {
                if ($doc['status'] === 'evaluated') {
                    throw new \Exception("Cannot unsubmit a document that has already been evaluated.");
                }

                $documentModel->update($docId, [
                    'status'       => 'draft',
                    'submitted_at' => null
                ]);

                return $this->respond(['status' => 'success', 'message' => 'Document unsubmitted successfully.']);
            }

            // ==========================================
            // SUBMIT LOGIC (No more cascading!)
            // ==========================================
            $documentModel->update($docId, [
                'status'       => 'submitted',
                'submitted_at' => date('Y-m-d H:i:s')
            ]);

            return $this->respond(['status' => 'success', 'message' => 'Document submitted.']);
        });
    }

    public function evaluate() {
        return $this->tryOrFail(function() {
            $docId = $this->request->getPost('doc_id');
            $finalRating = $this->request->getPost('final_rating');
            $userId = session()->get('user_id');

            $documentModel = new \App\Models\DocumentModel();
            
            $doc = $documentModel->find($docId);
            if (!$doc) {
                throw new \Exception("Document not found.");
            }

            // Update the document to lock it in as Evaluated
            $documentModel->update($docId, [
                'status'       => 'evaluated',
                'final_rating' => (float) $finalRating,
                'rated_at'     => date('Y-m-d H:i:s')
            ]);

            return $this->respond(['status' => 'success', 'message' => 'Document successfully evaluated and locked.']);
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
            if ($role === 'Admin') {
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

    public function updateTimeBasedStatuses()
    {
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        // 1. Trigger 'toEvaluate': 
        // If it's submitted and the start date has passed, it's time to evaluate.
        $db->table($this->table)
           ->where('status', 'submitted')
           ->where('eval_date_start <=', $now)
           ->where('eval_date_end >=', $now) // Ensure it hasn't expired yet
           ->update(['status' => 'toEvaluate']);

        // 2. Trigger 'unevaluated': 
        // If it was submitted or waiting for evaluation, but the deadline passed!
        $db->table($this->table)
           ->groupStart()
               ->where('status', 'toEvaluate')
               ->orWhere('status', 'submitted')
           ->groupEnd()
           ->where('eval_date_end <', $now)
           ->update(['status' => 'unevaluated']);
    }
        
    public function destroy() {
        $docId = $this->request->getPost('doc_id');
        $userId = session()->get('user_id');
        
        // Verify ownership via folder join since documents no longer have user_id[cite: 49, 50]
        if (!$this->getUserDocument($userId, $docId)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }
        
        (new \App\Models\DocumentModel())->delete($docId);

        return $this->response->setJSON(['status' => 'success']);
    }
}