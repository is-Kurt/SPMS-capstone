<?php

namespace App\Controllers\Profile;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Index extends BaseController
{
    public function index()
    {
        $userModel = new \App\Models\UserModel();
        $docModel = new \App\Models\DocumentModel();
        
        $userId = session()->get('user_id');
        $user = $userModel->find($userId);

        $data = [
            'title'    => 'My Profile',
            'user'     => $user,
            'docCount' => $docModel->where('user_id', $userId)->countAllResults(),
        ];

        return view('profile/index', $data);
    }
}
