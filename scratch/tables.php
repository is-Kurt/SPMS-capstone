<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$res = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
while ($r = $res->fetchArray(SQLITE3_ASSOC)) {
    echo $r['name'] . "\n";
}
