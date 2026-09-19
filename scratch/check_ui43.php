<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$stmt = $db->prepare("SELECT id, title, updated_at, tabs FROM documents WHERE id = 'ui43YgLNZOo'");
$res = $stmt->execute();
$r = $res->fetchArray(SQLITE3_ASSOC);
echo "Doc ui43YgLNZOo:\n";
echo "Title: " . $r['title'] . "\n";
echo "Updated at: " . $r['updated_at'] . "\n";
$tabs = json_decode($r['tabs'] ?? '', true);
echo json_encode($tabs[0]['formData'] ?? [], JSON_PRETTY_PRINT);
