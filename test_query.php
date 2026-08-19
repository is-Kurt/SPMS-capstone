<?php
$db = new SQLite3('/var/www/html/SPMS-capstone/writable/database/spms_db.sqlite3');
$res = $db->query("SELECT id, name, email, role FROM users WHERE role = 'Admin'");
while($row = $res->fetchArray(SQLITE3_ASSOC)) {
    print_r($row);
}
