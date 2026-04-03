<?php

namespace App\Controllers\Submissions;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Edit extends BaseController
{
    public function index()
    {
        $id = $this->request->getGet('Id');
        $submissionModel = new \App\Models\SubmissionModel();
        $data['doc'] = $submissionModel->find($id);

        return view('submissions/edit', $data);
    }

    public function store() {
        $userId = session()->get('user_id');
        $docId  = $this->request->getPost('doc_id');

        $submissionModel = new \App\Models\SubmissionModel();
        $documentModel   = new \App\Models\DocumentModel();

        $sourceDoc = $documentModel->find($docId);

        if (!$sourceDoc) {
            return $this->response->setJSON([
                'status'   => 'error',
                'message'  => 'Document not found',
                'csrfHash' => csrf_hash()
            ]);
        }

        $maxAttempts = 5;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $id = generate_short_id();

            if ($submissionModel->find($id) === null) {
                $saved = $submissionModel->save([
                    'id'              => $id,
                    'user_id'         => $userId,
                    'title'           => $sourceDoc['title'],
                    'content'         => $sourceDoc['content'],
                    'eval_date_start' => $sourceDoc['eval_date_start'],
                    'eval_date_end'   => $sourceDoc['eval_date_end']
                ]);
                break;
            }
        }

        if (!$saved) {
            return $this->response->setJSON([
                'status'   => 'error',
                'message'  => 'Failed to create submission',
                'csrfHash' => csrf_hash()
            ]);
        }

        return $this->response->setJSON([
            'status'   => 'success',
            'csrfHash' => csrf_hash()
        ]);
    }

    public function patch() {
        $content = $this->request->getPost('content');
        $title   = $this->request->getPost('title');
        $id      = $this->request->getPost('id');
        $documentModel = new \App\Models\SubmissionModel();
    
            $saved = $documentModel->save([
                'id'      => $id,
                'title'   => $title,
                'content' => $content,
            ]);

            if (!$saved) {
                return $this->response->setJSON([
                    'status'   => 'error',
                    'message'  => 'Validation failed',
                    'errors'   => $documentModel->errors(),
                    'csrfHash' => csrf_hash()
                ]);
            }

            return $this->response->setJSON([
                'status'   => 'success',
                'message'  => 'Document updated successfully',
                'csrfHash' => csrf_hash()
            ]);

    }

    public function rated() {
        $id = $this->request->getPost('doc_id');

        $documentModel = new \App\Models\SubmissionModel();

        $saved = $documentModel->save([
            'id'       => $id,
            'is_rated' => true,
        ]);

        try {
            if (!$saved) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Failed to save document',
                    'errors'  => $documentModel->errors()
                ]);
            }

            return $this->response->setJSON([
                'status'   => 'success',
                'message'  => 'Document updated successfully',
                'csrfHash' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status'   => 'error',
                'message'  => $e->getMessage(),
                'csrfHash' => csrf_hash()
            ]);
        }
    }
}
