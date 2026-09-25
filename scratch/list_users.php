<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$res = $db->query("SELECT u.id, u.email, u.first_name, u.last_name, r.name as role FROM users u LEFT JOIN user_roles ur ON ur.user_id = u.id LEFT JOIN roles r ON r.id = ur.role_id");
while ($u = $res->fetchArray(SQLITE3_ASSOC)) {
    echo "User: " . $u['id'] . " | " . $u['email'] . " | " . $u['first_name'] . " " . $u['last_name'] . " | " . $u['role'] . "\n";
}
