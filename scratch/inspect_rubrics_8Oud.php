<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$stmt = $db->prepare('SELECT tabs FROM documents WHERE id = "8Oudg1vVERM"');
$tabs = json_decode($stmt->execute()->fetchArray(SQLITE3_ASSOC)['tabs'], true);
$rubrics = $tabs[0]['formData']['rubrics'] ?? [];
print_r($rubrics);
