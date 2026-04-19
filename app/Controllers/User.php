<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class User extends BaseController
{
    public function find()
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
                'message' => 'No user found with that email.'
            ]);
        }

        log_message('debug', json_encode($user));
        return $this->response->setJSON([
            'status' => 'success',
            'user' => $user,
        ]);
    }
}
