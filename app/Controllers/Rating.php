<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Rating extends BaseController
{
    // STEP 1: The Directory View (List of all batches)
    public function index() {
        $ratingModel = new \App\Models\RatingModel();
        $data['ratings'] = $ratingModel->orderBy('created_at', 'DESC')->findAll();
        
        return view('rating/index', $data);
    }

    // STEP 2: The Departments View (List of departments inside a batch)
    public function departments() {
        $ratingId = $this->request->getGet('Id');
        $ratingModel = new \App\Models\RatingModel();
        
        $data['rating'] = $ratingModel->find($ratingId);
        if (!$data['rating']) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Get distinct departments for standard users in this batch
        $db = \Config\Database::connect();
        $data['departments'] = $db->table('user_ratings ur')
            ->select('u.department')
            ->join('documents d', 'd.id = ur.document_id')
            ->join('users u', 'u.id = d.user_id')
            ->where('ur.rating_id', $ratingId)
            ->where('u.role', 'user') 
            ->where('u.department IS NOT NULL')
            ->distinct()
            ->orderBy('u.department', 'ASC')
            ->get()->getResultArray();

        return view('rating/departments', $data);
    }

    // STEP 3: The Show View (Table of users inside ONE specific department)
    public function show() {
        $ratingId = $this->request->getGet('Id');
        $department = $this->request->getGet('dept'); 
        
        $ratingModel = new \App\Models\RatingModel();
        $data['rating'] = $ratingModel->find($ratingId);
        $data['department'] = $department;

        if (!$data['rating'] || !$department) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Get users ONLY for the selected department
        $db = \Config\Database::connect();
        $data['userRatings'] = $db->table('user_ratings ur')
            ->select("ur.id as ur_id, ur.remarks, 
                      (u.first_name || ' ' || u.last_name) as username, 
                      u.position, u.department, 
                      s.is_rated, s.final_rating, s.submitted_at", false)
            ->join('documents d', 'd.id = ur.document_id')
            ->join('users u', 'u.id = d.user_id')
            ->join('submissions s', 's.document_id = d.id', 'left')
            ->where('ur.rating_id', $ratingId)
            ->where('u.department', $department) 
            ->where('u.role', 'user') 
            ->orderBy('u.first_name', 'ASC')
            ->get()->getResultArray();

        return view('rating/show', $data);
    }

    public function destroy() {
        $ratingId = $this->request->getPost('doc_id'); 
        
        $ratingModel     = new \App\Models\RatingModel();
        $userRatingModel = new \App\Models\UserRatingModel();
        $documentModel   = new \App\Models\DocumentModel();
        
        $rating = $ratingModel->find($ratingId);
        
        if (!$rating) {
            return $this->response->setJSON([
                'status'  => 'error', 
                'message' => 'Rating batch not found.'
            ]);
        }

        // 1. Fetch all the linked user_ratings for this specific batch
        $userRatings = $userRatingModel->where('rating_id', $ratingId)->findAll();
        
        // Extract just the document IDs into a clean array
        $docIds = array_column($userRatings, 'document_id');

        // 2. Delete ALL the cloned documents from the employees' accounts!
        if (!empty($docIds)) {
            $documentModel->whereIn('id', $docIds)->delete();
        }

        // 3. Manually delete the junction rows (This fixes your SQLite orphaned row bug!)
        $userRatingModel->where('rating_id', $ratingId)->delete();

        // 4. Finally, delete the parent folder
        $ratingModel->delete($ratingId);

        return $this->response->setJSON([
            'status' => 'success'
        ]);
    }

    // 3. AJAX Endpoint to save the editable remark
    public function save() {
        $ur_id = $this->request->getPost('ur_id');
        $remarks = $this->request->getPost('remarks');

        $userRatingModel = new \App\Models\UserRatingModel();
        $userRatingModel->update($ur_id, ['remarks' => $remarks]);

        return $this->response->setJSON(['status' => 'success']);
    }
}