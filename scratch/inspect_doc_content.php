<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$row = $db->querySingle("SELECT tabs FROM documents WHERE id = '8Oudg1vVERM'", true);
$tabs = json_decode($row['tabs'] ?? '', true);
echo "=== TAB 0 FORMDATA ===\n";
print_r($tabs[0]['formData']);
echo "=== TAB 0 CONTENT SAMPLE ===\n";
echo substr($tabs[0]['content'] ?? '', 0, 1000) . "\n";
// Search for table rows in content
preg_match_all('/<tr[^>]*class="[^"]*table-row-[^"]*"[^>]*>/i', $tabs[0]['content'] ?? '', $matches);
echo "Table rows found in content HTML: " . count($matches[0]) . "\n";
print_r($matches[0]);
