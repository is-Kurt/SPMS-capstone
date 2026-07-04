<?php

namespace App\Models;

use CodeIgniter\Model;

class EvaluationRoutingModel extends Model
{
    protected $table            = 'evaluation_routings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'folder_id',
        'evaluator_id',
        'status',
        'evaluator_folder_id'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
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
     * For Folder.php & Rating.php: Fetches all cascaded evaluators with their job titles.
     */
    public function getEvaluatorsForFolder(string $folderId): array
    {
        return $this->select('evaluation_routings.*, u.first_name, u.last_name, pos.title as evaluator_position')
            ->join('users u', 'u.id = evaluation_routings.evaluator_id')
            ->join('plantillas p', 'p.user_id = u.id AND p.ended_at IS NULL', 'left')
            ->join('positions pos', 'pos.id = p.position_id', 'left')
            ->where('folder_id', $folderId)
            ->findAll();
    }
}
