<?php
require_once __DIR__ . '/test_curl.php';

preg_match('/<tbody id="tbody-core">(.*?)<\/tbody>/s', $docHtml, $tbodyMatch);
if ($tbodyMatch) {
    echo "tbody-core content length: " . strlen($tbodyMatch[1]) . "\n";
    echo "tbody-core content preview:\n" . substr(trim($tbodyMatch[1]), 0, 300) . "\n";
} else {
    echo "tbody-core not found!\n";
}
