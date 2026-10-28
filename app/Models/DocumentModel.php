<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentModel extends Model
{
    protected $table            = 'document_folders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id', 
        'title', 
        'tabs',
        'user_id',
        'cycle_id',
        'cycle_folder_id',
        'parent_folder_id',
        'status',
        'current_version',
        'final_rating',
        'target_submitted_at',
        'target_approved_at',
        'target_approved_by',
        'target_approver_role',
        'submitted_at',
        'rated_at',
        'deleted_at'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = false;

    protected array $casts = [
        'tabs' => '?json-array'
    ];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Backward-compatibility translation: allows legacy calls filtering by 'document_folder_id'
     * to smoothly filter by 'id'.
     */
    public function where($key, $value = null, ?bool $escape = null)
    {
        if ($key === 'document_folder_id') {
            $key = 'id';
        }
        return parent::where($key, $value, $escape);
    }

    public function whereIn(?string $key = null, $values = null, ?bool $escape = null)
    {
        if ($key === 'document_folder_id') {
            $key = 'id';
        }
        return parent::whereIn($key, $values, $escape);
    }

    /**
     * Backward-compatibility translation: map document_folder_id to id before saving.
     */
    public function save($data): bool
    {
        if (is_array($data)) {
            if (empty($data['id']) && !empty($data['document_folder_id'])) {
                $data['id'] = $data['document_folder_id'];
            }
            unset($data['document_folder_id']);
        }
        return parent::save($data);
    }

    /**
     * Secures document fetching by checking folder ownership and enriching with cycle deadlines.
     */
    public function getDocumentWithFolderInfo(string $docId): ?array
    {
        // 1. Direct fetch from document_folders (unified schema)
        $row = $this->db->table('document_folders df')
            ->select('df.*, df.id as document_folder_id, df.user_id as owner_id, df.status as folder_status, df.title as folder_title, df.deleted_at as folder_deleted_at,
                      df.target_submitted_at, df.target_approved_at, df.target_approved_by, df.target_approver_role,
                      df.routing_preset_id, df.final_rating,
                      u.doc_type, u.first_name, u.last_name, u.email,
                      pos.title as position_title,
                      cd.target_start, cd.target_end, cd.eval_start, cd.eval_end,
                      rc.title as cycle_title, rc.rating_period as cycle_rating_period,
                      rc.approving_authority_name, rc.approving_authority_position')
            ->join('users u', 'u.id = df.user_id', 'left')
            ->join('plantillas p', 'p.user_id = u.id AND p.ended_at IS NULL', 'left')
            ->join('positions pos', 'pos.id = p.position_id', 'left')
            ->join('rating_cycles rc', 'rc.id = df.cycle_id', 'left')
            ->join('cycle_deadlines cd', 'cd.cycle_id = COALESCE(df.cycle_id, df.cycle_folder_id, df.parent_folder_id, df.id) AND cd.doc_type = LOWER(COALESCE(u.doc_type, "ipcr"))', 'left')
            ->where('df.id', $docId)
            ->get()->getRowArray();



        if ($row) {
            $cycleFolderId = $row['cycle_folder_id'] ?? $row['cycle_id'] ?? $row['parent_folder_id'] ?? $row['id'];
            $deadlineModel = new \App\Models\CycleDeadlineModel();
            $flatDates = $deadlineModel->getCycleDatesFlat($cycleFolderId);
            $row = array_merge($row, $flatDates);

            $effectiveDocType = strtolower($row['doc_type'] ?? 'ipcr');
            $row['target_start'] = $row["{$effectiveDocType}_target_start"] ?? $row['target_start'] ?? null;
            $row['target_end']   = $row["{$effectiveDocType}_target_end"] ?? $row['target_end'] ?? null;
            $row['eval_start']   = $row["{$effectiveDocType}_eval_start"] ?? $row['eval_start'] ?? null;
            $row['eval_end']     = $row["{$effectiveDocType}_eval_end"] ?? $row['eval_end'] ?? null;
        }

        return $row;
    }

    public function getUserDocuments(int $userId, ?string $docId = null)
    {
        $builder = $this->db->table('document_folders df')
            ->select('df.*, df.id as document_folder_id, df.user_id')
            ->where('df.user_id', $userId)
            ->where('df.deleted_at IS NULL');

        if ($docId !== null) $builder->where('df.id', $docId);

        return $docId !== null ? $builder->get()->getRowArray() : $builder->get()->getResultArray();
    }
}