<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$res = $db->query("PRAGMA table_info(activity_logs)");
while ($c = $res->fetchArray(SQLITE3_ASSOC)) {
    echo $c['name'] . " (" . $c['type'] . ")\n";
}
