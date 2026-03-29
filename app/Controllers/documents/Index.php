<?php

namespace App\Controllers\Documents;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Index extends BaseController
{
    public function index() {
        $userId = session()->get('user_id');

        if (!$userId) {
            return redirect()->to('/login');
        }
        
        $documentModel = new \App\Models\DocumentModel();
        $filter = $this->request->getGet('docs');

        $builder = $documentModel->db->table('documents d')
            ->select('d.*, sd.collaborator_id')
            ->join('shared_documents sd', 'sd.document_id = d.id AND sd.collaborator_id = ' . $userId, 'left')
            ->where('(d.user_id = ' . $userId . ' OR sd.collaborator_id = ' . $userId . ')')
            ->orderBy('d.updated_at', 'DESC');

        if ($filter === 'shared') {
            $builder->where('sd.collaborator_id', $userId);
        } elseif ($filter === 'owned') {
            $builder->where('d.user_id', $userId);
        } elseif ($filter !== 'all') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data['filter'] = $filter;
        $data['docs'] = $builder->get()->getResultArray();

        return view('documents/index', $data);
    }
    
    public function delete() {
        $id = $this->request->getPost('id');
        $documentModel = new \App\Models\DocumentModel();

        $doc = $documentModel->find($id);

        if (!$doc || $doc['user_id'] != session()->get('user_id')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $documentModel->delete($id);

        return $this->response->setJSON([
            'status'   => 'success',
            'csrfHash' => csrf_hash()
        ]);
    }

    public function share() {
        $documentId     = $this->request->getPost('document_id');
        $collaboratorId = $this->request->getPost('collaborator_id');
        $userId         = session()->get('user_id');

        $documentModel       = new \App\Models\DocumentModel();
        $sharedDocumentModel = new \App\Models\SharedDocumentModel();

        // check document exists and belongs to the current user
        $doc = $documentModel->find($documentId);

        if (!$doc || $doc['user_id'] != $userId) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Unauthorized'
            ]);
        }

        // prevent sharing with yourself
        if ($collaboratorId == $userId) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'You cannot share a document with yourself'
            ]);
        }

        // prevent duplicate shares
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
            'permission'      => 'viewer', // default permission
        ]);

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => 'Document shared successfully',
            'csrfHash' => csrf_hash()
        ]);
    }
}