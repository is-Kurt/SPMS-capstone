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

        $db->table($this->table)
        ->where('status', FolderStatus::SUBMITTED->value)
        ->where('eval_date_start <=', $now)
        ->where('eval_date_end >=', $now)
        ->update(['status' => FolderStatus::TO_EVALUATE->value]);

        $db->table($this->table)
        ->groupStart()
            ->where('status', FolderStatus::TO_EVALUATE->value)
            ->orWhere('status', FolderStatus::SUBMITTED->value)
            ->orWhere('status', FolderStatus::DRAFT->value)
        ->groupEnd()
        ->where('eval_date_end <', $now)
        ->update(['status' => FolderStatus::UNEVALUATED->value]);
    }

    public function isFolderLocked($folder) {
    $isLocked = !in_array($folder['status'], [FolderStatus::DRAFT->value, FolderStatus::SUBMITTED->value]);
    $isPastDeadline = !empty($folder['eval_date_end']) && date('Y-m-d H:i:s') > $folder['eval_date_end'];
    
    return ($isLocked || $isPastDeadline);
}
}
