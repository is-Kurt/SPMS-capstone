<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$stmt = $db->prepare('SELECT tabs FROM documents WHERE id = "8Oudg1vVERM"');
$tabs = json_decode($stmt->execute()->fetchArray(SQLITE3_ASSOC)['tabs'], true);
$content = $tabs[0]['content'] ?? '';

preg_match_all('/<tbody[^>]*>.*?<\/tbody>/s', $content, $matches);
foreach ($matches[0] as $tb) {
    echo "TBODY: " . substr(strip_tags($tb), 0, 100) . "\n";
    echo "  HTML length: " . strlen($tb) . "\n";
    echo "  Has table-row: " . (strpos($tb, 'table-row') !== false ? 'YES' : 'NO') . "\n";
}
