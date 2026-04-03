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
        
        $rawTitle = trim($this->request->getPost('title'));
        $baseTitle = $rawTitle ?: 'Untitled Document';
        
        $newId = null;
        $maxAttempts = 5;

        $existingDocs = $documentModel->where('user_id', session()->get('user_id'))
                                      ->groupStart()
                                          ->where('title', $baseTitle)
                                          ->orLike('title', $baseTitle . ' (', 'after')
                                      ->groupEnd()
                                      ->findAll();
                                      
        $exactMatchFound = false;
        $maxSuffix = 0;

        foreach ($existingDocs as $doc) {
            $existingTitle = $doc['title'];

            // If we find the exact base string, flag it
            if ($existingTitle === $baseTitle) {
                $exactMatchFound = true;
                continue;
            }

            // Regex: Look ONLY at the very end of the string for a space, a parenthesis, numbers, and a closing parenthesis.
            // e.g., matches " (1)", " (42)", but ignores "(1) Hello"
            if (preg_match('/ \((\d+)\)$/', $existingTitle, $matches)) {
                $number = (int)$matches[1];
                $suffixLength = strlen(' (' . $number . ')');
                
                // Cut off the suffix and verify the prefix is exactly our base title
                $prefix = substr($existingTitle, 0, -$suffixLength);
                
                if ($prefix === $baseTitle) {
                    $exactMatchFound = true;
                    // Keep track of the highest number we've seen
                    if ($number > $maxSuffix) {
                        $maxSuffix = $number;
                    }
                }
            }
        }

        // 4. Construct the final safe title
        $finalTitle = $baseTitle;
        if ($exactMatchFound) {
            // Add 1 to the highest number found
            $finalTitle = $baseTitle . ' (' . ($maxSuffix + 1) . ')';
        }

        // 5. Generate ID and Save
        for ($i = 0; $i < $maxAttempts; $i++) {
            $id = generate_short_id();

            if ($documentModel->find($id) === null) {
                $documentModel->save([
                    'id'      => $id,
                    'title'   => $finalTitle, // Use the duplicate-safe title!
                    'content' => '',
                    'user_id' => session()->get('user_id')
                ]);
                $newId = $id;
                break;
            }
        }

        if ($newId) {
            return $this->response->setJSON([
                'status'   => 'success',
                'id'       => $newId,
                'csrfHash' => csrf_hash()
            ]);
        }

        return $this->response->setJSON([
            'status'   => 'error',
            'message'  => 'Could not generate a unique ID. Please try again.',
            'csrfHash' => csrf_hash()
        ])->setStatusCode(500);
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