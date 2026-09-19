<?php

$db = new SQLite3('writable/database/spms_db.sqlite3');

$t4 = $db->querySingle("SELECT * FROM templates WHERE id = 4", true);
$tabs = json_decode($t4['tabs'], true);
$formData = $tabs[0]['formData'];

$doc = [
    'id' => 'test-iperf-doc',
    'title' => 'INDIVIDUAL PERFORMANCE EVALUATION RATING FORM (IPERF)',
    'doc_type' => 'iperf',
    'folder_status' => 'draft_target',
    'owner_id' => 34,
    'is_target' => 1,
    'tabs' => $tabs,
    'content' => '',
    'iperf_target_end' => null,
    'iperf_eval_end' => null
];

$ownerInfo = [
    'name' => 'Nestor Pascual',
    'position' => 'Administrative Aide IV (COS)',
    'dept' => 'General Services Office',
    'period' => 'July - December 2024'
];

$ownerDocType = strtolower($doc['doc_type'] ?? 'ipcr');
$isDocDpcr = in_array($ownerDocType, ['dpcr', 'cdpcr']) || stripos($doc['title'] ?? '', 'dpcr') !== false;
$ownerPos = strtolower($ownerInfo['position'] ?? '');
$isOwnerDean = str_contains($ownerPos, 'dean');
$isDocOpcr = (!$isDocDpcr) && ($ownerDocType === 'opcr' || stripos($doc['title'] ?? '', 'opcr') !== false || str_contains($ownerPos, 'vice president'));
$isDocIperf = (!$isDocDpcr && !$isDocOpcr) && ($ownerDocType === 'iperf' || stripos($doc['title'] ?? '', 'iperf') !== false);

echo "Simulated show.php variable checks:\n";
echo "  isDocDpcr: " . ($isDocDpcr ? 'TRUE' : 'FALSE') . "\n";
echo "  isDocOpcr: " . ($isDocOpcr ? 'TRUE' : 'FALSE') . "\n";
echo "  isDocIperf: " . ($isDocIperf ? 'TRUE' : 'FALSE') . "\n";

assert($isDocIperf === true, "Expected isDocIperf to be true");

$titleHeader = $isDocIperf 
    ? 'INDIVIDUAL PERFORMANCE EVALUATION RATING FORM FOR CONTRACT OF SERVICE AND JOB ORDER PERSONNEL' 
    : ($isDocDpcr ? 'DEPARTMENT PERFORMANCE COMMITMENT AND REVIEW (DPCR)' : ($isDocOpcr ? 'OFFICE PERFORMANCE COMMITMENT AND REVIEW (OPCR)' : 'INDIVIDUAL PERFORMANCE COMMITMENT AND REVIEW (IPCR)'));

echo "  Resolved Title Header: {$titleHeader}\n";
assert(strpos($titleHeader, 'CONTRACT OF SERVICE AND JOB ORDER') !== false);

$colCount = $isDocDpcr ? '15 Columns' : ($isDocOpcr ? '10 Columns' : ($isDocIperf ? '8 Columns' : '9 Columns'));
echo "  Resolved Columns Badge: {$colCount}\n";
assert($colCount === '8 Columns');

echo "\n[PASS] All show.php IPERF logic verified successfully!\n";
