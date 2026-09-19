<?php
$lines = file('app/Views/document/show.php');
foreach ($lines as $i => $l) {
    if (stripos($l, 'date') !== false && (stripos($l, '<input') !== false || stripos($l, 'approver-date') !== false || stripos($l, 'sig-') !== false)) {
        echo ($i + 1) . ': ' . trim($l) . "\n";
    }
}
