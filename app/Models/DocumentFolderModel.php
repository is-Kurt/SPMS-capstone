<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Enums\FolderStatus;

class  DocumentFolderModel extends Model
{
    protected $table            = 'document_folders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id', 
        'title', 
        'user_id', 
        'parent_folder_id',
        'final_rating',
        'cycle_id',
        'cycle_folder_id',
        'tabs',
        'target_submitted_at',
        'target_approved_at',
        'target_approved_by',
        'target_approver_role',
        'submitted_at',
        'rated_at',
        'deadline_reminder_sent_at',
        'target_deadline_reminder_sent_at',
        'target_period_open_sent_at',
        'eval_period_open_sent_at',
        'routing_preset_id',
        'status',
        'current_version',
        'rating_period',
        'approving_authority_name',
        'approving_authority_position',
        'deleted_at'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = false;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = ['updateTimeBasedStatuses'];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * For Rating.php: Fetches the folders that belong on this evaluator's dashboard.
     * Admins see every folder system-wide (oversight); everyone else (Supervisor, HR)
     * only sees folders they've actually been routed to evaluate via a cascaded Team
     * (evaluation_routings.evaluator_id), not everyone in the organization.
     */
    public function getRatingDashboardFolders(int $userId, string $sysRole, ?string $parentFolderId = null, bool $isPresident = false): array
    {
        $cascadedCondition = ($sysRole === 'Admin')
            ? "(pf.parent_folder_id IS NULL AND (pf.user_id = " . (int)$userId . " OR er_me.evaluator_id IS NOT NULL))"
            : "(er_me.evaluator_id IS NOT NULL OR pf.user_id = " . (int)$userId . ")";

        $builder = $this->db->table('document_folders df')
            ->select("df.id as folder_id, df.user_id, (u.first_name || ' ' || u.last_name) as username,
                      REPLACE(GROUP_CONCAT(DISTINCT pos.title), ',', ', ') as position,
                      GROUP_CONCAT(DISTINCT pos.id) as position_id,
                      REPLACE(GROUP_CONCAT(DISTINCT un.name), ',', ', ') as department,
                      GROUP_CONCAT(DISTINCT un.id) as unit_id,
                      MAX(pos.is_teaching) as is_teaching,
                      COALESCE(MAX(p.employment_status), 'Permanent') as employment_status,
                      df.final_rating, df.status as folder_status,
                      df.current_version, df.target_submitted_at, df.submitted_at,
                      df.target_approved_at, df.target_approver_role,
                      MAX(er_me.status) as my_routing_status,
                      MAX(CASE WHEN {$cascadedCondition} THEN 1 ELSE 0 END) as is_cascaded_by_me")
            ->join('users u', 'u.id = df.user_id')
            ->join('plantillas p', 'p.user_id = u.id AND p.ended_at IS NULL', 'left')
            ->join('positions pos', 'pos.id = p.position_id', 'left')
            ->join('units un', 'un.id = p.unit_id', 'left')
            ->join('evaluation_routings er_me', 'er_me.folder_id = df.id AND er_me.evaluator_id = ' . (int)$userId, 'left')
            ->join('document_folders pf', 'pf.id = df.parent_folder_id', 'left')
            ->where('df.parent_folder_id IS NOT NULL')
            ->where('df.deleted_at IS NULL')
            ->where('df.user_id !=', (int)$userId);

        if ($parentFolderId) {
            $allDescendantIds = $this->getAllDescendantFolderIds($parentFolderId);
            if (!empty($allDescendantIds)) {
                $builder->whereIn('df.id', $allDescendantIds);
            } else {
                $builder->where('df.id', -1);
            }
        }

        if ($isPresident) {
            // President's exclusive scope: Executive review of Vice Presidents' institutional OPCRs
            $builder->groupStart()
                ->where('LOWER(u.doc_type)', 'opcr')
                ->orLike('pos.title', 'Vice President', 'both')
                ->orLike('u.email', 'vp', 'both')
                ->orLike('u.email', 'cao', 'both')
            ->groupEnd();
        } elseif ($sysRole === 'Admin') {
            // Admin has institutional oversight over all submissions in the cycle,
            // while distinguishing what Admin directly cascaded vs subordinate folders via is_cascaded_by_me
        } elseif ($sysRole === 'TWG') {
            $twgAssignmentModel = new \App\Models\TwgUnitAssignmentModel();
            // Strictly non-cascading: Only folders belonging directly to assigned units
            $allowedUnitIds = $twgAssignmentModel->getAssignedUnitIds($userId);
            if (!empty($allowedUnitIds)) {
                $builder->whereIn('p.unit_id', $allowedUnitIds);
            } else {
                $builder->where('df.id', -1);
            }
        } elseif ($sysRole !== 'PMT') {
            $builder->where('er_me.evaluator_id', $userId);
        }

        $builder->groupBy('df.id');
        $rows = $builder->get()->getResultArray();

        if (!empty($rows)) {
            $folderIds = array_column($rows, 'folder_id');
            $routeRows = $this->db->table('evaluation_routings er')
                ->select('er.folder_id, er.evaluator_id, er.status, GROUP_CONCAT(r.name) as eval_roles')
                ->join('user_roles ur', 'ur.user_id = er.evaluator_id', 'left')
                ->join('roles r', 'r.id = ur.role_id', 'left')
                ->whereIn('er.folder_id', $folderIds)
                ->groupBy('er.id')
                ->get()->getResultArray();

            $routesByFolder = [];
            foreach ($routeRows as $rr) {
                $routesByFolder[$rr['folder_id']][] = $rr;
            }

            foreach ($rows as &$f) {
                $fId = $f['folder_id'];
                $fRoutes = $routesByFolder[$fId] ?? [];
                $nonAdminRoutes = [];
                $adminRoutes = [];

                foreach ($fRoutes as $rr) {
                    if ((int)$rr['evaluator_id'] === (int)$f['user_id']) continue;
                    $rolesStr = strtoupper((string)($rr['eval_roles'] ?? ''));
                    $rolesArr = array_map('trim', explode(',', $rolesStr));
                    if (in_array('TWG', $rolesArr, true) || in_array('PMT', $rolesArr, true)) continue;

                    if (in_array('ADMIN', $rolesArr, true)) {
                        $adminRoutes[] = $rr;
                    } else {
                        $nonAdminRoutes[] = $rr;
                    }
                }

                $effectiveRoutes = !empty($nonAdminRoutes) ? $nonAdminRoutes : $adminRoutes;
                $allSupApproved = false;
                $anyReturned = false;

                if (!empty($effectiveRoutes)) {
                    $allSupApproved = true;
                    foreach ($effectiveRoutes as $er) {
                        $st = strtolower(trim((string)($er['status'] ?? '')));
                        if (!in_array($st, ['approved', 'target_approved'], true)) {
                            $allSupApproved = false;
                        }
                        if (in_array($st, ['returned', 'target_returned', 'eval_returned', 'reevaluate'], true)) {
                            $anyReturned = true;
                        }
                    }
                } else {
                    $allSupApproved = !empty($f['target_approved_at']) || in_array($f['folder_status'], [
                        FolderStatus::TARGET_APPROVED->value,
                        FolderStatus::EVAL_APPROVED->value
                    ], true);
                }

                $f['all_supervisors_approved'] = $allSupApproved;
                $f['has_returned_routing']     = $anyReturned;
            }
            unset($f);
        }

        return $rows;
    }

    /**
     * Recursively traverses and collects all descendant folder IDs under a given root folder.
     */
    public function getAllDescendantFolderIds(string|int $rootFolderId): array
    {
        $descendantIds = [];
        $frontier = [$rootFolderId];

        while (!empty($frontier)) {
            $children = $this->select('id')
                ->whereIn('parent_folder_id', $frontier)
                ->where('deleted_at IS NULL')
                ->findAll();

            if (empty($children)) {
                break;
            }

            $childIds = array_column($children, 'id');
            $newIds = array_diff($childIds, $descendantIds);
            if (empty($newIds)) {
                break;
            }

            foreach ($newIds as $cid) {
                $descendantIds[] = $cid;
            }
            $frontier = $newIds;
        }

        return $descendantIds;
    }

    /**
     * For CheckFolderDeadlines cron: still-Draft folders whose real deadline
     * (eval_date_end - the same field updateTimeBasedStatuses() uses to sweep
     * unfinished folders to UNEVALUATED, and what the "deadline missed" email
     * itself calls the deadline) is within $withinDays but hasn't passed yet,
     * and that haven't already had a reminder queued (deadline_reminder_sent_at).
     * A "<=" range instead of an exact-day match so a missed cron run still
     * catches the folder on its next run, instead of silently skipping it.
     * Excludes Admin-owned folders - Admins don't get this reminder even if
     * they happen to have their own Draft folder nearing deadline.
     */
    public function getNearingDeadlineFolders(int $withinDays = 3): array
    {
        $now    = date('Y-m-d H:i:s');
        $cutoff = date('Y-m-d H:i:s', strtotime("+{$withinDays} days"));

        return $this->db->table('document_folders df')
            ->select('df.id, u.email, u.first_name, u.doc_type, pos.title as position, cd.eval_end, cd.eval_end as ipcr_eval_end')
            ->join('users u', 'u.id = df.user_id')
            ->join('user_roles ur', 'ur.user_id = u.id', 'left')
            ->join('roles r', 'r.id = ur.role_id', 'left')
            ->join('plantillas p', 'p.user_id = u.id AND p.ended_at IS NULL', 'left')
            ->join('positions pos', 'pos.id = p.position_id', 'left')
            ->join('cycle_deadlines cd', 'cd.cycle_folder_id = COALESCE(df.cycle_folder_id, df.parent_folder_id, df.id) AND cd.doc_type = LOWER(COALESCE(u.doc_type, "ipcr"))', 'left')
            ->where('df.deleted_at IS NULL')
            ->where('df.status', FolderStatus::DRAFT_EVALUATION->value)
            ->where('df.deadline_reminder_sent_at IS NULL')
            ->groupStart()
                ->where('r.name !=', 'Admin')
                ->orWhere('r.name IS NULL')
            ->groupEnd()
            ->where('cd.eval_end >=', $now)
            ->where('cd.eval_end <=', $cutoff)
            ->get()->getResultArray();
    }

    /**
     * Gets folders whose target setting window closes within X days, and haven't
     * already received a target deadline reminder.
     */
    public function getNearingTargetDeadlineFolders(int $withinDays = 3): array
    {
        $now    = date('Y-m-d H:i:s');
        $cutoff = date('Y-m-d H:i:s', strtotime("+{$withinDays} days"));

        return $this->db->table('document_folders df')
            ->select('df.id, u.email, u.first_name, u.doc_type, pos.title as position, cd.target_end, cd.target_end as ipcr_target_end')
            ->join('users u', 'u.id = df.user_id')
            ->join('user_roles ur', 'ur.user_id = u.id', 'left')
            ->join('roles r', 'r.id = ur.role_id', 'left')
            ->join('plantillas p', 'p.user_id = u.id AND p.ended_at IS NULL', 'left')
            ->join('positions pos', 'pos.id = p.position_id', 'left')
            ->join('cycle_deadlines cd', 'cd.cycle_folder_id = COALESCE(df.cycle_folder_id, df.parent_folder_id, df.id) AND cd.doc_type = LOWER(COALESCE(u.doc_type, "ipcr"))', 'left')
            ->where('df.deleted_at IS NULL')
            ->where('df.status', FolderStatus::DRAFT_TARGET->value)
            ->where('df.target_deadline_reminder_sent_at IS NULL')
            ->groupStart()
                ->where('r.name !=', 'Admin')
                ->orWhere('r.name IS NULL')
            ->groupEnd()
            ->where('cd.target_end >=', $now)
            ->where('cd.target_end <=', $cutoff)
            ->get()->getResultArray();
    }

    public function updateTimeBasedStatuses()
    {
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');
        helper('email_queue');
        $userModel = new UserModel();

        // 0. Process Target Period Open (DRAFT_TARGET && target_date_start <= now)
        $startingTargetFolders = $db->table($this->table . ' df')
            ->select('df.id, df.user_id, df.title, u.email, u.first_name')
            ->join('users u', 'u.id = df.user_id')
            ->join('plantillas p', 'p.user_id = u.id AND p.ended_at IS NULL', 'left')
            ->join('positions pos', 'pos.id = p.position_id', 'left')
            ->join('cycle_deadlines cd', 'cd.cycle_folder_id = COALESCE(df.cycle_folder_id, df.parent_folder_id, df.id) AND cd.doc_type = LOWER(COALESCE(u.doc_type, "ipcr"))', 'left')
            ->where('df.deleted_at IS NULL')
            ->where('df.status', \App\Enums\FolderStatus::DRAFT_TARGET->value)
            ->where('df.target_period_open_sent_at IS NULL')
            ->where('cd.target_start <=', $now)
            ->get()->getResultArray();

        foreach ($startingTargetFolders as $folder) {
            if ($userModel->hasRole($folder['user_id'], 'Admin')) continue;

            $link = site_url("folders/" . $folder['id']);
            queue_email(
                $folder['email'],
                'New Evaluation Folder: Target Setting Period Open',
                render_email('target_period_open', [
                    'firstName' => $folder['first_name'],
                    'link'      => $link,
                ])
            );
        }

        if (!empty($startingTargetFolders)) {
            $db->table($this->table)
                ->whereIn('id', array_column($startingTargetFolders, 'id'))
                ->update(['target_period_open_sent_at' => $now]);
        }

        // 0.25 Process Expired Targets -> TARGET_MISSED (Missed Deadline for Phase 1 by Ratee)
        $unapprovedFolders = $db->table($this->table . ' df')
            ->select('df.id, df.user_id, u.email, u.first_name')
            ->join('users u', 'u.id = df.user_id')
            ->join('plantillas p', 'p.user_id = u.id AND p.ended_at IS NULL', 'left')
            ->join('positions pos', 'pos.id = p.position_id', 'left')
            ->join('cycle_deadlines cd', 'cd.cycle_folder_id = COALESCE(df.cycle_folder_id, df.parent_folder_id, df.id) AND cd.doc_type = LOWER(COALESCE(u.doc_type, "ipcr"))', 'left')
            ->where('df.deleted_at IS NULL')
            ->where('df.status', \App\Enums\FolderStatus::DRAFT_TARGET->value)
            ->where('df.target_submitted_at IS NULL')
            ->where('cd.target_end <', $now)
            ->get()->getResultArray();

        $missedFolderIds = [];
        foreach ($unapprovedFolders as $folder) {
            if ($userModel->hasRole($folder['user_id'], 'Admin')) continue;
            $missedFolderIds[] = $folder['id'];

            queue_email(
                $folder['email'],
                'Notice: Target Submission Deadline Missed',
                render_email('target_deadline_missed', ['firstName' => $folder['first_name']])
            );
        }
            
        if (!empty($missedFolderIds)) {
            $db->table($this->table)
                ->whereIn('id', $missedFolderIds)
                ->update(['status' => \App\Enums\FolderStatus::TARGET_MISSED->value]);
        }

        // 0.30 Process Expired Submitted Targets -> TARGET_UNAPPROVED (Submitted on time, but unapproved by authorities)
        $expiredSubmittedTargets = $db->table($this->table . ' df')
            ->select('df.id')
            ->join('users u', 'u.id = df.user_id')
            ->join('cycle_deadlines cd', 'cd.cycle_folder_id = COALESCE(df.cycle_folder_id, df.parent_folder_id, df.id) AND cd.doc_type = LOWER(COALESCE(u.doc_type, "ipcr"))', 'left')
            ->where('df.deleted_at IS NULL')
            ->where('df.status', \App\Enums\FolderStatus::TARGET_SUBMITTED->value)
            ->where('df.target_submitted_at IS NOT NULL')
            ->where('cd.target_end <', $now)
            ->get()->getResultArray();

        if (!empty($expiredSubmittedTargets)) {
            $db->table($this->table)
                ->whereIn('id', array_column($expiredSubmittedTargets, 'id'))
                ->update(['status' => \App\Enums\FolderStatus::TARGET_UNAPPROVED->value]);
        }

        // 1. Process TARGET_APPROVED -> DRAFT_EVALUATION (Eval Window Open)
        $startingFolders = $db->table($this->table . ' df')
            ->select('df.id, df.user_id, df.title, u.email, u.first_name')
            ->join('users u', 'u.id = df.user_id')
            ->join('plantillas p', 'p.user_id = u.id AND p.ended_at IS NULL', 'left')
            ->join('positions pos', 'pos.id = p.position_id', 'left')
            ->join('cycle_deadlines cd', 'cd.cycle_folder_id = COALESCE(df.cycle_folder_id, df.parent_folder_id, df.id) AND cd.doc_type = LOWER(COALESCE(u.doc_type, "ipcr"))', 'left')
            ->where('df.deleted_at IS NULL')
            ->whereIn('df.status', [
                \App\Enums\FolderStatus::TARGET_APPROVED->value,
                \App\Enums\FolderStatus::DRAFT_EVALUATION->value,
            ])
            ->where('df.eval_period_open_sent_at IS NULL')
            ->where('cd.eval_start <=', $now)
            ->where('cd.eval_end >=', $now)
            ->get()->getResultArray();

        $startingFolderIds = [];
        foreach ($startingFolders as $folder) {
            if ($userModel->hasRole($folder['user_id'], 'Admin')) continue;
            $startingFolderIds[] = $folder['id'];

            $link = site_url("folders/" . $folder['id']);
            queue_email(
                $folder['email'],
                'Action Required: Evaluation Period Open',
                render_email('evaluation_period_open', [
                    'firstName' => $folder['first_name'],
                    'title'     => $folder['title'],
                    'link'      => $link,
                ])
            );
        }

        if (!empty($startingFolderIds)) {
            $db->table($this->table)
                ->whereIn('id', $startingFolderIds)
                ->update([
                    'status' => \App\Enums\FolderStatus::DRAFT_EVALUATION->value,
                    'eval_period_open_sent_at' => $now
                ]);
        }

        // 2. Find folders that just expired (Before we change their status)
        $expiringFolders = $db->table($this->table . ' df')
            ->select('df.id, df.user_id, u.email, u.first_name')
            ->join('users u', 'u.id = df.user_id')
            ->join('plantillas p', 'p.user_id = u.id AND p.ended_at IS NULL', 'left')
            ->join('positions pos', 'pos.id = p.position_id', 'left')
            ->join('cycle_deadlines cd', 'cd.cycle_folder_id = COALESCE(df.cycle_folder_id, df.parent_folder_id, df.id) AND cd.doc_type = LOWER(COALESCE(u.doc_type, "ipcr"))', 'left')
            ->where('df.deleted_at IS NULL')
            ->whereNotIn('df.status', [
                \App\Enums\FolderStatus::EVAL_APPROVED->value,
                \App\Enums\FolderStatus::EVAL_SUBMITTED->value,
                \App\Enums\FolderStatus::UNEVALUATED->value,
                \App\Enums\FolderStatus::EVAL_UNRATED->value,
                \App\Enums\FolderStatus::TARGET_MISSED->value,
                \App\Enums\FolderStatus::TARGET_UNAPPROVED->value,
                \App\Enums\FolderStatus::DRAFT_TARGET->value,
                \App\Enums\FolderStatus::TARGET_SUBMITTED->value,
            ])
            ->where('df.submitted_at IS NULL')
            ->where('cd.eval_end <', $now)
            ->get()->getResultArray();

        $expiringFolderIds = [];
        foreach ($expiringFolders as $folder) {
            if ($userModel->hasRole($folder['user_id'], 'Admin')) continue;
            $expiringFolderIds[] = $folder['id'];

            queue_email(
                $folder['email'],
                'Notice: Evaluation Submission Deadline Missed',
                render_email('deadline_missed', ['firstName' => $folder['first_name']])
            );
        }
        
        if (!empty($expiringFolderIds)) {
            $db->table($this->table)
                ->whereIn('id', $expiringFolderIds)
                ->update(['status' => \App\Enums\FolderStatus::UNEVALUATED->value]);
        }

        // 2b. Expired Submitted Evaluation Folders -> EVAL_UNRATED (Submitted on time, but unrated by authorities)
        $expiredSubmittedEvals = $db->table($this->table . ' df')
            ->select('df.id')
            ->join('users u', 'u.id = df.user_id')
            ->join('cycle_deadlines cd', 'cd.cycle_folder_id = COALESCE(df.cycle_folder_id, df.parent_folder_id, df.id) AND cd.doc_type = LOWER(COALESCE(u.doc_type, "ipcr"))', 'left')
            ->where('df.deleted_at IS NULL')
            ->where('df.status', \App\Enums\FolderStatus::EVAL_SUBMITTED->value)
            ->where('df.submitted_at IS NOT NULL')
            ->where('cd.eval_end <', $now)
            ->get()->getResultArray();

        if (!empty($expiredSubmittedEvals)) {
            $db->table($this->table)
                ->whereIn('id', array_column($expiredSubmittedEvals, 'id'))
                ->update(['status' => \App\Enums\FolderStatus::EVAL_UNRATED->value]);
        }

        // 2c. Self-heal any folders previously misclassified as 'unevaluated' that actually had a submission
        $misclassifiedEvals = $db->table($this->table)
            ->select('id')
            ->where('deleted_at IS NULL')
            ->where('status', \App\Enums\FolderStatus::UNEVALUATED->value)
            ->where('submitted_at IS NOT NULL')
            ->get()->getResultArray();

        if (!empty($misclassifiedEvals)) {
            $db->table($this->table)
                ->whereIn('id', array_column($misclassifiedEvals, 'id'))
                ->update(['status' => \App\Enums\FolderStatus::EVAL_UNRATED->value]);
        }

        // Self-heal any folders previously misclassified as 'target_missed' that actually had target submission
        $misclassifiedTargets = $db->table($this->table)
            ->select('id')
            ->where('deleted_at IS NULL')
            ->where('status', \App\Enums\FolderStatus::TARGET_MISSED->value)
            ->where('target_submitted_at IS NOT NULL')
            ->get()->getResultArray();

        if (!empty($misclassifiedTargets)) {
            $db->table($this->table)
                ->whereIn('id', array_column($misclassifiedTargets, 'id'))
                ->update(['status' => \App\Enums\FolderStatus::TARGET_UNAPPROVED->value]);
        }
    }

    public function getRootCycleFolderId(string $folderId): string {
        $curr = $this->find($folderId);
        if (!$curr) return $folderId;
        if (!empty($curr['cycle_folder_id'])) return $curr['cycle_folder_id'];
        while ($curr && !empty($curr['parent_folder_id'])) {
            $curr = $this->find($curr['parent_folder_id']);
            if (!empty($curr['cycle_folder_id'])) return $curr['cycle_folder_id'];
        }
        return $curr['id'] ?? $folderId;
    }

    public function enrichFolderWithDeadlines(?array $folder): ?array {
        if (!$folder) return null;
        $cycleFolderId = $folder['cycle_id'] ?? $folder['cycle_folder_id'] ?? $this->getRootCycleFolderId($folder['id']);
        $deadlineModel = new \App\Models\CycleDeadlineModel();
        $flatDates = $deadlineModel->getCycleDatesFlat($cycleFolderId);
        $folderDates = $this->getFolderDates($folder);
        return array_merge($folder, $flatDates, $folderDates ?? []);
    }

    public function getFolderDates($folder) {
        if (empty($folder)) return null;

        $userModel = new \App\Models\UserModel();
        $owner = !empty($folder['user_id']) ? $userModel->find($folder['user_id']) : null;
        
        $docType = null;
        if ($owner) {
            $plantilla = $userModel->getActivePlantillaDetails($owner['id']);
            $pos = strtolower($plantilla['position'] ?? '');
            $email = strtolower($owner['email'] ?? '');
            $isDean = str_contains($pos, 'dean') || str_contains($email, 'dean');
            $isChair = str_contains($pos, 'chair') || str_contains($pos, 'head') || str_contains($email, 'chair');

            $docType = !empty($owner['doc_type']) ? strtolower($owner['doc_type']) : null;
            if (!$docType) {
                if ($isDean || $isChair) {
                    $docType = 'dpcr';
                } elseif (str_contains($pos, 'president') || str_contains($pos, 'vpaa') || str_contains($email, 'vpaa')) {
                    $docType = 'opcr';
                }
            }
        }
        $docType = $docType ?: 'ipcr';

        $cycleFolderId = $folder['cycle_id'] ?? $folder['cycle_folder_id'] ?? $this->getRootCycleFolderId($folder['id']);
        $deadlineModel = new \App\Models\CycleDeadlineModel();
        $deadline = $deadlineModel->getDeadline($cycleFolderId, $docType);

        if (empty($deadline) || (empty($deadline['target_start']) && empty($deadline['target_end']) && empty($deadline['eval_start']) && empty($deadline['eval_end']))) {
            $all = $deadlineModel->getDeadlinesForCycle($cycleFolderId);
            foreach ($all as $d) {
                if (!empty($d['target_end']) || !empty($d['eval_end'])) {
                    $deadline = $d;
                    break;
                }
            }
        }

        return [
            'target_date_start' => $deadline['target_start'] ?? null,
            'target_date_end'   => $deadline['target_end'] ?? null,
            'eval_date_start'   => $deadline['eval_start'] ?? null,
            'eval_date_end'     => $deadline['eval_end'] ?? null,
        ];
    }

    public function isFolderLocked($folder) {
        if (!$folder) return true; 

        // Archived/closed cycles are permanently frozen
        if (!empty($folder['deleted_at'])) {
            return true;
        }

        // Allow structure/cascade changes during target drafting and target approved phases
        $isLocked = !in_array($folder['status'], [
            FolderStatus::DRAFT_TARGET->value,
            FolderStatus::TARGET_SUBMITTED->value,
            FolderStatus::TARGET_APPROVED->value,
            FolderStatus::DRAFT_EVALUATION->value,
        ]);
        
        $dates = $this->getFolderDates($folder);
        $isPastDeadline = !empty($dates['eval_date_end']) && date('Y-m-d H:i:s') > $dates['eval_date_end'];
        
        return ($isLocked || $isPastDeadline);
    }
}
