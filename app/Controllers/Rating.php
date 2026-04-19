<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Rating extends BaseController
{
    // 1. The Directory View (List of all batches)
    public function index() {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/documents')->with('error', 'Unauthorized access.');
        }

        $ratingModel = new \App\Models\RatingModel();
        
        // Fetch all rating directories/batches, ordered by newest first
        $data['ratings'] = $ratingModel->orderBy('created_at', 'DESC')->findAll();
        
        return view('rating/index', $data);
    }

    // 2. The Table View (Inside a specific directory)
    public function show() {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/documents')->with('error', 'Unauthorized access.');
        }

        $ratingId = $this->request->getGet('Id');
        $ratingModel = new \App\Models\RatingModel();
        $data['rating'] = $ratingModel->find($ratingId);

        if (!$data['rating']) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Massive JOIN to get User, Document, Submission, and UserRating data all at once!
        $db = \Config\Database::connect();
        $data['userRatings'] = $db->table('user_ratings ur')
            ->select('ur.id as ur_id, ur.remarks, 
                      u.username, u.role, 
                      s.is_rated, s.final_rating, s.submitted_at')
            ->join('documents d', 'd.id = ur.document_id')
            ->join('users u', 'u.id = d.user_id')
            ->join('submissions s', 's.document_id = d.id', 'left') // Left join because they might not have submitted yet!
            ->where('ur.rating_id', $ratingId)
            ->get()->getResultArray();

        return view('rating/show', $data);
    }

    public function destroy() {
        // Reusing the 'doc_id' parameter sent by your existing deleteModal.js
        $ratingId = $this->request->getPost('doc_id'); 
        
        $ratingModel = new \App\Models\RatingModel();
        
        $rating = $ratingModel->find($ratingId);
        
        if (!$rating) {
            return $this->response->setJSON([
                'status'  => 'error', 
                'message' => 'Rating batch not found.'
            ]);
        }

        $ratingModel->delete($ratingId);

        return $this->response->setJSON([
            'status' => 'success'
        ]);
    }

    // 3. AJAX Endpoint to save the editable remark
    public function saveRemark() {
        $ur_id = $this->request->getPost('ur_id');
        $remarks = $this->request->getPost('remarks');

        $userRatingModel = new \App\Models\UserRatingModel();
        $userRatingModel->update($ur_id, ['remarks' => $remarks]);

        return $this->response->setJSON(['status' => 'success']);
    }
}