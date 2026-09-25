<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$pFolder = $db->querySingle("SELECT * FROM document_folders WHERE id = 'RdLBWbJbYak'", true);
print_r($pFolder);
$pDocs = $db->query("SELECT id, title, is_target, tabs FROM documents WHERE document_folder_id = 'RdLBWbJbYak'");
while ($pd = $pDocs->fetchArray(SQLITE3_ASSOC)) {
    echo "Parent doc: ID={$pd['id']}, Title={$pd['title']}, is_target={$pd['is_target']}\n";
    $tabs = json_decode($pd['tabs'] ?? '', true);
    echo "  Tabs: " . count($tabs ?: []) . "\n";
}
