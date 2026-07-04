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
        'content', 
        'document_folder_id', 
        'parent_doc_id',
        'is_target'
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
        return $this->db->table('documents d')
            ->select('d.*, df.user_id as owner_id, df.status as folder_status, df.eval_date_start')
            ->join('document_folders df', 'df.id = d.document_folder_id')
            ->where('d.id', $docId)
            ->get()->getRowArray();
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