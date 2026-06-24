<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Enums\InvitationStatus;

class Register extends BaseController
{
    public function index()
    {        
        $token = $this->request->getGet('token');
        if (!$token) return redirect()->to('/login')->with('error', 'Missing invitation token.');

        $db = \Config\Database::connect();
        
        // 1. Validate the token
        $invitation = $db->table('invitations')
                         ->where('token', $token)
                         ->where('status', InvitationStatus::PENDING->value)
                         ->where('expires_at >', date('Y-m-d H:i:s'))
                         ->get()->getRowArray();

        if (!$invitation) {
            return redirect()->to('/login')->with('error', 'This invitation link is invalid or has expired.');
        }

        // 2. Fetch required data for dropdowns
        $units = $db->table('units')->orderBy('name', 'ASC')->get()->getResultArray();
        $positions = $db->table('positions')->orderBy('title', 'ASC')->get()->getResultArray();

        return view('auth/signup', [
            'invitation' => $invitation,
            'units'      => $units,
            'positions'  => $positions
        ]);
    }

    public function store()
    {
        $db = \Config\Database::connect();
        $token = $this->request->getPost('token');

        // Security: Re-verify token on submission
        $invitation = $db->table('invitations')->where('token', $token)->where('status', InvitationStatus::PENDING->value)->get()->getRowArray();
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

        $db->transStart();

        $userModel = new \App\Models\UserModel();
        
        // 1. Create the User 
        $userId = $userModel->insert([
            'first_name' => $this->request->getPost('first_name'),
            'last_name'  => $this->request->getPost('last_name'),
            'email'      => $invitation['email'], 
            'password'   => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'is_active'  => 1
        ]);

        // 2. Link the User to their Role in the junction table
        if ($invitation['role_id']) {
            $db->table('user_roles')->insert([
                'user_id'    => $userId,
                'role_id'    => $invitation['role_id'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        // 3. Insert Multiple Positions into Plantilla
        $units = $this->request->getPost('units');
        $positions = $this->request->getPost('positions');

        if (is_array($positions) && is_array($units)) {
            foreach ($positions as $index => $posId) {
                if (!empty($posId) && !empty($units[$index])) {
                    $db->table('plantilla')->insert([
                        'user_id'     => $userId,
                        'position_id' => $posId,
                        'unit_id'     => $units[$index],
                        'started_at'  => date('Y-m-d')
                    ]);
                }
            }
        }

        // 3. Burn the Invitation Token
        $db->table('invitations')->where('id', $invitation['id'])->update(['status' => InvitationStatus::ACCEPTED->value]);

        $db->transComplete();

        return redirect()->to('/login')->with('success', 'Account fully configured! You may now log in.');
    }
}