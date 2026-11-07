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
        // Default weights now live in System Data > Doc Type Rules; old /accounts/weights links open it there.
        $openDocRules = $tab === 'weights';
        if ($openDocRules) $tab = 'system';
        $validTabs = ['directory', 'create', 'invitations', 'system', 'twg'];
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

        // Per person: current assignments and what needs the admin's attention (shown as flags in the directory).
        $db = \Config\Database::connect();
        $assignRows = $db->table('plantillas p')->select('p.id, p.user_id, p.unit_id, p.position_id, un.name as unit_name, pos.title as position_title, p.started_at')
            ->join('units un', 'un.id = p.unit_id', 'left')->join('positions pos', 'pos.id = p.position_id', 'left')
            ->where('p.ended_at IS NULL')->orderBy('p.id', 'ASC')->get()->getResultArray();
        $assignByUser = [];
        foreach ($assignRows as $a) {
            $a['is_head'] = $a['unit_id'] && $a['position_id'] && $org->isHeadOf((int) $a['unit_id'], (int) $a['position_id']);
            $a['co_heads'] = $a['is_head'] ? count($org->headsOf((int) $a['unit_id'])) : 0;
            $assignByUser[(int) $a['user_id']][] = $a;
        }
        $flagCount = 0;
        foreach ($users as &$usr) {
            $usr['assignments'] = $assignByUser[(int) $usr['id']] ?? [];
            $usr['flags'] = [];
            $roleName = explode(', ', (string) ($usr['role_name'] ?? ''))[0] ?: null;
            if (OrgStructure::isRatedRole($roleName) && (int) $usr['is_active'] === 1) {
                $allowed = $org->allowedDocTypes($roleName, $usr['employment_status']);
                $usr['allowed_docs'] = $allowed;
                if (!$usr['assignments']) $usr['flags'][] = 'No department assignment';
                if (!$org->isStatusOpenTo($roleName, $usr['employment_status'])) {
                    $usr['flags'][] = "Status {$usr['employment_status']} is not open to {$roleName}";
                }
                if (!in_array(strtoupper((string) $usr['doc_type']), $allowed, true)) {
                    $usr['flags'][] = ($usr['doc_type'] ? 'Form type ' . $usr['doc_type'] . ' not allowed' : 'Form type not set') . ($allowed ? ' (allowed: ' . implode(', ', $allowed) . ')' : ' (nothing allowed)');
                }
                foreach ($usr['assignments'] as $a) {
                    if ($a['co_heads'] > 1) $usr['flags'][] = 'Co-head of ' . $a['unit_name'];
                }
                // Role and position should agree: a Supervisor heads a unit, an Employee does not.
                $headAssignments = array_filter($usr['assignments'], fn($a) => $a['is_head']);
                if ($roleName === 'Supervisor' && $usr['assignments'] && !$headAssignments) {
                    $usr['flags'][] = 'Supervisor without a head position';
                } elseif ($roleName === 'Employee' && $headAssignments) {
                    $usr['flags'][] = 'Employee holding a head position (' . implode(', ', array_map(fn($a) => $a['position_title'] . ' · ' . $a['unit_name'], $headAssignments)) . ')';
                }
                if ($usr['flags']) $flagCount++;
            }
        }
        unset($usr);

        // What the Manage modal needs: which positions fit each unit (heads / members + General).
        $unitFit = [];
        foreach ($org->units() as $unitRow) {
            $unitFit[$unitRow['id']] = ['heads' => $org->headPositionIds($unitRow['id']), 'members' => $org->memberPositionIds($unitRow['id'])];
        }

        return view('accounts/index', [
            'flagCount'             => $flagCount,
            'unitFit'               => $unitFit,
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
            'openDocRules'          => $openDocRules,
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
            $db = \Config\Database::connect();
            $db->transStart();
            $userModel->update($targetId, ['is_active' => $newStatus]);
            $today = date('Y-m-d');
            $note = '';
            if ($newStatus === 0) {
                // Disabled: their assignments end today (kept as history, not deleted).
                $n = $db->table('plantillas')->where('user_id', $targetId)->where('ended_at IS NULL')->countAllResults();
                $db->table('plantillas')->where('user_id', $targetId)->where('ended_at IS NULL')->update(['ended_at' => $today, 'updated_at' => date('Y-m-d H:i:s')]);
                if ($n) $note = "; {$n} assignment(s) ended";
            } elseif ($db->table('plantillas')->where('user_id', $targetId)->where('ended_at IS NULL')->countAllResults() === 0) {
                // Re-enabled: start again the assignments they had when they were disabled (the last ones ended).
                $last = $db->table('plantillas')->selectMax('ended_at', 'd')->where('user_id', $targetId)->get()->getRowArray()['d'] ?? null;
                $rows = $last ? $db->table('plantillas')->where('user_id', $targetId)->where('ended_at', $last)->get()->getResultArray() : [];
                foreach ($rows as $r) {
                    $db->table('plantillas')->insert(['user_id' => $targetId, 'unit_id' => $r['unit_id'], 'position_id' => $r['position_id'],
                        'employment_status' => $user['employment_status'] ?? $r['employment_status'], 'started_at' => $today, 'created_at' => date('Y-m-d H:i:s')]);
                }
                if ($rows) $note = '; ' . count($rows) . ' assignment(s) restored';
            }
            $db->transComplete();
            OrgStructure::reset();

            $action = ($newStatus === 1) ? 'ACCOUNT_ACTIVATED' : 'ACCOUNT_DEACTIVATED';
            audit_log($action, 'ACCOUNT', 'user', (int) $targetId, "User account {$user['email']} set to " . ($newStatus === 1 ? 'Active' : 'Disabled') . $note);

            return $this->respond(['status' => 'success', 'is_active' => $newStatus]);
        });
    }

    /** POST /account/update-role - Replaces a user's role assignment with the selected one. Can't target yourself. */
    public function updateRole() {
        return $this->tryOrFail(function() {
            $targetId = (int) $this->request->getPost('user_id');
            $roleId   = $this->request->getPost('role_id');

            if ($targetId == session()->get('user_id')) {
                return $this->respondError('Cannot change your own role.', 400);
            }

            $user = (new UserModel())->find($targetId);
            $role = (new RoleModel())->find($roleId);
            if (!$user || !$role) return $this->respondError('User or role not found.', 400);

            $org = OrgStructure::instance();
            $oldRole = $org->roleOf($targetId);
            $newRole = $role['name'];
            if ($oldRole === $newRole) return $this->respond(['status' => 'success', 'role_name' => $newRole]);

            $db = \Config\Database::connect();
            $name = trim($user['first_name'] . ' ' . $user['last_name']);
            $wasRated = OrgStructure::isRatedRole($oldRole);
            $isRated  = OrgStructure::isRatedRole($newRole);
            $label = fn($a) => ($a['position_title'] ?? '?') . ' · ' . ($a['unit_name'] ?? '?');
            $active = $this->assignmentRows($targetId, true);
            $restorable = (!$wasRated && $isRated && !$active) ? $this->lastEndedAssignments($targetId) : [];
            $twgUnits = $oldRole === 'TWG'
                ? array_column((new \App\Models\TwgUnitAssignmentModel())->getAssignmentsByUser($targetId), 'unit_name') : [];

            // Step 1: say what will happen and ask (the page sends the request again with confirm=1).
            if (!$this->request->getPost('confirm')) {
                $lines = ["Change {$name}'s role from " . ($oldRole ?: 'none') . " to {$newRole}? It takes effect on their next page."];
                if ($wasRated && !$isRated) {
                    if ($active) {
                        $lines[] = 'Their department assignment' . (count($active) > 1 ? 's end' : ' ends') . ' today and stay' . (count($active) > 1 ? '' : 's') . ' in their history: ' . implode('; ', array_map($label, $active)) . '.';
                        foreach ($active as $a) {
                            if ($a['unit_id'] && $a['position_id'] && $org->isHeadOf((int) $a['unit_id'], (int) $a['position_id'])
                                && !$org->headsOf((int) $a['unit_id'], $targetId)) {
                                $lines[] = "{$a['unit_name']} will have no head until you assign one.";
                            }
                        }
                    }
                    $inProgress = $db->table('document_folders')->where('user_id', $targetId)->where('deleted_at IS NULL')
                        ->whereNotIn('status', [\App\Enums\FolderStatus::EVAL_APPROVED->value])->countAllResults();
                    if ($inProgress) $lines[] = ($inProgress === 1 ? 'Their unfinished paper stays' : "Their {$inProgress} unfinished papers stay") . ' on their account as they are.';
                    $lines[] = "Their papers and ratings are kept. Switching back later offers to restore these assignments.";
                } elseif (!$wasRated && $isRated) {
                    if ($twgUnits) $lines[] = 'Their TWG units are cleared: ' . implode(', ', $twgUnits) . '.';
                    if (!$org->isStatusOpenTo($newRole, $user['employment_status'])) {
                        $lines[] = "Their status {$user['employment_status']} is not open to {$newRole}; change it in Manage.";
                    }
                    if (!$restorable) $lines[] = 'Next, add their department and position in Manage (it opens after this).';
                } elseif ($wasRated && $isRated) {
                    $headOf = array_values(array_filter($active, fn($a) => $a['unit_id'] && $a['position_id'] && $org->isHeadOf((int) $a['unit_id'], (int) $a['position_id'])));
                    if ($newRole === 'Supervisor' && !$headOf) {
                        $lines[] = 'They hold no head position, so they will not head any unit until you change their assignment in Manage (it opens after this).';
                    } elseif ($newRole === 'Employee' && $headOf) {
                        $lines[] = 'They still hold ' . implode('; ', array_map($label, $headOf)) . ', a head position: change it in Manage (it opens after this), or they keep counting as its head.';
                    }
                    if (!$org->isStatusOpenTo($newRole, $user['employment_status'])) {
                        $lines[] = "Their status {$user['employment_status']} is not open to {$newRole}; they will be flagged until you change it in Manage.";
                    }
                    $allowed = $org->allowedDocTypes($newRole, $user['employment_status']);
                    if ($user['doc_type'] && !in_array(strtoupper($user['doc_type']), $allowed, true)) {
                        $lines[] = "Their form type {$user['doc_type']} does not fit {$newRole}" . ($allowed ? ' (allowed: ' . implode(', ', $allowed) . ')' : '') . '; they will be flagged until you change it.';
                    }
                }
                $offer = array_map(function ($a) use ($org, $targetId, $label) {
                    $text = $label($a);
                    if ($org->isHeadOf((int) $a['unit_id'], (int) $a['position_id'])) {
                        $others = array_map(fn($h) => trim($h['first_name'] . ' ' . $h['last_name']), $org->headsOf((int) $a['unit_id'], $targetId));
                        if ($others) $text .= ' (now held by ' . implode(', ', $others) . ': they would be co-heads)';
                    }
                    return $text;
                }, $restorable);
                return $this->respond(['status' => 'confirm', 'message' => implode("\n\n", $lines), 'restore' => $offer]);
            }

            // Step 2: apply.
            $today = date('Y-m-d');
            $now = date('Y-m-d H:i:s');
            $notes = [];
            $db->transStart();
            $db->table('user_roles')->where('user_id', $targetId)->delete();
            $db->table('user_roles')->insert(['user_id' => $targetId, 'role_id' => $roleId]);
            if ($wasRated && !$isRated && $active) {
                $db->table('plantillas')->where('user_id', $targetId)->where('ended_at IS NULL')->update(['ended_at' => $today, 'updated_at' => $now]);
                $notes[] = (count($active) === 1 ? 'Assignment ended: ' : count($active) . ' assignments ended: ') . implode('; ', array_map($label, $active));
            }
            if ($twgUnits && $newRole !== 'TWG') {
                (new \App\Models\TwgUnitAssignmentModel())->assignUnits($targetId, []);
                $notes[] = 'TWG units cleared: ' . implode(', ', $twgUnits);
            }
            if ($restorable && $this->request->getPost('restore')) {
                foreach ($restorable as $a) {
                    $db->table('plantillas')->insert(['user_id' => $targetId, 'unit_id' => $a['unit_id'], 'position_id' => $a['position_id'],
                        'employment_status' => $user['employment_status'] ?? $a['employment_status'], 'started_at' => $today, 'created_at' => $now]);
                }
                $notes[] = (count($restorable) === 1 ? 'Assignment restored: ' : count($restorable) . ' assignments restored: ') . implode('; ', array_map($label, $restorable));
            }
            $db->transComplete();
            OrgStructure::reset();
            $org = OrgStructure::instance();

            // Open Manage when the new role still needs a department, or the position does not fit the role.
            $openManage = false;
            if ($isRated) {
                $heads = $org->headedUnitIds($targetId);
                $openManage = !$org->assignmentsOf($targetId) || ($newRole === 'Supervisor' && !$heads) || ($newRole === 'Employee' && $heads);
            }
            audit_log('ROLE_CHANGED', 'ACCOUNT', 'user', $targetId, "Role for {$user['email']} changed from " . ($oldRole ?: 'none') . " to {$newRole}" . ($notes ? ' (' . implode('; ', $notes) . ')' : ''));

            return $this->respond([
                'status'      => 'success',
                'role_name'   => $newRole,
                'message'     => "{$name} is now {$newRole}." . ($notes ? ' ' . implode('. ', $notes) . '.' : ''),
                'reload'      => $wasRated !== $isRated || $openManage,
                'open_manage' => $openManage,
            ]);
        });
    }

    /** A person's assignments with unit and position names: current ones, or all. */
    private function assignmentRows(int $userId, bool $currentOnly): array
    {
        $b = \Config\Database::connect()->table('plantillas p')
            ->select('p.id, p.unit_id, p.position_id, p.employment_status, p.ended_at, un.name as unit_name, pos.title as position_title')
            ->join('units un', 'un.id = p.unit_id', 'left')->join('positions pos', 'pos.id = p.position_id', 'left')
            ->where('p.user_id', $userId);
        if ($currentOnly) $b->where('p.ended_at IS NULL');
        return $b->orderBy('p.id', 'ASC')->get()->getResultArray();
    }

    /** The assignments that ended last (e.g. when the person was switched to TWG), whose unit and position still exist. */
    private function lastEndedAssignments(int $userId): array
    {
        $rows = array_filter($this->assignmentRows($userId, false), fn($a) => $a['ended_at'] !== null);
        if (!$rows) return [];
        $last = max(array_column($rows, 'ended_at'));
        return array_values(array_filter($rows, fn($a) => $a['ended_at'] === $last && $a['unit_name'] !== null && $a['position_title'] !== null));
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
            if (!$targetUser) return $this->respondError('User not found.', 404);
            $targetEmail = $targetUser['email'];

            // Only an account without rating history can be deleted. Deleting removes the user row, and the
            // folders, routings, comments and evidence that point to it would be left without an owner.
            $db = \Config\Database::connect();
            $history = [
                'folders'              => $db->table('document_folders')->where('user_id', $targetId)->countAllResults(),
                'reviews'              => $db->table('evaluation_routings')->where('evaluator_id', $targetId)->countAllResults(),
                'comments'             => $db->table('document_comments')->where('user_id', $targetId)->countAllResults(),
                'evidence files'       => $db->table('document_attachments')->where('uploaded_by', $targetId)->countAllResults(),
            ];
            $history = array_filter($history);
            if ($history) {
                $what = implode(', ', array_map(fn($k, $v) => "{$v} {$k}", array_keys($history), $history));
                return $this->respondError("This account has rating records ({$what}), so it can't be deleted without losing them. Disable it instead.", 422);
            }
            $userModel->delete($targetId);

            audit_log('ACCOUNT_DELETED', 'ACCOUNT', 'user', (int) $targetId, "User account deleted: {$targetEmail}");

            return $this->respond(['status' => 'success']);
        });
    }

    /** Target user for the assignment / details endpoints: exists, not yourself, and a rated role. */
    private function managedUser(): array
    {
        $userId = (int) $this->request->getPost('user_id');
        $user = (new UserModel())->find($userId);
        if (!$user) return [null, null, 'User not found.'];
        $role = OrgStructure::instance()->roleOf($userId);
        if (!OrgStructure::isRatedRole($role)) return [null, null, 'Admin, TWG and PMT accounts have no department assignment or form type.'];
        return [$user, $role, null];
    }

    /** POST /account/user/assignment/add - Adds a unit + position assignment (any position: the admin decides). */
    public function addAssignment() {
        return $this->tryOrFail(function() {
            [$user, $role, $err] = $this->managedUser();
            if ($err) return $this->respondError($err, 422);
            $org = OrgStructure::instance();
            $unitId = (int) $this->request->getPost('unit_id');
            $positionId = (int) $this->request->getPost('position_id');
            if (!$org->unit($unitId)) return $this->respondError('Select a department / unit.', 422);
            $pos = $org->position($positionId);
            if (!$pos || (int) $pos['is_general_root'] === 1) return $this->respondError('Select a position.', 422);
            // Same rule as sign-up: a Supervisor takes the unit's head position, an Employee one directly under it or a General one.
            if (!in_array($positionId, $org->positionChoicesFor($role, $unitId), true)) {
                return $this->respondError("{$pos['title']} is not " . ($role === 'Employee' ? 'an Employee' : 'a Supervisor') . " position in {$org->unit($unitId)['name']}.", 422);
            }
            $db = \Config\Database::connect();
            if ($db->table('plantillas')->where('user_id', $user['id'])->where('unit_id', $unitId)->where('position_id', $positionId)->where('ended_at IS NULL')->countAllResults()) {
                return $this->respondError('They already hold this position in this unit.', 422);
            }
            $others = $org->isHeadOf($unitId, $positionId) ? $org->headsOf($unitId, (int) $user['id']) : [];
            $db->table('plantillas')->insert([
                'user_id' => $user['id'], 'unit_id' => $unitId, 'position_id' => $positionId,
                'employment_status' => $user['employment_status'], 'started_at' => date('Y-m-d'), 'created_at' => date('Y-m-d H:i:s'),
            ]);
            OrgStructure::reset();
            $unitName = $org->unit($unitId)['name'];
            audit_log('ASSIGNMENT_ADDED', 'ACCOUNT', 'user', (int) $user['id'], "{$user['email']}: {$pos['title']}, {$unitName}");
            $msg = "Added: {$pos['title']}, {$unitName}.";
            if ($others) $msg .= ' ' . implode(', ', array_map(fn($h) => trim($h['first_name'] . ' ' . $h['last_name']), $others)) . ' also heads this unit (co-heads).';
            return $this->respond(['status' => 'success', 'message' => $msg]);
        });
    }

    /** POST /account/user/assignment/end - Ends a current assignment today (kept as history, never deleted). */
    public function endAssignment() {
        return $this->tryOrFail(function() {
            [$user, $role, $err] = $this->managedUser();
            if ($err) return $this->respondError($err, 422);
            $db = \Config\Database::connect();
            $row = $db->table('plantillas p')->select('p.id, un.name as unit_name, pos.title')
                ->join('units un', 'un.id = p.unit_id', 'left')->join('positions pos', 'pos.id = p.position_id', 'left')
                ->where('p.id', (int) $this->request->getPost('plantilla_id'))->where('p.user_id', $user['id'])->where('p.ended_at IS NULL')
                ->get()->getRowArray();
            if (!$row) return $this->respondError('That assignment is not current.', 422);
            $db->table('plantillas')->where('id', $row['id'])->update(['ended_at' => date('Y-m-d'), 'updated_at' => date('Y-m-d H:i:s')]);
            OrgStructure::reset();
            audit_log('ASSIGNMENT_ENDED', 'ACCOUNT', 'user', (int) $user['id'], "{$user['email']}: {$row['title']}, {$row['unit_name']} ended");
            return $this->respond(['status' => 'success', 'message' => "Ended: {$row['title']}, {$row['unit_name']}."]);
        });
    }

    /**
     * POST /account/user/details - Sets a person's employment status and form type. The form type must be
     * one the rules allow for their role and (new) status. Changing it converts their target drafts (and
     * sends submitted, unapproved targets back to draft), so the first request only reports what would
     * happen; the admin confirms and the request is sent again with confirm=1.
     */
    public function updateUserDetails() {
        return $this->tryOrFail(function() {
            [$user, $role, $err] = $this->managedUser();
            if ($err) return $this->respondError($err, 422);
            $org = OrgStructure::instance();
            $status = trim((string) $this->request->getPost('employment_status'));
            if (!(new EmploymentStatusModel())->where('name', $status)->first()) return $this->respondError('Select an employment status.', 422);
            if ($status !== (string) $user['employment_status'] && !$org->isStatusOpenTo($role, $status)) {
                return $this->respondError("{$status} is not open to {$role} (System Data > Employment Statuses).", 422);
            }
            $allowed = $org->allowedDocTypes($role, $status);
            $doc = strtoupper(trim((string) $this->request->getPost('doc_type')));
            $current = strtoupper((string) $user['doc_type']);
            if ($doc === '' && count($allowed) === 1) $doc = $allowed[0];
            if ($doc !== $current && !in_array($doc, $allowed, true)) {
                return $this->respondError('That form type is not allowed for this role and employment status' . ($allowed ? ' (allowed: ' . implode(', ', $allowed) . ').' : '.'), 422);
            }

            $docChanges = $doc !== '' && $doc !== $current;
            if ($docChanges && !$this->request->getPost('confirm')) {
                $folders = (new DocumentFolderModel())->where('user_id', $user['id'])->where('deleted_at IS NULL')
                    ->whereIn('status', [\App\Enums\FolderStatus::DRAFT_TARGET->value, \App\Enums\FolderStatus::TARGET_SUBMITTED->value, \App\Enums\FolderStatus::TARGET_MISSED->value])
                    ->findAll();
                $submitted = count(array_filter($folders, fn($f) => $f['status'] === \App\Enums\FolderStatus::TARGET_SUBMITTED->value));
                $msg = "Change the form type from " . ($current ?: 'none') . " to {$doc}? ";
                $msg .= $folders
                    ? count($folders) . " target paper(s) still in target setting will be converted to {$doc}" . ($submitted ? ", and {$submitted} submitted one(s) will go back to draft for resubmission" : '') . '. Approved targets and evaluations are not touched.'
                    : 'They have no target papers in target setting, so no paper changes.';
                return $this->respond(['status' => 'confirm', 'message' => $msg]);
            }

            (new UserModel())->update($user['id'], ['employment_status' => $status, 'doc_type' => $doc !== '' ? $doc : null]);
            $result = $docChanges ? sync_user_papers_mid_target_setting((int) $user['id'], $doc) : ['updated_docs' => 0, 'updated_folders' => 0];
            OrgStructure::reset();
            audit_log('USER_DETAILS_UPDATED', 'ACCOUNT', 'user', (int) $user['id'], "{$user['email']}: status {$status}" . ($docChanges ? ", form type {$current} -> {$doc} ({$result['updated_docs']} paper(s) converted, {$result['updated_folders']} back to draft)" : ''));
            return $this->respond(['status' => 'success', 'message' => 'Saved.' . ($docChanges ? " {$result['updated_docs']} paper(s) converted." : '')]);
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

        if ((int) ($pos['is_general_root'] ?? 0) === 1) {
            return $this->respondError('The General group cannot be edited.', 422);
        }
        [$parentId, $err] = $this->parentPositionFrom($this->request->getPost('parent_id'), $id);
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

        // A new status is open to both rated roles until the admin unticks one.
        if (OrgStructure::instance()->hasStatusLinks()) {
            $db = \Config\Database::connect();
            foreach ($db->table('roles')->whereIn('name', ['Employee', 'Supervisor'])->get()->getResultArray() as $r) {
                $db->table('role_employment_statuses')->ignore(true)->insert(['role_id' => $r['id'], 'employment_status_id' => $id]);
            }
            OrgStructure::reset();
        }
        audit_log('EMPLOYMENT_STATUS_CREATED', 'ACCOUNT', 'employment_status', (int) $id, "Created employment status: {$name}");

        return $this->respond(['status' => 'success', 'item' => ['id' => $id, 'name' => $name]]);
    }

    /**
     * POST /account/employment-status/roles - Which rated roles may have this status (status_id, roles[] = role
     * names). At least one. Never changes anyone's status: people it no longer fits are flagged in the directory.
     */
    public function updateEmploymentStatusRoles() {
        return $this->tryOrFail(function() {
            $org = OrgStructure::instance();
            if (!$org->hasStatusLinks()) return $this->respondError('Run the database migrations first (php spark migrate).', 422);
            $status = (new EmploymentStatusModel())->find((int) $this->request->getPost('status_id'));
            if (!$status) return $this->respondError('Employment status not found.', 422);
            $names = array_values(array_intersect(['Employee', 'Supervisor'], (array) $this->request->getPost('roles')));
            if (!$names) return $this->respondError("Open {$status['name']} to at least one role.", 422);
            $db = \Config\Database::connect();
            $roleIds = array_column($db->table('roles')->whereIn('name', ['Employee', 'Supervisor'])->get()->getResultArray(), 'id', 'name');
            $db->transStart();
            $db->table('role_employment_statuses')->where('employment_status_id', $status['id'])->whereIn('role_id', array_values($roleIds))->delete();
            foreach ($names as $n) $db->table('role_employment_statuses')->insert(['role_id' => $roleIds[$n], 'employment_status_id' => $status['id']]);
            $db->transComplete();
            OrgStructure::reset();
            $closed = array_values(array_diff(['Employee', 'Supervisor'], $names));
            $affected = $closed ? $db->table('users u')->join('user_roles ur', 'ur.user_id = u.id')->join('roles r', 'r.id = ur.role_id')
                ->where('u.is_active', 1)->where('u.employment_status', $status['name'])->whereIn('r.name', $closed)->countAllResults() : 0;
            audit_log('EMPLOYMENT_STATUS_ROLES', 'ACCOUNT', 'employment_status', (int) $status['id'], "{$status['name']} open to: " . implode(', ', $names));
            return $this->respond(['status' => 'success',
                'message' => "{$status['name']} is open to " . implode(' and ', $names) . '.'
                    . ($affected ? " {$affected} " . implode('/', $closed) . ($affected === 1 ? ' has' : 's have') . ' this status and ' . ($affected === 1 ? 'is' : 'are') . ' now flagged in the User Directory.' : '')]);
        });
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
     * POST /account/doc-rules/update - Which doc types each role and each employment status allows (rows in
     * role_doc_types / employment_status_doc_types). A person may use the doc types allowed by both; a role or
     * status with nothing ticked has no limit. Nobody's current doc type is
     * changed here: people the new rules don't fit are flagged in the User Directory for the admin to fix.
     */
    public function updateDocRules() {
        return $this->tryOrFail(function() {
            $db = \Config\Database::connect();
            $org = OrgStructure::instance();
            $changes = [];
            $db->transStart();
            foreach ([['role', 'roles', 'role_doc_types', 'role_id'], ['status', 'employment_statuses', 'employment_status_doc_types', 'employment_status_id']] as [$kind, $parent, $table, $key]) {
                $present = (array) $this->request->getPost("{$kind}_present");
                $posted = (array) $this->request->getPost("{$kind}_types");
                foreach ($db->table($parent)->get()->getResultArray() as $row) {
                    if (!array_key_exists($row['id'], $present)) continue;
                    // Ticked types in enum order. At least one; all ticked = no limit (stored as no rows, so a
                    // form type added later is allowed too).
                    $types = array_values(array_intersect(DocType::values(), array_map('strtoupper', (array) ($posted[$row['id']] ?? []))));
                    if (!$types) {
                        $db->transRollback();
                        return $this->respondError("Tick at least one doc type for {$row['name']}.", 422);
                    }
                    $all = $types === DocType::values();
                    $before = $org->docRule($kind, $row['name']) ?? DocType::values();
                    if ($types === $before) continue;
                    $db->table($table)->where($key, $row['id'])->delete();
                    if (!$all) foreach ($types as $t) $db->table($table)->insert([$key => $row['id'], 'doc_type' => $t]);
                    $changes[] = "{$row['name']}: " . ($all ? 'all' : implode(',', $types));
                }
            }
            $db->transComplete();
            OrgStructure::reset();
            if ($changes) {
                audit_log('DOC_TYPE_RULES_UPDATED', 'ACCOUNT', 'system', 0, 'Doc-type rules changed: ' . implode('; ', $changes));
            }
            return $this->respond(['status' => 'success', 'message' => $changes ? 'Doc-type rules saved.' : 'No changes.']);
        });
    }

    /** Positions in tree order (parents before children, alphabetical), General first, with level and kind. */
    private function flattenPositionTree(OrgStructure $org): array
    {
        $all = $org->positions();
        $children = [];
        foreach ($all as $p) {
            $children[$p['parent_id'] !== null && isset($all[$p['parent_id']]) ? $p['parent_id'] : 0][] = $p;
        }
        foreach ($children as &$list) {
            usort($list, fn($a, $b) => ((int) $b['is_general_root'] <=> (int) $a['is_general_root']) ?: strcasecmp($a['title'], $b['title']));
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
        // Only the roles that fill in forms (Admin, TWG and PMT review; they never get a paper).
        $roles = array_values(array_filter($roleModel->orderBy('name', 'ASC')->findAll(), fn($r) => OrgStructure::isRatedRole($r['name'])));
        $statuses = $statusModel->orderBy('name', 'ASC')->findAll();
        foreach ($roles as &$r) $r['allowed'] = $org->docRule('role', $r['name']);
        foreach ($statuses as &$st) $st['allowed'] = $org->docRule('status', $st['name']);
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
        $open = ['Employee' => $org->statusesOpenTo('Employee'), 'Supervisor' => $org->statusesOpenTo('Supervisor')];
        foreach ($preview as &$pv) $pv['open'] = $org->isStatusOpenTo($pv['role'], $pv['status']);
        unset($pv);
        return ['roles' => $roles, 'statuses' => $statuses, 'preview' => $preview, 'open' => $open, 'linksReady' => $org->hasStatusLinks()];
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