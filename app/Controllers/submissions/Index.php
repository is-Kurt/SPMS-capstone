<?php

namespace App\Controllers\Submissions;

use App\Controllers\BaseController;

class Index extends BaseController
{
    public function index() {
        $userId = session()->get('user_id');
        $submissionModel = new \App\Models\SubmissionModel();
        $filter = $this->request->getGet('docs') ?? 'all';
        $now = date('Y-m-d H:i');

        if ($filter === 'locked') {
            $submissionModel->where('eval_date_start >', $now)
                            ->where('is_rated', false);
        } elseif ($filter === 'pending') {
            $submissionModel->where('eval_date_start <=', $now)
                            ->where('eval_date_end >=', $now)
                            ->where('is_rated', false);
        } elseif ($filter === 'unevaluated') {
            $submissionModel->where('eval_date_end <', $now)
                            ->where('is_rated', false);
        } elseif ($filter === 'evaluated') {
            $submissionModel->where('is_rated', true);
        } elseif ($filter !== 'all') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $docs = $submissionModel->where('user_id', $userId)
                                ->orderBy('updated_at', 'DESC')
                                ->findAll();

        $data['counts'] = [
            'all'         => $submissionModel->where('user_id', $userId)->countAllResults(),
            'locked'      => $submissionModel->where('user_id', $userId)->where('eval_date_start >', $now)->where('is_rated', false)->countAllResults(),
            'pending'     => $submissionModel->where('user_id', $userId)->where('eval_date_start <=', $now)->where('eval_date_end >=', $now)->where('is_rated', false)->countAllResults(),
            'unevaluated' => $submissionModel->where('user_id', $userId)->where('eval_date_end <', $now)->where('is_rated', false)->countAllResults(),
            'evaluated'   => $submissionModel->where('user_id', $userId)->where('is_rated', true)->countAllResults(),
        ];

        
        $data['docs'] = $docs;
        $data['filter'] = $filter;
        $data['now'] = $now;

        return view('submissions/index', $data);
    }

    public function delete() {
        $id = $this->request->getPost('id');
        $submissionModel = new \App\Models\submissionModel();

        $doc = $submissionModel->find($id);

        if (!$doc || $doc['user_id'] != session()->get('user_id')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $submissionModel->delete($id);

        return $this->response->setJSON([
            'status'   => 'success',
            'csrfHash' => csrf_hash()
        ]);
    }
}