<?php

namespace App\Controllers\Documents;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Index extends BaseController
{
    public function index() {
        $userId = session()->get('user_id');
        $documentModel = new \App\Models\DocumentModel();
        $filter = $this->request->getGet('docs') ?? 'all';

        $baseQuery = $documentModel->db->table('documents d')
            ->select('d.*, sd.collaborator_id, u.email, u.username')
            ->join('shared_documents sd', 'sd.document_id = d.id AND sd.collaborator_id = ' . $userId, 'left')
            ->join('users u', 'u.id = d.user_id', 'left')
            ->groupStart()
                ->where('d.user_id', $userId)
                ->orWhere('sd.collaborator_id', $userId)
            ->groupEnd()
            ->orderBy('d.updated_at', 'DESC');

        $allDocs = $baseQuery->get()->getResultArray();

        $data['counts'] = [
            'all'    => count($allDocs),
            'owned'  => count(array_filter($allDocs, fn($d) => $d['user_id'] == $userId)),
            'shared' => count(array_filter($allDocs, fn($d) => $d['collaborator_id'] == $userId)),
        ];

        if ($filter === 'shared') {
            $data['docs'] = array_values(array_filter($allDocs, fn($d) => $d['collaborator_id'] == $userId));
        } elseif ($filter === 'owned') {
            $data['docs'] = array_values(array_filter($allDocs, fn($d) => $d['user_id'] == $userId));
        } elseif ($filter === 'all') {
            $data['docs'] = $allDocs;
        } else {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data['filter'] = $filter;

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