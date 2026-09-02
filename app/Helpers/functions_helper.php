<?php

/**
 * Generates a cryptographically random URL-safe short ID.
 * Produces a base64-encoded string with URL-safe characters (A-Z, a-z, 0-9, -, _).
 *
 * @param int $length The desired length of the generated ID (defaults to 11)
 * @return string A random URL-safe string of the specified length
 */
function generate_short_id(int $length = 11): string
{
    // Generate 8 cryptographically secure random bytes
    $bytes = random_bytes(8);

    // Encode the raw bytes into a base64 string
    $encoded = base64_encode($bytes);
    
    // Swap base64 characters that are unsafe in URLs (+, /, =) with URL-safe equivalents
    $urlSafe = str_replace(['+', '/', '='], ['-', '_', ''], $encoded);
    
    // Trim to the requested length and return
    return substr($urlSafe, 0, $length);
}

/**
 * Generates a unique short ID, injects it into the data array, saves it, and returns the ID.
 *
 * @param \CodeIgniter\Model $dbModel
 * @param array $data
 * @return string|null Returns the generated ID on success, or null after max attempts.
 */
function create_unique_row($dbModel, array $data) {
    $maxAttempts = 999;

    for ($i = 0; $i < $maxAttempts; $i++) {
        $id = generate_short_id();
        
        // Check if the ID already exists in the database
        if ($dbModel->find($id) === null) {
            // Merge the generated unique ID into your input data
            $data['id'] = $id;
            
            // Save the data array containing the ID
            $dbModel->save($data);
            
            // Return the newly created ID to the controller immediately
            return $id; 
        }
    }

    return null; // Return null if it fails to find an open ID slot after 999 tries
}

/**
 * Resolves duplicate record titles by appending increment suffixes like (1), (2), etc.
 * Supports two modes: query mode (fetches potential collisions from the DB) and
 * pre-fetched mode (works against an already-retrieved array of rows).
 *
 * @param string $baseTitle The intended title to check and resolve
 * @param array|callable $scope Either a WHERE conditions array, a closure that receives $model
 *                              and chains extra conditions onto it (no return needed), or a
 *                              pre-fetched array of rows
 * @param string $titleColumn The column name holding the title, supports dot notation e.g. 'd.title'
 * @param \CodeIgniter\Model|null $model The target model (required in query mode, null in pre-fetched mode)
 * @return string The resolved unique title, with a suffix appended if the base title is already taken
 */
function resolve_unique_title(
    string $baseTitle,
    $scope = [],
    string $titleColumn = 'title',
    $model = null
    ): string {
        // Pre-fetched array mode
        if (!$model) {
            $columnKey = strpos($titleColumn, '.') !== false
                ? explode('.', $titleColumn)[1]
                : $titleColumn;
            $existingTitles = array_column($scope, $columnKey);
        } else {
            // Query mode - chain conditions onto $model itself rather than $model->builder()
            // or $model->db directly, so Model-level query scoping (soft-delete filtering)
            // still applies. findAll() below adds the deleted_at check on top of whatever
            // conditions got chained on here, instead of silently matching archived rows too.
            if (is_callable($scope)) {
                $scope($model);
            } elseif (!empty($scope)) {
                $model->where($scope);
            }

            $model->groupStart()
                      ->where($titleColumn, $baseTitle)
                      ->orLike($titleColumn, $baseTitle . ' (', 'after')
                  ->groupEnd();

            $results = $model->select($titleColumn)->findAll();
            $columnKey = strpos($titleColumn, '.') !== false
                ? explode('.', $titleColumn)[1]
                : $titleColumn;
            $existingTitles = array_column($results, $columnKey);
        }

        $exactMatchFound = false;
        $usedSuffixes = [];

        foreach ($existingTitles as $existingTitle) {
            if ($existingTitle === $baseTitle) {
                $exactMatchFound = true;
                continue;
            }

            // Detect any standard (1), (2) patterns trailing the string text root
            if (preg_match('/ \((\d+)\)$/', $existingTitle, $matches)) {
                $number = (int)$matches[1];
                $suffixLength = strlen(' (' . $number . ')');
                $prefix = substr($existingTitle, 0, -$suffixLength);

                if ($prefix === $baseTitle) {
                    $usedSuffixes[$number] = true;
                }
        }
        }

        // If no exact match is found, we don't need to append anything!
        if (!$exactMatchFound) {
            return $baseTitle;
        }

        // Loop forward starting at 1 until an empty array index slot is found
        $nextSuffix = 1;
        while (isset($usedSuffixes[$nextSuffix])) {
            $nextSuffix++;
        }

        return $baseTitle . ' (' . $nextSuffix . ')';
}

/**
 * Attempts to rebuild a lost session using the Remember Me cookie.
 * @return bool True if session exists or was restored, False if not.
 */
function restore_session_from_cookie(): bool {
    $session = session();
    
    // If they already have an active session, we're good!
    if ($session->get('isLoggedIn')) return true;

    // Check for the persistent cookie
    $token = $_COOKIE['remember_me'] ?? null;
    if (!$token) return false;

    $userModel = new \App\Models\UserModel();
    $user = $userModel->where('remember_token', hash('sha256', $token))
                      ->where('remember_token_expiry >', date('Y-m-d H:i:s'))
                      ->first();

    // If token is valid and user is not banned
    if ($user && isset($user['is_active']) && $user['is_active'] == 1) {
        $roleData = $userModel->db->table('user_roles ur')->select('r.name')
            ->join('roles r', 'r.id = ur.role_id')
            ->where('ur.user_id', $user['id'])->get()->getRowArray();
        
        $plantilla = $userModel->db->table('plantillas p')
            ->select('un.name as department, pos.title as position')
            ->join('units un', 'un.id = p.unit_id')
            ->join('positions pos', 'pos.id = p.position_id')
            ->where('p.user_id', $user['id'])
            ->where('p.ended_at IS NULL')->get()->getRowArray();

        $session->set([
            'user_id'    => $user['id'],
            'email'      => $user['email'],
            'role'       => $roleData ? $roleData['name'] : 'Employee',
            'department' => $plantilla ? $plantilla['department'] : null,
            'position'   => $plantilla ? $plantilla['position'] : null,
            'username'   => $user['first_name'] . ' ' . $user['last_name'],
            'isLoggedIn' => true,
            'avatar_image'  => $user['avatar_image'],
            'avatar_color'  => $user['avatar_color'] ?? '#' . substr(md5($user['email']), 0, 6),
            'avatar_letter' => $user['avatar_letter'] ?? strtoupper(substr($user['first_name'], 0, 1)),
        ]);
        return true;
    }
    
    return false;
}