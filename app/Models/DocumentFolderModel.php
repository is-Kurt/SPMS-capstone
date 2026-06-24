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
        'routing_preset_id',
        'status'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

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

        // 1. Process "Submitted" -> "To Evaluate"
        $startingFolders = $db->table($this->table . ' df')
            ->select('df.id, df.title, u.email, u.first_name')
            ->join('users u', 'u.id = df.user_id')
            ->where('df.status', \App\Enums\FolderStatus::SUBMITTED->value)
            ->where('df.eval_date_start <=', $now)
            ->where('df.eval_date_end >=', $now)
            ->get()->getResultArray();

        foreach ($startingFolders as $folder) {
            $link = site_url("folders/" . $folder['id']);
            queue_email(
                $folder['email'],
                'Action Required: Evaluation Period Open',
                "Hello {$folder['first_name']},<br><br>The official evaluation window for <b>{$folder['title']}</b> has now opened. You may now access your folder to conduct and lock in your self-evaluation.<br><br><a href='{$link}'>Click here to open your folder</a>"
            );
        }

        if (!empty($startingFolders)) {
            $db->table($this->table)
                ->whereIn('id', array_column($startingFolders, 'id'))
                ->update(['status' => \App\Enums\FolderStatus::TO_EVALUATE->value]);
        }

        // 2. Find folders that just expired (Before we change their status)
        $expiringFolders = $db->table($this->table . ' df')
            ->select('df.id, u.email, u.first_name')
            ->join('users u', 'u.id = df.user_id')
            ->whereNotIn('df.status', [
                \App\Enums\FolderStatus::APPROVED->value, 
                \App\Enums\FolderStatus::UNEVALUATED->value
            ])
            ->where('df.eval_date_end <', $now)
            ->get()->getResultArray();

        foreach ($expiringFolders as $folder) {
            queue_email(
                $folder['email'],
                'Notice: Evaluation Submission Deadline Missed',
                "Hello {$folder['first_name']},<br><br>The deadline for submitting your performance evaluation has passed. Your folder has been locked and marked as <b>Unevaluated</b>. Please contact your supervisor or the HR department if this is an error."
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
