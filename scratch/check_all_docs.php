<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$res = $db->query("SELECT id, title, document_folder_id, tabs, is_target FROM documents");
while ($r = $res->fetchArray(SQLITE3_ASSOC)) {
    echo "ID: " . $r['id'] . " | Title: " . $r['title'] . " | is_target: " . $r['is_target'] . " | folder: " . $r['document_folder_id'] . "\n";
    $tabs = json_decode($r['tabs'] ?? '', true);
    if ($tabs) {
        foreach ($tabs as $i => $t) {
            echo "  Tab $i: " . ($t['title'] ?? '') . "\n";
            echo "    FormData keys: " . (isset($t['formData']) ? implode(',', array_keys($t['formData'])) : 'none') . "\n";
            if (isset($t['formData']['categories'])) {
                echo "    Core rows: " . count($t['formData']['categories']['core'] ?? []) . "\n";
                echo "    Strategic rows: " . count($t['formData']['categories']['strategic'] ?? []) . "\n";
                echo "    Support rows: " . count($t['formData']['categories']['support'] ?? []) . "\n";
            }
            if (isset($t['content'])) {
                echo "    Content length: " . strlen($t['content']) . "\n";
            }
        }
    } else {
        echo "  No tabs or invalid JSON\n";
    }
}
