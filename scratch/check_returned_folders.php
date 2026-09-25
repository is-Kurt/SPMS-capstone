<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$res = $db->query("SELECT id, user_id, title, status, updated_at FROM document_folders WHERE status LIKE '%return%'");
while ($f = $res->fetchArray(SQLITE3_ASSOC)) {
    print_r($f);
}
