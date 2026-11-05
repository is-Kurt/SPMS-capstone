<?php
$pdo = new PDO('sqlite:' . __DIR__ . '/writable/database/spms_db.sqlite3');
$pdo->exec("UPDATE document_folders SET eval_period_open_sent_at = NULL WHERE id = '5k3rK8WhG00'");
echo "Set eval_period_open_sent_at to NULL.\n";
