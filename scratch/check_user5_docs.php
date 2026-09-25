<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$res = $db->query("SELECT d.id, d.title, d.tabs, df.status FROM documents d JOIN document_folders df ON df.id = d.document_folder_id WHERE df.user_id = 5");
while ($r = $res->fetchArray(SQLITE3_ASSOC)) {
    echo "Doc ID: " . $r['id'] . " | Title: " . $r['title'] . " | Status: " . $r['status'] . "\n";
    $tabs = json_decode($r['tabs'], true);
    if (!empty($tabs)) {
        foreach ($tabs as $i => $t) {
            echo "  Tab $i: " . ($t['title'] ?? '') . "\n";
            $cats = $t['formData']['categories'] ?? null;
            if ($cats) {
                echo "    Core count: " . count($cats['core'] ?? []) . "\n";
                echo "    Strat count: " . count($cats['strategic'] ?? []) . "\n";
                echo "    Supp count: " . count($cats['support'] ?? []) . "\n";
                foreach (($cats['core'] ?? []) as $ci => $crow) {
                    echo "      Core[$ci]: MFO=" . substr($crow['mfo'] ?? '', 0, 30) . " | Ind=" . substr($crow['indicators'] ?? '', 0, 30) . "\n";
                }
            } else {
                echo "    No formData.categories\n";
            }
            echo "    Content length: " . strlen($t['content'] ?? '') . "\n";
        }
    }
}
