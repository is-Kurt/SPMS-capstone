<?php

require 'vendor/autoload.php';

use App\Libraries\CscExcelExporter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

echo "=== IPERF & ALL TEMPLATES VERIFICATION SUITE ===\n\n";

$db = new SQLite3('writable/database/spms_db.sqlite3');

// 1. Check Template 4 in database
$template = $db->querySingle("SELECT * FROM templates WHERE id = 4", true);
if (!$template) {
    echo "[FAIL] Template 4 not found in database!\n";
} else {
    echo "[PASS] Template 4 found: {$template['title']}\n";
    $tabs = json_decode($template['tabs'], true);
    echo "       Tabs count: " . count($tabs) . "\n";
    if (!empty($tabs)) {
        $formData = $tabs[0]['formData'] ?? [];
        echo "       Doc Type in formData: " . ($formData['doc_type'] ?? 'none') . "\n";
        echo "       Classification in formData: " . ($formData['classification'] ?? 'none') . "\n";
        echo "       Weights: " . json_encode($formData['weights'] ?? []) . "\n";
        echo "       Core Deliverables count: " . count($formData['categories']['core'] ?? []) . "\n";
    }
}

// 2. Test CscExcelExporter exportIperf
echo "\n--- Testing CscExcelExporter::exportIperf ---\n";
$mockDoc = [
    'id' => 999,
    'title' => 'INDIVIDUAL PERFORMANCE EVALUATION RATING FORM (IPERF) FOR COS',
    'doc_type' => 'iperf',
    'folder_status' => 'draft_target',
    'owner_id' => 1
];

$mockFormData = [
    'title' => 'INDIVIDUAL PERFORMANCE EVALUATION RATING FORM FOR CONTRACT OF SERVICE AND JOB ORDER PERSONNEL',
    'doc_type' => 'iperf',
    'classification' => 'Contract of Service (COS)',
    'ratee' => [
        'name' => 'Juan Dela Cruz III',
        'position' => 'Administrative Aide VI',
        'dept' => 'Office of Public Affairs',
        'period' => 'July - December 2024'
    ],
    'weights' => ['core' => 1.0, 'strategic' => 0.0, 'support' => 0.0],
    'categories' => [
        'core' => [
            [
                'mfo' => 'Information and Communication Technology Support Services',
                'indicators' => '100% of technical support requests resolved within 24 hours (Target: 50 tickets)',
                'accomplishments' => 'Successfully resolved 52 out of 52 technical support tickets within standard response time',
                'q' => 5,
                't' => 4,
                'e' => 5,
                'remarks' => 'Outstanding response turnaround'
            ],
            [
                'mfo' => 'Database Backup and Records Archiving Maintenance',
                'indicators' => 'Weekly automated and verified system backups conducted (Target: 24 backups)',
                'accomplishments' => 'Completed 24 scheduled system backups with zero data loss and quarterly integrity check',
                'q' => 5,
                't' => 5,
                'e' => 4,
                'remarks' => 'Completed per timetable'
            ]
        ],
        'strategic' => [],
        'support' => []
    ],
    'pmtRemarks' => 'Commended for excellent technical support and timely completion of deliverables.',
    'signatories' => [
        'targetsPreparedName' => 'Juan Dela Cruz III',
        'targetsPreparedDate' => 'July 1, 2024',
        'targetsApprovedName' => 'Dr. Maria Santos',
        'targetsApprovedDate' => 'July 2, 2024',
        'evalRatedName' => 'Dr. Maria Santos',
        'evalRatedDate' => 'December 20, 2024',
        'evalConformeName' => 'Juan Dela Cruz III',
        'evalConformeDate' => 'December 21, 2024'
    ]
];

$mockOwnerInfo = [
    'name' => 'Juan Dela Cruz III',
    'position' => 'Administrative Aide VI',
    'dept' => 'Office of Public Affairs',
    'period' => 'July - December 2024'
];

$mockSuperiorInfo = [
    'first_name' => 'Maria',
    'last_name' => 'Santos',
    'position' => 'Director, Information Technology Services'
];

try {
    $spreadsheet = new Spreadsheet();
    $refMethod = new ReflectionMethod(CscExcelExporter::class, 'exportIperf');
    $refMethod->setAccessible(true);
    $refMethod->invoke(null, $spreadsheet, $mockFormData, $mockDoc, $mockOwnerInfo, $mockSuperiorInfo);

    $testFilePath = __DIR__ . '/test_iperf_output.xlsx';
    $writer = new XlsxWriter($spreadsheet);
    $writer->save($testFilePath);

    if (file_exists($testFilePath) && filesize($testFilePath) > 0) {
        echo "[PASS] Generated IPERF Excel file successfully: {$testFilePath} (" . filesize($testFilePath) . " bytes)\n";
        
        $reader = new XlsxReader();
        $readSpreadsheet = $reader->load($testFilePath);
        $readSheet = $readSpreadsheet->getActiveSheet();

        echo "       Sheet Title: " . $readSheet->getTitle() . "\n";
        echo "       A2 (University): " . $readSheet->getCell('A2')->getValue() . "\n";
        echo "       A4 (Title): " . $readSheet->getCell('A4')->getValue() . "\n";
        echo "       B6 (Employee): " . $readSheet->getCell('B6')->getValue() . "\n";
        echo "       F6 (Classification): " . $readSheet->getCell('F6')->getValue() . "\n";
        echo "       B7 (Position): " . $readSheet->getCell('B7')->getValue() . "\n";
        echo "       F7 (Rating Period): " . $readSheet->getCell('F7')->getValue() . "\n";
        echo "       B8 (Office): " . $readSheet->getCell('B8')->getValue() . "\n";
        echo "       A12 (Row 1 PPA): " . substr($readSheet->getCell('A12')->getValue(), 0, 40) . "...\n";
        echo "       G12 (Row 1 Avg Formula): " . $readSheet->getCell('G12')->getValue() . "\n";
        echo "       A14 (Overall Avg Label): " . $readSheet->getCell('A14')->getValue() . "\n";
        echo "       G14 (Overall Avg Formula): " . $readSheet->getCell('G14')->getValue() . "\n";
        echo "       H14 (Adjectival Formula): " . $readSheet->getCell('H14')->getValue() . "\n";
        echo "       A15 (Remarks Header): " . $readSheet->getCell('A15')->getValue() . "\n";
        echo "       A16 (Remarks Content): " . substr($readSheet->getCell('A16')->getValue(), 0, 40) . "...\n";
        echo "       A18 (Phase 1 Header): " . $readSheet->getCell('A18')->getValue() . "\n";
        echo "       C18 (Phase 2 Header): " . $readSheet->getCell('C18')->getValue() . "\n";
        echo "       E18 (Measures Header): " . $readSheet->getCell('E18')->getValue() . "\n";

        // Clean up test file
        @unlink($testFilePath);
        echo "       [PASS] Cleaned up temporary test file.\n";
    } else {
        echo "[FAIL] Failed to generate IPERF Excel file!\n";
    }
} catch (\Throwable $e) {
    echo "[FAIL] Exception during Excel export: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

// 3. Verify All 4 Templates in database
echo "\n--- Checking All 4 Database Templates Integrity ---\n";
$res = $db->query("SELECT id, title FROM templates ORDER BY id ASC");
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    echo "       Template ID {$row['id']}: {$row['title']}\n";
}

echo "\n=== ALL TESTS FINISHED ===\n";
