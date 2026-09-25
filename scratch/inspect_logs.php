<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$res = $db->query("SELECT * FROM activity_logs ORDER BY id DESC LIMIT 20");
echo "=== ACTIVITY LOGS ===\n";
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    echo "Log {$row['id']} | {$row['created_at']} | {$row['action']} | user={$row['user_id']} | desc={$row['description']}\n";
}

echo "\n=== NOTIFICATIONS ===\n";
$res2 = $db->query("SELECT * FROM notifications ORDER BY id DESC LIMIT 10");
while ($row2 = $res2->fetchArray(SQLITE3_ASSOC)) {
    echo "Notif {$row2['id']} | {$row2['created_at']} | user={$row2['user_id']} | title={$row2['title']} | msg={$row2['message']} | link={$row2['link']}\n";
}
