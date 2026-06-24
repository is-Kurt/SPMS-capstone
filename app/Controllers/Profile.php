<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;

class Profile extends BaseController
{
    public function index()
    {
        $userId = session()->get('user_id');
        $userModel      = new UserModel();
        $unitModel      = new \App\Models\UnitModel();
        $positionModel  = new \App\Models\PositionModel();
        $plantillaModel = new \App\Models\PlantillaModel();
        
        // Fetch current active positions for this user
        $currentPlantilla = $plantillaModel->where('user_id', $userId)->where('ended_at IS NULL')->findAll();
        
        // If they somehow have no positions, provide one empty array so the UI renders at least one card
        if (empty($currentPlantilla)) {
            $currentPlantilla = [['unit_id' => '', 'position_id' => '']];
        }

        $data = [
            'user'             => $userModel->find($userId),
            'units'            => $unitModel->orderBy('name', 'ASC')->findAll(),
            'positions'        => $positionModel->orderBy('title', 'ASC')->findAll(),
            'currentPlantilla' => $currentPlantilla
        ];

        return view('profile', $data);
    }

    public function updateGeneral()
    {
        $userId = session()->get('user_id');
        $userModel = new UserModel();

        // 1. Validation Rules
        $rules = [
            'first_name' => 'required|min_length[2]',
            'last_name'  => 'required|min_length[2]',
            'email'      => "required|valid_email|is_unique[users.email,id,{$userId}]"
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please check your inputs and try again.');
        }

        // Wrap the updates in a transaction since we are touching multiple tables
        $userModel->db->transStart();

        // 2. Update Database (Basic Info)
        $userModel->update($userId, [
            'first_name' => $this->request->getPost('first_name'),
            'last_name'  => $this->request->getPost('last_name'),
            'email'      => $this->request->getPost('email'),
        ]);

        // 3. Update Plantilla (Positions & Units)
        $units     = $this->request->getPost('units');
        $positions = $this->request->getPost('positions');

        // Wipe the current active designations
        $userModel->db->table('plantilla')
                      ->where('user_id', $userId)
                      ->where('ended_at IS NULL')
                      ->delete();

        // Loop through the submitted arrays and insert the fresh ones
        if (!empty($units) && !empty($positions)) {
            for ($i = 0; $i < count($units); $i++) {
                if (!empty($units[$i]) && !empty($positions[$i])) {
                    $userModel->db->table('plantilla')->insert([
                        'user_id'     => $userId,
                        'unit_id'     => $units[$i],
                        'position_id' => $positions[$i],
                        'created_at'  => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }

        $userModel->db->transComplete();

        // 4. Update Session variables so the UI updates instantly
        session()->set([
            'first_name' => $this->request->getPost('first_name'),
            'last_name'  => $this->request->getPost('last_name'),
            'email'      => $this->request->getPost('email'),
            'username'   => $this->request->getPost('first_name') . ' ' . $this->request->getPost('last_name')
        ]);

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword()
    {
        $userId = session()->get('user_id');
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        $rules = [
            'current_password' => 'required',
            'new_password'     => 'required|min_length[3]',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please check your password inputs.');
        }

        $currentPassword = $this->request->getPost('current_password');
        if (!password_verify($currentPassword, $user['password'])) {
            return redirect()->back()->withInput()->with('errors', [
                'current_password' => 'The current password you entered is incorrect.'
            ]);
        }

        $newPassword = password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT);
        $userModel->update($userId, ['password' => $newPassword]);

        return redirect()->back()->with('success', 'Password updated successfully.');
    }
}