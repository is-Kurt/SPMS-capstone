<?php
require_once 'scratch/update_templates.php';

$db = new SQLite3('writable/database/spms_db.sqlite3');
$tabsJson = buildTemplateTabs(
    'Office Performance Commitment and Review (OPCR) — Executive / College',
    ['core' => 0.60, 'strategic' => 0.25, 'support' => 0.15],
    '1. CORE OFFICE MANDATE (60%)'
);

$stmt = $db->prepare("UPDATE documents SET tabs = :tabs WHERE id = 'E59VMotpQZU'");
$stmt->bindValue(':tabs', $tabsJson, SQLITE3_TEXT);
$stmt->execute();
echo "Updated document E59VMotpQZU (OPCR) with official BSU SPMS format.\n";
