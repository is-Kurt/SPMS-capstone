<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$row = $db->querySingle('SELECT tabs FROM documents WHERE id = "E59VMotpQZU"', true);
$tabs = json_decode($row['tabs'], true);
$content = $tabs[0]['content'];
$pos = strpos($content, '<table class="spms-table">');
echo substr($content, $pos, 1800);
