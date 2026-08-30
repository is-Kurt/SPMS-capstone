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
        'ipcr_target_start', 'ipcr_target_end', 'ipcr_eval_start', 'ipcr_eval_end',
        'dpcr_target_start', 'dpcr_target_end', 'dpcr_eval_start', 'dpcr_eval_end',
        'opcr_target_start', 'opcr_target_end', 'opcr_eval_start', 'opcr_eval_end',
        'iperf_target_start', 'iperf_target_end', 'iperf_eval_start', 'iperf_eval_end',
        'target_submitted_at',
        'target_approved_at',
        'submitted_at',
        'rated_at',
        'deadline_reminder_sent_at',
        'target_deadline_reminder_sent_at',
        'target_period_open_sent_at',
        'eval_period_open_sent_at',
        'routing_preset_id',
        'status'
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
    protected $beforeInsert   = ['setDefaultEvalDates'];
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
    public function getRatingDashboardFolders(int $userId, string $sysRole, ?string $parentFolderId = null): array
    {
        $builder = $this->db->table('document_folders df')
            ->select("df.id as folder_id, df.user_id, (u.first_name || ' ' || u.last_name) as username,
                      REPLACE(GROUP_CONCAT(DISTINCT pos.title), ',', ', ') as position,
                      REPLACE(GROUP_CONCAT(DISTINCT un.name), ',', ', ') as department,
                      MAX(pos.is_teaching) as is_teaching,
                      df.final_rating, df.status as folder_status")
            ->join('users u', 'u.id = df.user_id')
            ->join('plantillas p', 'p.user_id = u.id AND p.ended_at IS NULL', 'left')
            ->join('positions pos', 'pos.id = p.position_id', 'left')
            ->join('units un', 'un.id = p.unit_id', 'left')
            ->where('df.parent_folder_id IS NOT NULL');

        if ($parentFolderId) {
            $builder->where('df.parent_folder_id', $parentFolderId);
        }

        if ($sysRole !== 'Admin' && $sysRole !== 'TWG') {
            $builder->join('evaluation_routings er_me', 'er_me.folder_id = df.id')
                    ->where('er_me.evaluator_id', $userId);
        }

        $builder->groupBy('df.id');
        return $builder->get()->getResultArray();
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
            ->select('df.id, u.email, u.first_name, u.doc_type, df.ipcr_eval_end, df.dpcr_eval_end, df.opcr_eval_end, df.iperf_eval_end')
            ->join('users u', 'u.id = df.user_id')
            ->join('user_roles ur', 'ur.user_id = u.id', 'left')
            ->join('roles r', 'r.id = ur.role_id', 'left')
            ->where('df.status', FolderStatus::DRAFT->value)
            ->where('df.deadline_reminder_sent_at IS NULL')
            ->groupStart()
                ->where('r.name !=', 'Admin')
                ->orWhere('r.name IS NULL')
            ->groupEnd()
            ->groupStart()
                ->groupStart()->where('LOWER(COALESCE(u.doc_type, "ipcr"))', 'ipcr')->where('df.ipcr_eval_end >=', $now)->where('df.ipcr_eval_end <=', $cutoff)->groupEnd()
                ->orGroupStart()->where('LOWER(u.doc_type)', 'dpcr')->where('df.dpcr_eval_end >=', $now)->where('df.dpcr_eval_end <=', $cutoff)->groupEnd()
                ->orGroupStart()->where('LOWER(u.doc_type)', 'opcr')->where('df.opcr_eval_end >=', $now)->where('df.opcr_eval_end <=', $cutoff)->groupEnd()
                ->orGroupStart()->where('LOWER(u.doc_type)', 'iperf')->where('df.iperf_eval_end >=', $now)->where('df.iperf_eval_end <=', $cutoff)->groupEnd()
            ->groupEnd()
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
            ->select('df.id, u.email, u.first_name, u.doc_type, df.ipcr_target_end, df.dpcr_target_end, df.opcr_target_end, df.iperf_target_end')
            ->join('users u', 'u.id = df.user_id')
            ->join('user_roles ur', 'ur.user_id = u.id', 'left')
            ->join('roles r', 'r.id = ur.role_id', 'left')
            ->where('df.status', FolderStatus::DRAFT_TARGET->value)
            ->where('df.target_deadline_reminder_sent_at IS NULL')
            ->groupStart()
                ->where('r.name !=', 'Admin')
                ->orWhere('r.name IS NULL')
            ->groupEnd()
            ->groupStart()
                ->groupStart()->where('LOWER(COALESCE(u.doc_type, "ipcr"))', 'ipcr')->where('df.ipcr_target_end >=', $now)->where('df.ipcr_target_end <=', $cutoff)->groupEnd()
                ->orGroupStart()->where('LOWER(u.doc_type)', 'dpcr')->where('df.dpcr_target_end >=', $now)->where('df.dpcr_target_end <=', $cutoff)->groupEnd()
                ->orGroupStart()->where('LOWER(u.doc_type)', 'opcr')->where('df.opcr_target_end >=', $now)->where('df.opcr_target_end <=', $cutoff)->groupEnd()
                ->orGroupStart()->where('LOWER(u.doc_type)', 'iperf')->where('df.iperf_target_end >=', $now)->where('df.iperf_target_end <=', $cutoff)->groupEnd()
            ->groupEnd()
            ->get()->getResultArray();
    }

    protected function setDefaultEvalDates(array $data): array {
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));

        $docTypes = ['ipcr', 'dpcr', 'opcr', 'iperf'];
        foreach ($docTypes as $type) {
            if (empty($data['data']["{$type}_eval_start"]) && empty($data['data']["{$type}_eval_end"])) {
                $data['data']["{$type}_eval_start"] = $today . ' 24:00:00';
                $data['data']["{$type}_eval_end"] = $tomorrow . ' 24:00:00';
            }
        }

        return $data;
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
            ->where('df.status', \App\Enums\FolderStatus::DRAFT_TARGET->value)
            ->where('df.target_period_open_sent_at IS NULL')
            ->groupStart()
                ->groupStart()->where('LOWER(COALESCE(u.doc_type, "ipcr"))', 'ipcr')->where('df.ipcr_target_start <=', $now)->groupEnd()
                ->orGroupStart()->where('LOWER(u.doc_type)', 'dpcr')->where('df.dpcr_target_start <=', $now)->groupEnd()
                ->orGroupStart()->where('LOWER(u.doc_type)', 'opcr')->where('df.opcr_target_start <=', $now)->groupEnd()
                ->orGroupStart()->where('LOWER(u.doc_type)', 'iperf')->where('df.iperf_target_start <=', $now)->groupEnd()
            ->groupEnd()
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

        // 0.25 Process Expired Targets -> TARGET_UNAPPROVED
        $unapprovedFolders = $db->table($this->table . ' df')
            ->select('df.id, df.user_id, u.email, u.first_name')
            ->join('users u', 'u.id = df.user_id')
            ->whereIn('df.status', [
                \App\Enums\FolderStatus::DRAFT_TARGET->value,
                \App\Enums\FolderStatus::PENDING_TARGET_APPROVAL->value,
                \App\Enums\FolderStatus::TARGET_RETURNED->value
            ])
            ->groupStart()
                ->groupStart()->where('LOWER(COALESCE(u.doc_type, "ipcr"))', 'ipcr')->where('df.ipcr_target_end <', $now)->groupEnd()
                ->orGroupStart()->where('LOWER(u.doc_type)', 'dpcr')->where('df.dpcr_target_end <', $now)->groupEnd()
                ->orGroupStart()->where('LOWER(u.doc_type)', 'opcr')->where('df.opcr_target_end <', $now)->groupEnd()
                ->orGroupStart()->where('LOWER(u.doc_type)', 'iperf')->where('df.iperf_target_end <', $now)->groupEnd()
            ->groupEnd()
            ->get()->getResultArray();

        foreach ($unapprovedFolders as $folder) {
            if ($userModel->hasRole($folder['user_id'], 'Admin')) continue;

            queue_email(
                $folder['email'],
                'Notice: Target Submission Deadline Missed',
                render_email('target_deadline_missed', ['firstName' => $folder['first_name']])
            );
        }
            
        if (!empty($unapprovedFolders)) {
            $db->table($this->table)
                ->whereIn('id', array_column($unapprovedFolders, 'id'))
                ->update(['status' => \App\Enums\FolderStatus::TARGET_UNAPPROVED->value]);
        }

        // 1. Process TARGET_APPROVED or SUBMITTED -> TO_EVALUATE (Eval Window Open)
        // Note: this query intentionally stays unfiltered by role - it also drives
        // the status update just below, and an Admin's own folder still needs to
        // transition normally even though (per the per-folder check in the loop
        // further down) Admins don't get emailed about it.
        $startingFolders = $db->table($this->table . ' df')
            ->select('df.id, df.user_id, df.title, u.email, u.first_name')
            ->join('users u', 'u.id = df.user_id')
            ->whereIn('df.status', [
                \App\Enums\FolderStatus::TARGET_APPROVED->value,
                \App\Enums\FolderStatus::SUBMITTED->value,
                \App\Enums\FolderStatus::TO_EVALUATE->value
            ])
            ->where('df.eval_period_open_sent_at IS NULL')
            ->groupStart()
                ->groupStart()->where('LOWER(COALESCE(u.doc_type, "ipcr"))', 'ipcr')->where('df.ipcr_eval_start <=', $now)->where('df.ipcr_eval_end >=', $now)->groupEnd()
                ->orGroupStart()->where('LOWER(u.doc_type)', 'dpcr')->where('df.dpcr_eval_start <=', $now)->where('df.dpcr_eval_end >=', $now)->groupEnd()
                ->orGroupStart()->where('LOWER(u.doc_type)', 'opcr')->where('df.opcr_eval_start <=', $now)->where('df.opcr_eval_end >=', $now)->groupEnd()
                ->orGroupStart()->where('LOWER(u.doc_type)', 'iperf')->where('df.iperf_eval_start <=', $now)->where('df.iperf_eval_end >=', $now)->groupEnd()
            ->groupEnd()
            ->get()->getResultArray();

        foreach ($startingFolders as $folder) {
            // Admins oversee the whole system rather than being evaluated employees,
            // so drafting/deadline/approval reminders don't apply to them even if
            // they happen to own a folder themselves - status still transitions
            // normally above/below, only the notification email is skipped.
            if ($userModel->hasRole($folder['user_id'], 'Admin')) continue;

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

        if (!empty($startingFolders)) {
            $db->table($this->table)
                ->whereIn('id', array_column($startingFolders, 'id'))
                ->update([
                    'status' => \App\Enums\FolderStatus::TO_EVALUATE->value,
                    'eval_period_open_sent_at' => $now
                ]);
        }

        // 2. Find folders that just expired (Before we change their status)
        // Also intentionally unfiltered by role for the same reason as above - the
        // status update below still needs to run for an Admin's own folder.
        $expiringFolders = $db->table($this->table . ' df')
            ->select('df.id, df.user_id, u.email, u.first_name')
            ->join('users u', 'u.id = df.user_id')
            ->whereNotIn('df.status', [
                \App\Enums\FolderStatus::APPROVED->value,
                \App\Enums\FolderStatus::UNEVALUATED->value
            ])
            ->groupStart()
                ->groupStart()->where('LOWER(COALESCE(u.doc_type, "ipcr"))', 'ipcr')->where('df.ipcr_eval_end <', $now)->groupEnd()
                ->orGroupStart()->where('LOWER(u.doc_type)', 'dpcr')->where('df.dpcr_eval_end <', $now)->groupEnd()
                ->orGroupStart()->where('LOWER(u.doc_type)', 'opcr')->where('df.opcr_eval_end <', $now)->groupEnd()
                ->orGroupStart()->where('LOWER(u.doc_type)', 'iperf')->where('df.iperf_eval_end <', $now)->groupEnd()
            ->groupEnd()
            ->get()->getResultArray();

        foreach ($expiringFolders as $folder) {
            if ($userModel->hasRole($folder['user_id'], 'Admin')) continue;

            queue_email(
                $folder['email'],
                'Notice: Evaluation Submission Deadline Missed',
                render_email('deadline_missed', ['firstName' => $folder['first_name']])
            );
        }
        
        if (!empty($expiringFolders)) {
            $db->table($this->table)
                ->whereIn('id', array_column($expiringFolders, 'id'))
                ->update(['status' => \App\Enums\FolderStatus::UNEVALUATED->value]);
        }
    }

    public function isFolderLocked($folder) {
        if (!$folder) return true; 

        // Allow structure/cascade changes only during the target drafting phase (or legacy draft)
        $isLocked = !in_array($folder['status'], [
            FolderStatus::DRAFT_TARGET->value,
            FolderStatus::TARGET_RETURNED->value,
            FolderStatus::DRAFT->value
        ]);
        
        $isPastDeadline = !empty($folder['eval_date_end']) && date('Y-m-d H:i:s') > $folder['eval_date_end'];
        
        return ($isLocked || $isPastDeadline);
    }
}
