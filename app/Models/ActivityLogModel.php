<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table            = 'activity_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'action',
        'category',
        'entity_type',
        'entity_id',
        'ip_address',
        'user_agent',
        'details',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * Builds base query with user & role relations and optional filters applied.
     */
    protected function buildFilteredQuery(array $filters = [])
    {
        $builder = $this->db->table('activity_logs al')
            ->select('al.*, u.first_name, u.last_name, u.email, u.avatar_color, u.avatar_letter, GROUP_CONCAT(DISTINCT r.name) as role_names')
            ->join('users u', 'u.id = al.user_id', 'left')
            ->join('user_roles ur', 'ur.user_id = u.id', 'left')
            ->join('roles r', 'r.id = ur.role_id', 'left')
            ->groupBy('al.id');

        if (!empty($filters['category']) && $filters['category'] !== 'ALL') {
            $builder->where('al.category', $filters['category']);
        }

        if (!empty($filters['action'])) {
            $builder->where('al.action', $filters['action']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('al.created_at >=', $filters['date_from'] . ' 00:00:00');
        }

        if (!empty($filters['date_to'])) {
            $builder->where('al.created_at <=', $filters['date_to'] . ' 23:59:59');
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $builder->groupStart()
                ->like('al.action', $search)
                ->orLike('al.details', $search)
                ->orLike('al.ip_address', $search)
                ->orLike('u.first_name', $search)
                ->orLike('u.last_name', $search)
                ->orLike('u.email', $search)
                ->groupEnd();
        }

        return $builder;
    }

    /**
     * Get paginated logs matching filters.
     */
    public function getLogs(array $filters = [], int $perPage = 25, int $page = 1): array
    {
        $builder = $this->buildFilteredQuery($filters);
        $offset = max(0, ($page - 1) * $perPage);

        $rows = $builder->orderBy('al.created_at', 'DESC')
                        ->limit($perPage, $offset)
                        ->get()
                        ->getResultArray();

        foreach ($rows as &$row) {
            $row['user_name'] = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            if (empty($row['user_name'])) {
                $row['user_name'] = !empty($row['email']) ? $row['email'] : 'System / Guest';
            }
            if (!empty($row['role_names'])) {
                $row['role_name'] = str_replace(',', ', ', $row['role_names']);
            } else {
                $row['role_name'] = '—';
            }
        }

        return $rows;
    }

    /**
     * Count total logs matching filters.
     */
    public function countLogs(array $filters = []): int
    {
        $builder = $this->db->table('activity_logs al')
            ->join('users u', 'u.id = al.user_id', 'left');

        if (!empty($filters['category']) && $filters['category'] !== 'ALL') {
            $builder->where('al.category', $filters['category']);
        }

        if (!empty($filters['action'])) {
            $builder->where('al.action', $filters['action']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('al.created_at >=', $filters['date_from'] . ' 00:00:00');
        }

        if (!empty($filters['date_to'])) {
            $builder->where('al.created_at <=', $filters['date_to'] . ' 23:59:59');
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $builder->groupStart()
                ->like('al.action', $search)
                ->orLike('al.details', $search)
                ->orLike('al.ip_address', $search)
                ->orLike('u.first_name', $search)
                ->orLike('u.last_name', $search)
                ->orLike('u.email', $search)
                ->groupEnd();
        }

        return $builder->countAllResults();
    }

    /**
     * Get all logs matching filters for CSV/Excel export (limited to 5000 records for safety).
     */
    public function getAllForExport(array $filters = []): array
    {
        $builder = $this->buildFilteredQuery($filters);
        $rows = $builder->orderBy('al.created_at', 'DESC')
                        ->limit(5000)
                        ->get()
                        ->getResultArray();

        foreach ($rows as &$row) {
            $row['user_name'] = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            if (empty($row['user_name'])) {
                $row['user_name'] = !empty($row['email']) ? $row['email'] : 'System / Guest';
            }
            if (!empty($row['role_names'])) {
                $row['role_name'] = str_replace(',', ', ', $row['role_names']);
            } else {
                $row['role_name'] = '—';
            }
        }

        return $rows;
    }
}
