<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$res = $db->query("SELECT u.id, u.first_name, u.last_name, u.email, df.id as folder_id, df.title as folder_title, df.status, d.id as doc_id, d.title as doc_title, d.tabs
                   FROM users u
                   JOIN document_folders df ON df.user_id = u.id
                   LEFT JOIN documents d ON d.document_folder_id = df.id
                   WHERE u.first_name LIKE '%Ara%' OR u.last_name LIKE '%Santos%'");

while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    echo "User: {$row['first_name']} {$row['last_name']} (ID: {$row['id']})\n";
    echo "Folder: ID={$row['folder_id']} | Title={$row['folder_title']} | Status={$row['status']}\n";
    echo "Doc: ID={$row['doc_id']} | Title={$row['doc_title']}\n";
    $tabs = json_decode($row['tabs'] ?? '', true);
    if ($tabs) {
        foreach ($tabs as $idx => $t) {
            echo "  Tab {$idx}: {$t['title']}\n";
            if (!empty($t['formData'])) {
                $fd = $t['formData'];
                $cats = $fd['categories'] ?? [];
                echo "    Core rows count: " . count($cats['core'] ?? []) . "\n";
                echo "    Strategic rows count: " . count($cats['strategic'] ?? []) . "\n";
                echo "    Support rows count: " . count($cats['support'] ?? []) . "\n";
                echo "    formData keys: " . implode(', ', array_keys($fd)) . "\n";
                echo "    formData ratee: " . json_encode($fd['ratee'] ?? []) . "\n";
                echo "    formData revisionHistory: " . json_encode($fd['revisionHistory'] ?? []) . "\n";
                if (!empty($cats['core'])) {
                    echo "    First core row: " . json_encode($cats['core'][0]) . "\n";
                }
            } else {
                echo "    formData is EMPTY or NULL\n";
            }
            if (!empty($t['content'])) {
                echo "    Content length: " . strlen($t['content']) . "\n";
                echo "    Content preview: " . substr(strip_tags($t['content']), 0, 100) . "\n";
            }
        }
    } else {
        echo "  tabs is NULL or invalid\n";
    }
    echo "--------------------------------------------------------\n";
}
