<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Document extends BaseController
{
    public function index($docId = null) {
        $userId  = session()->get('user_id');
        $sysRole = session()->get('role');
        
        // If there's no ID in the URL, throw a 404
        if (!$docId) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $db = \Config\Database::connect();
        
        $docInfo = $db->table('documents d')
            ->select('d.*, df.user_id as owner_id, df.status as folder_status, df.eval_date_start')
            ->join('document_folders df', 'df.id = d.document_folder_id')
            ->where('d.id', $docId)->get()->getRowArray();

        if (!$docInfo) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $docOwnerId = $docInfo['owner_id'];
        
        $isGuide = false;
        $routingStatus = null;

        // 1. Authorization & Routing Check
        if ($docOwnerId !== $userId) {
            $routingModel = new \App\Models\EvaluationRoutingModel();
            
            // Check if this user is explicitly assigned to evaluate this folder
            $routing = $routingModel->where('folder_id', $docInfo['document_folder_id'])
                                    ->where('evaluator_id', $userId)
                                    ->first();
            
            if ($routing) {
                // User is an assigned evaluator
                // Fallback support for both Array and Entity return types
                $routingStatus = is_object($routing) ? $routing->status : $routing['status'];
            } elseif ($sysRole === 'Admin') {
                // Admin acts as an implicit evaluator but has no specific routing row
                $routingStatus = null;
            } else {
                // If not the owner, not an evaluator, and not an admin, 
                // they are viewing this as a read-only guide template.
                $isGuide = true;
            }
        }

        $data['routingStatus'] = $routingStatus;
        $data['doc'] = $docInfo;
        $data['isGuide'] = $isGuide; 
        
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

    public function update() {
        $userId  = session()->get('user_id');
        $sysRole = session()->get('role');
        $doc_id  = $this->request->getPost('id');

        $db = \Config\Database::connect();

        // 1. Fetch owner info and folder ID
        $docOwnerInfo = $db->table('documents d')
            ->select('df.user_id as owner_id, df.id as folder_id')
            ->join('document_folders df', 'df.id = d.document_folder_id')
            ->where('d.id', $doc_id)->get()->getRowArray();

        if (!$docOwnerInfo) return $this->response->setJSON(['status' => 'error', 'message' => 'Document not found']);

        $isAuthorized = false;

        // 2. Collaborative Authorization Check
        if ($docOwnerInfo['owner_id'] === $userId || $sysRole === 'Admin') {
            $isAuthorized = true; 
        } else {
            // Check if user is an evaluator for this document's folder
            $routingModel = new \App\Models\EvaluationRoutingModel();
            $isEvaluator = $routingModel->where('folder_id', $docOwnerInfo['folder_id'])
                                        ->where('evaluator_id', $userId)
                                        ->countAllResults() > 0;
            
            if ($isEvaluator) {
                $isAuthorized = true;
            }
        }

        if (!$isAuthorized) return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);

        $documentModel = new \App\Models\DocumentModel();
        $documentModel->save([
            'id'      => $doc_id,
            'title'   => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
        ]);

        return $this->response->setJSON(['status' => 'success']);
    }
    
    public function setTarget() {
        return $this->tryOrFail(function() {
            $docId = $this->request->getPost('doc_id');
            $folderId = $this->request->getPost('folder_id');
            $db = \Config\Database::connect();

            // Reset all documents in this folder to 0 (Evidence)
            $db->table('documents')->where('document_folder_id', $folderId)->update(['is_target' => 0]);
            // Set the selected one to 1 (Target)
            $db->table('documents')->where('id', $docId)->update(['is_target' => 1]);

            return $this->respond(['status' => 'success', 'message' => 'Target document updated.']);
        });
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