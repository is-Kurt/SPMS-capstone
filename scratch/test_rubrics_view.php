<?php

$html = file_get_contents('app/Views/document/show.php');

$checks = [
    'No btn-view-rubric-cards' => strpos($html, 'btn-view-rubric-cards') === false,
    'No btn-view-rubric-sheet' => strpos($html, 'btn-view-rubric-sheet') === false,
    'No rubrics-cards-container' => strpos($html, 'rubrics-cards-container') === false,
    'Has rubrics-sheet-container' => strpos($html, 'id="rubrics-sheet-container"') !== false,
    'Has digital-rubrics-table' => strpos($html, 'id="digital-rubrics-table"') !== false,
    'Has digital-rubrics-tbody' => strpos($html, 'id="digital-rubrics-tbody"') !== false,
    'Autosaves instantly badge exists' => strpos($html, 'Autosaves instantly') !== false,
    'No emoji ⚡' => strpos($html, '⚡') === false,
    'No emoji 🎴' => strpos($html, '🎴') === false,
    'No emoji 📑' => strpos($html, '📑') === false,
    'No emoji 🔒' => strpos($html, '🔒') === false,
    'No emoji 📋' => strpos($html, '📋') === false,
    'No emoji ⚠️' => strpos($html, '⚠️') === false,
    'No emoji 📄' => strpos($html, '📄') === false,
];

$allPassed = true;
foreach ($checks as $title => $passed) {
    echo ($passed ? "[PASS]" : "[FAIL]") . " " . $title . "\n";
    if (!$passed) $allPassed = false;
}

if ($allPassed) {
    echo "\n=== ALL CHECKS PASSED PERFECTLY ===\n";
} else {
    echo "\n=== SOME CHECKS FAILED ===\n";
}
