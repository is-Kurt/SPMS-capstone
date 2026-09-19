<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$res = $db->query('SELECT d.id, d.title, d.is_target, df.id as folder_id, df.status, u.email, u.first_name, u.last_name, d.updated_at FROM documents d JOIN document_folders df ON df.id = d.document_folder_id LEFT JOIN users u ON u.id = df.user_id ORDER BY d.updated_at DESC');
while ($r = $res->fetchArray(SQLITE3_ASSOC)) {
    echo $r['id'] . ' | ' . $r['title'] . ' | ' . $r['email'] . ' (' . $r['first_name'] . ' ' . $r['last_name'] . ') | folder: ' . $r['folder_id'] . ' (' . $r['status'] . ') | target: ' . $r['is_target'] . ' | updated: ' . $r['updated_at'] . PHP_EOL;
}
