<?php
$dbPath = 'writable/database/spms_db.sqlite3';
$pdo = new PDO("sqlite:" . $dbPath);

// 1. Fix templates
$stmt = $pdo->query("SELECT id, title, tabs FROM templates");
$templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($templates as $t) {
    $tabs = json_decode($t['tabs'] ?? '', true);
    $changed = false;
    if (is_array($tabs)) {
        foreach ($tabs as $idx => &$tb) {
            if (empty(trim($tb['title'] ?? ''))) {
                $tb['title'] = 'Target Form';
                $changed = true;
                echo "Fixing Template {$t['id']} ({$t['title']}) Tab $idx title -> 'Target Form'\n";
            }
        }
        if ($changed) {
            $upd = $pdo->prepare("UPDATE templates SET tabs = ? WHERE id = ?");
            $upd->execute([json_encode($tabs), $t['id']]);
        }
    }
}

// 2. Fix documents
$stmt = $pdo->query("SELECT id, title, tabs FROM documents");
$docs = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($docs as $d) {
    $tabs = json_decode($d['tabs'] ?? '', true);
    $changed = false;
    if (is_array($tabs)) {
        foreach ($tabs as $idx => &$tb) {
            if (empty(trim($tb['title'] ?? ''))) {
                $tb['title'] = 'Target Form';
                $changed = true;
                echo "Fixing Doc {$d['id']} ({$d['title']}) Tab $idx title -> 'Target Form'\n";
            }
        }
        if ($changed) {
            $upd = $pdo->prepare("UPDATE documents SET tabs = ? WHERE id = ?");
            $upd->execute([json_encode($tabs), $d['id']]);
        }
    }
}

echo "Done fixing tab titles!\n";
