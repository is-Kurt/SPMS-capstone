<?php

namespace App\Controllers;

use App\Libraries\OrgStructure;
use App\Models\UserModel;
use App\Models\RoutingPresetModel;

/**
 * Accounts for supervisors (2026-11-07): the admin's User Directory, scoped to their branch (the units they head and
 * everything below). Like the admin's Manage (2026-11-08) they add and end people's positions in their branch and set
 * the form type; the role follows the position (a head position makes them a unit head). A Dean manages up to the
 * Department Chairs; nobody is raised to the supervisor's own level, and nobody is left without a position (moving
 * someone = add the new position, then end the old one). Employment status, accounts, invites and System Data stay
 * with the admin.
 */
class StaffAccounts extends BaseController
{
    /** GET /staff-accounts */
    public function index()
    {
        if (session()->get('role') !== 'Supervisor') return redirect()->to(site_url('/'));
        $sid = (int) session()->get('user_id');
        $org = OrgStructure::instance();
        $rows = $org->branchAssignments($sid);
        if (!$rows) return redirect()->to(site_url('teams'));

        // One entry per person, with the positions they hold in this branch (2026-11-08: one row per person).
        $people = [];
        foreach ($rows as $r) {
            $uid = (int) $r['user_id'];
            if (!isset($people[$uid])) {
                $role = $org->roleOf($uid);
                $allowed = $org->allowedDocTypes($role, $r['employment_status']);
                $doc = strtoupper((string) $r['doc_type']);
                $flags = [];
                if (!in_array($doc, $allowed, true)) {
                    $flags[] = ($doc ? "Form type {$doc} not allowed" : 'Form type not set') . ($allowed ? ' (allowed: ' . implode(', ', $allowed) . ')' : ' (nothing allowed: ask the administrator)');
                }
                if (!$org->isStatusOpenTo($role, $r['employment_status'])) {
                    $flags[] = "Status {$r['employment_status']} is not open to " . ($role === 'Supervisor' ? 'unit heads' : 'members') . ': ask the administrator';
                }
                $people[$uid] = [
                    'user_id' => $uid, 'name' => trim($r['first_name'] . ' ' . $r['last_name']), 'email' => $r['email'],
                    'role' => $role, 'status' => $r['employment_status'], 'doc' => $doc, 'flags' => $flags,
                    'assignments' => [], 'total' => count($org->assignmentsOf($uid)),   // all their current positions
                ];
            }
            $people[$uid]['assignments'][] = [
                'id' => (int) $r['plantilla_id'], 'unit_id' => (int) $r['unit_id'], 'unit' => $org->unit((int) $r['unit_id'])['name'] ?? '',
                'position_id' => (int) $r['position_id'], 'position' => $org->position((int) $r['position_id'])['title'] ?? '',
                'head' => $r['head'], 'since' => $r['started_at'] ?? null,
            ];
        }

        // Units of the branch as a tree, and the positions the supervisor may give in each.
        $branch = $org->branchUnitIds($sid);
        $units = [];
        $walk = function (int $parent, int $level) use (&$walk, $org, $branch, &$units, $sid) {
            $kids = array_filter($org->units(), fn($u) => (int) ($u['parent_id'] ?? 0) === $parent && in_array((int) $u['id'], $branch, true));
            usort($kids, fn($a, $b) => strcasecmp($a['name'], $b['name']));
            foreach ($kids as $u) {
                $units[] = ['id' => (int) $u['id'], 'name' => $u['name'], 'level' => $level,
                    'positions' => array_map(fn($id) => ['id' => $id, 'title' => $org->position($id)['title'] ?? '', 'head' => $org->isHeadOf((int) $u['id'], $id)],
                                             $org->positionsForUnitBy($sid, (int) $u['id']))];
                $walk((int) $u['id'], $level + 1);
            }
        };
        $tops = array_filter($branch, fn($id) => !in_array((int) ($org->unit($id)['parent_id'] ?? 0), $branch, true));
        foreach ($tops as $top) {
            $u = $org->unit($top);
            $units[] = ['id' => (int) $u['id'], 'name' => $u['name'], 'level' => 0,
                'positions' => array_map(fn($id) => ['id' => $id, 'title' => $org->position($id)['title'] ?? '', 'head' => $org->isHeadOf((int) $u['id'], $id)],
                                         $org->positionsForUnitBy($sid, (int) $u['id']))];
            $walk((int) $u['id'], 1);
        }

        $rules = ['roles' => [], 'statuses' => []];
        foreach (['Employee', 'Supervisor'] as $r) $rules['roles'][$r] = $org->docRule('role', $r);
        foreach ((new \App\Models\EmploymentStatusModel())->findAll() as $st) $rules['statuses'][$st['name']] = $org->docRule('status', $st['name']);

        return view('staff_accounts/index', ['people' => $people, 'units' => $units, 'rules' => $rules, 'docTypes' => \App\Enums\DocType::values()]);
    }

    /** The person (in this supervisor's branch, not themselves) a request is about, or an error. */
    private function branchPerson(int $sid, int $userId): array
    {
        if ($userId === $sid) return [null, 'Ask the administrator to change your own positions.'];
        $inBranch = array_filter(OrgStructure::instance()->branchAssignments($sid), fn($r) => (int) $r['user_id'] === $userId);
        if (!$inBranch) return [null, 'This person is not in your branch.'];
        return [(new UserModel())->find($userId), null];
    }

    /** Tells the person and the admins what a supervisor changed. */
    private function tellAboutChange(int $sid, array $user, string $verb, string $position, ?string $newRole): void
    {
        $who = trim((string) session()->get('username'));
        $name = trim($user['first_name'] . ' ' . $user['last_name']);
        notify_user((int) $user['id'], ['sender_id' => $sid, 'type' => 'system', 'title' => 'Your details were updated',
            'message' => "{$who} {$verb} your position {$position}" . ($newRole ? '; you are now ' . staff_role_label($newRole) : '') . '.', 'link' => 'profile', 'icon' => 'user']);
        notify_admins(['sender_id' => $sid, 'type' => 'system', 'title' => 'Branch change by a supervisor',
            'message' => "{$who} {$verb} {$name}'s position {$position}" . ($newRole ? '; they are now ' . staff_role_label($newRole) : '') . '.', 'link' => 'accounts', 'icon' => 'user']);
    }

    /**
     * POST /staff-accounts/assignment/add - user_id, unit_id, position_id, confirm. A position a supervisor may give
     * in a unit of their branch (OrgStructure::positionsForUnitBy). Making someone a unit head asks first.
     */
    public function addAssignment()
    {
        return $this->tryOrFail(function () {
            if (session()->get('role') !== 'Supervisor') return $this->respondError('Only unit heads manage accounts here.', 403);
            $sid = (int) session()->get('user_id');
            [$user, $err] = $this->branchPerson($sid, (int) $this->request->getPost('user_id'));
            if ($err) return $this->respondError($err, 403);
            $org = OrgStructure::instance();
            $unitId = (int) $this->request->getPost('unit_id');
            $posId = (int) $this->request->getPost('position_id');
            $unitName = $org->unit($unitId)['name'] ?? 'that unit';
            if (!in_array($posId, $org->positionsForUnitBy($sid, $unitId), true)) {
                return $this->respondError("That position is not one you can give in {$unitName}.", 422);
            }
            $makesHead = $org->isHeadOf($unitId, $posId) && $org->roleOf((int) $user['id']) !== 'Supervisor';
            if ($makesHead && !$this->request->getPost('confirm')) {
                $name = trim($user['first_name'] . ' ' . $user['last_name']);
                $lines = ["Make {$name} " . ($org->position($posId)['title'] ?? '') . " of {$unitName}? This makes them a unit head: they get My Staff and Accounts for {$unitName}. The administrator is notified."];
                $others = $org->headsOf($unitId, (int) $user['id']);
                if ($others) $lines[] = implode(', ', array_map(fn($h) => trim($h['first_name'] . ' ' . $h['last_name']), $others)) . ' already heads this unit; they would be co-heads.';
                if (!$org->isStatusOpenTo('Supervisor', $user['employment_status'])) $lines[] = "Their status {$user['employment_status']} is not open to unit heads; the administrator will need to change it.";
                $allowed = $org->allowedDocTypes('Supervisor', $user['employment_status']);
                if ($allowed && !in_array(strtoupper((string) $user['doc_type']), $allowed, true)) $lines[] = 'Their form type ' . ($user['doc_type'] ?: 'none') . ' does not fit a unit head (allowed: ' . implode(', ', $allowed) . '): set it below afterwards.';
                return $this->respond(['status' => 'confirm', 'message' => implode("\n\n", $lines)]);
            }
            $res = \App\Libraries\Assignments::add($user, $unitId, $posId);
            if (!$res['ok']) return $this->respondError($res['message'], 422);
            $this->tellAboutChange($sid, $user, 'added', "{$res['title']}, {$res['unit']}", $res['role']);
            return $this->respond(['status' => 'success', 'message' => $res['message']]);
        });
    }

    /** POST /staff-accounts/assignment/end - plantilla_id. A position in their branch; never someone's only position. */
    public function endAssignment()
    {
        return $this->tryOrFail(function () {
            if (session()->get('role') !== 'Supervisor') return $this->respondError('Only unit heads manage accounts here.', 403);
            $sid = (int) session()->get('user_id');
            $org = OrgStructure::instance();
            $row = $org->branchAssignment($sid, (int) $this->request->getPost('plantilla_id'));
            if (!$row) return $this->respondError('That position is not in your branch.', 403);
            [$user, $err] = $this->branchPerson($sid, (int) $row['user_id']);
            if ($err) return $this->respondError($err, 403);
            if (count($org->assignmentsOf((int) $user['id'])) <= 1) {
                return $this->respondError('This is their only position. To move them, add the new position first, then end this one. When they leave, the administrator disables the account.', 422);
            }
            $res = \App\Libraries\Assignments::end($user, (int) $row['plantilla_id']);
            if (!$res['ok']) return $this->respondError($res['message'], 422);
            $this->tellAboutChange($sid, $user, 'ended', "{$res['title']}, {$res['unit']}", $res['role']);
            return $this->respond(['status' => 'success', 'message' => $res['message']]);
        });
    }

    /**
     * POST /staff-accounts/update - plantilla_id, unit_id, position_id, doc_type, confirm. The first call says what
     * will change (moves, unit head yes/no, papers that convert); the page sends it again with confirm=1.
     */
    public function update()
    {
        return $this->tryOrFail(function () {
            if (session()->get('role') !== 'Supervisor') return $this->respondError('Only unit heads manage accounts here.', 403);
            $sid = (int) session()->get('user_id');
            $org = OrgStructure::instance();
            $row = $org->branchAssignment($sid, (int) $this->request->getPost('plantilla_id'));
            if (!$row) return $this->respondError('This person is not in your branch.', 403);
            $userId = (int) $row['user_id'];
            $name = trim($row['first_name'] . ' ' . $row['last_name']);
            $oldUnit = (int) $row['unit_id'];
            $oldPos = (int) $row['position_id'];
            $newUnit = (int) ($this->request->getPost('unit_id') ?: $oldUnit);
            $newPos = (int) ($this->request->getPost('position_id') ?: $oldPos);
            $moving = $newUnit !== $oldUnit;
            $posChange = $moving || $newPos !== $oldPos;
            if ($posChange && !in_array($newPos, $org->positionsForUnitBy($sid, $newUnit), true)) {
                return $this->respondError('That position is not one you can give in ' . ($org->unit($newUnit)['name'] ?? 'that unit') . '.', 422);
            }

            // Their role after the change: a unit head if this position is a head one, or they head another unit.
            $otherHead = false;
            foreach ($org->assignmentsOf($userId) as $a) {
                if ((int) $a['id'] !== (int) $row['plantilla_id'] && $a['unit_id'] && $a['position_id'] && $org->isHeadOf((int) $a['unit_id'], (int) $a['position_id'])) $otherHead = true;
            }
            $roleNow = $org->roleOf($userId);
            $roleAfter = ($otherHead || $org->isHeadOf($newUnit, $newPos)) ? 'Supervisor' : 'Employee';
            $allowedAfter = $org->allowedDocTypes($roleAfter, $row['employment_status']);
            $currentDoc = strtoupper((string) $row['doc_type']);
            $doc = strtoupper(trim((string) $this->request->getPost('doc_type')));
            if ($doc === '' || $doc === $currentDoc) $doc = in_array($currentDoc, $allowedAfter, true) || count($allowedAfter) !== 1 ? $currentDoc : $allowedAfter[0];
            $docChanges = $doc !== $currentDoc;
            if ($docChanges && !in_array($doc, $allowedAfter, true)) {
                return $this->respondError('That form type is not allowed for ' . ($roleAfter === 'Supervisor' ? 'a unit head' : 'a member') . ' with their employment status.', 422);
            }
            if (!$posChange && !$docChanges) return $this->respond(['status' => 'success', 'message' => 'No changes.']);

            $unitName = fn($id) => $org->unit($id)['name'] ?? '?';
            $posName = fn($id) => $org->position($id)['title'] ?? '?';
            if (!$this->request->getPost('confirm')) {
                $lines = [];
                if ($moving) $lines[] = "Move {$name} from {$unitName($oldUnit)} ({$posName($oldPos)}) to {$unitName($newUnit)} as {$posName($newPos)}. The old assignment ends today and stays in their history.";
                elseif ($posChange) $lines[] = "Change {$name}'s position from {$posName($oldPos)} to {$posName($newPos)} in {$unitName($newUnit)}.";
                if ($roleAfter !== $roleNow) {
                    $lines[] = $roleAfter === 'Supervisor'
                        ? 'This makes them a unit head: they get My Staff and the dashboard for ' . $unitName($newUnit) . '. The administrator is notified.'
                        : 'They stop being a unit head (now a member). Papers already routed to them stay with them for now. The administrator is notified.';
                }
                if (!$org->isStatusOpenTo($roleAfter, $row['employment_status'])) {
                    $lines[] = "Their status {$row['employment_status']} is not open to " . ($roleAfter === 'Supervisor' ? 'unit heads' : 'members') . '; the administrator will need to change it.';
                }
                if ($docChanges) {
                    $papers = \Config\Database::connect()->table('document_folders')->select('status')->where('user_id', $userId)->where('deleted_at IS NULL')
                        ->whereIn('status', [\App\Enums\FolderStatus::DRAFT_TARGET->value, \App\Enums\FolderStatus::TARGET_SUBMITTED->value, \App\Enums\FolderStatus::TARGET_MISSED->value])
                        ->get()->getResultArray();
                    $submitted = count(array_filter($papers, fn($f) => $f['status'] === \App\Enums\FolderStatus::TARGET_SUBMITTED->value));
                    $lines[] = 'Form type ' . ($currentDoc ?: 'none') . " -> {$doc}: " . ($papers
                        ? count($papers) . ' target paper(s) in target setting convert' . ($submitted ? ", {$submitted} submitted one(s) go back to draft" : '') . '; approved targets and evaluations are not touched.'
                        : 'no target papers in target setting to convert.');
                } elseif (!in_array($currentDoc, $allowedAfter, true) && $allowedAfter) {
                    $lines[] = "Their form type {$currentDoc} does not fit any more (allowed: " . implode(', ', $allowedAfter) . '): pick one, or it stays flagged.';
                }
                return $this->respond(['status' => 'confirm', 'message' => implode("\n\n", $lines)]);
            }

            $db = \Config\Database::connect();
            $changes = [];
            $db->transStart();
            if ($posChange) {
                $today = date('Y-m-d');
                $db->table('plantillas')->where('id', (int) $row['plantilla_id'])->update(['ended_at' => $today, 'updated_at' => date('Y-m-d H:i:s')]);
                $db->table('plantillas')->insert(['user_id' => $userId, 'unit_id' => $newUnit, 'position_id' => $newPos,
                    'employment_status' => $row['assignment_status'] ?? $row['employment_status'], 'started_at' => $today, 'created_at' => date('Y-m-d H:i:s')]);
                $changes[] = $moving ? "moved to {$unitName($newUnit)} as {$posName($newPos)} (was {$posName($oldPos)}, {$unitName($oldUnit)})"
                                     : "position {$posName($oldPos)} -> {$posName($newPos)} ({$unitName($newUnit)})";
            }
            if ($docChanges) {
                (new UserModel())->update($userId, ['doc_type' => $doc]);
                $changes[] = 'form type ' . ($currentDoc ?: 'none') . " -> {$doc}";
            }
            $db->transComplete();
            OrgStructure::reset();
            $newRole = $posChange ? sync_rated_role($userId) : null;      // unit head or member follows the position
            if ($newRole) $changes[] = 'now ' . staff_role_label($newRole);
            if ($posChange) refresh_draft_paper_snapshots([$userId]);
            if ($posChange) reconcile_reviews();                       // a move can take them out of a supervisor's scope
            if ($docChanges) sync_user_papers_mid_target_setting($userId, $doc);

            $who = trim((string) session()->get('username'));
            audit_log('BRANCH_ACCOUNT_UPDATED', 'ACCOUNT', 'user', $userId, "{$row['email']} by {$who}: " . implode('; ', $changes));
            notify_user($userId, ['sender_id' => $sid, 'type' => 'system', 'title' => 'Your details were updated',
                'message' => "{$who} changed your " . implode(', ', $changes) . '.', 'link' => 'profile', 'icon' => 'user']);
            if ($newRole || $moving) {
                notify_admins(['sender_id' => $sid, 'type' => 'system', 'title' => 'Branch change by a supervisor',
                    'message' => "{$who}: {$name} " . implode(', ', $changes) . '.', 'link' => 'accounts', 'icon' => 'user']);
            }
            return $this->respond(['status' => 'success', 'message' => "Saved for {$name}: " . implode('; ', $changes) . '.']);
        });
    }
}
