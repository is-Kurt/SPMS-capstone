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
        $docId = $this->request->getPost('doc_id');

        $submissionModel = new \App\Models\SubmissionModel();
        $documentModel = new \App\Models\DocumentModel();

        $sourceDoc = $documentModel->find($docId);

        while (true) {
            $id = generate_short_id();

            if ($submissionModel->find($id) === null) {
                $submissionModel->save([
                    'id' => $id,
                    'user_id' => $userId,
                    'title' => $sourceDoc['title'],
                    'content' => $sourceDoc['content'],
                    'eval_date_start' => $sourceDoc['eval_date_start'],
                    'eval_date_end' => $sourceDoc['eval_date_end']
                ]);
                break;
            }
        };

        return redirect()->to(site_url('submissions?docs=all'));
    }

    public function patch() {
        $content = $this->request->getPost('content');
        $title   = $this->request->getPost('title');
        $id      = $this->request->getPost('id');
        $message = 'Document updated successfully';
        $documentModel = new \App\Models\SubmissionModel();

        $documentModel->save([
            'id'      => $id,
            'title'   => $title,
            'content' => $content,
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => $message,
            'csrfHash' => csrf_hash()
        ]);
    }

    public function rated() {
        $id = $this->request->getPost('doc_id');

        $documentModel = new \App\Models\SubmissionModel();

        $documentModel->save([
            'id'       => $id,
            'is_rated' => true,
        ]);

        return redirect()->to(site_url('submissions?docs=evaluated'));
    }
}
