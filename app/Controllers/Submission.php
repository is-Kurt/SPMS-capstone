<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\I18n\Time;

class Submission extends BaseController
{
    public function index(): string {
        $userId = session()->get('user_id');
        $submissionModel = new \App\Models\SubmissionModel();
        $filter = $this->request->getGet('docs') ?? 'all_submissions';
        $now = date('Y-m-d H:i');

        // 1. Build the base query joining Submissions to Documents
        $builder = $submissionModel->db->table('submissions s')
            ->select('s.*, d.title, d.eval_date_start, d.eval_date_end, d.updated_at')
            ->join('documents d', 'd.id = s.document_id')
            ->where('d.user_id', $userId)
            ->orderBy('s.submitted_at', 'DESC');

        // 2. Apply filters based on the joined document dates
        if ($filter === 'locked') {
            $builder->where('d.eval_date_start >', $now)
                    ->where('s.is_rated', false);
        } elseif ($filter === 'pending') {
            $builder->where('d.eval_date_start <=', $now)
                    ->where('d.eval_date_end >=', $now)
                    ->where('s.is_rated', false);
        } elseif ($filter === 'unevaluated') {
            $builder->where('d.eval_date_end <', $now)
                    ->where('s.is_rated', false);
        } elseif ($filter === 'evaluated') {
            $builder->where('s.is_rated', true);
        } elseif ($filter !== 'all_submissions') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data['counts'] = $this->_getCounts();
        $data['docs']   = $builder->get()->getResultArray();
        $data['filter'] = $filter;
        $data['now']    = $now;

        return view('submission/index', $data);
    }

    public function show(): string {
        // We expect the Document ID from the URL
        $documentId = $this->request->getGet('Id'); 
        
        $db = \Config\Database::connect();
        
        // Join the two tables so the view gets EVERYTHING it needs
        $doc = $db->table('submissions s')
            ->select('s.id as submission_id, s.is_rated, s.final_rating, s.submitted_at, 
                    d.id, d.user_id, d.title, d.content, d.eval_date_start, d.eval_date_end, d.created_at')
            ->join('documents d', 'd.id = s.document_id')
            ->where('d.id', $documentId)
            ->get()
            ->getRowArray();

        if (!$doc) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data['doc'] = $doc;
        return view('submission/show', $data);
    }

    public function store() {
        $userId = session()->get('user_id');
        $docId  = $this->request->getPost('doc_id');

        $submissionModel = new \App\Models\SubmissionModel();
        $documentModel   = new \App\Models\DocumentModel();

        // 1. Verify the document actually exists and belongs to the user
        $doc = $documentModel->find($docId);
        if (!$doc || $doc['user_id'] != $userId) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Unauthorized or Document not found'
            ]);
        }

        // 2. Ensure they haven't already submitted it
        $existing = $submissionModel->where('document_id', $docId)->first();
        if ($existing) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'This document has already been submitted.'
            ]);
        }

        // 3. Simply insert the pointer! No massive cloning needed.
        $submissionModel->save([
            'document_id'  => $docId,
            'is_rated'     => false,
            'submitted_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON([
            'status' => 'success'
        ]);
    }

    public function rate() {
        $docId = $this->request->getPost('doc_id');
        $finalRating = $this->request->getPost('final_rating');

        $submissionModel = new \App\Models\SubmissionModel();
        
        // Find the submission row attached to this document
        $submission = $submissionModel->where('document_id', $docId)->first();

        if (!$submission) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Submission not found for this document.'
            ]);
        }

        $saved = $submissionModel->update($submission['id'], [
            'is_rated'     => true,
            'rated_at'     => date('Y-m-d H:i:s'),
            'final_rating' => $finalRating
        ]);

        if (!$saved) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to save rating.',
                'errors'  => $submissionModel->errors()
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Document rated successfully'
        ]);
    }

    public function destroy() {
        // NOTE: This now acts as an "Unsubmit" button! 
        // It deletes the submission row, but the main Document is kept perfectly intact.
        $doc_id = $this->request->getPost('doc_id');
        $userId = session()->get('user_id');

        $submissionModel = new \App\Models\SubmissionModel(); 
        $documentModel   = new \App\Models\DocumentModel();

        // Security check via the parent document
        $doc = $documentModel->find($doc_id);
        if (!$doc || $doc['user_id'] != $userId) {
            return $this->response->setJSON([
                'status'  => 'error', 
                'message' => 'Unauthorized'
            ]);
        }

        $submission = $submissionModel->where('document_id', $doc_id)->first();
        if ($submission) {
            $submissionModel->delete($submission['id']);
        }

        return $this->response->setJSON([
            'status' => 'success'
        ]);
    }

    public function count() {
        return $this->response->setJSON([
            'status' => 'success',
            'counts' => $this->_getCounts()
        ]);
    }

    private function _getCounts() {
        $userId = session()->get('user_id');
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i');

        // Run one fast query fetching only the columns we need to do the math
        $allSubs = $db->table('submissions s')
            ->select('s.is_rated, d.eval_date_start, d.eval_date_end')
            ->join('documents d', 'd.id = s.document_id')
            ->where('d.user_id', $userId)
            ->get()->getResultArray();

        $counts = [
            'all_submissions' => count($allSubs),
            'locked'          => 0,
            'pending'         => 0,
            'unevaluated'     => 0,
            'evaluated'       => 0,
        ];

        // Loop through the results to bucket them correctly
        foreach ($allSubs as $s) {
            if ($s['is_rated']) {
                $counts['evaluated']++;
            } else {
                if ($s['eval_date_start'] > $now) {
                    $counts['locked']++;
                } elseif ($s['eval_date_start'] <= $now && $s['eval_date_end'] >= $now) {
                    $counts['pending']++;
                } elseif ($s['eval_date_end'] < $now) {
                    $counts['unevaluated']++;
                }
            }
        }

        return $counts;
    }
}