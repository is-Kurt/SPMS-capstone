<?php

namespace App\Controllers\Submissions;

use App\Controllers\BaseController;

class Index extends BaseController
{
    public function index() {
        $userId = session()->get('user_id');
        $submissionModel = new \App\Models\SubmissionModel();
        $filter = $this->request->getGet('docs');
        $now = date('Y-m-d H:i');

        if ($filter === 'locked') {
            $submissionModel->where('eval_date_start >', $now)
                            ->where('is_rated', 0);
        } elseif ($filter === 'pending') {
            $submissionModel->where('eval_date_start <=', $now)
                            ->where('eval_date_end >=', $now)
                            ->where('is_rated', 0);
        } elseif ($filter === 'unevaluated') {
            $submissionModel->where('eval_date_end <', $now)
                            ->where('is_rated', 0);
        } elseif ($filter === 'evaluated') {
            $submissionModel->where('is_rated', 1);
        } elseif ($filter !== 'all') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $docs = $submissionModel->where('user_id', $userId)
                                ->orderBy('updated_at', 'DESC')
                                ->findAll();

        $data['counts'] = [
            'all'         => $submissionModel->where('user_id', $userId)->countAllResults(),
            'locked'      => $submissionModel->where('user_id', $userId)->where('eval_date_start >', $now)->where('is_rated', 0)->countAllResults(),
            'pending'     => $submissionModel->where('user_id', $userId)->where('eval_date_start <=', $now)->where('eval_date_end >=', $now)->where('is_rated', 0)->countAllResults(),
            'unevaluated' => $submissionModel->where('user_id', $userId)->where('eval_date_end <', $now)->where('is_rated', 0)->countAllResults(),
            'evaluated'   => $submissionModel->where('user_id', $userId)->where('is_rated', 1)->countAllResults(),
        ];

        
        $data['docs'] = $docs;
        $data['filter'] = $filter;
        $data['now'] = $now;

        return view('submissions/index', $data);
    }
}