<?php
$db = new SQLite3('writable/spms_db');
$res = $db->query("SELECT f.id as folder_id, f.title as folder_title, f.status, d.id as doc_id, d.title as doc_title, d.tabs FROM document_folders f JOIN documents d ON d.document_folder_id = f.id ORDER BY f.created_at DESC LIMIT 5");
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    echo "Folder ID: " . $row['folder_id'] . " | Status: " . $row['status'] . "\n";
    echo "Doc ID: " . $row['doc_id'] . "\n";
    $tabs = json_decode($row['tabs'], true);
    echo "Num tabs: " . (is_array($tabs) ? count($tabs) : 0) . "\n";
    if (is_array($tabs) && count($tabs) > 0) {
        echo "Tab 1 Length: " . strlen($tabs[0]['content']) . "\n";
    }
    echo "------------------\n";
}
