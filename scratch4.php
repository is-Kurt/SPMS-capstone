<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$res = $db->query("SELECT created_at, updated_at FROM documents WHERE id = '7IXrF1HezrQ'");
$row = $res->fetchArray(SQLITE3_ASSOC);
print_r($row);
