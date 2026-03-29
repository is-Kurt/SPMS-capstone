<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Users extends BaseController
{
    public function search()
    {
        $email = $this->request->getGet('email');

        if (!$email) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Email is required'
            ]);
        }

        $userModel = new \App\Models\UserModel();

        $user = $userModel->where('email', $email)->first();

        if (!$user) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'No user found'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'user'   => [
                'id'    => $user['id'],
                'email' => $user['email'],
            ]
        ]);
    }
}
