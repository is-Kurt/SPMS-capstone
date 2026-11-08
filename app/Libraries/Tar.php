<?php

namespace App\Libraries;

/**
 * Minimal .tar.gz reader and writer (2026-11-08) for the backups. PHP's PharData cannot read the archives the
 * nightly job makes (GNU tar with a "./" entry), and the server may not run tar itself (exec is disabled), so this
 * reads and writes plain ustar archives that GNU tar reads too. Paths with ".." or a leading "/" are refused.
 */
final class Tar
{
    /** Writes $dest (.tar.gz). $entries: [name in the archive => file or directory (added with everything under it)]. */
    public static function create(string $dest, array $entries): void
    {
        $part = $dest . '.part';
        $gz = gzopen($part, 'wb6');
        if (!$gz) throw new \RuntimeException('Cannot write ' . basename($dest));
        try {
            foreach ($entries as $name => $src) self::add($gz, trim(str_replace('\\', '/', (string) $name), '/'), $src);
            gzwrite($gz, str_repeat("\0", 1024));
        } finally {
            gzclose($gz);
        }
        if (!rename($part, $dest)) throw new \RuntimeException('Cannot write ' . basename($dest));
    }

    /**
     * Extracts $src (.tar.gz) into $dir. $only: the names to take (as in the archive, without "./"); null takes all.
     * Returns the names extracted (files only).
     */
    public static function extract(string $src, string $dir, ?array $only = null): array
    {
        $gz = gzopen($src, 'rb');
        if (!$gz) throw new \RuntimeException('Cannot read ' . basename($src));
        $got = [];
        $long = null;
        try {
            while (true) {
                $h = self::read($gz, 512);
                if (strlen($h) < 512 || trim($h, "\0") === '') break;
                $name = rtrim(substr($h, 0, 100), "\0");
                $prefix = rtrim(substr($h, 345, 155), "\0");
                if (substr($h, 257, 5) === 'ustar' && $prefix !== '' && $h[156] !== 'L') $name = $prefix . '/' . $name;
                if ($long !== null) { $name = $long; $long = null; }
                $size = (int) octdec(trim(substr($h, 124, 12), " \0"));
                $type = $h[156];
                $block = (int) (ceil($size / 512) * 512);
                if ($type === 'L') { $long = rtrim(substr(self::read($gz, $block), 0, $size), "\0"); continue; }   // GNU long name
                $clean = self::clean($name);
                $want = $clean !== '' && ($only === null || in_array($clean, $only, true));
                if ($type === '5') {
                    if ($want && !is_dir("{$dir}/{$clean}")) mkdir("{$dir}/{$clean}", 0777, true);
                    self::skip($gz, $block);
                    continue;
                }
                if (($type !== '0' && $type !== "\0") || !$want) { self::skip($gz, $block); continue; }   // links, pax headers, others
                $path = "{$dir}/{$clean}";
                if (!is_dir(dirname($path))) mkdir(dirname($path), 0777, true);
                $out = fopen($path, 'wb');
                if (!$out) throw new \RuntimeException('Cannot write ' . $clean);
                for ($left = $size; $left > 0; ) {
                    $chunk = self::read($gz, min($left, 1 << 16));
                    if ($chunk === '') throw new \RuntimeException('The archive is cut short');
                    fwrite($out, $chunk);
                    $left -= strlen($chunk);
                }
                fclose($out);
                self::skip($gz, $block - $size);
                $got[] = $clean;
            }
        } finally {
            gzclose($gz);
        }
        return $got;
    }

    private static function add($gz, string $name, string $src): void
    {
        if (is_dir($src)) {
            self::header($gz, $name . '/', 0, '5', 0755, filemtime($src) ?: time());
            $items = array_diff(scandir($src) ?: [], ['.', '..']);
            sort($items);
            foreach ($items as $f) self::add($gz, "{$name}/{$f}", "{$src}/{$f}");
            return;
        }
        if (!is_file($src)) return;
        $size = filesize($src);
        self::header($gz, $name, $size, '0', 0644, filemtime($src) ?: time());
        $in = fopen($src, 'rb');
        while (!feof($in)) gzwrite($gz, fread($in, 1 << 16));
        fclose($in);
        $pad = (512 - $size % 512) % 512;
        if ($pad) gzwrite($gz, str_repeat("\0", $pad));
    }

    private static function header($gz, string $name, int $size, string $type, int $mode, int $mtime): void
    {
        if (strlen($name) > 100) {   // GNU long name entry first
            self::raw($gz, '././@LongLink', strlen($name) + 1, 'L', 0644, 0);
            $data = $name . "\0";
            gzwrite($gz, str_pad($data, (int) (ceil(strlen($data) / 512) * 512), "\0"));
            $name = substr($name, 0, 100);
        }
        self::raw($gz, $name, $size, $type, $mode, $mtime);
    }

    private static function raw($gz, string $name, int $size, string $type, int $mode, int $mtime): void
    {
        $h = pack('a100a8a8a8a12a12a8a1a100a6a2a32a32a8a8a155a12',
            $name, sprintf('%07o', $mode), sprintf('%07o', 0), sprintf('%07o', 0), sprintf('%011o', $size),
            sprintf('%011o', $mtime), '        ', $type, '', 'ustar', '00', '', '', '', '', '', '');
        $sum = 0;
        for ($i = 0; $i < 512; $i++) $sum += ord($h[$i]);
        gzwrite($gz, substr_replace($h, sprintf('%06o', $sum) . "\0 ", 148, 8));
    }

    /** A name inside the archive, without "./" and empty parts; ".." refuses the archive. */
    private static function clean(string $name): string
    {
        $parts = [];
        foreach (explode('/', str_replace('\\', '/', $name)) as $p) {
            if ($p === '' || $p === '.') continue;
            if ($p === '..') throw new \RuntimeException('The archive has an unsafe path');
            $parts[] = $p;
        }
        return implode('/', $parts);
    }

    private static function read($gz, int $n): string
    {
        $buf = '';
        while (strlen($buf) < $n && !gzeof($gz)) {
            $chunk = gzread($gz, $n - strlen($buf));
            if ($chunk === false || $chunk === '') break;
            $buf .= $chunk;
        }
        return $buf;
    }

    private static function skip($gz, int $n): void
    {
        while ($n > 0) {
            $chunk = self::read($gz, min($n, 1 << 16));
            if ($chunk === '') break;
            $n -= strlen($chunk);
        }
    }
}
