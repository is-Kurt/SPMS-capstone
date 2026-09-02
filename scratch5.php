<?php
$db = new SQLite3('writable/spms_db');
$res = $db->query("SELECT * FROM documents WHERE id = '7IXrF1HezrQ'");
$row = $res->fetchArray(SQLITE3_ASSOC);
print_r($row);
