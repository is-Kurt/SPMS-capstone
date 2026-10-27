<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentModel extends Model
{
    protected $table            = 'documents';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id', 
        'title', 
        'tabs',
        'document_folder_id'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = false;

    protected array $casts = [
        'tabs' => 'json-array'
    ];
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
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * For Document.php & BaseController: Secures document fetching by checking folder ownership.
     */
    public function getDocumentWithFolderInfo(string $docId): ?array
    {
        $row = $this->db->table('documents d')
            ->select('d.*, df.user_id as owner_id, df.status as folder_status, df.parent_folder_id, df.cycle_folder_id, df.title as folder_title, df.deleted_at as folder_deleted_at,
                      df.target_submitted_at, df.target_approved_at, df.target_approved_by, df.target_approver_role,
                      df.routing_preset_id, df.final_rating,
                      cd.target_start, cd.target_end, cd.eval_start, cd.eval_end,
                      u.doc_type')
            ->join('document_folders df', 'df.id = d.document_folder_id')
            ->join('users u', 'u.id = df.user_id', 'left')
            ->join('plantillas p', 'p.user_id = u.id AND p.ended_at IS NULL', 'left')
            ->join('positions pos', 'pos.id = p.position_id', 'left')
            ->join('cycle_deadlines cd', 'cd.cycle_folder_id = COALESCE(df.cycle_folder_id, df.parent_folder_id, df.id) AND cd.doc_type = LOWER(COALESCE(u.doc_type, "ipcr"))', 'left')
            ->where('d.id', $docId)
            ->get()->getRowArray();

        if ($row) {
            $effectiveDocType = strtolower($row['doc_type'] ?? 'ipcr');
            $row["{$effectiveDocType}_target_start"] = $row['target_start'] ?? null;
            $row["{$effectiveDocType}_target_end"]   = $row['target_end'] ?? null;
            $row["{$effectiveDocType}_eval_start"]   = $row['eval_start'] ?? null;
            $row["{$effectiveDocType}_eval_end"]     = $row['eval_end'] ?? null;
            $row['cdpcr_target_end'] = $row['target_end'] ?? null;
            $row['dpcr_target_end']  = $row['target_end'] ?? null;
        }

        return $row;
    }

    public function getUserDocuments(int $userId, ?string $docId = null)
    {
        $builder = $this->db->table('documents d')
            ->select('d.*, df.user_id')
            ->join('document_folders df', 'df.id = d.document_folder_id')
            ->where('df.user_id', $userId);

        if ($docId !== null) $builder->where('d.id', $docId);

        return $docId !== null ? $builder->get()->getRowArray() : $builder->get()->getResultArray();
    }
}