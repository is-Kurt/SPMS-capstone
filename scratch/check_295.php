<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$stmt = $db->prepare('SELECT * FROM activity_logs WHERE id = 295');
$r = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
print_r($r);
$stmt2 = $db->prepare('SELECT * FROM document_folders WHERE id = :id');
$stmt2->bindValue(':id', $r['entity_id']);
$f = $stmt2->execute()->fetchArray(SQLITE3_ASSOC);
print_r($f);
