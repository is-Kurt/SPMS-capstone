<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$res = $db->query("SELECT id, title, tabs FROM templates");
while ($r = $res->fetchArray(SQLITE3_ASSOC)) {
    echo "=== ID: " . $r['id'] . " | Title: " . $r['title'] . " ===\n";
    $tabs = json_decode($r['tabs'], true);
    if ($tabs && is_array($tabs)) {
        foreach ($tabs as $t) {
            echo "Tab ID: " . ($t['id'] ?? '') . " | Title: " . ($t['title'] ?? '') . "\n";
            echo "Content Preview: " . substr(strip_tags($t['content'] ?? ''), 0, 100) . "\n";
            echo "Has formData: " . (isset($t['formData']) ? 'YES' : 'NO') . "\n";
        }
    } else {
        echo "Tabs raw: " . substr($r['tabs'], 0, 100) . "\n";
    }
    echo "\n";
}
