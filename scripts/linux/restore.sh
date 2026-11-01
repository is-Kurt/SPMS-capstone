#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$(cd "$SCRIPT_DIR/../.." && pwd)"
DEST_DIR="${2:-$APP_DIR/../backups}"
PHP_BIN="${PHP_BIN:-php}"

# Helper to find active database file
find_db_path() {
    local dir="$1"
    if [[ -f "$dir/.env" ]]; then
        local env_db
        env_db="$(grep -E '^[[:space:]]*database\.default\.database[[:space:]]*=' "$dir/.env" | head -n 1 | cut -d '=' -f2- | tr -d ' "\r' || true)"
        if [[ -n "$env_db" ]]; then
            if [[ "$env_db" == *"/writable/"* ]]; then
                local rel="${env_db#*/writable/}"
                echo "$dir/writable/$rel"
                return 0
            elif [[ -f "$env_db" ]]; then
                echo "$env_db"
                return 0
            elif [[ -f "$dir/$env_db" ]]; then
                echo "$dir/$env_db"
                return 0
            fi
        fi
    fi

    local candidates=(
        "$dir/writable/spms_db"
        "$dir/writable/database/spms_db.sqlite3"
        "$dir/writable/spms_db.sqlite3"
        "$dir/writable/database.sqlite"
    )
    for cand in "${candidates[@]}"; do
        if [[ -f "$cand" && -s "$cand" ]]; then
            echo "$cand"
            return 0
        fi
    done

    echo "$dir/writable/database/spms_db.sqlite3"
}

# Safe SQLite snapshot with multiple tool fallbacks (PHP -> sqlite3 CLI -> Docker php_app)
sqlite_snapshot() {
    local src="$1"
    local dst="$2"

    if command -v "$PHP_BIN" &>/dev/null; then
        "$PHP_BIN" -r '$s=new SQLite3($argv[1]);$d=new SQLite3($argv[2]);$s->backup($d);echo $d->querySingle("PRAGMA integrity_check"),PHP_EOL;' -- "$src" "$dst"
    elif command -v sqlite3 &>/dev/null; then
        sqlite3 "$src" ".backup '$dst'"
        sqlite3 "$dst" "PRAGMA integrity_check;"
    elif command -v docker &>/dev/null && docker ps --format '{{.Names}}' | grep -q '^php_app$'; then
        docker exec -i php_app php -r '$s=new SQLite3($argv[1]);$d=new SQLite3($argv[2]);$s->backup($d);echo $d->querySingle("PRAGMA integrity_check"),PHP_EOL;' -- "$src" "$dst"
    else
        echo "Error: Neither php, sqlite3, nor docker php_app found to perform safe SQLite snapshot." >&2
        return 1
    fi
}

DB_PATH="$(find_db_path "$APP_DIR")"

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

# Pre-restore safety snapshot (only if DB file exists and is not empty)
if [[ -f "$DB_PATH" && -s "$DB_PATH" ]]; then
    NOW="$(date +'%Y%m%d-%H%M')"
    SAFETY_SNAPSHOT="$DEST_DIR/before-restore-$NOW.sqlite3"
    echo ">>> [2/4] Saving safety snapshot of current DB to $SAFETY_SNAPSHOT..."
    sqlite_snapshot "$DB_PATH" "$SAFETY_SNAPSHOT"
else
    echo ">>> [2/4] Skipping safety snapshot (no existing active database found at $DB_PATH)..."
fi

# Restore database
echo ">>> [3/4] Restoring SQLite database to $DB_PATH..."
mkdir -p "$(dirname "$DB_PATH")"
if [[ -f "$R/spms_db.sqlite3" ]]; then
    rm -f "$DB_PATH" "${DB_PATH}-wal" "${DB_PATH}-shm"
    cp "$R/spms_db.sqlite3" "$DB_PATH"
    chmod 666 "$DB_PATH" 2>/dev/null || true
    if command -v sqlite3 &>/dev/null; then
        sqlite3 "$DB_PATH" "PRAGMA integrity_check;"
    elif command -v "$PHP_BIN" &>/dev/null; then
        "$PHP_BIN" -r '$d=new SQLite3($argv[1]);echo $d->querySingle("PRAGMA integrity_check"),PHP_EOL;' -- "$DB_PATH"
    fi
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

