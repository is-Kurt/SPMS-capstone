<?php
// Simulate Folder::submitTarget for user 6 on folder X2s5QEZn84I
$db = new SQLite3('writable/database/spms_db.sqlite3');
$folderId = 'X2s5QEZn84I';
$userId = 6;

$res = $db->query("SELECT * FROM document_folders WHERE id = '$folderId'");
$folder = $res->fetchArray(SQLITE3_ASSOC);

if (!$folder || $folder['user_id'] != $userId) {
    die("Unauthorized\n");
}

// Parent folder check:
if (!empty($folder['parent_folder_id'])) {
    $pRes = $db->query("SELECT * FROM document_folders WHERE id = '{$folder['parent_folder_id']}'");
    $parentFolder = $pRes->fetchArray(SQLITE3_ASSOC);
    if ($parentFolder) {
        $pDocRes = $db->query("SELECT * FROM documents WHERE document_folder_id = '{$parentFolder['id']}'");
        $parentDoc = $pDocRes->fetchArray(SQLITE3_ASSOC);
        
        $mDocRes = $db->query("SELECT * FROM documents WHERE document_folder_id = '$folderId'");
        $myDoc = $mDocRes->fetchArray(SQLITE3_ASSOC);
        
        $myDocTitleUpper = strtoupper($myDoc['title'] ?? '');
        $isMyDocOpcr = str_contains($myDocTitleUpper, 'OPCR') || str_contains($myDocTitleUpper, 'OFFICE');
        
        $parentDocTitleUpper = strtoupper($parentDoc['title'] ?? '');
        $isParentDocOpcr = str_contains($parentDocTitleUpper, 'OPCR') || str_contains($parentDocTitleUpper, 'OFFICE');
        
        echo "Parent doc: " . ($parentDoc['title'] ?? 'none') . "\n";
        echo "My doc: " . ($myDoc['title'] ?? 'none') . "\n";
        echo "Parent folder status: " . $parentFolder['status'] . "\n";
        echo "isMyDocOpcr: " . ($isMyDocOpcr ? 'true' : 'false') . "\n";
        echo "isParentDocOpcr: " . ($isParentDocOpcr ? 'true' : 'false') . "\n";
        
        if ($parentDoc && !$isMyDocOpcr) {
            if (!$isParentDocOpcr && $parentFolder['status'] !== 'target_approved') {
                echo "FAIL: Cannot submit targets yet: The superior basis commitments (\"{$parentFolder['title']}\") have not been approved by the higher-up yet.\n";
            } else {
                echo "PASS parent check! Dean can submit against OPCR institutional basis.\n";
            }
        }
    }
}
