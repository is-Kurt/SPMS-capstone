<?php
$db = new SQLite3('writable/spms_db');
$res = $db->query("SELECT id, title, tabs FROM documents ORDER BY created_at DESC LIMIT 5");
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    echo "ID: " . $row['id'] . "\n";
    echo "Title: " . $row['title'] . "\n";
    echo "Tabs: " . substr($row['tabs'], 0, 100) . "...\n";
    echo "-------------------\n";
}
