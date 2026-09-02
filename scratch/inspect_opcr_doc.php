<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$row = $db->querySingle("SELECT id, title, tabs FROM documents WHERE id = 'E59VMotpQZU'", true);
echo "Document: " . $row['title'] . "\n";
$tabs = json_decode($row['tabs'], true);
echo "Tabs count: " . count($tabs) . "\n";
echo "Tab 0 title: " . $tabs[0]['title'] . "\n";
echo "Tab 0 formData: \n" . json_encode($tabs[0]['formData'], JSON_PRETTY_PRINT) . "\n";
echo "Tab 0 content snippet (first 500 chars):\n" . substr($tabs[0]['content'], 0, 500) . "\n";
echo "Tab 0 content snippet (last 500 chars):\n" . substr($tabs[0]['content'], -500) . "\n";
