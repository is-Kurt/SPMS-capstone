<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$row = $db->querySingle('SELECT tabs FROM documents WHERE id = "E59VMotpQZU"', true);
$tabs = json_decode($row['tabs'], true);
echo "CATEGORIES:\n";
var_dump($tabs[0]['formData']['categories']);
