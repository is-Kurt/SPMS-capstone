#!/usr/bin/env bash
set -euo pipefail

# Resolve project root (two levels up from scripts/linux/)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$(cd "$SCRIPT_DIR/../.." && pwd)"
DEST_DIR="${1:-$APP_DIR/../backups}"
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
STAMP="$(date +'%Y%m%d-%H%M')"
WORK_DIR="$DEST_DIR/spms-$STAMP"

mkdir -p "$DEST_DIR" "$WORK_DIR"
echo -e "\033[36m>>> Starting SPMS Backup ($STAMP)...\033[0m"

# 1. Safe SQLite live snapshot
if [[ -f "$DB_PATH" ]]; then
    echo ">>> [1/4] Creating SQLite database snapshot from $DB_PATH..."
    sqlite_snapshot "$DB_PATH" "$WORK_DIR/spms_db.sqlite3"
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

# 5. Clean up old backups (retention: keep last 30 days)
find "$DEST_DIR" -maxdepth 1 -name "spms-*.tar.gz" -type f -mtime +30 -delete 2>/dev/null || true

echo -e "\n\033[32m[OK] Backup completed successfully!\033[0m"
ls -lh "$FINAL_ARCHIVE"

