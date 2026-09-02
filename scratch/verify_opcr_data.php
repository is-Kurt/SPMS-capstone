<?php
// Test rendering document view for E59VMotpQZU
$db = new SQLite3('writable/database/spms_db.sqlite3');
$doc = $db->querySingle("SELECT d.*, df.user_id as owner_id, df.status as folder_status FROM documents d JOIN document_folders df ON df.id = d.document_folder_id WHERE d.id = 'E59VMotpQZU'", true);
echo "Document Title: " . $doc['title'] . "\n";
echo "Is Target: " . $doc['is_target'] . "\n";
$tabs = json_decode($doc['tabs'], true);
echo "Tab Title: " . $tabs[0]['title'] . "\n";
echo "FormData Title: " . ($tabs[0]['formData']['title'] ?? 'none') . "\n";
