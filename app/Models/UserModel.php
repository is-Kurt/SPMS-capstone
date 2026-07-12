<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'email',
        'password',
        'first_name',
        'last_name',
        'remember_token',
        'remember_token_expiry',
        'is_active',
        'reset_code', 
        'reset_code_expires_at',
        'avatar_image', 
        'avatar_color', 
        'avatar_letter',
        'reset_attempts', 
        'reset_last_attempt_at'
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

    // Validation - base field-shape rules only. Request-specific concerns (password
    // confirmation matching, email-uniqueness-excluding-self, current-password
    // verification) stay in the controllers since they aren't real column rules.
    // No 'password' rule here on purpose: every caller hashes the password before
    // insert()/update(), so a min_length rule here would only ever see a ~60-char
    // bcrypt hash and could never meaningfully enforce raw password strength -
    // that check has to happen in the controller, before hashing.
    // $cleanValidationRules (below) means a partial update() only validates whichever
    // of these fields are actually present in that call's data.
    protected $validationRules      = [
        'first_name' => 'required|min_length[2]|max_length[100]',
        'last_name'  => 'required|min_length[2]|max_length[100]',
        'email'      => 'required|valid_email',
    ];
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
     * For AccountManagement.php: Fetches every user with their position, unit, and role.
     */
    public function getAllUsersWithDetails(): array
    {
        $users = $this->db->table('users u')
            ->select("u.id, u.first_name, u.last_name, u.email, u.is_active,
                      GROUP_CONCAT(DISTINCT pos.title) as position,
                      GROUP_CONCAT(DISTINCT un.name) as department,
                      GROUP_CONCAT(DISTINCT r.name) as role_name,
                      GROUP_CONCAT(DISTINCT r.id) as role_id")
            ->join('plantillas p', 'p.user_id = u.id AND p.ended_at IS NULL', 'left')
            ->join('positions pos', 'pos.id = p.position_id', 'left')
            ->join('units un', 'un.id = p.unit_id', 'left')
            ->join('user_roles ur', 'ur.user_id = u.id', 'left')
            ->join('roles r', 'r.id = ur.role_id', 'left')
            ->groupBy('u.id')
            ->orderBy('u.last_name', 'ASC')
            ->get()->getResultArray();

        return $this->formatConcatStrings($users);
    }

    /**
     * For Team.php: Fetches eligible subordinates (excluding current user and Admins).
     */
    public function getEligibleTeamMembers(int $excludeUserId): array
    {
        $users = $this->db->table('users u')
            ->select("u.id as user_id, u.first_name, u.last_name, u.email, 
                      GROUP_CONCAT(DISTINCT pos.id) as position_id, 
                      GROUP_CONCAT(DISTINCT pos.title) as position, 
                      MAX(pos.is_teaching) as is_teaching, 
                      GROUP_CONCAT(DISTINCT un.id) as unit_id, 
                      GROUP_CONCAT(DISTINCT un.name) as department")
            ->join('plantillas p', 'p.user_id = u.id AND p.ended_at IS NULL', 'left')
            ->join('positions pos', 'pos.id = p.position_id', 'left')
            ->join('units un', 'un.id = p.unit_id', 'left')
            ->join('user_roles ur', 'ur.user_id = u.id', 'left')
            ->join('roles r', 'r.id = ur.role_id', 'left')
            ->where('u.is_active', 1)
            ->where('u.id !=', $excludeUserId) 
            ->groupStart()
                ->where('r.name !=', 'Admin')
                ->orWhere('r.name IS NULL')
            ->groupEnd()
            ->groupBy('u.id') 
            ->orderBy('u.last_name', 'ASC')
            ->get()->getResultArray();

        return $this->formatConcatStrings($users);
    }

    // Helper to clean up SQL GROUP_CONCAT commas
    private function formatConcatStrings(array $users): array
    {
        foreach ($users as &$u) {
            if (isset($u['position']))   $u['position']   = str_replace(',', ', ', $u['position']);
            if (isset($u['department'])) $u['department'] = str_replace(',', ', ', $u['department']);
            if (isset($u['role_name']))  $u['role_name']  = str_replace(',', ', ', $u['role_name']);
        }
        return $users;
    }

    /**
     * For Folder.php: Fetches the active position and department for authorization checks.
     */
    public function getActivePlantillaDetails(int $userId): ?array
    {
        return $this->db->table('plantillas p')
            ->select('pos.title as position, un.id as unit_id, un.name as department')
            ->join('positions pos', 'pos.id = p.position_id')
            ->join('units un', 'un.id = p.unit_id')
            ->where('p.user_id', $userId)
            ->where('p.ended_at IS NULL')
            ->get()->getRowArray();
    }

    /**
     * For Folder.php: Fetches Admin info for the Master Guide display.
     */
    public function getAdminPosition(int $adminId): ?array
    {
        return $this->select('users.id, users.first_name, users.last_name, pos.title as admin_position')
            ->join('plantillas p', 'p.user_id = users.id AND p.ended_at IS NULL', 'left')
            ->join('positions pos', 'pos.id = p.position_id', 'left')
            ->where('users.id', $adminId)
            ->first();
    }

    /**
     * True if this user currently holds the given system role. Used to keep Admin
     * accounts out of the folder/document workflow notification emails (drafting,
     * deadlines, approvals) - Admins oversee the whole system rather than being
     * evaluated employees, so those reminders don't apply to them even if they
     * happen to own or be routed to a folder.
     */
    public function hasRole(int $userId, string $roleName): bool
    {
        return $this->db->table('user_roles ur')
            ->join('roles r', 'r.id = ur.role_id')
            ->where('ur.user_id', $userId)
            ->where('r.name', $roleName)
            ->countAllResults() > 0;
    }
}
