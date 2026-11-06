<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Encrypts evidence files (MOVs) that were saved unencrypted. Until 2026-11-06 no encryption key was
 * set, so uploads silently fell back to storing the raw file, against NFR-03 (Data-at-Rest Security).
 * Safe to run more than once: a file that already decrypts with the current key is left as it is.
 * Files in uploads/movs/ that no attachment row points to (left over from deleted attachments) are
 * encrypted too, and listed separately; nothing is deleted.
 *
 *   php spark spms:encrypt-evidence --dry-run    # only report what would change
 *   php spark spms:encrypt-evidence
 */
class EncryptEvidence extends BaseCommand
{
    protected $group       = 'SPMS';
    protected $name        = 'spms:encrypt-evidence';
    protected $description = 'Encrypts evidence files that are still stored unencrypted (needs encryption.key in .env).';
    protected $options     = ['--dry-run' => 'Only report which files would be encrypted.'];

    public function run(array $params)
    {
        $dryRun    = CLI::getOption('dry-run') !== null || array_key_exists('dry-run', $params);
        $encrypter = \Config\Services::encrypter();     // throws if encryption.key is missing: nothing is changed then
        $rows = \Config\Database::connect()->table('document_attachments')
            ->select('id, file_path, file_type, link_url')->get()->getResultArray();

        $done = $already = $missing = $unreferenced = 0;
        $referenced = [];
        foreach ($rows as $row) {
            if (!empty($row['file_path'])) {
                $referenced[realpath(WRITEPATH . $row['file_path']) ?: WRITEPATH . $row['file_path']] = true;
            }
        }
        $files = [];
        foreach ($rows as $row) {
            if (!empty($row['link_url']) || str_starts_with((string) $row['file_type'], 'link/') || empty($row['file_path'])) {
                continue;                                // web links have no file
            }
            $path = WRITEPATH . $row['file_path'];
            if (!is_file($path)) {
                $missing++;
                continue;
            }
            $files[] = [$path, "attachment {$row['id']}"];
        }
        foreach (glob(WRITEPATH . 'uploads/movs/*') ?: [] as $path) {
            if (is_file($path) && !str_ends_with($path, '.enc-tmp') && !isset($referenced[realpath($path)])) {
                $files[] = [$path, 'unreferenced file'];
                $unreferenced++;
            }
        }

        foreach ($files as [$path, $label]) {
            $raw = file_get_contents($path);
            try {
                $encrypter->decrypt($raw);
                $already++;                              // already encrypted with this key
                continue;
            } catch (\Throwable $e) {
                // not decryptable with this key: a plain file
            }
            if (!$dryRun) {
                $tmp = $path . '.enc-tmp';
                file_put_contents($tmp, $encrypter->encrypt($raw), LOCK_EX);
                if ($encrypter->decrypt(file_get_contents($tmp)) !== $raw) {
                    @unlink($tmp);
                    CLI::error("{$label}: check after encrypting failed, file left unchanged.");
                    continue;
                }
                rename($tmp, $path);
            }
            $done++;
            CLI::write(($dryRun ? 'would encrypt ' : 'encrypted ') . $label . ' (' . basename($path) . ')');
        }
        CLI::write(sprintf('%s: %d, already encrypted: %d, file missing: %d; unreferenced files checked: %d',
            $dryRun ? 'Would encrypt' : 'Encrypted', $done, $already, $missing, $unreferenced), 'green');
    }
}
