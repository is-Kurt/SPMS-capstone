<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$stmt = $db->prepare("SELECT id, title, updated_at, tabs FROM documents WHERE id = 'a1Oz0dRNU5Q'");
$res = $stmt->execute();
$r = $res->fetchArray(SQLITE3_ASSOC);
echo "Current doc a1Oz0dRNU5Q:\n";
echo "Title: " . $r['title'] . "\n";
echo "Updated at: " . $r['updated_at'] . "\n";
$tabs = json_decode($r['tabs'] ?? '', true);
echo "Tabs count: " . count($tabs) . "\n";
echo "Tab 0 formData ratee: " . json_encode($tabs[0]['formData']['ratee'] ?? []) . "\n";
echo "Tab 0 formData categories core count: " . count($tabs[0]['formData']['categories']['core'] ?? []) . "\n";
foreach (($tabs[0]['formData']['categories']['core'] ?? []) as $idx => $row) {
    echo "  Row $idx mfo: " . $row['mfo'] . " | indicators: " . $row['indicators'] . " | accountable: " . ($row['accountable'] ?? '') . "\n";
}
