<?php

namespace App\Libraries;

/**
 * The password that protects the exported Excel files (2026-11-08). The Admin sets it in Accounts > Excel Password;
 * each one is kept (excel_passwords, encrypted with the app key) with the dates it was in use, so an older file can
 * still be unlocked: a file keeps the password it was downloaded with. Each file says since when its password was in
 * use (Comments, CscExcelExporter::applyProtection); its Created date tells too. The first entry is the built-in "spms".
 */
final class ExcelPassword
{
    public const BUILT_IN = 'spms';
    public const MIN = 6;
    public const MAX = 64;

    public static function ready(): bool
    {
        return \Config\Database::connect()->tableExists('excel_passwords');
    }

    /** The password in use now: ['no' => int, 'password' => string, 'since' => ?string]. */
    public static function current(): array
    {
        foreach (self::history() as $h) if ($h['password'] !== null) return $h;   // never protect with an unknown password
        return ['no' => 1, 'password' => self::BUILT_IN, 'since' => null, 'until' => null, 'set_by' => 'Built in'];
    }

    /**
     * Every password, newest first: no (1 = built-in), password, since and until (null = from the start / still in
     * use), set_by (name). A row that cannot be decrypted (the app key changed) shows password null.
     */
    public static function history(): array
    {
        $rows = [];
        if (self::ready()) {
            $rows = \Config\Database::connect()->table('excel_passwords p')
                ->select("p.id, p.secret, p.created_at, TRIM(COALESCE(u.first_name, '') || ' ' || COALESCE(u.last_name, '')) AS set_by_name", false)
                ->join('users u', 'u.id = p.set_by', 'left')->orderBy('p.id', 'ASC')->get()->getResultArray();
        }
        $out = [['no' => 1, 'password' => self::BUILT_IN, 'since' => null, 'until' => $rows[0]['created_at'] ?? null, 'set_by' => 'Built in']];
        foreach ($rows as $i => $r) {
            $out[] = ['no' => $i + 2, 'password' => self::decrypt((string) $r['secret']), 'since' => $r['created_at'],
                      'until' => $rows[$i + 1]['created_at'] ?? null, 'set_by' => $r['set_by_name'] ?: '—'];
        }
        return array_reverse($out);
    }

    /** Sets a new password. Returns null when saved, else the reason it was refused. */
    public static function set(string $password, int $byUserId): ?string
    {
        if (!self::ready()) return 'Run the database update first (php spark migrate).';
        if ($password !== trim($password)) return 'The password cannot start or end with a space.';
        $len = mb_strlen($password);
        if ($len < self::MIN || $len > self::MAX) return 'Use ' . self::MIN . ' to ' . self::MAX . ' characters.';
        if ($password === self::current()['password']) return 'That is already the password in use.';
        \Config\Database::connect()->table('excel_passwords')->insert([
            'secret' => base64_encode(\Config\Services::encrypter()->encrypt($password)),
            'set_by' => $byUserId, 'created_at' => date('Y-m-d H:i:s'),
        ]);
        return null;
    }

    private static function decrypt(string $secret): ?string
    {
        try {
            return \Config\Services::encrypter()->decrypt(base64_decode($secret, true) ?: '');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
