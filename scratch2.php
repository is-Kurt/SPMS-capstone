<?php
$db = new SQLite3('writable/spms_db');
$res = $db->query("SELECT id, title, tabs FROM documents WHERE id = '7IXrF1HezrQ'");
$row = $res->fetchArray(SQLITE3_ASSOC);
echo "ID: " . $row['id'] . "\n";
$tabs = json_decode($row['tabs'], true);
echo "Number of tabs: " . count($tabs) . "\n";
if (count($tabs) > 0) {
    echo "Tab 1 Title: " . $tabs[0]['title'] . "\n";
    echo "Tab 1 Content Length: " . strlen($tabs[0]['content']) . "\n";
}
