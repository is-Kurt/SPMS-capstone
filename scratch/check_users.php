<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$r = $db->querySingle('SELECT id, first_name, last_name, doc_type FROM users WHERE id = 5', true);
print_r($r);
