<?php

namespace App\Libraries;

use App\Enums\FolderStatus;

/**
 * Revoke with history (2026-11-07). A paper's supervisor is whoever cascaded to it (evaluation_routings). When that
 * supervisor goes out of scope for the paper's owner (the owner moved, the supervisor moved, left, was disabled or is
 * no longer a head of a unit above the owner), their review is revoked:
 *
 *  - a copy of the form is kept in the paper's version history ("Revoked: <name>"),
 *  - their routing is removed (they lose access),
 *  - if no supervisor is left on it: while target setting is open the form starts again from a clean template, even
 *    when its targets were approved (2026-11-08: the new supervisor approves them and TWG verifies them again); once
 *    target setting has closed the approved commitment stays and the new supervisor rates it. Either way the paper
 *    waits for a supervisor (cannot be submitted) until a new head cascades to it, which reuses the paper and adds
 *    their targets,
 *  - the owner, the old supervisor and the new head (or the admins when there is none) are notified.
 *
 * Admin, TWG and PMT reviewers are not tied to the hierarchy and are never revoked. Finished papers are left alone.
 */
class ReviewHandover
{
    private const FINISHED = ['eval_approved', 'unevaluated', 'eval_unrated'];
    private const TARGET_SETTING = ['draft_target', 'target_submitted', 'target_unapproved', 'target_missed'];

    /** Is the waiting flag there yet (migration 000025)? Until then nothing is revoked. */
    public static function ready(): bool
    {
        static $ready = null;
        return $ready ??= (bool) \Config\Database::connect()->query("SELECT 1 FROM pragma_table_info('document_folders') WHERE name = 'waiting_supervisor'")->getRowArray();
    }

    /** The units whose heads may supervise this person: their units and all above (a head: only the units above). */
    private static function supervisingUnits(OrgStructure $org, int $userId): array
    {
        $out = [];
        foreach ($org->assignmentsOf($userId) as $a) {
            if (!$a['unit_id'] || !$a['position_id']) continue;
            $id = (int) $a['unit_id'];
            if ($org->isHeadOf($id, (int) $a['position_id'])) $id = (int) ($org->unit($id)['parent_id'] ?? 0);
            for ($guard = 0; $id && $guard < 50; $guard++) {
                $out[$id] = $id;
                $id = (int) ($org->unit($id)['parent_id'] ?? 0);
            }
        }
        return array_values($out);
    }

    /**
     * Why an unfinished paper is on hold, or null. On hold: its owner is not rated staff now (switched to TWG, PMT or
     * Admin, or disabled). The paper is kept as it is, so switching back (Restore) or enabling brings it back with the
     * same reviewers; meanwhile reviewers do not see it or act on it, it is left out of the counts, and the Admin sees
     * it labelled and can delete it (2026-11-08). A cycle's own root folders and finished papers are never on hold.
     */
    public static function holdReason(?string $ownerRole, $ownerActive, ?string $status, $parentFolderId): ?string
    {
        if (empty($parentFolderId) || in_array($status, self::FINISHED, true)) return null;
        if ((int) $ownerActive !== 1) return 'account disabled';
        return OrgStructure::isRatedRole($ownerRole) ? null : 'now ' . ($ownerRole ?: 'without a role');
    }

    /** holdReason() for a document_folders row. */
    public static function onHold(array $folder): ?string
    {
        if (empty($folder['parent_folder_id'])) return null;
        $u = \Config\Database::connect()->table('users')->select('is_active')->where('id', $folder['user_id'])->get()->getRowArray();
        if (!$u) return null;   // no such account (old data): not a hold, as before
        return self::holdReason(OrgStructure::instance()->roleOf((int) $folder['user_id']), $u['is_active'], $folder['status'] ?? null, $folder['parent_folder_id']);
    }

    /** SQL: the paper (alias $f, owner alias $u) is NOT on hold. For lists and counts. */
    public static function notOnHoldSql(string $f = 'df', string $u = 'u'): string
    {
        $finished = "'" . implode("','", self::FINISHED) . "'";
        return "({$f}.parent_folder_id IS NULL OR {$f}.status IN ({$finished}) OR ({$u}.is_active = 1 AND EXISTS ("
             . "SELECT 1 FROM user_roles ur_h JOIN roles r_h ON r_h.id = ur_h.role_id WHERE ur_h.user_id = {$u}.id AND r_h.name IN ('Employee', 'Supervisor'))))";
    }

    /**
     * May this supervisor review this person's paper now? The rule reconcile() keeps reviews by: an active head of
     * one of the person's units or a unit above them. A staff list keeps members who have since moved away, so the
     * cascade and sync check this; giving such a member a review would only be revoked at the next account change.
     */
    public static function canSupervise(int $supervisorId, int $ownerId): bool
    {
        $org = OrgStructure::instance();
        if (in_array($org->roleOf($supervisorId), ['Admin', 'TWG', 'PMT'], true)) return true;
        $active = \Config\Database::connect()->table('users')->where('id', $supervisorId)->where('is_active', 1)->countAllResults() > 0;
        return $active && (bool) array_intersect($org->headedUnitIds($supervisorId), self::supervisingUnits($org, $ownerId));
    }

    /** The heads who would supervise this person now (nearest head of their unit, or above it for a head). */
    private static function newHeadsFor(OrgStructure $org, int $userId): array
    {
        foreach ($org->assignmentsOf($userId) as $a) {
            if (!$a['unit_id'] || !$a['position_id']) continue;
            $id = (int) $a['unit_id'];
            if ($org->isHeadOf($id, (int) $a['position_id'])) $id = (int) ($org->unit($id)['parent_id'] ?? 0);
            if ($id && ($heads = array_values(array_diff($org->nearestHeadIdsOf($id), [$userId])))) return $heads;
        }
        return [];
    }

    /**
     * Checks every unfinished paper and revokes the supervisors who are out of scope. Run after any change to
     * assignments, roles, accounts or the structure. Returns [folderId => [revoked names]]. $dry: only report.
     */
    public static function reconcile(bool $dry = false): array
    {
        if (!self::ready()) return [];
        $db = \Config\Database::connect();
        OrgStructure::reset();
        $org = OrgStructure::instance();
        $papers = $db->table('document_folders f')->select('f.id, f.user_id, f.parent_folder_id, f.cycle_id, f.cycle_folder_id, f.waiting_supervisor, f.status, f.title, f.tabs, u.first_name, u.last_name, u.email, u.is_active, u.doc_type')
            ->join('users u', 'u.id = f.user_id')
            ->where('f.deleted_at IS NULL')->where('f.parent_folder_id IS NOT NULL')->whereNotIn('f.status', self::FINISHED)
            ->get()->getResultArray();
        $summary = [];
        $roleOf = [];
        foreach ($papers as $p) {
            $ownerId = (int) $p['user_id'];
            if ((int) $p['is_active'] !== 1 || !OrgStructure::isRatedRole($org->roleOf($ownerId))) continue;
            if ((int) $p['waiting_supervisor'] === 1 && $p['status'] === FolderStatus::TARGET_APPROVED->value && self::targetWindowOpen($p)) {
                $summary[$p['id']] = ['owner' => $p['email'], 'status' => $p['status'], 'revoked' => ['(approved targets reopened)'], 'others_left' => 0];
                if (!$dry) self::reopenApprovedTargets($p, $org);
                continue;
            }
            $routes = $db->table('evaluation_routings')->where('folder_id', $p['id'])->where('deleted_at IS NULL')->get()->getResultArray();
            if (!$routes) continue;
            $allowedUnits = self::supervisingUnits($org, $ownerId);
            $revoked = [];
            $keep = 0;
            $keptFolders = [];
            foreach ($routes as $r) {
                $eid = (int) $r['evaluator_id'];
                if ($eid === $ownerId) continue;
                $roleOf[$eid] ??= $org->roleOf($eid);
                // Not tied to the hierarchy: the Admin's reviews (given from a cycle's root folder). A review given from
                // the reviewer's own staff paper was given as a head, so it follows the head rule even after they
                // switch to TWG/PMT/Admin (2026-11-08: a head switched to TWG kept reviewing their old staff).
                if (in_array($roleOf[$eid], ['Admin', 'TWG', 'PMT'], true) && !self::fromStaffPaper($r['evaluator_folder_id'] ?? null)) continue;
                $evaluator = $db->table('users')->select('id, first_name, last_name, is_active')->where('id', $eid)->get()->getRowArray();
                $inScope = $evaluator && (int) $evaluator['is_active'] === 1
                    && array_intersect($org->headedUnitIds($eid), $allowedUnits);
                if ($inScope) { $keep++; if (!empty($r['evaluator_folder_id'])) $keptFolders[] = $r['evaluator_folder_id']; continue; }
                $revoked[] = ['route' => (int) $r['id'], 'id' => $eid, 'name' => $evaluator ? trim($evaluator['first_name'] . ' ' . $evaluator['last_name']) : "user {$eid}"];
            }
            if (!$revoked) continue;
            $summary[$p['id']] = ['owner' => $p['email'], 'status' => $p['status'], 'revoked' => array_column($revoked, 'name'), 'others_left' => $keep];
            if (!$dry) self::revoke($p, $revoked, $keep, $org, $keptFolders);
        }
        return $summary;
    }

    /** Was this review given from a staff paper (not a cycle's root folder)? */
    private static function fromStaffPaper(?string $evaluatorFolderId): bool
    {
        static $cache = [];
        if (!$evaluatorFolderId) return false;
        return $cache[$evaluatorFolderId] ??= (bool) \Config\Database::connect()->table('document_folders')
            ->where('id', $evaluatorFolderId)->where('parent_folder_id IS NOT NULL')->countAllResults();
    }

    /** Target setting still open for this paper (its target deadline has not passed)? */
    private static function targetWindowOpen(array $paper): bool
    {
        $dates = (new \App\Models\DocumentFolderModel())->getFolderDates($paper);
        return !empty($dates['target_date_end']) && $dates['target_date_end'] >= date('Y-m-d H:i:s');
    }

    /** The folder fields that start a paper again from a clean template in target setting. */
    private static function cleanDraft(array $paper, OrgStructure $org): array
    {
        $docType = strtoupper((string) ($paper['doc_type'] ?: $org->defaultDocTypeFor((int) $paper['user_id'])));
        $template = get_default_form_template($docType) ?: [['id' => 'tab-' . uniqid(), 'title' => 'Target Form', 'formData' => ['doc_type' => $docType, 'title' => $docType], 'content' => '']];
        return ['tabs' => json_encode($template), 'status' => FolderStatus::DRAFT_TARGET->value, 'target_submitted_at' => null,
                'target_approved_at' => null, 'target_approved_by' => null, 'target_approver_role' => null];
    }

    /**
     * A paper revoked before 2026-11-08 kept its approved targets while target setting was still open: reopen them the
     * same way (its copy is already in the version history from that revoke).
     */
    private static function reopenApprovedTargets(array $paper, OrgStructure $org): void
    {
        $db = \Config\Database::connect();
        $db->table('document_folders')->where('id', $paper['id'])->update(self::cleanDraft($paper, $org) + ['updated_at' => date('Y-m-d H:i:s')]);
        refresh_draft_paper_snapshots([(int) $paper['user_id']]);
        notify_user((int) $paper['user_id'], ['type' => 'system', 'title' => 'Targets back to draft', 'link' => 'folders/' . $paper['id'], 'icon' => 'folder',
            'message' => "Your targets on \"{$paper['title']}\" were approved by a supervisor who no longer reviews you. Target setting is still open, so the form starts again from a clean template for your new supervisor; the approved copy is in its version history."]);
        audit_log('REVIEW_REVOKED', 'FOLDER', 'folder', 0, "{$paper['id']} ({$paper['email']}): approved targets reopened (supervisor revoked earlier, target setting open)");
    }

    private static function revoke(array $paper, array $revoked, int $remaining, OrgStructure $org, array $keptFolders = []): void
    {
        $db = \Config\Database::connect();
        $ownerId = (int) $paper['user_id'];
        $names = implode(', ', array_column($revoked, 'name'));
        $reason = 'No longer their supervisor (moved, left or no longer a head above them).';
        $waiting = $remaining === 0 && !$org->isTopHead($ownerId);   // a VP reports to the Admin: never waiting
        // Approved targets go back to a clean draft too while target setting is open: the new supervisor approves them.
        $inTargetSetting = in_array($paper['status'], self::TARGET_SETTING, true)
            || ($waiting && $paper['status'] === FolderStatus::TARGET_APPROVED->value && self::targetWindowOpen($paper));

        // 1. Keep a copy of the form in the version history.
        $versions = new \App\Models\DocumentVersionModel();
        $latest = $versions->where('folder_id', $paper['id'])->orderBy('version_number', 'DESC')->first();
        $ver = $latest ? (int) $latest['version_number'] + 1 : 1;
        $now = date('Y-m-d H:i:s');
        $versions->insert([
            'folder_id' => $paper['id'], 'version_number' => $ver, 'phase' => $inTargetSetting ? 'target' : 'evaluation',
            'tabs' => is_string($paper['tabs']) ? $paper['tabs'] : json_encode($paper['tabs']),
            'returned_by' => $revoked[0]['id'], 'returned_by_name' => $names, 'returned_by_role' => 'Revoked',
            'returned_at' => $now, 'return_remarks' => $reason,
        ]);

        // 2. Their access goes.
        $db->table('evaluation_routings')->whereIn('id', array_column($revoked, 'route'))->delete();

        // 3. No supervisor left: start the form again in target setting (the agreed commitment stays afterwards).
        $update = ['current_version' => $ver + 1, 'updated_at' => $now];
        // Another supervisor stays: when the paper came from a revoked one's folder, it now comes from theirs
        // (as Folder::uncascadeTeam does).
        if ($keptFolders && !empty($paper['parent_folder_id'])) {
            $parentOwner = $db->table('document_folders')->select('user_id')->where('id', $paper['parent_folder_id'])->get()->getRowArray();
            if ($parentOwner && in_array((int) $parentOwner['user_id'], array_column($revoked, 'id'), true)) $update['parent_folder_id'] = $keptFolders[0];
        }
        if ($waiting) {
            $update['waiting_supervisor'] = 1;
            if ($inTargetSetting) $update += self::cleanDraft($paper, $org);
        }
        $db->table('document_folders')->where('id', $paper['id'])->update($update);
        refresh_draft_paper_snapshots([$ownerId]);

        // 4. Tell everyone concerned.
        $ownerName = trim($paper['first_name'] . ' ' . $paper['last_name']);
        $link = 'folders/' . $paper['id'];
        notify_user($ownerId, ['type' => 'system', 'title' => 'Your reviewer changed', 'link' => $link, 'icon' => 'folder',
            'message' => "{$names} no longer reviews your \"{$paper['title']}\" paper. A copy of your form is in its version history (v{$ver})."
                . ($waiting ? ' It is waiting for your new supervisor to cascade to you' . ($inTargetSetting ? '; your form starts again from a clean template.' : '.') : '')]);
        foreach ($revoked as $r) {
            notify_user($r['id'], ['type' => 'system', 'title' => 'Review removed', 'link' => 'ratings', 'icon' => 'folder',
                'message' => "You no longer review {$ownerName}'s \"{$paper['title']}\" paper: they are no longer under you."]);
        }
        if ($waiting) {
            $heads = self::newHeadsFor($org, $ownerId);
            $msg = "{$ownerName} is waiting for a supervisor on \"{$paper['title']}\". Cascade to them from My Staff.";
            if ($heads) foreach ($heads as $h) notify_user($h, ['type' => 'system', 'title' => 'Someone is waiting for you', 'message' => $msg, 'link' => 'teams', 'icon' => 'user']);
            else notify_admins(['type' => 'system', 'title' => 'Paper waiting for a supervisor', 'message' => "{$msg} Their unit has no head right now.", 'link' => 'accounts', 'icon' => 'user']);
        }
        audit_log('REVIEW_REVOKED', 'FOLDER', 'folder', 0, "{$paper['id']} ({$paper['email']}): revoked {$names}; copy kept as v{$ver}"
            . ($waiting ? ($inTargetSetting ? '; form reset, waiting for a supervisor' : '; waiting for a supervisor') : ''));
    }
}
