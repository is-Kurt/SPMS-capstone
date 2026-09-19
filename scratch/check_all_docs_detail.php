<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$res = $db->query("SELECT d.id, d.title, d.updated_at as doc_updated, df.id as folder_id, df.title as folder_title, df.status, u.first_name, u.last_name, d.tabs 
FROM documents d 
JOIN document_folders df ON df.id = d.document_folder_id 
LEFT JOIN users u ON u.id = df.user_id 
ORDER BY d.updated_at DESC");

while ($r = $res->fetchArray(SQLITE3_ASSOC)) {
    $tabs = json_decode($r['tabs'] ?? '', true);
    $firstCoreMfo = $tabs[0]['formData']['categories']['core'][0]['mfo'] ?? '(no mfo)';
    $rateeName = $tabs[0]['formData']['ratee']['name'] ?? '(no ratee)';
    echo "Doc: {$r['id']} | {$r['title']} | User: {$r['first_name']} {$r['last_name']} | Folder: {$r['folder_id']} ({$r['status']}) | Doc Updated: {$r['doc_updated']}\n";
    echo "   Ratee: {$rateeName} | 1st Core MFO: {$firstCoreMfo}\n";
}
