<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$res = $db->query("SELECT * FROM activity_logs ORDER BY id DESC LIMIT 30");
$rows = [];
while ($r = $res->fetchArray(SQLITE3_ASSOC)) {
    $rows[] = $r;
}
foreach (array_reverse($rows) as $r) {
    echo "ID: " . $r['id'] . " | User: " . $r['user_id'] . " | Action: " . $r['action'] . " | Time: " . $r['created_at'] . "\n  Details: " . $r['details'] . "\n";
}
