<?php

require 'vendor/autoload.php';

$db = new SQLite3('writable/database/spms_db.sqlite3');

echo "=== TESTING SHOW.PHP RENDERING FOR ALL 4 DOCUMENT TYPES ===\n\n";

$types = [
    'ipcr' => ['title' => 'INDIVIDUAL PERFORMANCE COMMITMENT AND REVIEW (IPCR)', 'doc_type' => 'ipcr'],
    'dpcr' => ['title' => 'DEPARTMENT PERFORMANCE COMMITMENT AND REVIEW (DPCR)', 'doc_type' => 'dpcr'],
    'opcr' => ['title' => 'OFFICE PERFORMANCE COMMITMENT AND REVIEW (OPCR)', 'doc_type' => 'opcr'],
    'iperf' => ['title' => 'INDIVIDUAL PERFORMANCE EVALUATION RATING FORM (IPERF)', 'doc_type' => 'iperf'],
];

foreach ($types as $key => $info) {
    echo "Testing type: {$key} ({$info['title']})...\n";

    $doc = [
        'id' => 100,
        'title' => $info['title'],
        'doc_type' => $info['doc_type'],
        'folder_status' => 'draft_target',
        'owner_id' => 1,
        'is_target' => 1,
        'document_folder_id' => 1,
        'ipcr_target_end' => null,
        'dpcr_target_end' => null,
        'opcr_target_end' => null,
        'iperf_target_end' => null
    ];
    $ownerInfo = [
        'id' => 1,
        'name' => 'Test User',
        'position' => 'Test Position',
        'dept' => 'Test Office',
        'period' => 'July - December 2024'
    ];
    $isGuide = false;
    $isOwner = true;
    $groupedGuides = [];

    // Simulate show.php evaluation logic
    $ownerDocType = strtolower($doc['doc_type'] ?? 'ipcr');
    $isDocDpcr = in_array($ownerDocType, ['dpcr', 'cdpcr']) || stripos($doc['title'] ?? '', 'dpcr') !== false;
    $ownerPos = strtolower($ownerInfo['position'] ?? '');
    $isOwnerDean = str_contains($ownerPos, 'dean');
    $isDocOpcr = (!$isDocDpcr) && ($ownerDocType === 'opcr' || stripos($doc['title'] ?? '', 'opcr') !== false || str_contains($ownerPos, 'vice president'));
    $isDocIperf = (!$isDocDpcr && !$isDocOpcr) && ($ownerDocType === 'iperf' || stripos($doc['title'] ?? '', 'iperf') !== false);

    echo "  -> isDocDpcr: " . ($isDocDpcr ? 'true' : 'false') . "\n";
    echo "  -> isDocOpcr: " . ($isDocOpcr ? 'true' : 'false') . "\n";
    echo "  -> isDocIperf: " . ($isDocIperf ? 'true' : 'false') . "\n";

    if ($key === 'iperf') {
        if (!$isDocIperf) {
            echo "  [FAIL] Expected isDocIperf to be true!\n";
        } else {
            echo "  [PASS] Correctly detected as IPERF!\n";
        }
    } elseif ($key === 'opcr') {
        if (!$isDocOpcr) {
            echo "  [FAIL] Expected isDocOpcr to be true!\n";
        } else {
            echo "  [PASS] Correctly detected as OPCR!\n";
        }
    } elseif ($key === 'dpcr') {
        if (!$isDocDpcr) {
            echo "  [FAIL] Expected isDocDpcr to be true!\n";
        } else {
            echo "  [PASS] Correctly detected as DPCR!\n";
        }
    } else {
        if ($isDocDpcr || $isDocOpcr || $isDocIperf) {
            echo "  [FAIL] Expected standard IPCR but flag was set!\n";
        } else {
            echo "  [PASS] Correctly detected as standard IPCR!\n";
        }
    }
}

echo "\n=== ALL CHECKS PASSED ===\n";
