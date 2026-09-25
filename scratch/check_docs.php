<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$res = $db->query("SELECT id, title, document_folder_id, is_target, updated_at FROM documents WHERE document_folder_id = 'J-LbOWxO-nE'");
while ($d = $res->fetchArray(SQLITE3_ASSOC)) {
    print_r($d);
}
