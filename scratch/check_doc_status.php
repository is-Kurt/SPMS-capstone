<?php
$db = new SQLite3('writable/database/spms_db.sqlite3');
$doc = $db->querySingle("SELECT d.*, df.status as folder_status, df.opcr_target_start, df.opcr_target_end, df.opcr_eval_start, df.opcr_eval_end FROM documents d JOIN document_folders df ON df.id = d.document_folder_id WHERE d.id = 'E59VMotpQZU'", true);
print_r($doc);
