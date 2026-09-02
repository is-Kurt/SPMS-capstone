<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$res = $db->query("SELECT id, title, document_folder_id FROM documents");
while ($r = $res->fetchArray(SQLITE3_ASSOC)) {
    echo "ID: " . $r['id'] . " | Title: " . $r['title'] . " | Folder: " . $r['document_folder_id'] . "\n";
}
