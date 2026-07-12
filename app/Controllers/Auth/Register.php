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
use App\Models\RoleModel;

/**
 * Handles the invite-only signup flow: a user can only reach signup via a valid,
 * unexpired invitation token (created by AccountManagement::sendInvites()).
 */
class Register extends BaseController
{
    /** GET /signup?token=... - Validates the invite token and shows the signup form. */
    public function index()
    {
        $token = $this->request->getGet('token');
        if (!$token) return redirect()->to('/login');

        $invitationModel = new InvitationModel();
        $unitModel       = new UnitModel();
        $positionModel   = new PositionModel();
        $roleModel       = new RoleModel();
        
        $invitation = $invitationModel->where('token', $token)
                         ->where('status', InvitationStatus::PENDING->value)
                         ->where('expires_at >', date('Y-m-d H:i:s'))
                         ->first();

        if (!$invitation) {
            return redirect()->to('/login');
        }

        // A valid invite always wins over whatever session this browser happens to have -
        // log out any current user so the invitee lands on a clean registration form
        // instead of being redirected away by an unrelated active session.
        if (session()->get('isLoggedIn')) {
            setcookie('remember_me', '', time() - 3600, '/');
            session()->destroy();
        }

        $role = $invitation['role_id'] ? $roleModel->find($invitation['role_id']) : null;
        $isAdminInvite = $role && strtolower($role['name']) === 'admin';

        $units = $unitModel->orderBy('name', 'ASC')->findAll();
        $positions = $positionModel->orderBy('title', 'ASC')->findAll();

        return view('auth/signup', [
            'invitation'    => $invitation,
            'units'         => $units,
            'positions'     => $positions,
            'isAdminInvite' => $isAdminInvite
        ]);
    }

    /**
     * POST /signup - Creates the account, assigns the invited role, records the
     * plantilla (unit+position) rows unless this is an admin invite, and marks
     * the invitation as accepted so the token can't be reused.
     */
    public function store()
    {
        $token = $this->request->getPost('token');

        $invitationModel = new InvitationModel();
        $userModel       = new UserModel();
        $plantillaModel  = new PlantillaModel();
        $roleModel       = new RoleModel();

        $invitation = $invitationModel->where('token', $token)
                                      ->where('status', InvitationStatus::PENDING->value)
                                      ->first();
                                      
        if (!$invitation) return redirect()->to('/login');

        // Determine if admin invite
        $role = $invitation['role_id'] ? $roleModel->find($invitation['role_id']) : null;
        $isAdminInvite = $role && strtolower($role['name']) === 'admin';

        $validation = \Config\Services::validation();
        $rules = [
            'first_name' => 'required|min_length[2]|max_length[100]',
            'last_name'  => 'required|min_length[2]|max_length[100]',
            'password'   => 'required|min_length[8]',
            'confirm-password' => 'required|matches[password]'
        ];

        if (!$isAdminInvite) {
            $rules['units.*'] = 'required';
            $rules['positions.*'] = 'required';
        }

        $validation->setRules($rules);

        if (!$validation->run($this->request->getPost())) {
            return redirect()->back()->withInput();
        }

        $userModel->db->transStart();

        $userId = $userModel->insert([
            'first_name' => $this->request->getPost('first_name'),
            'last_name'  => $this->request->getPost('last_name'),
            'email'      => strtolower($invitation['email']),
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

        if (!$isAdminInvite) {
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
        }

        $invitationModel->update($invitation['id'], ['status' => InvitationStatus::ACCEPTED->value]);

        $userModel->db->transComplete();

        return redirect()->to('/login');
    }
}