<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$r = $db->querySingle("SELECT tabs FROM templates WHERE id = 3", true);
$tabs = json_decode($r['tabs'], true);
$html = $tabs[0]['content'] ?? '';
echo "Content length: " . strlen($html) . "\n";
echo "Has tbody-core: " . (strpos($html, 'tbody-core') !== false ? 'YES' : 'NO') . "\n";
echo "Has table-row-core: " . (strpos($html, 'table-row-core') !== false ? 'YES' : 'NO') . "\n";
echo "Has tfoot-add-core: " . (strpos($html, 'tfoot-add-core') !== false ? 'YES' : 'NO') . "\n";
if (preg_match('/<table class="spms-table".*?<\/table>/s', $html, $m)) {
    echo "TABLE LENGTH: " . strlen($m[0]) . "\n";
    echo "TABLE PREVIEW:\n" . substr($m[0], 0, 1000) . "\n...\n" . substr($m[0], -500) . "\n";
} else {
    echo "NO spms-table found in content!\n";
}
