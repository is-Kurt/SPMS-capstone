<?php
$pdo = new PDO('sqlite:' . __DIR__ . '/writable/spms_db');
$pdo->exec("UPDATE document_folders SET eval_period_open_sent_at = NULL WHERE id = '5k3rK8WhG00'");
echo "Set eval_period_open_sent_at to NULL.\n";
