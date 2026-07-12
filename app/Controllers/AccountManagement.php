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

/**
 * Admin-only "Accounts" section: the user directory, email invitations, and the
 * lookup data admins manage system-wide (roles, job positions, departments/units).
 */
class AccountManagement extends BaseController
{
    /** GET /accounts[/{tab}] - Lists every user plus the reference lists (roles/positions/units) shown on the page. */
    public function index(?string $tab = null) {
        $validTabs = ['directory', 'create', 'invitations', 'system'];
        $activeTab = in_array($tab, $validTabs, true) ? $tab : 'directory';

        $userModel = new UserModel();
        
        $users = $userModel->getAllUsersWithDetails();

        foreach ($users as &$u) {
            if ($u['position'])   $u['position']   = str_replace(',', ', ', $u['position']);
            if ($u['department']) $u['department'] = str_replace(',', ', ', $u['department']);
            if ($u['role_name'])  $u['role_name']  = str_replace(',', ', ', $u['role_name']);
        }

        $roleModel       = new RoleModel();
        $positionModel   = new PositionModel();
        $unitModel       = new UnitModel();
        $invitationModel = new InvitationModel();

        $roles       = $roleModel->orderBy('name', 'ASC')->findAll();
        $positions   = $positionModel->orderBy('title', 'ASC')->findAll();
        $units       = $unitModel->orderBy('name', 'ASC')->findAll();
        $invitations = $invitationModel->getAllWithRoleNames();

        return view('accounts/index', [
            'users'       => $users,
            'roles'       => $roles,
            'positions'   => $positions,
            'units'       => $units,
            'invitations' => $invitations,
            'activeTab'   => $activeTab
        ]);
    }

    /**
     * POST /account/send-invites - Bulk-invites a pasted list of email addresses,
     * skipping any that already have an account or an unexpired pending invite,
     * and queues an invite email (with signup link/token) for each valid one.
     */
    public function sendInvites() {
        return $this->tryOrFail(function() {
            $rawEmails = $this->request->getPost('emails');
            $roleId    = $this->request->getPost('role_id');

            $emails = preg_split('/[\s,;]+/', trim($rawEmails));
            $validEmails = [];

            $userModel = new UserModel();
            $invitationModel = new InvitationModel();
            $now = date('Y-m-d H:i:s');

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

            if (empty($validEmails)) {
                throw new \Exception('No valid or new email addresses found (they may already have accounts or pending invites).');
            }

            $roleModel = new RoleModel();
            $roleName  = $roleModel->find($roleId)['name'] ?? 'No Role';
            $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

            // Sent back so the Invitations dashboard tab can append these rows live,
            // without needing a page reload to see what was just queued.
            $createdInvitations = [];

            $userModel->db->transStart();
            foreach ($validEmails as $email) {
                $token = bin2hex(random_bytes(32));

                $invitationModel->insert([
                    'email'      => $email,
                    'token'      => $token,
                    'status'     => InvitationStatus::PENDING->value,
                    'role_id'    => $roleId,
                    'expires_at' => $expiresAt,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $createdInvitations[] = [
                    'id'              => $invitationModel->getInsertID(),
                    'email'           => $email,
                    'role_name'       => $roleName,
                    'created_display' => date('M d, Y'),
                    'expires_display' => date('M d, Y - h:i A', strtotime($expiresAt)),
                ];

                $inviteLink = site_url("signup?token={$token}");

                queue_email(
                    $email,
                    'Invitation to join SPMS',
                    render_email('invitation', ['link' => $inviteLink])
                );
            }
            $userModel->db->transComplete();

            return $this->respond([
                'status'      => 'success',
                'message'     => count($validEmails) . ' invitations queued for sending!',
                'invitations' => $createdInvitations,
            ]);
        });
    }

    /** POST /account/invite/delete - Cancels an invitation (e.g. to fix a mistaken role before it's used) or clears an old record. */
    public function deleteInvite() {
        try {
            $invitationModel = new InvitationModel();
            $invitationModel->delete($this->request->getPost('id'));
            return $this->respond(['status' => 'success']);
        } catch (\Exception $e) {
            return $this->respondError('Could not delete this invitation.');
        }
    }

    /** POST /account/invite/delete-bulk - Deletes every invitation id given (used by the Invitations tab's "Delete Filtered" action). */
    public function deleteInvitesBulk() {
        $ids = $this->request->getPost('ids');

        if (empty($ids) || !is_array($ids)) {
            return $this->respondError('No invitations selected.');
        }

        try {
            $invitationModel = new InvitationModel();
            $invitationModel->delete($ids);
            return $this->respond(['status' => 'success', 'deleted' => count($ids)]);
        } catch (\Exception $e) {
            return $this->respondError('Could not delete the selected invitations.');
        }
    }

    /**
     * AJAX-only: processes a batch of the queued outgoing emails (polled repeatedly
     * from the page until the queue is empty). Development-only - on a real server
     * the email queue is drained by the spms:update-statuses/spms:check-folder-deadlines
     * cron commands instead, so this stays a no-op there even if called directly.
     */
    public function processQueueAjax() {
        if (ENVIRONMENT !== 'development') return $this->response->setStatusCode(404);
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

    /** POST /account/toggle - Enables/disables a user account (blocks login without deleting data). Can't target yourself. */
    public function toggleStatus() {
        return $this->tryOrFail(function() {
            $targetId = $this->request->getPost('user_id');
            if ($targetId == session()->get('user_id')) {
                throw new \Exception('Cannot disable yourself.');
            }

            $userModel = new UserModel();
            $user = $userModel->find($targetId);
            if (!$user) throw new \Exception('User not found.');

            $newStatus = $user['is_active'] == 1 ? 0 : 1;
            $userModel->update($targetId, ['is_active' => $newStatus]);

            return $this->respond(['status' => 'success', 'is_active' => $newStatus]);
        });
    }

    /** POST /account (DELETE) - Permanently deletes a user account. Can't target yourself. */
    public function destroy() {
        return $this->tryOrFail(function() {
            $targetId = $this->request->getPost('user_id');
            if ($targetId == session()->get('user_id')) {
                throw new \Exception('Cannot delete yourself.');
            }

            $userModel = new UserModel();
            $userModel->delete($targetId);

            return $this->respond(['status' => 'success']);
        });
    }

    // --- System Data CRUD: manages the lookup tables used by the invite form and profile/plantilla pickers. ---
    // Roles have no "add" endpoint - role names are hardcoded into access-control checks
    // throughout the app (nav visibility, route filters), so they're managed in the backend only.

    /** POST /account/role/delete - Blocked by a DB constraint (caught below) while any user still holds this role. */
    public function deleteRole() {
        try {
            $roleModel = new RoleModel();
            $roleModel->delete($this->request->getPost('id'));
            return $this->respond(['status' => 'success']);
        } catch (\Exception $e) {
            return $this->respondError('Cannot delete this role because it is currently assigned to users.');
        }
    }

    /** POST /account/position/add */
    public function addPosition() {
        $title = trim($this->request->getPost('title'));
        $isTeaching = $this->request->getPost('is_teaching') ? 1 : 0;

        if (empty($title)) return $this->respondError('Position title is required.');

        $positionModel = new PositionModel();
        $id = $positionModel->insert([
            'title' => $title,
            'is_teaching' => $isTeaching,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return $this->respond(['status' => 'success', 'item' => ['id' => $id, 'title' => $title, 'is_teaching' => $isTeaching]]);
    }

    /**
     * POST /account/position/delete - Deleting an in-use position doesn't fail: the
     * plantillas.position_id FK is ON DELETE SET NULL, so any affected users keep
     * their plantilla row (and stay visible everywhere) but with no position.
     */
    public function deletePosition() {
        try {
            $positionModel = new PositionModel();
            $positionModel->delete($this->request->getPost('id'));
            return $this->respond(['status' => 'success']);
        } catch (\Exception $e) {
            return $this->respondError('Could not delete this position.');
        }
    }

    /** POST /account/unit/add - `parent_id` lets units nest (e.g. a college under a campus). */
    public function addUnit() {
        $name = trim($this->request->getPost('name'));
        $parentId = $this->request->getPost('parent_id') ?: null;

        if (empty($name)) return $this->respondError('Unit name is required.');

        $unitModel = new UnitModel();
        $id = $unitModel->insert([
            'name' => $name,
            'parent_id' => $parentId,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $parentName = null;
        if ($parentId) {
            $parent = $unitModel->find($parentId);
            $parentName = $parent['name'] ?? null;
        }

        return $this->respond(['status' => 'success', 'item' => ['id' => $id, 'name' => $name, 'parent_id' => $parentId, 'parent_name' => $parentName]]);
    }

    /**
     * POST /account/unit/delete - Deleting an in-use unit doesn't fail: the
     * plantillas.unit_id FK is ON DELETE SET NULL, so any affected users keep
     * their plantilla row (and stay visible everywhere) but with no unit.
     * Note: units.parent_id is ON DELETE CASCADE, so deleting a unit that has
     * sub-units still deletes that whole sub-tree - that part is unchanged.
     */
    public function deleteUnit() {
        try {
            $unitModel = new UnitModel();
            $unitModel->delete($this->request->getPost('id'));
            return $this->respond(['status' => 'success']);
        } catch (\Exception $e) {
            return $this->respondError('Could not delete this unit.');
        }
    }
}