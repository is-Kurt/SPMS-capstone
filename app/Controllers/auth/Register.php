<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Enums\InvitationStatus;
use App\Models\UserModel;
use App\Models\UnitModel;
use App\Models\PositionModel;
use App\Models\InvitationModel;
use App\Models\PlantillaModel;

class Register extends BaseController
{
    public function index()
    {        
        $token = $this->request->getGet('token');
        if (!$token) return redirect()->to('/login')->with('error', 'Missing invitation token.');

        $invitationModel = new InvitationModel();
        $unitModel       = new UnitModel();
        $positionModel   = new PositionModel();
        
        $invitation = $invitationModel->where('token', $token)
                         ->where('status', InvitationStatus::PENDING->value)
                         ->where('expires_at >', date('Y-m-d H:i:s'))
                         ->first();

        if (!$invitation) {
            return redirect()->to('/login')->with('error', 'This invitation link is invalid or has expired.');
        }

        $units = $unitModel->orderBy('name', 'ASC')->findAll();
        $positions = $positionModel->orderBy('title', 'ASC')->findAll();

        return view('auth/signup', [
            'invitation' => $invitation,
            'units'      => $units,
            'positions'  => $positions
        ]);
    }

    public function store()
    {
        $token = $this->request->getPost('token');

        $invitationModel = new InvitationModel();
        $userModel       = new UserModel();
        $plantillaModel  = new PlantillaModel();

        $invitation = $invitationModel->where('token', $token)
                                      ->where('status', InvitationStatus::PENDING->value)
                                      ->first();
                                      
        if (!$invitation) return redirect()->to('/login')->with('error', 'Invalid token submission.');

        $validation = \Config\Services::validation();
        $validation->setRules([
            'first_name' => 'required|min_length[2]|max_length[100]',
            'last_name'  => 'required|min_length[2]|max_length[100]',
            'password'   => 'required|min_length[8]',
            'confirm-password' => 'required|matches[password]',
            'units.*'     => 'required', // Validate arrays!
            'positions.*' => 'required'
        ]);

        if (!$validation->run($this->request->getPost())) {
            return redirect()->back()->withInput();
        }

        $userModel->db->transStart();

        $userId = $userModel->insert([
            'first_name' => $this->request->getPost('first_name'),
            'last_name'  => $this->request->getPost('last_name'),
            'email'      => $invitation['email'], 
            'password'   => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'is_active'  => 1
        ]);

        if ($invitation['role_id']) {
            $userModel->db->table('user_roles')->insert([
                'user_id'    => $userId,
                'role_id'    => $invitation['role_id'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        $units = $this->request->getPost('units');
        $positions = $this->request->getPost('positions');

        if (is_array($positions) && is_array($units)) {
            foreach ($positions as $index => $posId) {
                if (!empty($posId) && !empty($units[$index])) {
                    $plantillaModel->insert([
                        'user_id'     => $userId,
                        'position_id' => $posId,
                        'unit_id'     => $units[$index],
                        'started_at'  => date('Y-m-d')
                    ]);
                }
            }
        }

        $invitationModel->update($invitation['id'], ['status' => InvitationStatus::ACCEPTED->value]);

        $userModel->db->transComplete();

        return redirect()->to('/login')->with('success', 'Account fully configured! You may now log in.');
    }
}