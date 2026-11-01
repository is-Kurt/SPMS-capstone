#!/usr/bin/env bash
set -euo pipefail

# Resolve project root (two levels up from scripts/linux/)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$(cd "$SCRIPT_DIR/../.." && pwd)"
DEST_DIR="${1:-$APP_DIR/../backups}"

DB_PATH="$APP_DIR/writable/database/spms_db.sqlite3"
PHP_BIN="${PHP_BIN:-php}"
STAMP="$(date +'%Y%m%d-%H%M')"
WORK_DIR="$DEST_DIR/spms-$STAMP"

mkdir -p "$DEST_DIR" "$WORK_DIR"
echo -e "\033[36m>>> Starting SPMS Backup ($STAMP)...\033[0m"

# 1. Safe SQLite live snapshot
if [[ -f "$DB_PATH" ]]; then
    echo ">>> [1/4] Creating SQLite database snapshot..."
    "$PHP_BIN" -r '$s=new SQLite3($argv[1]);$d=new SQLite3($argv[2]);$s->backup($d);echo $d->querySingle("PRAGMA integrity_check"),PHP_EOL;' -- "$DB_PATH" "$WORK_DIR/spms_db.sqlite3"
else
    echo "Warning: Database file not found at $DB_PATH"
fi

# 2. Archive uploads
echo ">>> [2/4] Archiving uploads..."
mkdir -p "$APP_DIR/writable/uploads" "$APP_DIR/public/uploads"
tar -czf "$WORK_DIR/writable-uploads.tar.gz" -C "$APP_DIR/writable" uploads
tar -czf "$WORK_DIR/public-uploads.tar.gz"   -C "$APP_DIR/public"   uploads

# 3. Copy .env
echo ">>> [3/4] Copying environment config..."
if [[ -f "$APP_DIR/.env" ]]; then
    cp "$APP_DIR/.env" "$WORK_DIR/env.backup"
fi

# 4. Master archive bundle
echo ">>> [4/4] Creating master archive..."
FINAL_ARCHIVE="$DEST_DIR/spms-$STAMP.tar.gz"
tar -czf "$FINAL_ARCHIVE" -C "$WORK_DIR" .
rm -rf "$WORK_DIR"

echo -e "\n\033[32m[OK] Backup completed successfully!\033[0m"
ls -lh "$FINAL_ARCHIVE"

