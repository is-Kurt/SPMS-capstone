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
 * @param array|callable $scope Either a WHERE conditions array, a closure that receives $model->db
 *                              and returns a configured builder, or a pre-fetched array of rows
 * @param string $titleColumn The column name holding the title, supports dot notation e.g. 'd.title'
 * @param \CodeIgniter\Model|null $model The target model (required in query mode, null in pre-fetched mode)
 * @return string The resolved unique title, with a suffix appended if the base title is already taken
 */
function resolve_unique_title(
    string $baseTitle,
    array $scope = [],
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
            // Query mode
            if (is_callable($scope)) {
                $builder = $scope($model->db);
            } else {
                $builder = $model->builder();
                if (!empty($scope)) {
                    $builder->where($scope);
                }
            }

            $builder->groupStart()
                        ->where($titleColumn, $baseTitle)
                        ->orLike($titleColumn, $baseTitle . ' (', 'after')
                    ->groupEnd();

            $results = $builder->select($titleColumn)->get()->getResultArray();
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