<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$row = $db->querySingle("SELECT tabs FROM documents WHERE id = '8Oudg1vVERM'", true);
$tabs = json_decode($row['tabs'] ?? '', true);
$content = $tabs[0]['content'] ?? '';

// Find tbody-core in content
preg_match('/<tbody id="tbody-core"[^>]*>(.*?)<\/tbody>/s', $content, $m);
echo "=== TBODY CORE CONTENT ===\n";
echo $m[0] ?? '(not found)';
