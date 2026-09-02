<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$row = $db->querySingle("SELECT id, title, tabs FROM templates WHERE id = 3", true);
echo "Template: " . $row['title'] . "\n";
$tabs = json_decode($row['tabs'], true);
echo "Tabs count: " . count($tabs) . "\n";
echo "Tab 0 title: " . $tabs[0]['title'] . "\n";
echo "Tab 0 formData: \n" . json_encode($tabs[0]['formData'], JSON_PRETTY_PRINT) . "\n";
$content = $tabs[0]['content'];
echo "Content length: " . strlen($content) . "\n";
if (preg_match('/<h1[^>]*>(.*?)<\/h1>/s', $content, $m)) {
    echo "H1: " . trim($m[1]) . "\n";
}
if (preg_match_all('/<tbody id="tbody-[^"]*">.*?<\/tbody>/s', $content, $matches)) {
    foreach ($matches[0] as $m) {
        echo "Tbody snippet: " . substr(strip_tags($m), 0, 150) . "\n";
    }
}
