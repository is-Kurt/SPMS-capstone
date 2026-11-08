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
        'evaluator_folder_id',
        'created_at',
        'updated_at',
        'deleted_at',
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
    protected $afterInsert    = ['clearWaitingSupervisor'];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * A supervisor was routed to a paper (a cascade): it is no longer waiting for one (see ReviewHandover).
     * Admin, TWG and PMT routings do not count as a supervisor.
     */
    protected function clearWaitingSupervisor(array $data): array
    {
        $row = $data['data'] ?? [];
        if (empty($row['folder_id']) || empty($row['evaluator_id']) || !\App\Libraries\ReviewHandover::ready()) return $data;
        $role = \App\Libraries\OrgStructure::instance()->roleOf((int) $row['evaluator_id']);
        if (!in_array($role, ['Admin', 'TWG', 'PMT'], true)) {
            // A waiting paper still points at the revoked supervisor's folder: it now comes from the new one's.
            if (!empty($row['evaluator_folder_id'])) {
                $this->db->table('document_folders')->where('id', $row['folder_id'])->where('waiting_supervisor', 1)
                    ->update(['parent_folder_id' => $row['evaluator_folder_id']]);
            }
            $this->db->table('document_folders')->where('id', $row['folder_id'])->update(['waiting_supervisor' => 0]);
        }
        return $data;
    }

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
