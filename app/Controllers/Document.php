<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\DocumentModel;
use App\Models\DocumentFolderModel;
use App\Models\EvaluationRoutingModel;
use App\Models\TemplateModel;

/**
 * Handles individual documents (the actual IPCR/DPCR/OPCR pages inside a folder):
 * viewing/editing content, creating new documents from a template, and marking
 * which document in a folder counts as the "target" used for the final rating.
 */
class Document extends BaseController
{
    /** GET /document/{docId} - Opens the TinyMCE editor view for a single document. */
    public function index($docId = null) {
        $userId  = session()->get('user_id');
        $sysRole = session()->get('role');
        
        if (!$docId) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $documentModel = new DocumentModel();
        
        $docInfo = $documentModel->getDocumentWithFolderInfo($docId);

        if (!$docInfo) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $docOwnerId = $docInfo['owner_id'];
        $isGuide = false;
        $routingStatus = null;

        if ($docOwnerId !== $userId) {
            $routingModel = new EvaluationRoutingModel();
            $routing = $routingModel->where('folder_id', $docInfo['document_folder_id'])
                                    ->where('evaluator_id', $userId)
                                    ->first();
            
            if ($routing) {
                $routingStatus = is_object($routing) ? $routing->status : $routing['status'];
            } elseif ($sysRole === 'Admin') {
                $routingStatus = null;
            } else {
                $isGuide = true;
            }
        }

        $data['routingStatus'] = $routingStatus;
        $data['doc'] = $docInfo;
        $data['isGuide'] = $isGuide; 
        
        return view('document/show', $data);
    }
    
    /** POST /document - Creates a new (optionally template-seeded) document inside a folder. */
    public function store() {
        return $this->tryOrFail(function() {
            $documentModel = new DocumentModel();
            $userId   = session()->get('user_id');
            $folderId = $this->request->getPost('folder_id');
            $title  = trim($this->request->getPost('title')) ?: 'Untitled Document';
            $templateId = $this->request->getPost('template');
            $initialContent = '';

            if (!empty($templateId)) {
                $templateModel = new TemplateModel();
                $template = $templateModel->find($templateId);
                if ($template) {
                    $initialContent = $template['content'];
                }
            }

            $docs = $documentModel->getUserDocuments($userId);
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

    /** POST /document/update - Autosave endpoint: persists title/content for an owner or assigned evaluator. */
    public function update() {
        $userId  = session()->get('user_id');
        $sysRole = session()->get('role');
        $docId  = $this->request->getPost('id');

        $documentModel = new DocumentModel();

        $docOwnerInfo = $documentModel->db->table('documents d')
            ->select('df.user_id as owner_id, df.id as folder_id')
            ->join('document_folders df', 'df.id = d.document_folder_id')
            ->where('d.id', $docId)->get()->getRowArray();

        if (!$docOwnerInfo) return $this->response->setJSON(['status' => 'error', 'message' => 'Document not found']);

        $isAuthorized = false;

        if ($docOwnerInfo['owner_id'] === $userId || $sysRole === 'Admin') {
            $isAuthorized = true; 
        } else {
            $routingModel = new EvaluationRoutingModel();
            $isEvaluator = $routingModel->where('folder_id', $docOwnerInfo['folder_id'])
                                        ->where('evaluator_id', $userId)
                                        ->countAllResults() > 0;
            if ($isEvaluator) $isAuthorized = true;
        }

        if (!$isAuthorized) return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);

        $documentModel->save([
            'id'      => $docId,
            'title'   => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
        ]);

        return $this->response->setJSON(['status' => 'success']);
    }
    
    /**
     * POST /document/set-target - Marks one document (the ★) in a folder as the
     * basis for the final rating. Clears the flag on every other document in the
     * same folder first, since only one document can be the target at a time.
     */
    public function setTarget() {
        return $this->tryOrFail(function() {
            $docId = $this->request->getPost('doc_id');
            $folderId = $this->request->getPost('folder_id');

            $documentModel = new DocumentModel();

            $documentModel->where('document_folder_id', $folderId)->set(['is_target' => 0])->update();
            $documentModel->where('id', $docId)->set(['is_target' => 1])->update();

            return $this->respond(['status' => 'success', 'message' => 'Target document updated.']);
        });
    }

    /** POST /document/delete - Deletes a document after confirming the requester owns its folder. */
    public function destroy() {
        $docId = $this->request->getPost('doc_id');
        $userId = session()->get('user_id');
        $documentModel = new DocumentModel();

        // Verify ownership via folder join since documents no longer have user_id
        if (!$documentModel->getUserDocuments($userId, $docId)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $documentModel->delete($docId);

        return $this->response->setJSON(['status' => 'success']);
    }
}