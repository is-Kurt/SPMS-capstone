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

        $rawAvatar = $user['avatar_image'] ?? $user['avatar'] ?? null;
        $session->set([
            'user_id'    => $user['id'],
            'email'      => $user['email'],
            'role'       => $roleData ? $roleData['name'] : 'Employee',
            'department' => $plantilla ? $plantilla['department'] : null,
            'position'   => $plantilla ? $plantilla['position'] : null,
            'username'   => $user['first_name'] . ' ' . $user['last_name'],
            'doc_type'   => $user['doc_type'] ?? null,
            'isLoggedIn' => true,
            'avatar_image'  => $rawAvatar,
            'avatar_color'  => $user['avatar_color'] ?? '#' . substr(md5($user['email']), 0, 6),
            'avatar_letter' => $user['avatar_letter'] ?? strtoupper(substr($user['first_name'], 0, 1)),
        ]);
        return true;
    }
    
    return false;
}

/**
 * Checks whether an avatar image file physically exists on the server disk.
 * Returns false if the file was deleted or cannot be found, preventing broken images.
 */
function has_avatar_file(?string $filename): bool
{
    if (empty($filename)) {
        return false;
    }
    $cleanName = basename(trim((string)$filename));
    if ($cleanName === '' || $cleanName === '.' || $cleanName === '..') {
        return false;
    }
    $basePath = defined('FCPATH') ? FCPATH : (realpath(__DIR__ . '/../../public') . DIRECTORY_SEPARATOR);
    $path = $basePath . 'uploads/avatars/' . $cleanName;
    return is_file($path) && file_exists($path);
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
 * Cleans text by stripping HTML tags, decoding entities, and trimming all unicode whitespace.
 */
function clean_spms_text($val): string
{
    if ($val === null) return '';
    $s = html_entity_decode((string)$val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $s = strip_tags($s);
    $s = preg_replace('/[\s\x{00a0}\x{200b}]+/u', ' ', $s);
    return trim($s);
}

/**
 * Checks if a document table row is completely empty (no targets, indicators, or substantive entries).
 * Completely empty rows must never be cascaded to subordinates.
 */
function is_spms_row_empty(?array $row): bool
{
    if (empty($row) || !is_array($row)) return true;

    $mfo = clean_spms_text($row['mfo'] ?? ($row['office_ppa'] ?? ($row['paps'] ?? ($row['mfo_title'] ?? ''))));
    $ind = clean_spms_text($row['indicators'] ?? ($row['success_indicators'] ?? ($row['expected_outputs'] ?? ($row['success_indicator'] ?? ''))));
    $acc = clean_spms_text($row['accomplishments'] ?? '');
    $std5 = clean_spms_text($row['std_5'] ?? '');
    $std4 = clean_spms_text($row['std_4'] ?? '');
    $std3 = clean_spms_text($row['std_3'] ?? '');
    $std2 = clean_spms_text($row['std_2'] ?? '');
    $std1 = clean_spms_text($row['std_1'] ?? '');

    // In SPMS, an active deliverable row must have at least an MFO or an indicator.
    // If both MFO and Indicators are blank, the row cannot be cascaded as a deliverable.
    if ($mfo === '' && $ind === '') {
        return true;
    }

    return false;
}

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

    $defaultCoreRow = [
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
    ];

    $defaultStratRow = [
        'row_id'          => 'row_strat_1',
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
    ];

    $defaultSuppRow = [
        'row_id'          => 'row_supp_1',
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
    ];

    return [
        [
            'id'       => 'tab-target',
            'title'    => 'Target Form',
            'formData' => [
                'doc_type'   => $lower,
                'title'      => $title,
                'categories' => [
                    'core'      => [$defaultCoreRow],
                    'strategic' => ($docType === 'IPERF') ? [] : [$defaultStratRow],
                    'support'   => ($docType === 'IPERF') ? [] : [$defaultSuppRow],
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
                            if (!is_spms_row_empty($r)) {
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

    // Target paper type if not given: the person's form type, or the one the doc-type rules give them.
    if (empty($targetPaperType)) {
        $targetPaperType = !empty($user['doc_type'])
            ? strtoupper(trim($user['doc_type']))
            : \App\Libraries\OrgStructure::instance()->defaultDocTypeFor($userId);
    }

    $targetPaperType = \App\Enums\DocType::canonical(strtoupper(trim($targetPaperType)))->value;

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

// ---------------------------------------------------------------------------------------------------------------
// The ratee's position and department on a paper (2026-11-07)
//
// A paper keeps the position(s) and department(s) its owner had when it was created (supervisor cascade, cycle
// start or first opening): document_folders.owner_snapshot. It is never re-read live, so a later change does not
// rewrite submitted or approved papers. Deliberate changes (assignments, role switch, unit / position renames,
// a supervisor's edit) refresh only papers still in draft (paper_snapshot_draft_statuses).
// ---------------------------------------------------------------------------------------------------------------

/** Is document_folders.owner_snapshot there yet (migration 000024)? Until then papers print live details. */
function paper_snapshots_ready(): bool
{
    static $ready = null;
    return $ready ??= (bool) \Config\Database::connect()->query("SELECT 1 FROM pragma_table_info('document_folders') WHERE name = 'owner_snapshot'")->getRowArray();
}

/** Statuses of a paper that is not submitted yet, so its owner details may still follow changes. */
function paper_snapshot_draft_statuses(): array
{
    return [
        \App\Enums\FolderStatus::DRAFT_TARGET->value,
        \App\Enums\FolderStatus::TARGET_UNAPPROVED->value,
        \App\Enums\FolderStatus::TARGET_MISSED->value,
    ];
}

/** A person's current position(s) and department(s): [['position' => ..., 'dept' => ...], ...] (empty if none). */
function paper_owner_roles(int $userId): array
{
    $rows = (new \App\Models\UserModel())->getAllActivePlantillaDetails($userId);
    return array_values(array_map(fn($r) => ['position' => (string) ($r['position'] ?? ''), 'dept' => (string) ($r['department'] ?? '')], $rows));
}

/** Saves the owner's details on a paper once (only when it has none yet). */
function seed_paper_owner_snapshot(string $folderId, int $userId): void
{
    if (!paper_snapshots_ready()) return;
    $db = \Config\Database::connect();
    $row = $db->table('document_folders')->select('owner_snapshot')->where('id', $folderId)->get()->getRowArray();
    if (!$row || !empty($row['owner_snapshot'])) return;
    $roles = paper_owner_roles($userId);
    if (!$roles) return;                       // nothing to save yet: the paper shows live details until there is
    $db->table('document_folders')->where('id', $folderId)
        ->update(['owner_snapshot' => json_encode(['roles' => $roles, 'at' => date('Y-m-d H:i:s')])]);
}

/** After a deliberate change: re-saves the owner details on these people's papers that are still in draft. */
function refresh_draft_paper_snapshots(array $userIds): int
{
    if (!paper_snapshots_ready()) return 0;
    $db = \Config\Database::connect();
    $count = 0;
    foreach (array_unique(array_map('intval', array_filter($userIds))) as $userId) {
        $roles = paper_owner_roles($userId);
        if (!$roles) continue;                 // no current assignment: keep what the paper says
        $count += $db->table('document_folders')->where('user_id', $userId)->where('deleted_at IS NULL')
            ->whereIn('status', paper_snapshot_draft_statuses())
            ->update(['owner_snapshot' => json_encode(['roles' => $roles, 'at' => date('Y-m-d H:i:s')])]) ? $db->affectedRows() : 0;
    }
    return $count;
}

/** The people holding any assignment in these units (to refresh their drafts after a unit rename). */
function users_in_units(array $unitIds): array
{
    if (!$unitIds) return [];
    return array_column(\Config\Database::connect()->table('plantillas')->select('user_id')->distinct()
        ->whereIn('unit_id', $unitIds)->where('ended_at IS NULL')->get()->getResultArray(), 'user_id');
}

/** The people holding this position (to refresh their drafts after a position rename). */
function users_with_position(int $positionId): array
{
    return array_column(\Config\Database::connect()->table('plantillas')->select('user_id')->distinct()
        ->where('position_id', $positionId)->where('ended_at IS NULL')->get()->getResultArray(), 'user_id');
}

/**
 * Name, position and department to print on a paper: the details saved on it, else (an old paper with none
 * saved, or an owner without an assignment) the owner's current ones. $docInfo needs owner_id and owner_snapshot.
 */
function paper_owner_info(array $docInfo): array
{
    $ownerId = (int) ($docInfo['owner_id'] ?? $docInfo['user_id'] ?? 0);
    $user = (new \App\Models\UserModel())->find($ownerId);
    $saved = !empty($docInfo['owner_snapshot']) ? json_decode((string) $docInfo['owner_snapshot'], true) : null;
    $roles = is_array($saved['roles'] ?? null) && $saved['roles'] ? $saved['roles'] : paper_owner_roles($ownerId);
    if (!$roles) {
        $role = \App\Libraries\OrgStructure::instance()->roleOf($ownerId);
        $roles = [['position' => $role ?: 'Faculty', 'dept' => '']];
    }
    return [
        'name'     => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
        'position' => $roles[0]['position'] ?? '',
        'dept'     => $roles[0]['dept'] ?? '',
        'roles'    => $roles,
    ];
}

/**
 * May a person change their own form type on their profile? Heads (who hold a head position) always may. Others
 * only while none of their papers is past drafting: once targets are submitted, approved or missed, or the
 * evaluation has started, their supervisor (My Staff > Staff Details) or the admin (Manage) changes it, after a
 * preview. Finished papers from past cycles do not count.
 */
function own_doc_type_locked(int $userId): bool
{
    if (\App\Libraries\OrgStructure::instance()->isHead($userId)) return false;
    return \Config\Database::connect()->table('document_folders')->where('user_id', $userId)->where('deleted_at IS NULL')
        ->whereIn('status', [
            \App\Enums\FolderStatus::TARGET_SUBMITTED->value,
            \App\Enums\FolderStatus::TARGET_APPROVED->value,
            \App\Enums\FolderStatus::TARGET_MISSED->value,
            \App\Enums\FolderStatus::DRAFT_EVALUATION->value,
            \App\Enums\FolderStatus::EVAL_SUBMITTED->value,
        ])->countAllResults() > 0;
}


// ---------------------------------------------------------------------------------------------------------------
// Role follows position (2026-11-07): a Staff account is a Supervisor while they hold a head position, otherwise an
// Employee. Run after anything that can change who holds a head position (assignments, unit head links, the
// position tree). Their form type is fixed when the new role allows exactly one; otherwise it is flagged as usual.
// ---------------------------------------------------------------------------------------------------------------

/** Brings one person's role in line with their position. Returns the new role when it changed, else null. */
function sync_rated_role(int $userId): ?string
{
    $org = \App\Libraries\OrgStructure::instance();
    $change = $org->syncRatedRole($userId);
    if (!$change) return null;
    [$old, $new] = $change;
    \App\Libraries\OrgStructure::reset();
    $org = \App\Libraries\OrgStructure::instance();
    $user = (new \App\Models\UserModel())->find($userId);
    $note = '';
    $allowed = $org->allowedDocTypes($new, $user['employment_status'] ?? null);
    $current = strtoupper((string) ($user['doc_type'] ?? ''));
    // The form type follows too when the current one no longer fits: the only one allowed, or the first the rules
    // allow (the same default as for someone with none set). It used to stay put when several fit, leaving e.g. a new
    // Department Chair on IPCR (2026-11-08).
    if (!in_array($current, $allowed, true) && $allowed) {
        $pick = $allowed[0];
        (new \App\Models\UserModel())->update($userId, ['doc_type' => $pick]);
        sync_user_papers_mid_target_setting($userId, $pick);
        $note = ", form type " . ($current ?: 'none') . " -> {$pick}" . (count($allowed) > 1 ? ' (also allowed: ' . implode(', ', array_slice($allowed, 1)) . ')' : '');
    }
    audit_log('ROLE_FOLLOWS_POSITION', 'ACCOUNT', 'user', $userId, ($user['email'] ?? "user {$userId}") . ": {$old} -> {$new} (" . ($new === 'Supervisor' ? 'now holds a head position' : 'no longer holds a head position') . "){$note}");
    return $new;
}

/** The same for every Staff account (after the position tree or a unit's head link changed). Returns changes. */
function sync_all_rated_roles(): array
{
    $rows = \Config\Database::connect()->table('user_roles ur')->select('ur.user_id')->join('roles r', 'r.id = ur.role_id')
        ->whereIn('r.name', ['Employee', 'Supervisor'])->get()->getResultArray();
    $changed = [];
    foreach (array_column($rows, 'user_id') as $uid) {
        if ($new = sync_rated_role((int) $uid)) $changed[(int) $uid] = $new;
    }
    return $changed;
}

/** "Staff: unit head" / "Staff: member" for the two rated roles; other roles as they are. */
function staff_role_label(?string $role): string
{
    return match ($role) { 'Supervisor' => 'Staff: unit head', 'Employee' => 'Staff: member', default => (string) $role };
}

/** Short form where it is clear the person is Staff (header badge, Doc Type Rules, audit log): "Unit head" / "Member". */
function staff_role_short(?string $role): string
{
    return match ($role) { 'Supervisor' => 'Unit head', 'Employee' => 'Member', default => (string) $role };
}

/** Sends the same notification to every active Admin (e.g. a supervisor made someone a unit head). */
function notify_admins(array $payload): void
{
    $admins = \Config\Database::connect()->table('users u')->select('u.id')->join('user_roles ur', 'ur.user_id = u.id')
        ->join('roles r', 'r.id = ur.role_id')->where('r.name', 'Admin')->where('u.is_active', 1)->get()->getResultArray();
    helper('notification');
    foreach (array_column($admins, 'id') as $id) notify_user((int) $id, $payload);
}

/** After an org change: revoke supervisors who went out of scope on unfinished papers (ReviewHandover). */
function reconcile_reviews(): void
{
    helper('notification');
    \App\Libraries\ReviewHandover::reconcile();
}
