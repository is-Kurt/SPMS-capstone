<?php

require 'vendor/autoload.php';

$db = new SQLite3('writable/database/spms_db.sqlite3');

echo "=== TESTING IPERF DOCUMENT CREATION & RENDERING FLOW ===\n\n";

// 1. Fetch Template 4
$t4 = $db->querySingle("SELECT * FROM templates WHERE id = 4", true);
if (!$t4) {
    die("[FAIL] Template 4 not found!\n");
}
echo "[PASS] Fetched Template 4: {$t4['title']}\n";

$tabs = json_decode($t4['tabs'], true);
$formData = $tabs[0]['formData'];

// 2. Insert temporary test folder and document
$db->exec("INSERT INTO document_folders (user_id, title, status, created_at, updated_at) 
           VALUES (34, '2024-2nd-Semester-IPERF-Test', 'draft_target', datetime('now'), datetime('now'))");
$folderId = $db->lastInsertRowID();
echo "[PASS] Created test folder ID: {$folderId}\n";

$tabsJson = json_encode($tabs);
$stmt = $db->prepare("INSERT INTO documents (document_folder_id, title, tabs, is_target, created_at, updated_at) 
                      VALUES (:fid, 'IPERF - Nestor Pascual (COS)', :tabs, 1, datetime('now'), datetime('now'))");
$stmt->bindValue(':fid', $folderId, SQLITE3_INTEGER);
$stmt->bindValue(':tabs', $tabsJson, SQLITE3_TEXT);
$stmt->execute();
$docId = $db->lastInsertRowID();
echo "[PASS] Created test document ID: {$docId}\n";

// 3. Test DocumentModel getDocumentWithFolderInfo
// Initialize minimal CI environment
defined('FCPATH') || define('FCPATH', realpath('public') . DIRECTORY_SEPARATOR);
$app = require_once 'vendor/codeigniter4/framework/system/Test/bootstrap.php';

$docModel = new \App\Models\DocumentModel();
$docInfo = $docModel->getDocumentWithFolderInfo((string)$docId);

if (!$docInfo) {
    die("[FAIL] Could not fetch docInfo from DocumentModel!\n");
}
echo "[PASS] Fetched docInfo successfully.\n";
echo "       Owner ID: {$docInfo['owner_id']}\n";
echo "       Folder Status: {$docInfo['folder_status']}\n";
echo "       Doc Type from user join: {$docInfo['doc_type']}\n";

// 4. Test View rendering of show.php
$userModel = new \App\Models\UserModel();
$ownerUser = $userModel->find($docInfo['owner_id']);
$ownerInfo = [
    'name'     => trim(($ownerUser['first_name'] ?? '') . ' ' . ($ownerUser['last_name'] ?? '')),
    'position' => 'Administrative Aide IV (COS)',
    'dept'     => 'General Services Office',
    'period'   => $docInfo['folder_title'] ?? '',
];

$viewData = [
    'doc' => $docInfo,
    'ownerInfo' => $ownerInfo,
    'isGuide' => false,
    'isOwner' => true,
    'isCycleArchived' => false,
    'groupedGuides' => [],
    'rootFolderId' => $folderId,
    'rateeNav' => null,
    'routingStatus' => null,
    'parentFolder' => null,
    'isParentTargetApproved' => true,
    'basisDoc' => null,
    'superiorUser' => null,
    'basisFormData' => null,
    'basisDocContent' => '',
    'isEmbed' => false,
    'attachmentsByRow' => [],
    'currentReviewerRole' => 'Supervisor',
    'currentReviewerName' => 'Supervisor User'
];

try {
    $renderedHtml = view('document/show', $viewData);
    echo "[PASS] show.php rendered successfully! HTML length: " . strlen($renderedHtml) . " bytes\n";

    // Verify key IPERF elements in HTML output
    $checks = [
        'INDIVIDUAL PERFORMANCE EVALUATION RATING FORM FOR CONTRACT OF SERVICE AND JOB ORDER PERSONNEL' => 'IPERF title',
        'ratee-classification' => 'Classification dropdown',
        'Contract of Service (COS)' => 'COS option',
        'tbody-core' => 'Flat deliverables core tbody',
        'OVERALL AVERAGE RATING' => 'Overall average rating row',
        'iperf-overall-average' => 'IPERF overall average badge',
        'sig-targets-prepared-name' => 'Targets prepared signature',
        'sig-targets-approved-name' => 'Targets approved signature',
        'sig-eval-rated-name' => 'Evaluation rated signature',
        'sig-eval-conforme-name' => 'Evaluation conforme signature',
        'Measures:' => 'Measures legend',
        'Rating Guide:' => 'Rating guide legend'
    ];

    foreach ($checks as $needle => $label) {
        if (strpos($renderedHtml, $needle) !== false) {
            echo "       [PASS] Found {$label} in rendered HTML\n";
        } else {
            echo "       [FAIL] Missing {$label} in rendered HTML!\n";
        }
    }
} catch (\Throwable $e) {
    echo "[FAIL] Exception during view rendering: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

// 5. Clean up test records
$db->exec("DELETE FROM documents WHERE id = {$docId}");
$db->exec("DELETE FROM document_folders WHERE id = {$folderId}");
echo "[PASS] Cleaned up test document and folder.\n";

echo "\n=== ALL FLOW TESTS COMPLETE ===\n";
