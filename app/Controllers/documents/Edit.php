<?php

namespace App\Controllers\Documents;

use App\Controllers\BaseController;

class Edit extends BaseController
{
    public function index(): string {
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
        return view('documents/edit', $data);
    }

    public function store() {
        $documentModel = new \App\Models\DocumentModel();
        
        $maxAttempts = 5;
        for ($i = 0; $i < $maxAttempts; $i++) {
            $id = generate_short_id();

            if ($documentModel->find($id) === null) {
                if ($documentModel->find($id) === null) {
                            $documentModel->save([
                                'id'      => $id,
                                'title'   => 'Untitled IPCR',
                                'content' => '',
                                'user_id' => session()->get('user_id')
                            ]);
                            break;
                        }
            }
        }

        return redirect()->to(site_url('document?Id=' . $id));
    }

    public function patch() {
        sleep(1);
        $content = $this->request->getPost('content');
        $title   = $this->request->getPost('title');
        $id      = $this->request->getPost('id');
        $eval_date_start = $this->request->getPost('doc_date_start');
        $eval_date_end = $this->request->getPost('doc_date_end');

        $message = 'Document updated successfully';
        $documentModel = new \App\Models\DocumentModel();
        
        $documentModel->save([
            'id'      => $id,
            'title'   => $title,
            'content' => $content,
            'eval_date_start' => date('Y-m-d H:i', strtotime($eval_date_start)),
            'eval_date_end' => date('Y-m-d H:i', strtotime($eval_date_end))
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => $message,
            'csrfHash' => csrf_hash()
        ]);
    }
}