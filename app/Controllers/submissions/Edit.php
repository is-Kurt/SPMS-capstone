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

        $baseTitle = $sourceDoc['title'];
        $existingDocs = $submissionModel->where('user_id', $userId)
                                        ->groupStart()
                                            ->where('title', $baseTitle)
                                            ->orLike('title', $baseTitle . ' (', 'after')
                                        ->groupEnd()
                                        ->findAll();

        $exactMatchFound = false;
        $baseMatchFound = false;
        $usedSuffixes = [];

        foreach ($existingDocs as $doc) {
            $existingTitle = $doc['title'];

            // If we find the exact base string, flag it
            if ($existingTitle === $baseTitle) {
                $baseMatchFound = true;
                $exactMatchFound = true;
                continue;
            }

            // Regex: Look ONLY at the very end of the string
            if (preg_match('/ \((\d+)\)$/', $existingTitle, $matches)) {
                $number = (int)$matches[1];
                $suffixLength = strlen(' (' . $number . ')');
                
                // Cut off the suffix and verify the prefix is exactly our base title
                $prefix = substr($existingTitle, 0, -$suffixLength);
                
                if ($prefix === $baseTitle) {
                    // Save this number as a "key" in our array so we know it is taken
                    $usedSuffixes[$number] = true;
                }
            }
        }

        // 4. Construct the final safe title
        $finalTitle = $baseTitle;
        
        if ($exactMatchFound) {
            if (!$baseMatchFound) return;

            $nextSuffix = 1;
            
            // Keep counting up until we find a number that is NOT in our used list
            while (isset($usedSuffixes[$nextSuffix])) {
                $nextSuffix++;
            }
            
            // Add the lowest available gap number
            $finalTitle = $baseTitle . ' (' . $nextSuffix . ')';
        }

        $maxAttempts = 5;
        for ($i = 0; $i < $maxAttempts; $i++) {
            $id = generate_short_id();

            if ($submissionModel->find($id) === null) {
                $saved = $submissionModel->save([
                    'id'              => $id,
                    'user_id'         => $userId,
                    'title'           => $finalTitle,
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
