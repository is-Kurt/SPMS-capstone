<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$doc = $db->querySingle("SELECT d.*, u.email, u.name, u.role FROM documents d JOIN users u ON u.id = d.user_id WHERE d.id = 'E59VMotpQZU'", true);
print_r($doc);
