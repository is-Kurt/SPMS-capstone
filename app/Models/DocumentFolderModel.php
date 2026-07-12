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
        'eval_date_start', 
        'eval_date_end', 
        'submitted_at',
        'rated_at',
        'deadline_reminder_sent_at',
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
    public function getRatingDashboardFolders(int $userId, string $sysRole): array
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
            ->join('units un', 'un.id = p.unit_id', 'left');

        if ($sysRole !== 'Admin') {
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
            ->select('df.id, u.email, u.first_name, df.eval_date_end')
            ->join('users u', 'u.id = df.user_id')
            ->join('user_roles ur', 'ur.user_id = u.id', 'left')
            ->join('roles r', 'r.id = ur.role_id', 'left')
            ->where('df.status', FolderStatus::DRAFT->value)
            ->where('df.eval_date_end >=', $now)
            ->where('df.eval_date_end <=', $cutoff)
            ->where('df.deadline_reminder_sent_at IS NULL')
            ->groupStart()
                ->where('r.name !=', 'Admin')
                ->orWhere('r.name IS NULL')
            ->groupEnd()
            ->get()->getResultArray();
    }

    protected function setDefaultEvalDates(array $data): array {
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));

        if (empty($data['data']['eval_date_start']) && empty($data['data']['eval_date_end'])) {
            $data['data']['eval_date_start'] = $today . ' 24:00:00';
            $data['data']['eval_date_end'] = $tomorrow . ' 24:00:00';
        }

        return $data;
    }

    public function updateTimeBasedStatuses()
    {
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');
        helper('email_queue');
        $userModel = new UserModel();

        // 1. Process "Submitted" -> "To Evaluate"
        // Note: this query intentionally stays unfiltered by role - it also drives
        // the status update just below, and an Admin's own folder still needs to
        // transition normally even though (per the per-folder check in the loop
        // further down) Admins don't get emailed about it.
        $startingFolders = $db->table($this->table . ' df')
            ->select('df.id, df.user_id, df.title, u.email, u.first_name')
            ->join('users u', 'u.id = df.user_id')
            ->where('df.status', \App\Enums\FolderStatus::SUBMITTED->value)
            ->where('df.eval_date_start <=', $now)
            ->where('df.eval_date_end >=', $now)
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
                ->update(['status' => \App\Enums\FolderStatus::TO_EVALUATE->value]);
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
            ->where('df.eval_date_end <', $now)
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

        $isLocked = !in_array($folder['status'], [FolderStatus::DRAFT->value, FolderStatus::SUBMITTED->value]);
        $isPastDeadline = !empty($folder['eval_date_end']) && date('Y-m-d H:i:s') > $folder['eval_date_end'];
        
        return ($isLocked || $isPastDeadline);
    }
}
