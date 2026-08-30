<?php
$db = new SQLite3('writable/spms_db');
$res = $db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name IN ('documents', 'evaluation_routings', 'document_folders');");
while ($row = $res->fetchArray()) {
    echo $row['sql'] . "\n\n";
}
