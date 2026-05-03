<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\I18n\Time;

class Submission extends BaseController
{
    /**
     * Helper to verify if a document belongs to a folder owned by the user.
     */
    private function verifyDocumentOwner($docId, $userId) {
        $db = \Config\Database::connect();
        return $db->table('documents d')
            ->join('document_folders df', 'df.id = d.document_folder_id')
            ->where('d.id', $docId)
            ->where('df.user_id', $userId)
            ->get()->getRowArray();
    }

    public function index(): string {
        $userId = session()->get('user_id');
        $submissionModel = new \App\Models\SubmissionModel();
        $filter = $this->request->getGet('docs') ?? 'all_submissions';
        $now = date('Y-m-d H:i');

        $builder = $submissionModel->db->table('submissions s')
            ->select('s.*, d.title, d.eval_date_start, d.eval_date_end, df.title as folder_title')
            ->join('documents d', 'd.id = s.document_id')
            ->join('document_folders df', 'df.id = d.document_folder_id') 
            ->where('df.user_id', $userId) // Ownership verification via folder
            ->orderBy('s.submitted_at', 'DESC');

        // Apply filters (locked, pending, unevaluated, evaluated)
        if ($filter === 'locked') {
            $builder->where('d.eval_date_start >', $now)->where('s.is_rated', false);
        } elseif ($filter === 'pending') {
            $builder->where('d.eval_date_start <=', $now)->where('d.eval_date_end >=', $now)->where('s.is_rated', false);
        } elseif ($filter === 'unevaluated') {
            $builder->where('d.eval_date_end <', $now)->where('s.is_rated', false);
        } elseif ($filter === 'evaluated') {
            $builder->where('s.is_rated', true);
        }

        $data['counts'] = $this->_getCounts();
        $data['docs']   = $builder->get()->getResultArray();
        $data['filter'] = $filter;
        $data['now']    = $now;

        return view('submission/index', $data);
    }

    public function show(): string {
        $documentId = $this->request->getGet('Id'); 
        $returnUrl  = $this->request->getGet('return'); // Check for the rating tab referral[cite: 17]
        $userId     = session()->get('user_id');
        $role       = session()->get('role');
        $userDept   = session()->get('department');

        $db = \Config\Database::connect();
        
        $doc = $db->table('submissions s')
            ->select('s.id as submission_id, s.is_rated, s.final_rating, s.submitted_at, 
                    d.id, d.title, d.content, d.eval_date_start, d.eval_date_end, d.created_at,
                    df.user_id as owner_id, u.department as owner_dept')
            ->join('documents d', 'd.id = s.document_id')
            ->join('document_folders df', 'df.id = d.document_folder_id')
            ->join('users u', 'u.id = df.user_id') 
            ->where('d.id', $documentId)
            ->get()->getRowArray();

        if (!$doc) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $isOwner = ($doc['owner_id'] === $userId);
        $isRatingMode = !empty($returnUrl); // Rating mode is active if 'return' exists

        // --- ENFORCED LOGIC ---
        // 1. Owners can always enter.
        // 2. Non-owners MUST be Admins or the correct Supervisor AND in Rating Mode.
        $canAccess = false;
        if ($isOwner) {
            $canAccess = true;
        } elseif ($isRatingMode) {
            if ($role === 'admin') {
                $canAccess = true;
            } elseif ($role === 'supervisor' && $doc['owner_dept'] === $userDept) {
                $canAccess = true;
            }
        }

        if (!$canAccess) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data['doc'] = $doc;
        $data['isRatingMode'] = $isRatingMode; // Pass flag to the view
        $data['returnUrl'] = $returnUrl;
        
        return view('submission/show', $data);
    }

    public function store() {
        $userId = session()->get('user_id');
        $docId  = $this->request->getPost('doc_id');

        // 1. Verify ownership via the parent folder[cite: 51]
        if (!$this->verifyDocumentOwner($docId, $userId)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized or Document not found']);
        }

        $submissionModel = new \App\Models\SubmissionModel();
        $existing = $submissionModel->where('document_id', $docId)->first();
        if ($existing) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'This document has already been submitted.']);
        }

        $submissionModel->save([
            'document_id'  => $docId,
            'is_rated'     => false,
            'submitted_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON(['status' => 'success']);
    }

    public function destroy() {
        $doc_id = $this->request->getPost('doc_id');
        $userId = session()->get('user_id');

        // 1. Security check via the parent folder join[cite: 51]
        if (!$this->verifyDocumentOwner($doc_id, $userId)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $submissionModel = new \App\Models\SubmissionModel(); 
        $submission = $submissionModel->where('document_id', $doc_id)->first();
        if ($submission) {
            $submissionModel->delete($submission['id']);
        }

        return $this->response->setJSON(['status' => 'success']);
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

        // Fetch counts using the folder join to respect new schema[cite: 50, 51]
        $allSubs = $db->table('submissions s')
            ->select('s.is_rated, d.eval_date_start, d.eval_date_end')
            ->join('documents d', 'd.id = s.document_id')
            ->join('document_folders df', 'df.id = d.document_folder_id')
            ->where('df.user_id', $userId)
            ->get()->getResultArray();

        $counts = ['all_submissions' => count($allSubs), 'locked' => 0, 'pending' => 0, 'unevaluated' => 0, 'evaluated' => 0];

        foreach ($allSubs as $s) {
            if ($s['is_rated']) { $counts['evaluated']++; } 
            else {
                if ($s['eval_date_start'] > $now) { $counts['locked']++; } 
                elseif ($s['eval_date_end'] < $now) { $counts['unevaluated']++; } 
                else { $counts['pending']++; }
            }
        }
        return $counts;
    }
}