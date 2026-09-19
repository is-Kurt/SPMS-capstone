<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$folderId = 'zFK3kodkAXU';
$res = $db->query("SELECT * FROM document_folders WHERE id = '$folderId'");
$folder = $res->fetchArray(SQLITE3_ASSOC);
echo "Folder:\n";
print_r($folder);

if (!empty($folder['parent_folder_id'])) {
    $pRes = $db->query("SELECT * FROM document_folders WHERE id = '{$folder['parent_folder_id']}'");
    $parentFolder = $pRes->fetchArray(SQLITE3_ASSOC);
    echo "Parent folder:\n";
    print_r($parentFolder);
}
