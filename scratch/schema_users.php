<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$res = $db->query("PRAGMA table_info(users)");
while($r = $res->fetchArray(SQLITE3_ASSOC)) {
    echo $r['name'] . " (" . $r['type'] . ")\n";
}
