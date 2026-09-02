<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');

// Fetch OPCR template tabs (ID 3)
$tpl = $db->querySingle("SELECT tabs FROM templates WHERE id = 3", true);
if (!$tpl || empty($tpl['tabs'])) {
    die("OPCR template not found.\n");
}

$stmt = $db->prepare("UPDATE documents SET tabs = :tabs, is_target = 1 WHERE title LIKE '%OPCR%' OR id = 'E59VMotpQZU'");
$stmt->bindValue(':tabs', $tpl['tabs'], SQLITE3_TEXT);
$stmt->execute();

echo "Document E59VMotpQZU updated with OPCR template and is_target = 1.\n";

// Also check document table
$doc = $db->querySingle("SELECT id, title, is_target FROM documents WHERE id = 'E59VMotpQZU'", true);
echo "Document ID: " . $doc['id'] . " | Title: " . $doc['title'] . " | is_target: " . $doc['is_target'] . "\n";
