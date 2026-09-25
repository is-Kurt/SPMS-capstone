<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$stmt = $db->prepare('SELECT * FROM documents WHERE id = :id');
$stmt->bindValue(':id', '8Oudg1vVERM', SQLITE3_TEXT);
$res = $stmt->execute();
$doc = $res->fetchArray(SQLITE3_ASSOC);
if (!$doc) {
    echo "Doc not found\n";
    exit;
}
echo "Doc title: " . $doc['title'] . "\n";
echo "Folder ID: " . $doc['document_folder_id'] . "\n";
$tabs = json_decode($doc['tabs'], true);
echo "Tabs count: " . count($tabs) . "\n";
foreach ($tabs as $i => $tab) {
    echo "Tab $i id: " . ($tab['id'] ?? 'none') . " title: " . ($tab['title'] ?? 'none') . "\n";
    if (isset($tab['formData'])) {
        echo "  formData keys: " . implode(', ', array_keys($tab['formData'])) . "\n";
        if (isset($tab['formData']['categories'])) {
            echo "  categories: " . json_encode($tab['formData']['categories']) . "\n";
        }
        if (isset($tab['formData']['rubrics'])) {
            echo "  rubrics count: " . count($tab['formData']['rubrics']) . "\n";
            echo "  rubrics keys: " . implode(', ', array_keys($tab['formData']['rubrics'])) . "\n";
        }
        if (isset($tab['formData']['revisionHistory'])) {
            echo "  revisionHistory: " . json_encode($tab['formData']['revisionHistory']) . "\n";
        }
    }
    echo "  content length: " . strlen($tab['content'] ?? '') . "\n";
    if (strpos($tab['content'] ?? '', 'table-row-core') !== false) {
        echo "  content has table-row-core: YES\n";
    } else {
        echo "  content has table-row-core: NO\n";
    }
}
