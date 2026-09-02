<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$doc = $db->querySingle("SELECT id, title, tabs, updated_at FROM documents WHERE id = 'E59VMotpQZU'", true);
echo "Document: " . $doc['title'] . " | updated_at: " . $doc['updated_at'] . "\n";
$tabs = json_decode($doc['tabs'], true);
$content = $tabs[0]['content'] ?? '';
echo "Content length: " . strlen($content) . "\n";
echo "Has tbody-core: " . (strpos($content, 'tbody-core') !== false ? 'YES' : 'NO') . "\n";
echo "Has table-row-core: " . (strpos($content, 'table-row-core') !== false ? 'YES' : 'NO') . "\n";
echo "Has spms-table: " . (strpos($content, 'spms-table') !== false ? 'YES' : 'NO') . "\n";

if (preg_match('/<table class="spms-table".*?<\/table>/s', $content, $m)) {
    echo "TABLE LENGTH: " . strlen($m[0]) . "\n";
    echo "TABLE PREVIEW:\n" . $m[0] . "\n";
} else {
    echo "NO spms-table in content!\n";
}
