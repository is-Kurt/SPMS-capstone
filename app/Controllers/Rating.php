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

        // Updated JOIN: Join documents to folders to reach the user[cite: 50, 51]
        $db = \Config\Database::connect();
        $data['departments'] = $db->table('user_ratings ur')
            ->select('u.department')
            ->join('documents d', 'd.id = ur.document_id')
            ->join('document_folders df', 'df.id = d.document_folder_id') 
            ->join('users u', 'u.id = df.user_id') 
            ->where('ur.rating_id', $ratingId)
            ->where('u.role', 'user') 
            ->where('u.department IS NOT NULL')
            ->distinct()
            ->orderBy('u.department', 'ASC')
            ->get()->getResultArray();

        return view('rating/departments', $data);
    }

    public function show() {
        $ratingId   = $this->request->getGet('Id');
        $role       = session()->get('role');
        $userDept   = session()->get('department');
        $department = ($role === 'supervisor') ? $userDept : $this->request->getGet('dept');

        if (empty($department)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $db         = \Config\Database::connect();

        $data['docHeaders'] = $db->table('user_ratings ur')
        ->select('d.title')
        ->join('documents d', 'd.id = ur.document_id')
        ->where('ur.rating_id', $ratingId)
        ->distinct()
        ->get()->getResultArray();

        // 2. Fetch data: Added d.id as doc_id and s.id as sub_id
        $rawRatings = $db->table('user_ratings ur')
            ->select("ur.id as ur_id, u.id as user_id, (u.first_name || ' ' || u.last_name) as username, 
                    u.position, d.title as doc_title, d.id as doc_id, 
                    s.id as sub_id, s.final_rating, s.is_rated", false)
            ->join('documents d', 'd.id = ur.document_id')
            ->join('document_folders df', 'df.id = d.document_folder_id')
            ->join('users u', 'u.id = df.user_id')
            ->join('submissions s', 's.document_id = d.id', 'left') // Left join handles unsubmitted docs
            ->where('ur.rating_id', $ratingId)
            ->where('u.department', $department)
            ->get()->getResultArray();

        // Pivot logic: Now storing the IDs for the view links[cite: 31]
        $userRows = [];
        foreach ($rawRatings as $row) {
            $userRows[$row['user_id']]['info'] = [
                'username' => $row['username'], 
                'position' => $row['position'],
                'ur_id'    => $row['ur_id']
            ];
            $userRows[$row['user_id']]['scores'][$row['doc_title']] = [
                'doc_id' => $row['doc_id'],
                'sub_id' => $row['sub_id'], // Will be NULL if no submission exists[cite: 31]
                'rating' => $row['final_rating'],
                'rated'  => $row['is_rated']
            ];
        }

        $data['userRows']   = $userRows;
        $data['rating']     = (new \App\Models\RatingModel())->find($ratingId);
        $data['department'] = $department;

        return view('rating/show', $data);
    }

    public function destroy() {
        $ratingId = $this->request->getPost('doc_id'); 
        
        $ratingModel         = new \App\Models\RatingModel();
        $userRatingModel     = new \App\Models\UserRatingModel();
        $folderModel         = new \App\Models\DocumentFolderModel();
        $documentModel       = new \App\Models\DocumentModel();
        
        $rating = $ratingModel->find($ratingId);
        if (!$rating) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Rating batch not found.']);
        }

        // 1. Fetch all documents associated with this batch
        $userRatings = $userRatingModel->where('rating_id', $ratingId)->findAll();
        $docIds = array_column($userRatings, 'document_id');

        if (!empty($docIds)) {
            // 2. Identify the unique cloned folders associated with these documents[cite: 50]
            $folders = $documentModel->select('document_folder_id')
                                     ->whereIn('id', $docIds)
                                     ->distinct()
                                     ->findAll();
            
            $folderIds = array_column($folders, 'document_folder_id');

            // 3. Delete cloned folders (Cascades to documents automatically)[cite: 46]
            if (!empty($folderIds)) {
                $folderModel->whereIn('id', $folderIds)->delete();
            }
        }

        // 4. Manually delete junction rows and parent batch record
        $userRatingModel->where('rating_id', $ratingId)->delete();
        $ratingModel->delete($ratingId);

        return $this->response->setJSON(['status' => 'success']);
    }

    public function save() {
        $ur_id = $this->request->getPost('ur_id');
        $remarks = $this->request->getPost('remarks');

        $userRatingModel = new \App\Models\UserRatingModel();
        $userRatingModel->update($ur_id, ['remarks' => $remarks]);

        return $this->response->setJSON(['status' => 'success']);
    }
}