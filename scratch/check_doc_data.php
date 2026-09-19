<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$stmt = $db->prepare("SELECT id, title, tabs FROM documents WHERE id = 'dVyNQE5Z_e8'");
$res = $stmt->execute();
$r = $res->fetchArray(SQLITE3_ASSOC);
echo "Document " . $r['id'] . " (" . $r['title'] . "):\n";
$tabs = json_decode($r['tabs'] ?? '', true);
echo json_encode($tabs[0]['formData'] ?? [], JSON_PRETTY_PRINT);
