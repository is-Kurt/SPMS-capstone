<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$res = $db->query('SELECT id, title, tabs FROM templates');
echo "=== TEMPLATES ===\n";
while ($r = $res->fetchArray(SQLITE3_ASSOC)) {
    echo "ID: " . $r['id'] . " | Title: " . $r['title'] . "\n";
    $tabs = json_decode($r['tabs'] ?? '', true);
    if ($tabs) {
        foreach ($tabs as $t) {
            echo "  Tab Title: " . ($t['title'] ?? '') . "\n";
            $fd = $t['formData'] ?? [];
            echo "  Categories: " . json_encode($fd['categories'] ?? null) . "\n";
            if (!empty($t['content'])) {
                echo "  Content length: " . strlen($t['content']) . "\n";
                echo "  Content snippet: " . substr(strip_tags($t['content']), 0, 100) . "...\n";
            }
        }
    } else {
        echo "  Tabs is empty\n";
    }
}

echo "\n=== DOCUMENTS ===\n";
$res2 = $db->query('SELECT id, title, tabs FROM documents LIMIT 10');
while ($r2 = $res2->fetchArray(SQLITE3_ASSOC)) {
    echo "ID: " . $r2['id'] . " | Title: " . $r2['title'] . "\n";
    $tabs2 = json_decode($r2['tabs'] ?? '', true);
    if ($tabs2) {
        foreach ($tabs2 as $t2) {
            echo "  Tab Title: " . ($t2['title'] ?? '') . "\n";
            $fd2 = $t2['formData'] ?? [];
            echo "  Categories: " . json_encode($fd2['categories'] ?? null) . "\n";
            if (!empty($t2['content'])) {
                echo "  Content length: " . strlen($t2['content']) . "\n";
                echo "  Content snippet: " . substr(strip_tags($t2['content']), 0, 100) . "...\n";
            }
        }
    } else {
        echo "  Tabs is empty\n";
    }
}
