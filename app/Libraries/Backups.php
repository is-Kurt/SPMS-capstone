<?php

namespace App\Libraries;

/**
 * Backups and restore for the Admin (2026-11-08). A backup is the database (an SQLite online-backup copy, safe while
 * people work) plus the uploaded files, in one .tar.gz laid out like the nightly job's (scripts/linux/backup.sh).
 *
 *  - Made here ("Back up now", and one automatically before every restore): writable/backups/, not web-reachable.
 *  - Nightly (every midnight, kept 30 days by the job): the server's backups folder, read only.
 *
 * Restore: checks the backup, backs up the current state first, takes the site down for the few seconds it takes
 * (every other request gets "The site is down"), copies the backup into the live database with SQLite's backup API
 * (never swapping the file under open connections), puts the uploads back, brings the schema up to today's code
 * (migrations + the org setup) and opens the site again. Restores are logged in writable/backups/restore-log.jsonl
 * (the audit trail is rolled back with the database) and in the audit trail afterwards.
 */
final class Backups
{
    private const KEEP = ['manual' => 20, 'before-restore' => 10];
    private const FLAG = 'maintenance.flag';
    public const KINDS = ['nightly' => 'Every midnight', 'manual' => 'Back up now', 'before-restore' => 'Before a restore'];

    public static function dir(): string
    {
        return WRITEPATH . 'backups' . DIRECTORY_SEPARATOR;
    }

    /** The nightly job's folder (next to the app), or null when this server has none. */
    public static function nightlyDir(): ?string
    {
        $d = realpath(ROOTPATH . '..' . DIRECTORY_SEPARATOR . 'backups');
        return $d && is_dir($d) ? $d . DIRECTORY_SEPARATOR : null;
    }

    public static function dbPath(): string
    {
        return config('Database')->default['database'];
    }

    /** Every backup, newest first: id, kind, label, at (Y-m-d H:i:s), size, note, by, path. */
    public static function all(): array
    {
        $out = [];
        foreach (glob(self::dir() . 'spms-*.tar.gz') ?: [] as $f) {
            if (!preg_match('/^spms-(manual|before-restore)-(\d{8})-(\d{6})\.tar\.gz$/', basename($f), $m)) continue;
            $meta = is_file($f . '.json') ? (json_decode((string) file_get_contents($f . '.json'), true) ?: []) : [];
            $out[] = ['id' => basename($f), 'kind' => $m[1], 'at' => self::stamp($m[2], $m[3]), 'size' => filesize($f),
                      'note' => $meta['note'] ?? '', 'by' => $meta['by'] ?? '', 'path' => $f];
        }
        if ($nd = self::nightlyDir()) {
            foreach (glob($nd . 'spms-*.tar.gz') ?: [] as $f) {
                if (!preg_match('/^spms-(\d{8})-(\d{4})\.tar\.gz$/', basename($f), $m)) continue;
                $out[] = ['id' => 'nightly/' . basename($f), 'kind' => 'nightly', 'at' => self::stamp($m[1], $m[2] . '00'),
                          'size' => filesize($f), 'note' => '', 'by' => '', 'path' => $f];
            }
        }
        usort($out, fn($a, $b) => strcmp($b['at'], $a['at']) ?: strcmp($b['id'], $a['id']));
        foreach ($out as &$b) $b['label'] = self::KINDS[$b['kind']] . ', ' . date('M j, Y g:i A', strtotime($b['at']));
        return $out;
    }

    public static function find(string $id): ?array
    {
        foreach (self::all() as $b) if ($b['id'] === $id) return $b;
        return null;
    }

    /** The restores done so far, newest first (from the log kept outside the database). */
    public static function restoreLog(int $limit = 10): array
    {
        $f = self::dir() . 'restore-log.jsonl';
        if (!is_file($f)) return [];
        $rows = array_filter(array_map(fn($l) => json_decode($l, true), file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: []));
        return array_slice(array_reverse(array_values($rows)), 0, $limit);
    }

    /** Makes a backup now. $kind: manual or before-restore. Returns the new entry. */
    public static function create(string $kind, string $note, string $by): array
    {
        return self::locked(fn() => self::make($kind, $note, $by));
    }

    /** Deletes a backup made here (the nightly ones are the nightly job's). */
    public static function delete(string $id): void
    {
        $b = self::find($id);
        if (!$b) throw new \RuntimeException('That backup was not found.');
        if ($b['kind'] === 'nightly') throw new \RuntimeException('Nightly backups are removed by the nightly job after 30 days.');
        @unlink($b['path']);
        @unlink($b['path'] . '.json');
    }

    /**
     * Restores a backup. Returns ['restored' => entry, 'before' => the backup of the state before].
     * Throws with a message for the Admin when the backup cannot be used; the site is never left down.
     */
    public static function restore(string $id, string $by): array
    {
        @set_time_limit(300);
        ignore_user_abort(true);   // a closed browser must not stop it halfway
        return self::locked(function () use ($id, $by) {
            $b = self::find($id);
            if (!$b) throw new \RuntimeException('That backup was not found.');
            $tmp = self::tempDir();
            try {
                // 1. Read and check the backup before touching anything
                Tar::extract($b['path'], $tmp, ['spms_db.sqlite3', 'writable-uploads.tar.gz', 'public-uploads.tar.gz']);
                $snap = $tmp . 'spms_db.sqlite3';
                if (!is_file($snap)) throw new \RuntimeException('This backup has no database in it.');
                self::checkDatabase($snap);
                $uploads = [];
                foreach (['writable-uploads.tar.gz' => WRITEPATH . 'uploads', 'public-uploads.tar.gz' => FCPATH . 'uploads'] as $arc => $target) {
                    if (!is_file($tmp . $arc)) continue;
                    $into = $tmp . basename($arc, '.tar.gz');
                    Tar::extract($tmp . $arc, $into);
                    if (is_dir($into . DIRECTORY_SEPARATOR . 'uploads')) $uploads[$target] = $into . DIRECTORY_SEPARATOR . 'uploads';
                }
                foreach (array_keys($uploads) as $target) {
                    if (!self::treeWritable($target)) throw new \RuntimeException('The server cannot write ' . basename(dirname($target)) . '/uploads, so the files could not be put back. Nothing was changed.');
                }

                // 2. The current state, so this restore can be undone
                $before = self::make('before-restore', 'Before restoring: ' . $b['label'], $by);

                // 3. Site down, copy in, site up
                self::maintenanceOn();
                $copying = false;
                try {
                    sleep(2);   // requests already running finish
                    \Config\Database::connect()->close();
                    $copying = true;
                    $src = new \SQLite3($snap, SQLITE3_OPEN_READONLY);
                    $dst = new \SQLite3(self::dbPath());
                    $dst->busyTimeout(15000);
                    if (!$src->backup($dst)) throw new \RuntimeException('The database could not be copied in: ' . $dst->lastErrorMsg());
                    $src->close();
                    $dst->exec('PRAGMA wal_checkpoint(TRUNCATE)');
                    $dst->close();
                    foreach ($uploads as $target => $from) self::replaceContents($target, $from);
                    // An older backup lacks newer tables: bring it up to today's code
                    $runner = \Config\Services::migrations(null, null, false);
                    $runner->setNamespace(null);
                    $runner->latest();
                    if (class_exists(\App\Commands\OrgBootstrap::class)) command('spms:org-bootstrap');
                    OrgStructure::reset();
                } catch (\Throwable $e) {
                    if (!$copying) throw $e;
                    log_message('error', 'Restore stopped partway: ' . $e->getMessage());
                    throw new \RuntimeException('The restore stopped partway (' . $e->getMessage() . '). To go back, restore the newest "Before a restore" backup: ' . $before['label'] . '.');
                } finally {
                    self::maintenanceOff();
                }

                $line = ['at' => date('Y-m-d H:i:s'), 'by' => $by, 'restored' => $b['label'], 'id' => $b['id'], 'before' => $before['id']];
                file_put_contents(self::dir() . 'restore-log.jsonl', json_encode($line) . "\n", FILE_APPEND | LOCK_EX);
                return ['restored' => $b, 'before' => $before];
            } finally {
                self::removeTree($tmp);
            }
        });
    }

    // ---------------------------------------------------------------- site down while restoring

    public static function maintenanceOn(int $seconds = 300): void
    {
        file_put_contents(WRITEPATH . self::FLAG, json_encode(['until' => time() + $seconds]), LOCK_EX);
    }

    public static function maintenanceOff(): void
    {
        @unlink(WRITEPATH . self::FLAG);
    }

    /** Is the site down for a restore? A flag left behind by a crash expires after 5 minutes. */
    public static function inMaintenance(): bool
    {
        $f = WRITEPATH . self::FLAG;
        if (!is_file($f)) return false;
        $d = json_decode((string) @file_get_contents($f), true);
        if (($d['until'] ?? 0) < time()) { @unlink($f); return false; }
        return true;
    }

    // ---------------------------------------------------------------- internals

    private static function make(string $kind, string $note, string $by): array
    {
        if (!isset(self::KEEP[$kind])) throw new \InvalidArgumentException('Unknown backup kind');
        if (!is_dir(self::dir())) mkdir(self::dir(), 0775, true);
        $name = 'spms-' . $kind . '-' . date('Ymd-His') . '.tar.gz';
        for ($i = 0; is_file(self::dir() . $name) && $i < 5; $i++) { sleep(1); $name = 'spms-' . $kind . '-' . date('Ymd-His') . '.tar.gz'; }
        $tmp = self::tempDir();
        try {
            $src = new \SQLite3(self::dbPath());
            $src->busyTimeout(15000);
            $dst = new \SQLite3($tmp . 'spms_db.sqlite3');
            if (!$src->backup($dst)) throw new \RuntimeException('The database could not be copied: ' . $src->lastErrorMsg());
            $ok = $dst->querySingle('PRAGMA integrity_check');
            $src->close(); $dst->close();
            if ($ok !== 'ok') throw new \RuntimeException('The copy did not pass the integrity check.');
            Tar::create($tmp . 'writable-uploads.tar.gz', ['uploads' => WRITEPATH . 'uploads']);
            Tar::create($tmp . 'public-uploads.tar.gz', ['uploads' => FCPATH . 'uploads']);
            $meta = ['kind' => $kind, 'note' => $note, 'by' => $by, 'at' => date('Y-m-d H:i:s')];
            file_put_contents($tmp . 'meta.json', json_encode($meta, JSON_PRETTY_PRINT));
            Tar::create(self::dir() . $name, [
                'spms_db.sqlite3' => $tmp . 'spms_db.sqlite3', 'writable-uploads.tar.gz' => $tmp . 'writable-uploads.tar.gz',
                'public-uploads.tar.gz' => $tmp . 'public-uploads.tar.gz', 'meta.json' => $tmp . 'meta.json',
            ]);
            file_put_contents(self::dir() . $name . '.json', json_encode($meta));
        } finally {
            self::removeTree($tmp);
        }
        self::prune($kind);
        return self::find($name) ?? ['id' => $name, 'kind' => $kind];
    }

    /** One backup or restore at a time. */
    private static function locked(callable $fn)
    {
        if (!is_dir(self::dir())) mkdir(self::dir(), 0775, true);
        $lock = fopen(self::dir() . '.lock', 'c');
        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) throw new \RuntimeException('Another backup or restore is running. Try again in a moment.');
        try {
            return $fn();
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private static function checkDatabase(string $file): void
    {
        try {
            $db = new \SQLite3($file, SQLITE3_OPEN_READONLY);
            $ok = $db->querySingle('PRAGMA integrity_check');
            $tables = (int) $db->querySingle("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name IN ('users', 'document_folders', 'migrations')");
            $db->close();
        } catch (\Throwable $e) {
            throw new \RuntimeException('This backup\'s database cannot be opened.');
        }
        if ($ok !== 'ok') throw new \RuntimeException('This backup\'s database is damaged (integrity check failed).');
        if ($tables < 3) throw new \RuntimeException('This backup is not an SPMS database.');
    }

    private static function prune(string $kind): void
    {
        $mine = array_values(array_filter(self::all(), fn($b) => $b['kind'] === $kind));
        foreach (array_slice($mine, self::KEEP[$kind]) as $old) {
            @unlink($old['path']);
            @unlink($old['path'] . '.json');
        }
    }

    /**
     * Makes $target hold exactly what $from holds: removes what the backup does not have, adds and replaces the rest.
     * Folders that exist in both stay as they are (owner and permissions); a file is replaced by writing a new one
     * and renaming it over the old, so the folder needs to be writable, not the old file.
     */
    private static function replaceContents(string $target, string $from): void
    {
        $have = array_diff(scandir($target) ?: [], ['.', '..']);
        $want = array_diff(scandir($from) ?: [], ['.', '..']);
        foreach (array_diff($have, $want) as $f) self::removeTree($target . DIRECTORY_SEPARATOR . $f);
        foreach ($want as $f) {
            $s = $from . DIRECTORY_SEPARATOR . $f;
            $d = $target . DIRECTORY_SEPARATOR . $f;
            if (is_dir($s)) {
                if (file_exists($d) && !is_dir($d)) @unlink($d);
                if (!is_dir($d)) mkdir($d, 0775, true);
                self::replaceContents($d, $s);
                continue;
            }
            if (is_dir($d)) self::removeTree($d);
            if (is_file($d) && filesize($d) === filesize($s) && md5_file($d) === md5_file($s)) continue;   // unchanged
            $part = $d . '.restore-part';
            if (!copy($s, $part) || !rename($part, $d)) {
                @unlink($part);
                throw new \RuntimeException('Could not put back the file ' . $f);
            }
        }
    }

    /** Can the server write this folder and every folder under it? */
    private static function treeWritable(string $dir): bool
    {
        if (!is_writable($dir)) return false;
        foreach (array_diff(scandir($dir) ?: [], ['.', '..']) as $f) {
            if (is_dir($dir . DIRECTORY_SEPARATOR . $f) && !self::treeWritable($dir . DIRECTORY_SEPARATOR . $f)) return false;
        }
        return true;
    }

    private static function tempDir(): string
    {
        $d = self::dir() . 'tmp-' . bin2hex(random_bytes(6)) . DIRECTORY_SEPARATOR;
        mkdir($d, 0700, true);
        return $d;
    }

    private static function removeTree(string $path): void
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR);
        if (is_link($path) || is_file($path)) { @unlink($path); return; }
        if (!is_dir($path)) return;
        foreach (array_diff(scandir($path) ?: [], ['.', '..']) as $f) self::removeTree($path . DIRECTORY_SEPARATOR . $f);
        @rmdir($path);
    }

    private static function stamp(string $ymd, string $his): string
    {
        return substr($ymd, 0, 4) . '-' . substr($ymd, 4, 2) . '-' . substr($ymd, 6, 2) . ' '
             . substr($his, 0, 2) . ':' . substr($his, 2, 2) . ':' . substr($his, 4, 2);
    }
}
