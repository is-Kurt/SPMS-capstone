<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$res = $db->query("SELECT d.id, d.title, d.document_folder_id, f.title as folder_title, f.user_id, f.status as folder_status, f.parent_folder_id, u.first_name, u.last_name, r.name as role_name 
FROM documents d 
JOIN document_folders f ON d.document_folder_id = f.id 
JOIN users u ON f.user_id = u.id 
LEFT JOIN user_roles ur ON u.id = ur.user_id 
LEFT JOIN roles r ON ur.role_id = r.id 
WHERE d.title LIKE '%OPCR%' OR f.title LIKE '%OPCR%'");

while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    echo "Doc #{$row['id']} ({$row['title']}) | Folder #{$row['document_folder_id']} '{$row['folder_title']}' | Status: {$row['folder_status']} | User: {$row['first_name']} {$row['last_name']} (Role: {$row['role_name']}) | ParentFolder: {$row['parent_folder_id']}\n";
}
