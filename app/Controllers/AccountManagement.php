<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Enums\InvitationStatus;
use App\Enums\EmailStatus;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\PositionModel;
use App\Models\UnitModel;
use App\Models\InvitationModel;

class AccountManagement extends BaseController
{
    public function index() {
        $userModel = new UserModel();
        
        $users = $userModel->asArray()
            ->select("users.id, users.first_name, users.last_name, users.email, users.is_active, 
                      GROUP_CONCAT(DISTINCT pos.title) as position, 
                      GROUP_CONCAT(DISTINCT un.name) as department, 
                      GROUP_CONCAT(DISTINCT r.name) as role_name")
            ->join('plantillas p', 'p.user_id = users.id AND p.ended_at IS NULL', 'left')
            ->join('positions pos', 'pos.id = p.position_id', 'left')
            ->join('units un', 'un.id = p.unit_id', 'left')
            ->join('user_roles ur', 'ur.user_id = users.id', 'left')
            ->join('roles r', 'r.id = ur.role_id', 'left')
            ->groupBy('users.id')
            ->orderBy('users.last_name', 'ASC')
            ->findAll();

        foreach ($users as &$u) {
            if ($u['position'])   $u['position']   = str_replace(',', ', ', $u['position']);
            if ($u['department']) $u['department'] = str_replace(',', ', ', $u['department']);
            if ($u['role_name'])  $u['role_name']  = str_replace(',', ', ', $u['role_name']);
        }

        $roleModel     = new RoleModel();
        $positionModel = new PositionModel();
        $unitModel     = new UnitModel();

        $roles     = $roleModel->orderBy('name', 'ASC')->findAll();
        $positions = $positionModel->orderBy('title', 'ASC')->findAll();
        $units     = $unitModel->orderBy('name', 'ASC')->findAll();

        return view('auth/accounts', [
            'users'     => $users,
            'roles'     => $roles,
            'positions' => $positions,
            'units'     => $units
        ]);
    }

    public function sendInvites() {
        $rawEmails = $this->request->getPost('emails');
        $roleId    = $this->request->getPost('role_id');

        $emails = preg_split('/[\s,;]+/', trim($rawEmails));
        $validEmails = [];
        
        $userModel = new UserModel();
        $invitationModel = new InvitationModel();
        $now = date('Y-m-d H:i:s'); // Current time for expiration check

        foreach ($emails as $email) {
            $email = trim($email);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                
                // 1. Check if they already have an account
                $hasAccount = $userModel->where('email', $email)->countAllResults() > 0;
                
                // 2. Check if they already have a pending, unexpired invitation
                $hasPendingInvite = $invitationModel->where('email', $email)
                                                    ->where('status', InvitationStatus::PENDING->value)
                                                    ->where('expires_at >', $now)
                                                    ->countAllResults() > 0;

                // Only add them if BOTH are false
                if (!$hasAccount && !$hasPendingInvite) {
                    $validEmails[] = $email;
                }
            }
        }

        if (empty($validEmails)) return redirect()->back()->with('error', 'No valid or new email addresses found (they may already have accounts or pending invites).');

        $userModel->db->transStart();
        foreach ($validEmails as $email) {
            $token = bin2hex(random_bytes(32)); 

            $invitationModel->insert([
                'email'      => $email,
                'token'      => $token,
                'status'     => InvitationStatus::PENDING->value,
                'role_id'    => $roleId, 
                'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days')),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $inviteLink = site_url("signup?token={$token}");
            
            queue_email(
                $email, 
                'Invitation to join SPMS', 
                "You have been invited to join the system. Click here: {$inviteLink}"
            );
        }
        $userModel->db->transComplete();

        return redirect()->back()->with('success', count($validEmails) . ' invitations queued for sending!');
    }

    public function processQueueAjax() {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);
        session_write_close();

        $result = process_email_queue(5);

        if ($result['processed'] === 0 && $result['remaining'] === 0) {
            return $this->response->setJSON([
                'status'      => 'success', 
                'queue_state' => 'complete', 
                'message'     => 'Queue empty'
            ]);
        }

        return $this->response->setJSON([
            'status'      => 'success', 
            'queue_state' => 'working', 
            'processed'   => $result['processed'],
            'remaining'   => $result['remaining']
        ]);
    }

    public function toggleStatus() {
        $targetId = $this->request->getPost('user_id');
        if ($targetId == session()->get('user_id')) return redirect()->back()->with('error', 'Cannot disable yourself.');

        $userModel = new UserModel();
        $user = $userModel->find($targetId);

        if ($user) {
            $newStatus = $user['is_active'] == 1 ? 0 : 1;
            $userModel->update($targetId, ['is_active' => $newStatus]);
            return redirect()->back()->with('success', $newStatus == 1 ? 'Account enabled.' : 'Account disabled.');
        }
        return redirect()->back()->with('error', 'User not found.');
    }

    public function destroy() {
        $targetId = $this->request->getPost('user_id');
        if ($targetId == session()->get('user_id')) return redirect()->back()->with('error', 'Cannot delete yourself.');

        $userModel = new UserModel();
        $userModel->delete($targetId); 

        return redirect()->back()->with('success', 'User account deleted permanently.');
    }

    public function addRole() {
        $name = trim($this->request->getPost('name'));
        if (empty($name)) return redirect()->back()->with('error', 'Role name is required.');

        try {
            $roleModel = new RoleModel();
            $roleModel->insert(['name' => $name, 'created_at' => date('Y-m-d H:i:s')]);
            return redirect()->back()->with('success', 'Role added successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to add role. It may already exist.');
        }
    }

    public function deleteRole() {
        try {
            $roleModel = new RoleModel();
            $roleModel->delete($this->request->getPost('id'));
            return redirect()->back()->with('success', 'Role deleted.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Cannot delete this role because it is currently assigned to users.');
        }
    }

    public function addPosition() {
        $title = trim($this->request->getPost('title'));
        $isTeaching = $this->request->getPost('is_teaching') ? 1 : 0;

        if (empty($title)) return redirect()->back()->with('error', 'Position title is required.');

        $positionModel = new PositionModel();
        $positionModel->insert([
            'title' => $title, 
            'is_teaching' => $isTeaching, 
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return redirect()->back()->with('success', 'Position added successfully.');
    }

    public function deletePosition() {
        try {
            $positionModel = new PositionModel();
            $positionModel->delete($this->request->getPost('id'));
            return redirect()->back()->with('success', 'Position deleted.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Cannot delete this position. It is in use.');
        }
    }

    public function addUnit() {
        $name = trim($this->request->getPost('name'));
        $parentId = $this->request->getPost('parent_id') ?: null; 

        if (empty($name)) return redirect()->back()->with('error', 'Unit name is required.');

        $unitModel = new UnitModel();
        $unitModel->insert([
            'name' => $name, 
            'parent_id' => $parentId, 
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return redirect()->back()->with('success', 'Unit added successfully.');
    }

    public function deleteUnit() {
        try {
            $unitModel = new UnitModel();
            $unitModel->delete($this->request->getPost('id'));
            return redirect()->back()->with('success', 'Unit deleted.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Cannot delete unit. It contains sub-units or is assigned to users.');
        }
    }
}