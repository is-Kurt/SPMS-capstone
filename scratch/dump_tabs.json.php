<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$row = $db->querySingle("SELECT tabs FROM documents WHERE id = '8Oudg1vVERM'", true);
echo $row['tabs'];
