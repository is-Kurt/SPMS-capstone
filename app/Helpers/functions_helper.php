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

        // Security: Regenerate session ID when restoring from persistent cookie
        $session->regenerate();

        $session->set([
            'user_id'    => $user['id'],
            'email'      => $user['email'],
            'role'       => $roleData ? $roleData['name'] : 'Employee',
            'department' => $plantilla ? $plantilla['department'] : null,
            'position'   => $plantilla ? $plantilla['position'] : null,
            'username'   => $user['first_name'] . ' ' . $user['last_name'],
            'doc_type'   => $user['doc_type'] ?? null,
            'isLoggedIn' => true,
            'avatar_image'  => $user['avatar_image'] ?? $user['avatar'] ?? null,
            'avatar_color'  => $user['avatar_color'] ?? '#' . substr(md5($user['email']), 0, 6),
            'avatar_letter' => $user['avatar_letter'] ?? strtoupper(substr($user['first_name'], 0, 1)),
        ]);
        return true;
    }
    
    return false;
}

/**
 * Converts an existing document to a target performance paper type (IPCR, DPCR, OPCR, IPERF).
 * Preserves user-entered targets (MFOs, indicators, remarks) while adapting category
 * weights, fields, and signatories to match the target form structure.
 *
 * @param string $docId Document ID to convert
 * @param string $targetPaperType Target paper type ('IPCR', 'DPCR', 'OPCR', 'IPERF')
 * @return bool True if document was converted/updated, False if no change needed or invalid.
 */
/**
 * Returns the official standard form blueprint / tabs JSON for a given doc_type
 * (OPCR, DPCR, IPCR, IPERF) without requiring a database query.
 */
function get_default_form_template(string $docType): array
{
    $docType = strtoupper(trim($docType));
    $officialTitles = [
        'OPCR'  => 'OFFICE PERFORMANCE COMMITMENT AND REVIEW (OPCR)',
        'DPCR'  => 'DEPARTMENT PERFORMANCE COMMITMENT AND REVIEW (DPCR)',
        'IPCR'  => 'INDIVIDUAL PERFORMANCE COMMITMENT AND REVIEW (IPCR)',
        'IPERF' => 'INDIVIDUAL PERFORMANCE EVALUATION RATING FORM FOR CONTRACT OF SERVICE AND JOB ORDER PERSONNEL',
    ];

    $title = $officialTitles[$docType] ?? $docType;
    $lower = strtolower($docType);

    return [
        [
            'id'       => 'tab-target',
            'title'    => 'Target Form',
            'formData' => [
                'doc_type'   => $lower,
                'title'      => $title,
                'categories' => [
                    'core' => [
                        [
                            'row_id'          => 'row_core_1',
                            'mfo'             => '',
                            'indicators'      => '',
                            'budget'          => '',
                            'accountable'     => '',
                            'accomplishments' => '',
                            'q'               => '',
                            't'               => '',
                            'e'               => '',
                            'remarks'         => '',
                            'twg_comment'     => ''
                        ]
                    ],
                    'strategic' => [],
                    'support'   => []
                ]
            ],
            'content'  => ''
        ]
    ];
}

function convert_document_paper_type(string $docId, string $targetPaperType): bool
{
    $documentModel = new \App\Models\DocumentModel();

    $doc = $documentModel->find($docId);
    if (!$doc) return false;

    $targetPaperType = strtoupper(trim($targetPaperType));
    if (!in_array($targetPaperType, ['IPCR', 'DPCR', 'OPCR', 'IPERF'], true)) {
        return false;
    }

    $currentTitleUpper = strtoupper(trim($doc['title'] ?? ''));
    $tabs = !empty($doc['tabs']) ? (is_string($doc['tabs']) ? json_decode($doc['tabs'], true) : $doc['tabs']) : [];
    $tabTitleUpper = strtoupper(trim($tabs[0]['formData']['title'] ?? ''));

    // Identify current paper type from document title or tab form title
    $currentDocType = null;
    $combinedTitleUpper = $currentTitleUpper . ' ' . $tabTitleUpper;
    if (str_contains($combinedTitleUpper, 'OPCR') || str_contains($combinedTitleUpper, 'OFFICE')) {
        $currentDocType = 'OPCR';
    } elseif (str_contains($combinedTitleUpper, 'DPCR') || str_contains($combinedTitleUpper, 'DIVISION') || str_contains($combinedTitleUpper, 'DEPARTMENT')) {
        $currentDocType = 'DPCR';
    } elseif (str_contains($combinedTitleUpper, 'IPERF') || str_contains($combinedTitleUpper, 'NON-TEACHING') || str_contains($combinedTitleUpper, 'CONTRACT OF SERVICE')) {
        $currentDocType = 'IPERF';
    } elseif (str_contains($combinedTitleUpper, 'IPCR') || str_contains($combinedTitleUpper, 'INDIVIDUAL PERFORMANCE COMMITMENT')) {
        $currentDocType = 'IPCR';
    }

    $tabDocType = !empty($tabs[0]['formData']['doc_type']) ? strtoupper(trim($tabs[0]['formData']['doc_type'])) : null;

    // If tab formData doc_type already matches target (or fallback title doc_type matches when tabDocType is unset), no conversion needed
    if ($tabDocType === $targetPaperType || ($tabDocType === null && $currentDocType === $targetPaperType)) {
        return false;
    }

    $officialTitles = [
        'OPCR'  => 'OFFICE PERFORMANCE COMMITMENT AND REVIEW (OPCR)',
        'DPCR'  => 'DEPARTMENT PERFORMANCE COMMITMENT AND REVIEW (DPCR)',
        'IPCR'  => 'INDIVIDUAL PERFORMANCE COMMITMENT AND REVIEW (IPCR)',
        'IPERF' => 'INDIVIDUAL PERFORMANCE EVALUATION RATING FORM FOR CONTRACT OF SERVICE AND JOB ORDER PERSONNEL',
    ];

    // Load official blueprint for the target form
    $tplTabs = get_default_form_template($targetPaperType);
    $tplFormData = $tplTabs[0]['formData'] ?? [];

    // Check if document has user-entered content
    $hasExistingContent = false;
    if (!empty($tabs)) {
        foreach ($tabs as $t) {
            if (!empty($t['content']) && trim(strip_tags($t['content'])) !== '') {
                $hasExistingContent = true;
                break;
            }
            if (!empty($t['formData']['weights']) || !empty($t['formData']['rubrics'])) {
                $hasExistingContent = true;
                break;
            }
            if (!empty($t['formData']['categories'])) {
                foreach (['core', 'strategic', 'support'] as $c) {
                    if (!empty($t['formData']['categories'][$c])) {
                        foreach ($t['formData']['categories'][$c] as $r) {
                            if (!empty($r['mfo']) || !empty($r['indicators']) || !empty($r['accomplishments'])) {
                                $hasExistingContent = true;
                                break 3;
                            }
                        }
                    }
                }
            }
        }
    }

    if (!$hasExistingContent || empty($tabs)) {
        // Document is empty: replace with clean official template tabs
        $newTabs = !empty($tplTabs) ? $tplTabs : [
            [
                'id'       => 'tab-' . uniqid(),
                'title'    => 'Target Form',
                'formData' => [
                    'doc_type' => strtolower($targetPaperType),
                    'title'    => $officialTitles[$targetPaperType] ?? $targetPaperType
                ],
                'content'  => ''
            ]
        ];
        $updatePayload = ['tabs' => $newTabs];
        if (empty($doc['title']) || in_array(strtoupper(trim($doc['title'])), ['OPCR', 'DPCR', 'IPCR', 'IPERF'], true)) {
            $updatePayload['title'] = $targetPaperType;
        }
        return $documentModel->update($docId, $updatePayload);
    }

    // Document has user-entered targets: adapt rows to the target form type
    foreach ($tabs as &$t) {
        if (!isset($t['formData']) || !is_array($t['formData'])) {
            $t['formData'] = [];
        }

        $t['formData']['doc_type'] = strtolower($targetPaperType);
        $t['formData']['title']    = $officialTitles[$targetPaperType] ?? $targetPaperType;

        if ($targetPaperType === 'IPERF') {
            // Flatten strategic and support rows into core
            $coreRows = $t['formData']['categories']['core'] ?? [];
            foreach (['strategic', 'support'] as $cat) {
                if (!empty($t['formData']['categories'][$cat])) {
                    foreach ($t['formData']['categories'][$cat] as $row) {
                        if (!empty($row['mfo']) || !empty($row['indicators'])) {
                            $coreRows[] = $row;
                        }
                    }
                }
            }
            foreach ($coreRows as $idx => &$cr) {
                $cr['row_id'] = $cr['row_id'] ?? ('row_core_' . ($idx + 1));
                if (!isset($cr['twg_comment'])) $cr['twg_comment'] = '';
            }
            unset($cr);

            $t['formData']['categories'] = [
                'core'      => $coreRows,
                'strategic' => [],
                'support'   => []
            ];
            $t['formData']['weights'] = [
                'core'      => 1,
                'strategic' => 0,
                'support'   => 0
            ];
            if (!empty($tplFormData['signatories'])) {
                $t['formData']['signatories'] = $tplFormData['signatories'];
            }
        } else {
            // Converting to IPCR, DPCR, or OPCR
            if (!isset($t['formData']['categories']) || !is_array($t['formData']['categories'])) {
                $t['formData']['categories'] = ['core' => [], 'strategic' => [], 'support' => []];
            } else {
                foreach (['core', 'strategic', 'support'] as $cat) {
                    if (!isset($t['formData']['categories'][$cat])) {
                        $t['formData']['categories'][$cat] = [];
                    }
                }
            }
            // Reset IPERF 100% flat weights
            if (isset($t['formData']['weights']) && ($t['formData']['weights']['core'] ?? 0) == 1 && ($t['formData']['weights']['strategic'] ?? 0) == 0) {
                unset($t['formData']['weights']);
            }
            if (!empty($tplFormData['signatories'])) {
                $t['formData']['signatories'] = $tplFormData['signatories'];
            }
        }
    }
    unset($t);

    $updatePayload = ['tabs' => $tabs];
    if (empty($doc['title']) || in_array(strtoupper(trim($doc['title'])), ['OPCR', 'DPCR', 'IPCR', 'IPERF'], true)) {
        $updatePayload['title'] = $targetPaperType;
    }
    return $documentModel->update($docId, $updatePayload);
}

/**
 * Synchronizes all document folders belonging to a user that are currently
 * in the target setting phase to match the user's new/current paper type.
 *
 * @param int $userId Target user ID
 * @param string|null $targetPaperType Explicit paper type ('IPCR', 'DPCR', 'OPCR', 'IPERF'). If null, resolved from profile/position.
 * @return array Summary of updated folders and documents count
 */
function sync_user_papers_mid_target_setting(int $userId, ?string $targetPaperType = null): array
{
    $userModel     = new \App\Models\UserModel();
    $folderModel   = new \App\Models\DocumentFolderModel();
    $documentModel = new \App\Models\DocumentModel();

    $user = $userModel->find($userId);
    if (!$user) return ['updated_docs' => 0, 'updated_folders' => 0];

    // Determine target paper type if not provided
    if (empty($targetPaperType)) {
        if (!empty($user['doc_type'])) {
            $targetPaperType = strtoupper(trim($user['doc_type']));
        } else {
            $plantilla = $userModel->getActivePlantillaDetails($userId);
            $posTitle = strtolower($plantilla['position'] ?? '');
            $userEmail = strtolower($user['email'] ?? '');
            if (str_contains($posTitle, 'president') || str_contains($posTitle, 'vice president') || str_contains($userEmail, 'vpaa') || str_contains($userEmail, 'cao')) {
                $targetPaperType = 'OPCR';
            } elseif (str_contains($posTitle, 'dean') || str_contains($posTitle, 'chair') || str_contains($posTitle, 'head') || str_contains($userEmail, 'dean') || str_contains($userEmail, 'chair')) {
                $targetPaperType = 'DPCR';
            } elseif (($plantilla['is_teaching'] ?? 1) == 0) {
                $targetPaperType = 'IPERF';
            } else {
                $targetPaperType = 'IPCR';
            }
        }
    }

    $targetPaperType = strtoupper(trim($targetPaperType));
    if (!in_array($targetPaperType, ['IPCR', 'DPCR', 'OPCR', 'IPERF'], true)) {
        $targetPaperType = 'IPCR';
    }

    // Mid target setting statuses (before targets are approved or evaluation begins)
    $targetSettingStatuses = [
        \App\Enums\FolderStatus::DRAFT_TARGET->value,
        \App\Enums\FolderStatus::TARGET_SUBMITTED->value,
        \App\Enums\FolderStatus::TARGET_MISSED->value,
    ];

    $folders = $folderModel->where('user_id', $userId)
                           ->whereIn('status', $targetSettingStatuses)
                           ->where('deleted_at IS NULL')
                           ->findAll();

    $updatedDocs = 0;
    $updatedFolders = 0;

    foreach ($folders as $folder) {
        $docs = $documentModel->where('document_folder_id', $folder['id'])->findAll();
        $folderHadDocUpdated = false;

        foreach ($docs as $doc) {
            $changed = convert_document_paper_type($doc['id'], $targetPaperType);
            if ($changed) {
                $updatedDocs++;
                $folderHadDocUpdated = true;
            }
        }

        // If paper type changed while pending approval, revert folder to draft_target so ratee can review & resubmit
        if ($folderHadDocUpdated && $folder['status'] === \App\Enums\FolderStatus::TARGET_SUBMITTED->value) {
            $folderModel->update($folder['id'], [
                'status'              => \App\Enums\FolderStatus::DRAFT_TARGET->value,
                'target_submitted_at' => null
            ]);
            $updatedFolders++;
        }
    }

    return [
        'updated_docs'    => $updatedDocs,
        'updated_folders' => $updatedFolders
    ];
}