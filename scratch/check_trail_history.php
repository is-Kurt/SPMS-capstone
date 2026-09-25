<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$stmt = $db->prepare('SELECT * FROM document_folders WHERE id = "J-LbOWxO-nE"');
$f = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
print_r($f);
$stmt2 = $db->prepare('SELECT * FROM activity_logs WHERE details LIKE "%J-LbOWxO-nE%" OR details LIKE "%Trail%" OR details LIKE "%8Oudg1vVERM%"');
$res2 = $stmt2->execute();
while ($r = $res2->fetchArray(SQLITE3_ASSOC)) {
    echo "Log: " . $r['id'] . " | " . $r['user_id'] . " | " . $r['action'] . " | " . $r['created_at'] . " | " . $r['details'] . "\n";
}
