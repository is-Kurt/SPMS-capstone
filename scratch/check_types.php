<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$res = $db->query("SELECT df.user_id as owner_id FROM documents d JOIN document_folders df ON df.id = d.document_folder_id WHERE d.id = 'dVyNQE5Z_e8'");
$row = $res->fetchArray(SQLITE3_ASSOC);
echo "owner_id type: " . gettype($row['owner_id']) . " value: " . var_export($row['owner_id'], true) . "\n";

$res = $db->query("SELECT id FROM users WHERE email = 'deptchair@test.com'");
$user = $res->fetchArray(SQLITE3_ASSOC);
echo "user id type: " . gettype($user['id']) . " value: " . var_export($user['id'], true) . "\n";
