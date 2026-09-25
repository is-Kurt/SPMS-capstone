<?php
$lines = file('app/Views/document/_doc_rows.php');
foreach ($lines as $num => $line) {
    if (stripos($line, 'return') !== false || stripos($line, 'revision') !== false) {
        echo ($num + 1) . ": " . trim($line) . "\n";
    }
}
