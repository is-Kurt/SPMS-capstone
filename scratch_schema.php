<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$res = $db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name IN ('documents', 'evaluation_routings', 'document_folders');");
while ($row = $res->fetchArray()) {
    echo $row['sql'] . "\n\n";
}
