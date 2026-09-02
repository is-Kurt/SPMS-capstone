<?php
$db = new SQLite3('/var/www/html/SPMS-capstone/writable/database.db');
$res = $db->query('SELECT email, password FROM users');
while ($row = $res->fetchArray()) {
    echo $row['email'] . "\n";
}
