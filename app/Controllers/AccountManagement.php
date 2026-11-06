<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Enums\InvitationStatus;
use App\Enums\EmailStatus;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\PositionModel;
use App\Models\UnitModel;
use App\Models\EmploymentStatusModel;
use App\Models\InvitationModel;
use App\Models\DefaultFunctionWeightModel;
use App\Models\DocumentFolderModel;
use App\Libraries\OrgStructure;
use App\Enums\DocType;

/**
 * Admin-only "Accounts" section: the user directory, email invitations, and the
 * lookup data admins manage system-wide (roles, job positions, departments/units).
 */
class AccountManagement extends BaseController
{
    /** GET /accounts[/{tab}] - Lists every user plus the reference lists (roles/positions/units) shown on the page. */
    public function index(?string $tab = null) {
        $validTabs = ['directory', 'create', 'invitations', 'system', 'twg', 'weights'];
        $activeTab = in_array($tab, $validTabs, true) ? $tab : 'directory';

        $userModel = new UserModel();
        
        $users = $userModel->getAllUsersWithDetails();

        foreach ($users as &$u) {
            if ($u['position'])   $u['position']   = str_replace(',', ', ', $u['position']);
            if ($u['department']) $u['department'] = str_replace(',', ', ', $u['department']);
            if ($u['role_name'])  $u['role_name']  = str_replace(',', ', ', $u['role_name']);
        }
        unset($u);   // $u stays a reference to the last user otherwise

        $roleModel             = new RoleModel();
        $positionModel         = new PositionModel();
        $unitModel             = new UnitModel();
        $employmentStatusModel = new EmploymentStatusModel();
        $invitationModel       = new InvitationModel();
        $twgAssignmentModel    = new \App\Models\TwgUnitAssignmentModel();
        $defaultWeightModel    = new DefaultFunctionWeightModel();

        $roles                 = $roleModel->where('name !=', 'Admin')->orderBy('name', 'ASC')->findAll();
        $positions             = $positionModel->orderBy('title', 'ASC')->findAll();
        $units                 = $unitModel->orderBy('name', 'ASC')->findAll();
        $employmentStatuses    = $employmentStatusModel->orderBy('name', 'ASC')->findAll();
        $invitations           = $invitationModel->getAllWithRoleNames();
        $twgUsers              = $twgAssignmentModel->getTwgUsers();
        $twgAssignmentsGrouped = $twgAssignmentModel->getAllAssignmentsGrouped();
        $twgUnitCoverageMeta   = $twgAssignmentModel->getUnitCoverageMetadata();
        $defaultWeights        = $defaultWeightModel->getAllWeightsKeyed();

        // Organisation structure (2026-11-07): position tree, each unit's head, doc-type rules and their preview.
        OrgStructure::reset();
        $org = OrgStructure::instance();
        $unitHeads = [];
        foreach ($org->units() as $unitRow) {
            $unitHeads[$unitRow['id']] = [
                'titles'  => array_map(fn($id) => $org->position($id)['title'] ?? '', $org->headPositionIds($unitRow['id'])),
                'linked'  => $unitRow['head_position_id'] !== null,
                'link_id' => $unitRow['head_position_id'],
                'holders' => array_map(fn($h) => trim($h['first_name'] . ' ' . $h['last_name']), $org->headsOf($unitRow['id'])),
            ];
        }
        $hasOpenCycle = (new DocumentFolderModel())->where('parent_folder_id IS NULL')->where('deleted_at IS NULL')->countAllResults() > 0;

        return view('accounts/index', [
            'positionTree'          => $this->flattenPositionTree($org),
            'unitHeads'             => $unitHeads,
            'hasOpenCycle'          => $hasOpenCycle,
            'docRules'              => $this->docRulesData($org, $roleModel, $employmentStatusModel),
            'docTypes'              => DocType::values(),
            'users'                 => $users,
            'roles'                 => $roles,
            'positions'             => $positions,
            'units'                 => $units,
            'employmentStatuses'    => $employmentStatuses,
            'invitations'           => $invitations,
            'activeTab'             => $activeTab,
            'twgUsers'              => $twgUsers,
            'twgAssignmentsGrouped' => $twgAssignmentsGrouped,
            'twgUnitCoverageMeta'   => $twgUnitCoverageMeta,
            'defaultWeights'        => $defaultWeights,
        ]);
    }

    /**
     * POST /account/send-invites - Bulk-invites a pasted list of email addresses,
     * skipping any that already have an account or an unexpired pending invite,
     * and queues an invite email (with signup link/token) for each valid one.
     */
    public function sendInvites() {
        return $this->tryOrFail(function() {
            $rawEmails = (string) $this->request->getPost('emails');
            $roleId    = $this->request->getPost('role_id');

            // The invite sets the person's role and department (2026-11-07). Employees and Supervisors need a
            // department; Admin, TWG and PMT accounts have none. A Supervisor becomes the head of that unit.
            $role = (new RoleModel())->find($roleId);
            if (!$role) return $this->respondError('Select a role.', 422);
            $org = OrgStructure::instance();
            $unitId = null;
            $note = '';
            if (OrgStructure::isRatedRole($role['name'])) {
                $unitId = (int) $this->request->getPost('unit_id');
                if (!$unitId || !$org->unit($unitId)) {
                    return $this->respondError('Select the department / unit these people will join.', 422);
                }
                if ($role['name'] === 'Supervisor') {
                    $heads = $org->headPositionIds($unitId);
                    if (!$heads) {
                        return $this->respondError('That department / unit has no head position yet. Link one in System Data (Departments & Units) first.', 422);
                    }
                    $holders = array_map(fn($h) => trim($h['first_name'] . ' ' . $h['last_name']), $org->headsOf($unitId));
                    if ($holders) $note = ' Note: ' . implode(', ', $holders) . ' already heads that unit, so they will be co-heads.';
                }
            }

            // Split by whitespace, commas, or semicolons and normalize
            $rawList = preg_split('/[\s,;]+/', trim($rawEmails));
            $cleaned = [];
            foreach ($rawList as $e) {
                $e = strtolower(trim($e));
                if ($e !== '' && filter_var($e, FILTER_VALIDATE_EMAIL)) {
                    $cleaned[] = $e;
                }
            }

            // Filter out initial duplicates pasted into the textarea
            $uniqueEmails = array_values(array_unique($cleaned));
            $validEmails = [];

            $userModel = new UserModel();
            $invitationModel = new InvitationModel();
            $now = date('Y-m-d H:i:s');

            foreach ($uniqueEmails as $email) {
                // 1. Check if they already have an account
                $hasAccount = $userModel->where('email', $email)->countAllResults() > 0;

                // 2. Check if they already have a pending, unexpired invitation
                $hasPendingInvite = $invitationModel->where('email', $email)
                                                    ->where('status', InvitationStatus::PENDING->value)
                                                    ->where('expires_at >', $now)
                                                    ->countAllResults() > 0;

                // Only add them if BOTH are false
                if (!$hasAccount && !$hasPendingInvite && !in_array($email, $validEmails, true)) {
                    $validEmails[] = $email;
                }
            }

            if (empty($validEmails)) {
                return $this->respondError('No valid or new email addresses found (they may already have accounts or pending invites).', 400);
            }

            $roleName  = $role['name'];
            $unitName  = $unitId ? ($org->unit($unitId)['name'] ?? null) : null;
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
                    'unit_id'    => $unitId,
                    'expires_at' => $expiresAt,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $createdInvitations[] = [
                    'id'              => $invitationModel->getInsertID(),
                    'email'           => $email,
                    'role_name'       => $roleName,
                    'unit_name'       => $unitName,
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

            $response = $this->respond([
                'status'      => 'success',
                'message'     => count($validEmails) . ' invitations queued for sending!' . $note,
                'invitations' => $createdInvitations,
            ]);

            // Send only the first few right away: they run after the response inside this same
            // PHP worker, and PHP-FPM kills a request after 60 s (request_terminate_timeout).
            // The rest stay queued for the every-minute cron (spark spms:routine, 30 per run).
            return dispatch_email_now($response, min(count($validEmails), 5));
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

    /** POST /account/invite/resend - Resends the invitation email using the existing token. */
    public function resendInvite() {
        try {
            $invitationModel = new \App\Models\InvitationModel();
            $invite = $invitationModel->find($this->request->getPost('id'));
            
            if (!$invite) {
                return $this->respondError('Invitation not found.');
            }

            $inviteLink = site_url("signup?token={$invite['token']}");

            queue_email(
                $invite['email'],
                'Invitation to join SPMS',
                render_email('invitation', ['link' => $inviteLink])
            );
            
            $response = $this->respond([
                'status' => 'success',
                'message' => 'Invitation resent to ' . $invite['email']
            ]);
            
            return dispatch_email_now($response, 1);
        } catch (\Exception $e) {
            return $this->respondError('Could not resend this invitation.');
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
        if (!$this->request->isAJAX()) {
            return redirect()->to(site_url('accounts'));
        }
        if (ENVIRONMENT !== 'development') return $this->response->setStatusCode(404);
        
        if (isset($_SESSION['_ci_previous_url']) && str_contains($_SESSION['_ci_previous_url'], 'account/process-queue')) {
            unset($_SESSION['_ci_previous_url']);
        }

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
            'sent'        => $result['sent'],
            'failed'      => $result['failed'],
            'remaining'   => $result['remaining']
        ]);
    }

    /** POST /account/toggle - Enables/disables a user account (blocks login without deleting data). Can't target yourself. */
    public function toggleStatus() {
        return $this->tryOrFail(function() {
            $targetId = $this->request->getPost('user_id');
            if ($targetId == session()->get('user_id')) {
                return $this->respondError('Cannot disable yourself.', 400);
            }

            $userModel = new UserModel();
            $user = $userModel->find($targetId);
            if (!$user) return $this->respondError('User not found.', 400);

            $newStatus = $user['is_active'] == 1 ? 0 : 1;
            $userModel->update($targetId, ['is_active' => $newStatus]);

            $action = ($newStatus === 1) ? 'ACCOUNT_ACTIVATED' : 'ACCOUNT_DEACTIVATED';
            audit_log($action, 'ACCOUNT', 'user', (int) $targetId, "User account {$user['email']} set to " . ($newStatus === 1 ? 'Active' : 'Disabled'));

            return $this->respond(['status' => 'success', 'is_active' => $newStatus]);
        });
    }

    /** POST /account/update-role - Replaces a user's role assignment with the selected one. Can't target yourself. */
    public function updateRole() {
        return $this->tryOrFail(function() {
            $targetId = $this->request->getPost('user_id');
            $roleId   = $this->request->getPost('role_id');

            if ($targetId == session()->get('user_id')) {
                return $this->respondError('Cannot change your own role.', 400);
            }

            $userModel = new UserModel();
            $roleModel = new RoleModel();

            $user = $userModel->find($targetId);
            $role = $roleModel->find($roleId);
            if (!$user || !$role) return $this->respondError('User or role not found.', 400);

            $db = \Config\Database::connect();
            $db->table('user_roles')->where('user_id', $targetId)->delete();
            $db->table('user_roles')->insert(['user_id' => $targetId, 'role_id' => $roleId]);

            audit_log('ROLE_CHANGED', 'ACCOUNT', 'user', (int) $targetId, "Role for {$user['email']} changed to {$role['name']}");

            return $this->respond(['status' => 'success', 'role_name' => $role['name']]);
        });
    }

    /** POST /account/twg-assignments/update - Updates assigned units/offices for a TWG member. */
    public function updateTwgAssignments() {
        return $this->tryOrFail(function() {
            $targetId = (int) $this->request->getPost('user_id');
            $unitIds  = $this->request->getPost('unit_ids') ?? [];

            if (!is_array($unitIds)) {
                $unitIds = array_filter(explode(',', (string) $unitIds));
            }

            $userModel = new UserModel();
            $targetUser = $userModel->find($targetId);
            if (!$targetUser) return $this->respondError('User not found.', 404);

            $twgAssignmentModel = new \App\Models\TwgUnitAssignmentModel();
            $twgAssignmentModel->assignUnits($targetId, $unitIds);

            $assigned = $twgAssignmentModel->getAssignmentsByUser($targetId);
            $assignedNames = array_column($assigned, 'unit_name');
            $assignedText = !empty($assignedNames) ? implode(', ', $assignedNames) : 'None';

            audit_log('TWG_ASSIGNMENTS_UPDATED', 'ACCOUNT', 'user', $targetId, "TWG department & unit assignments updated for {$targetUser['email']}: {$assignedText}");

            return $this->respond([
                'status'                => 'success',
                'message'               => 'Assigned departments & units updated successfully.',
                'assigned'              => $assigned,
                'allAssignmentsGrouped' => $twgAssignmentModel->getAllAssignmentsGrouped(),
                'unitCoverageMeta'      => $twgAssignmentModel->getUnitCoverageMetadata(),
            ]);
        });
    }

    /** POST /account (DELETE) - Permanently deletes a user account. Can't target yourself. */
    public function destroy() {
        return $this->tryOrFail(function() {
            $targetId = $this->request->getVar('user_id') ?? $this->request->getPost('user_id');
            if ($targetId == session()->get('user_id')) {
                return $this->respondError('Cannot delete yourself.', 400);
            }

            $userModel = new UserModel();
            $targetUser = $userModel->find($targetId);
            $targetEmail = $targetUser ? $targetUser['email'] : "ID #{$targetId}";
            $userModel->delete($targetId);

            audit_log('ACCOUNT_DELETED', 'ACCOUNT', 'user', (int) $targetId, "User account deleted: {$targetEmail}");

            return $this->respond(['status' => 'success']);
        });
    }

    // --- System Data CRUD: manages the lookup tables used by the invite form and profile/plantilla pickers. ---
    // Roles have no CRUD endpoints here at all - role names are hardcoded into access-control
    // checks throughout the app (nav visibility, route filters), so they're managed in the backend only.

    /** Parent position from a form field: null for top level; an error message when it is not usable. */
    private function parentPositionFrom($raw, ?int $selfId = null): array
    {
        $parentId = ($raw !== null && $raw !== '' && $raw !== '0') ? (int) $raw : null;
        if ($parentId === null) return [null, null];
        $org = OrgStructure::instance();
        if (!$org->position($parentId)) return [null, 'The parent position was not found.'];
        if ($selfId !== null && $org->wouldLoop($selfId, $parentId)) {
            return [null, 'A position cannot be placed under itself or under one of its own sub-positions.'];
        }
        return [$parentId, null];
    }

    /** POST /account/position/add - `parent_id` places the position in the tree (a position with children is a head position). */
    public function addPosition() {
        $title = trim($this->request->getPost('title') ?? '');
        $isTeaching = $this->request->getPost('is_teaching') ? 1 : 0;

        if (empty($title)) return $this->respondError('Position title is required.', 422);
        [$parentId, $err] = $this->parentPositionFrom($this->request->getPost('parent_id'));
        if ($err) return $this->respondError($err, 422);

        $positionModel = new PositionModel();
        $existing = $positionModel->where('LOWER(title)', strtolower($title))->first();
        if ($existing) {
            return $this->respondError("A position with the title '{$title}' already exists.", 422);
        }

        $id = $positionModel->insert([
            'title'       => $title,
            'is_teaching' => $isTeaching,
            'parent_id'   => $parentId,
            'created_at'  => date('Y-m-d H:i:s')
        ]);
        OrgStructure::reset();
        $parentTitle = $parentId ? (OrgStructure::instance()->position($parentId)['title'] ?? '') : null;
        audit_log('POSITION_CREATED', 'ACCOUNT', 'position', (int) $id, "Created position: {$title}" . ($parentTitle ? " (under {$parentTitle})" : ' (top level)'));
        return $this->respond(['status' => 'success', 'item' => ['id' => $id, 'title' => $title, 'is_teaching' => $isTeaching, 'parent_id' => $parentId]]);
    }

    /** POST /account/position/update - Renames a position, sets teaching, or moves it in the tree. */
    public function updatePosition() {
        $id = (int) $this->request->getPost('id');
        $title = trim($this->request->getPost('title') ?? '');
        $isTeaching = $this->request->getPost('is_teaching') ? 1 : 0;

        $positionModel = new PositionModel();
        $pos = $positionModel->find($id);
        if (!$pos) return $this->respondError('Position not found.', 422);
        if (empty($title)) return $this->respondError('Position title is required.', 422);
        if ($positionModel->where('LOWER(title)', strtolower($title))->where('id !=', $id)->first()) {
            return $this->respondError("A position with the title '{$title}' already exists.", 422);
        }

        $isGeneralRoot = (int) ($pos['is_general_root'] ?? 0) === 1;
        [$parentId, $err] = $isGeneralRoot ? [null, null] : $this->parentPositionFrom($this->request->getPost('parent_id'), $id);
        if ($err) return $this->respondError($err, 422);

        // A position that heads a unit (linked) cannot move under General: General positions never head anything.
        $org = OrgStructure::instance();
        if ($parentId !== null && $org->isGeneral($parentId)
            && \Config\Database::connect()->table('units')->where('head_position_id', $id)->countAllResults() > 0) {
            return $this->respondError('This position heads a department / unit, so it cannot be a General position. Change that unit\'s head position first.', 422);
        }

        $oldParent = $pos['parent_id'] ? ($org->position((int) $pos['parent_id'])['title'] ?? '') : 'top level';
        $positionModel->update($id, ['title' => $title, 'is_teaching' => $isTeaching, 'parent_id' => $parentId,
                                     'updated_at' => date('Y-m-d H:i:s')]);
        OrgStructure::reset();
        $newParent = $parentId ? (OrgStructure::instance()->position($parentId)['title'] ?? '') : 'top level';
        audit_log('POSITION_UPDATED', 'ACCOUNT', 'position', $id, "Updated position {$title}" . ($oldParent !== $newParent ? " (moved from {$oldParent} to {$newParent})" : ''));
        return $this->respond(['status' => 'success']);
    }

    /**
     * POST /account/position/delete - Only an unused position can be deleted. plantillas.position_id is
     * ON DELETE CASCADE, so deleting a position that anyone holds (or held) would silently delete those
     * assignments and their history; rename or move it instead.
     */
    public function deletePosition() {
        $id = (int) $this->request->getPost('id');
        $positionModel = new PositionModel();
        $pos = $positionModel->find($id);
        if (!$pos) return $this->respondError('Position not found.', 422);
        $db = \Config\Database::connect();
        if ((int) ($pos['is_general_root'] ?? 0) === 1) {
            return $this->respondError('The General group cannot be deleted.', 422);
        }
        if ($positionModel->where('parent_id', $id)->countAllResults() > 0) {
            return $this->respondError('This position has positions under it. Move or delete those first.', 422);
        }
        $used = $db->table('plantillas')->where('position_id', $id)->countAllResults();
        if ($used > 0) {
            return $this->respondError("This position is used by {$used} assignment(s), including past ones kept as history. Rename or move it instead of deleting it.", 422);
        }
        if ($db->table('units')->where('head_position_id', $id)->countAllResults() > 0) {
            return $this->respondError('A department / unit uses this as its head position. Change that unit first.', 422);
        }
        $positionModel->delete($id);
        OrgStructure::reset();
        audit_log('POSITION_DELETED', 'ACCOUNT', 'position', $id, "Deleted position: {$pos['title']}");
        return $this->respond(['status' => 'success']);
    }

    /** POST /account/unit/add - `parent_id` lets units nest (e.g. a college under a campus). */
    public function addUnit() {
        $name = trim($this->request->getPost('name') ?? '');
        $parentId = $this->request->getPost('parent_id') ?: null;

        if (empty($name)) return $this->respondError('Department / unit name is required.', 422);
        [$headPositionId, $err] = $this->headPositionFrom($this->request->getPost('head_position_id'));
        if ($err) return $this->respondError($err, 422);

        $unitModel = new UnitModel();
        $existing = $unitModel->where('LOWER(name)', strtolower($name))->first();
        if ($existing) {
            return $this->respondError("A department / unit named '{$name}' already exists.", 422);
        }

        $id = $unitModel->insert([
            'name' => $name,
            'parent_id' => $parentId,
            'head_position_id' => $headPositionId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        OrgStructure::reset();

        $parentName = null;
        if ($parentId) {
            $parent = $unitModel->find($parentId);
            $parentName = $parent['name'] ?? null;
        }

        audit_log('UNIT_CREATED', 'ACCOUNT', 'unit', (int) $id, "Created new department / unit: {$name}" . ($parentName ? " (under {$parentName})" : ""));

        return $this->respond(['status' => 'success', 'item' => ['id' => $id, 'name' => $name, 'parent_id' => $parentId, 'parent_name' => $parentName]]);
    }

    /**
     * POST /account/unit/update - Edits a unit's name or changes its parent_id (transferring
     * a department to another college/unit). Prevents cyclic loops (a unit cannot be its own parent
     * or parented to its own descendants).
     */
    public function updateUnit() {
        $unitId = (int) $this->request->getPost('id');
        $name = trim($this->request->getPost('name') ?? '');
        $parentId = $this->request->getPost('parent_id');
        $parentId = ($parentId !== '' && $parentId !== null && $parentId !== '0') ? (int) $parentId : null;
        [$headPositionId, $err] = $this->headPositionFrom($this->request->getPost('head_position_id'));
        if ($err) return $this->respondError($err, 422);

        if (!$unitId) return $this->respondError('Invalid department / unit ID.', 422);
        if (empty($name)) return $this->respondError('Department / unit name is required.', 422);

        $unitModel = new UnitModel();
        $unit = $unitModel->find($unitId);
        if (!$unit) return $this->respondError('Department / unit not found.', 422);

        // Prevent duplicate unit names
        $existing = $unitModel->where('LOWER(name)', strtolower($name))->where('id !=', $unitId)->first();
        if ($existing) {
            return $this->respondError("A department / unit named '{$name}' already exists.", 422);
        }

        // Prevent self-parenting
        if ($parentId && $parentId === $unitId) {
            return $this->respondError('A department / unit cannot be its own parent.', 422);
        }

        // Prevent circular parenting (cannot set parent to one of its own descendants)
        if ($parentId) {
            $descendants = $unitModel->getDescendantIds([$unitId]);
            if (in_array($parentId, $descendants, true)) {
                return $this->respondError('Cannot set parent to a descendant department / unit.', 422);
            }
        }

        $oldParentId = $unit['parent_id'] ? (int) $unit['parent_id'] : null;
        $oldParentName = $oldParentId ? ($unitModel->find($oldParentId)['name'] ?? "Unit #{$oldParentId}") : 'Top Level Node';
        $newParentName = $parentId ? ($unitModel->find($parentId)['name'] ?? "Unit #{$parentId}") : 'Top Level Node';

        $unitModel->update($unitId, [
            'name'             => $name,
            'parent_id'        => $parentId,
            'head_position_id' => $headPositionId,
            'updated_at'       => date('Y-m-d H:i:s')
        ]);
        OrgStructure::reset();

        $logMsg = "Updated unit #{$unitId} ({$name})";
        if ($oldParentId !== $parentId) {
            $logMsg = "Transferred unit {$name} from {$oldParentName} to {$newParentName}";
        }
        $oldLink = !empty($unit['head_position_id']) ? (int) $unit['head_position_id'] : null;
        if ($oldLink !== $headPositionId) {
            $org = OrgStructure::instance();
            $logMsg .= '; head position ' . ($headPositionId ? 'linked to ' . ($org->position($headPositionId)['title'] ?? '') : 'now inherited from the parent unit');
        }
        audit_log('UNIT_UPDATED', 'ACCOUNT', 'unit', $unitId, $logMsg);

        return $this->respond([
            'status' => 'success',
            'item'   => [
                'id'          => $unitId,
                'name'        => $name,
                'parent_id'   => $parentId,
                'parent_name' => $parentId ? $newParentName : null
            ]
        ]);
    }

    /** Head position for a unit from a form field: null = inherit from the parent unit. */
    private function headPositionFrom($raw): array
    {
        $id = ($raw !== null && $raw !== '' && $raw !== '0') ? (int) $raw : null;
        if ($id === null) return [null, null];
        $org = OrgStructure::instance();
        if (!$org->position($id)) return [null, 'The head position was not found.'];
        if ($org->isGeneral($id)) return [null, 'A General position cannot head a department / unit.'];
        return [$id, null];
    }

    /**
     * POST /account/unit/delete - Only an unused unit can be deleted. plantillas.unit_id is ON DELETE
     * CASCADE, so deleting a unit anyone is (or was) assigned to would silently delete those assignments
     * and their history; sub-units must be moved or deleted first.
     */
    public function deleteUnit() {
        try {
            $unitModel = new UnitModel();
            $unitId = $this->request->getPost('id');
            $unit = $unitModel->find($unitId);
            $unitName = $unit['name'] ?? "ID #{$unitId}";
            $db = \Config\Database::connect();
            if ($unitModel->where('parent_id', $unitId)->countAllResults() > 0) {
                return $this->respondError('This department / unit has sub-units. Move or delete them first.', 422);
            }
            $used = $db->table('plantillas')->where('unit_id', $unitId)->countAllResults();
            if ($used > 0) {
                return $this->respondError("{$used} assignment(s) use this department / unit, including past ones kept as history. Rename it instead of deleting it.", 422);
            }
            if ($db->table('invitations')->where('unit_id', $unitId)->where('status', \App\Enums\InvitationStatus::PENDING->value)->countAllResults() > 0) {
                return $this->respondError('A pending invitation is for this department / unit. Cancel it first.', 422);
            }

            $unitModel->delete($unitId);
            OrgStructure::reset();

            audit_log('UNIT_DELETED', 'ACCOUNT', 'unit', (int) $unitId, "Deleted department / unit: {$unitName}");

            return $this->respond(['status' => 'success']);
        } catch (\Exception $e) {
            return $this->respondError('Could not delete this department / unit.', 422);
        }
    }

    /** POST /account/employment-status/add */
    public function addEmploymentStatus() {
        $name = trim($this->request->getPost('name') ?? '');
        if (empty($name)) return $this->respondError('Employment status name is required.');

        $model = new EmploymentStatusModel();
        $existing = $model->where('LOWER(name)', strtolower($name))->first();
        if ($existing) {
            return $this->respondError("An employment status named '{$name}' already exists.");
        }

        $id = $model->insert([
            'name'       => $name,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        audit_log('EMPLOYMENT_STATUS_CREATED', 'ACCOUNT', 'employment_status', (int) $id, "Created employment status: {$name}");

        return $this->respond(['status' => 'success', 'item' => ['id' => $id, 'name' => $name]]);
    }

    /** POST /account/employment-status/delete */
    public function deleteEmploymentStatus() {
        try {
            $id = $this->request->getPost('id');
            $model = new EmploymentStatusModel();
            $status = $model->find($id);
            $name = $status['name'] ?? "ID #{$id}";

            $model->delete($id);

            audit_log('EMPLOYMENT_STATUS_DELETED', 'ACCOUNT', 'employment_status', (int) $id, "Deleted employment status: {$name}");

            return $this->respond(['status' => 'success']);
        } catch (\Exception $e) {
            return $this->respondError('Could not delete this employment status.');
        }
    }

    /**
     * POST /account/doc-rules/update - Which doc types each role and each employment status allows.
     * A person may use the doc types allowed by both. "No limit" stores NULL. Nobody's current doc type is
     * changed here: people the new rules don't fit are flagged in the User Directory for the admin to fix.
     */
    public function updateDocRules() {
        return $this->tryOrFail(function() {
            $db = \Config\Database::connect();
            $read = function (string $kind, $id) {
                if ($this->request->getPost("{$kind}_nolimit")[$id] ?? null) return null;
                $types = (array) ($this->request->getPost("{$kind}_types")[$id] ?? []);
                return DocType::toCsv($types);
            };
            $changes = [];
            foreach ($db->table('roles')->get()->getResultArray() as $r) {
                if (!array_key_exists($r['id'], (array) $this->request->getPost('role_present'))) continue;
                $csv = $read('role', $r['id']);
                if ($csv !== $r['allowed_doc_types']) {
                    $db->table('roles')->where('id', $r['id'])->update(['allowed_doc_types' => $csv]);
                    $changes[] = "{$r['name']}: " . ($csv === null ? 'no limit' : ($csv === '' ? 'nothing' : $csv));
                }
            }
            foreach ($db->table('employment_statuses')->get()->getResultArray() as $st) {
                if (!array_key_exists($st['id'], (array) $this->request->getPost('status_present'))) continue;
                $csv = $read('status', $st['id']);
                if ($csv !== $st['allowed_doc_types']) {
                    $db->table('employment_statuses')->where('id', $st['id'])->update(['allowed_doc_types' => $csv]);
                    $changes[] = "{$st['name']}: " . ($csv === null ? 'no limit' : ($csv === '' ? 'nothing' : $csv));
                }
            }
            OrgStructure::reset();
            if ($changes) {
                audit_log('DOC_TYPE_RULES_UPDATED', 'ACCOUNT', 'system', 0, 'Doc-type rules changed: ' . implode('; ', $changes));
            }
            return $this->respond(['status' => 'success', 'message' => $changes ? 'Doc-type rules saved.' : 'No changes.']);
        });
    }

    /** Positions in tree order (parents before children, alphabetical), General last, with level and kind. */
    private function flattenPositionTree(OrgStructure $org): array
    {
        $all = $org->positions();
        $children = [];
        foreach ($all as $p) {
            $children[$p['parent_id'] !== null && isset($all[$p['parent_id']]) ? $p['parent_id'] : 0][] = $p;
        }
        foreach ($children as &$list) {
            usort($list, fn($a, $b) => [$a['is_general_root'], strcasecmp($a['title'], $b['title'])] <=> [$b['is_general_root'], 0]);
        }
        unset($list);
        $out = [];
        $walk = function ($parent, $level) use (&$walk, &$out, $children, $org) {
            foreach ($children[$parent] ?? [] as $p) {
                $p['level'] = $level;
                $p['is_head'] = $org->isHeadPosition($p['id']);
                $p['is_general'] = $org->isGeneral($p['id']);
                $p['has_children'] = !empty($children[$p['id']]);
                $out[] = $p;
                $walk($p['id'], $level + 1);
            }
        };
        $walk(0, 0);
        return $out;
    }

    /** Rules per role / status and the preview of every role x status combination (with how many people it affects). */
    private function docRulesData(OrgStructure $org, RoleModel $roleModel, EmploymentStatusModel $statusModel): array
    {
        $db = \Config\Database::connect();
        $roles = array_values(array_filter($roleModel->orderBy('name', 'ASC')->findAll(), fn($r) => in_array($r['name'], ['Employee', 'Supervisor', 'Admin'], true)));
        $statuses = $statusModel->orderBy('name', 'ASC')->findAll();
        foreach ($roles as &$r) $r['allowed'] = DocType::fromCsv($r['allowed_doc_types']);
        foreach ($statuses as &$st) $st['allowed'] = DocType::fromCsv($st['allowed_doc_types']);
        unset($r, $st);

        $people = $db->table('users u')->select('u.id, u.doc_type, u.employment_status, r.name as role')
            ->join('user_roles ur', 'ur.user_id = u.id', 'left')->join('roles r', 'r.id = ur.role_id', 'left')
            ->where('u.is_active', 1)->get()->getResultArray();
        $preview = [];
        foreach (['Employee', 'Supervisor'] as $role) {
            foreach ($statuses as $st) {
                $allowed = $org->allowedDocTypes($role, $st['name']);
                $inCombo = array_filter($people, fn($p) => $p['role'] === $role && strcasecmp((string) $p['employment_status'], $st['name']) === 0);
                $misfits = array_filter($inCombo, fn($p) => !in_array(strtoupper((string) $p['doc_type']), $allowed, true));
                $byType = [];
                foreach ($inCombo as $p) {
                    $t = strtoupper((string) $p['doc_type']) ?: 'NONE';
                    $byType[$t] = ($byType[$t] ?? 0) + 1;
                }
                $preview[] = ['role' => $role, 'status' => $st['name'], 'allowed' => $allowed,
                              'people' => count($inCombo), 'misfits' => count($misfits), 'by_type' => $byType];
            }
        }
        return ['roles' => $roles, 'statuses' => $statuses, 'preview' => $preview];
    }

    /**
     * POST /account/update-default-weights
     * Updates default function percentage weights for OPCR, DPCR, and IPCR.
     */
    public function updateDefaultWeights() {
        return $this->tryOrFail(function() {
            $weightsData = $this->request->getPost('weights');
            if (empty($weightsData) || !is_array($weightsData)) {
                return $this->respondError('Invalid weights data provided.');
            }

            $model = new DefaultFunctionWeightModel();
            $allowedTypes = ['OPCR', 'DPCR', 'IPCR'];
            $updated = [];

            foreach ($weightsData as $docType => $row) {
                $docType = strtoupper(trim((string)$docType));
                if (!in_array($docType, $allowedTypes, true)) {
                    continue;
                }

                $core = isset($row['core_weight']) ? (float)$row['core_weight'] : 0.0;
                $strat = isset($row['strategic_weight']) ? (float)$row['strategic_weight'] : 0.0;
                $supp = isset($row['support_weight']) ? (float)$row['support_weight'] : 0.0;
                $desc = isset($row['description']) ? trim((string)$row['description']) : null;

                if ($core < 0 || $core > 100 || $strat < 0 || $strat > 100 || $supp < 0 || $supp > 100) {
                    return $this->respondError("Percentage weights for {$docType} must each be between 0% and 100%.");
                }

                $total = round($core + $strat + $supp, 2);
                if (abs($total - 100.0) > 0.01) {
                    return $this->respondError("The weights for {$docType} must total exactly 100%. Current sum: {$total}%.");
                }

                $existing = $model->where('doc_type', $docType)->first();
                $payload = [
                    'doc_type'         => $docType,
                    'core_weight'      => $core,
                    'strategic_weight' => $strat,
                    'support_weight'   => $supp,
                    'updated_at'       => date('Y-m-d H:i:s'),
                ];
                if ($desc !== null) {
                    $payload['description'] = $desc;
                }

                if ($existing) {
                    $model->update($existing['id'], $payload);
                    $updated[$docType] = array_merge($existing, $payload);
                } else {
                    $payload['created_at'] = date('Y-m-d H:i:s');
                    $newId = $model->insert($payload);
                    $payload['id'] = $newId;
                    $updated[$docType] = $payload;
                }
            }

            audit_log('FUNCTION_WEIGHTS_UPDATED', 'ACCOUNT', 'system', 0, "Updated default function percentage weights for OPCR, DPCR, and IPCR");

            return $this->respond([
                'status'  => 'success',
                'message' => 'Default percentage weights updated successfully.',
                'weights' => $model->getAllWeightsKeyed()
            ]);
        });
    }
}