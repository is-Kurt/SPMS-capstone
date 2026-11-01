#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$(cd "$SCRIPT_DIR/../.." && pwd)"
DEST_DIR="${2:-$APP_DIR/../backups}"

DB_PATH="$APP_DIR/writable/database/spms_db.sqlite3"
PHP_BIN="${PHP_BIN:-php}"

# If no backup file specified, automatically pick the latest
BACKUP_FILE="${1:-}"
if [[ -z "$BACKUP_FILE" ]]; then
    BACKUP_FILE="$(ls -t "$DEST_DIR"/spms-*.tar.gz 2>/dev/null | head -n 1 || true)"
    if [[ -z "$BACKUP_FILE" ]]; then
        echo "Error: No backup files found in $DEST_DIR" >&2
        exit 1
    fi
elif [[ ! -f "$BACKUP_FILE" && -f "$DEST_DIR/$BACKUP_FILE" ]]; then
    BACKUP_FILE="$DEST_DIR/$BACKUP_FILE"
fi

if [[ ! -f "$BACKUP_FILE" ]]; then
    echo "Error: Backup file not found: $BACKUP_FILE" >&2
    exit 1
fi

echo -e "\033[36m>>> Restoring from: $BACKUP_FILE\033[0m"

R="$(mktemp -d /tmp/spms-restore-XXXXXX)"
cleanup() { rm -rf "$R"; }
trap cleanup EXIT

echo ">>> [1/4] Extracting backup bundle..."
tar -xzf "$BACKUP_FILE" -C "$R"

# Pre-restore safety snapshot
if [[ -f "$DB_PATH" ]]; then
    NOW="$(date +'%Y%m%d-%H%M')"
    SAFETY_SNAPSHOT="$DEST_DIR/before-restore-$NOW.sqlite3"
    echo ">>> [2/4] Saving safety snapshot of current DB to $SAFETY_SNAPSHOT..."
    "$PHP_BIN" -r '$s=new SQLite3($argv[1]);$d=new SQLite3($argv[2]);$s->backup($d);' -- "$DB_PATH" "$SAFETY_SNAPSHOT"
fi

# Restore database
echo ">>> [3/4] Restoring SQLite database..."
mkdir -p "$(dirname "$DB_PATH")"
if [[ -f "$R/spms_db.sqlite3" ]]; then
    "$PHP_BIN" -r '$s=new SQLite3($argv[1]);$d=new SQLite3($argv[2]);$s->backup($d);echo $d->querySingle("PRAGMA integrity_check"),PHP_EOL;' -- "$R/spms_db.sqlite3" "$DB_PATH"
fi

# Restore upload folders
echo ">>> [4/4] Restoring upload folders..."
rm -rf "$APP_DIR/writable/uploads" "$APP_DIR/public/uploads"
if [[ -f "$R/writable-uploads.tar.gz" ]]; then
    tar -xzf "$R/writable-uploads.tar.gz" -C "$APP_DIR/writable"
fi
if [[ -f "$R/public-uploads.tar.gz" ]]; then
    tar -xzf "$R/public-uploads.tar.gz" -C "$APP_DIR/public"
fi

echo -e "\n\033[32m[OK] Recovery completed successfully!\033[0m"

