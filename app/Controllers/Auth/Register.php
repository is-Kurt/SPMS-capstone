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
use App\Models\EmploymentStatusModel;

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
        $isTwgInvite = $role && strtolower($role['name']) === 'twg';
        $isPmtInvite = $role && strtolower($role['name']) === 'pmt';

        $employmentStatuses = (new EmploymentStatusModel())->orderBy('name', 'ASC')->findAll();
        $assignment = $this->assignmentFor($invitation, $role['name'] ?? null);

        return view('auth/signup', [
            'invitation'         => $invitation,
            'assignment'         => $assignment,
            'employmentStatuses' => $employmentStatuses,
            'isAdminInvite'      => $isAdminInvite,
            'isTwgInvite'        => $isTwgInvite,
            'isPmtInvite'        => $isPmtInvite
        ]);
    }

    /**
     * The department the admin chose for this invitation, the positions the person may pick there (a
     * Supervisor: the unit's head position(s); an Employee: its member positions and the General ones), and
     * the doc types each employment status allows for this role. Null when the role has no department.
     */
    private function assignmentFor(array $invitation, ?string $roleName): ?array
    {
        if (!\App\Libraries\OrgStructure::isRatedRole($roleName)) return null;
        $org = \App\Libraries\OrgStructure::instance();
        $unitId = (int) ($invitation['unit_id'] ?? 0);
        $unit = $unitId ? $org->unit($unitId) : null;
        $positions = [];
        foreach ($unit ? $org->positionChoicesFor($roleName, $unitId) : [] as $id) {
            $positions[] = ['id' => $id, 'title' => $org->position($id)['title'] ?? ''];
        }
        $docsByStatus = [];
        foreach ((new EmploymentStatusModel())->orderBy('name', 'ASC')->findAll() as $st) {
            if (!$org->isStatusOpenTo($roleName, $st['name'])) continue;   // only statuses open to this role
            $docsByStatus[$st['name']] = $org->allowedDocTypes($roleName, $st['name']);
        }
        return ['unit' => $unit, 'role' => $roleName, 'positions' => $positions, 'docsByStatus' => $docsByStatus];
    }

    /**
     * POST /signup - Creates the account with the invited role. A rated role (Employee / Supervisor) joins
     * the department set on the invitation, in a position allowed there (picked from the list, or set when
     * there is only one), with their employment status and a doc type the rules allow for both. Admin, TWG
     * and PMT accounts have no department. The invitation is then marked accepted so it can't be reused.
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

        // Determine if admin, twg, or pmt invite
        $role = $invitation['role_id'] ? $roleModel->find($invitation['role_id']) : null;
        $isAdminInvite = $role && strtolower($role['name']) === 'admin';
        $isTwgInvite = $role && strtolower($role['name']) === 'twg';
        $isPmtInvite = $role && strtolower($role['name']) === 'pmt';
        $skipDetails = $isAdminInvite || $isTwgInvite || $isPmtInvite;

        $validation = \Config\Services::validation();
        $rules = [
            'first_name' => 'required|min_length[2]|max_length[100]',
            'last_name'  => 'required|min_length[2]|max_length[100]',
            'password'   => 'required|min_length[8]',
            'confirm-password' => 'required|matches[password]'
        ];
        $validation->setRules($rules);
        if (!$validation->run($this->request->getPost())) {
            return redirect()->back()->withInput();
        }

        // Rated roles: the department comes from the invitation; position, status and doc type are checked
        // against what is allowed there (the form only offers those, but the request could say anything).
        $assignment = $skipDetails ? null : $this->assignmentFor($invitation, $role['name'] ?? null);
        $positionId = null; $status = null; $docType = null;
        if (!$skipDetails) {
            if (!$assignment || !$assignment['unit']) {
                return redirect()->back()->withInput()->with('error', 'This invitation has no department. Ask the administrator to send a new invitation.');
            }
            $allowedPositions = array_column($assignment['positions'], 'id');
            $positionId = count($allowedPositions) === 1 ? $allowedPositions[0] : (int) $this->request->getPost('position_id');
            if (!in_array($positionId, $allowedPositions, true)) {
                $validation->setError('position_id', 'Please select your position.');
                return redirect()->back()->withInput();
            }
            $status = trim((string) $this->request->getPost('employment_status'));
            if (!array_key_exists($status, $assignment['docsByStatus'])) {
                $validation->setError('employment_status', 'Please select your employment status.');
                return redirect()->back()->withInput();
            }
            $allowedDocs = $assignment['docsByStatus'][$status];
            $postedDoc = strtoupper(trim((string) $this->request->getPost('doc_type')));
            if (count($allowedDocs) === 1) {
                $docType = $allowedDocs[0];
            } elseif ($allowedDocs) {
                if (!in_array($postedDoc, $allowedDocs, true)) {
                    $validation->setError('doc_type', 'Please select your form type.');
                    return redirect()->back()->withInput();
                }
                $docType = $postedDoc;
            }
            // No doc type allowed at all: the account is still created; the admin sees it flagged.
        }

        $userModel->db->transStart();

        $userId = $userModel->insert([
            'first_name'        => $this->request->getPost('first_name'),
            'last_name'         => $this->request->getPost('last_name'),
            'email'             => strtolower($invitation['email']),
            'password'          => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'is_active'         => 1,
            'doc_type'          => $docType,
            'employment_status' => $status,
        ]);

        if ($invitation['role_id']) {
            $userModel->db->table('user_roles')->insert([
                'user_id'    => $userId,
                'role_id'    => $invitation['role_id'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        if (!$skipDetails) {
            $plantillaModel->insert([
                'user_id'           => $userId,
                'position_id'       => $positionId,
                'unit_id'           => (int) $assignment['unit']['id'],
                'employment_status' => $status,
                'started_at'        => date('Y-m-d'),
                'created_at'        => date('Y-m-d H:i:s'),
            ]);
        }

        $invitationModel->update($invitation['id'], ['status' => InvitationStatus::ACCEPTED->value]);

        $userModel->db->transComplete();

        return redirect()->to('/login');
    }
}