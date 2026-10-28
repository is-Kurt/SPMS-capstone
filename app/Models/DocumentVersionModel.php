<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentVersionModel extends Model
{
    protected $table            = 'document_versions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'folder_id',
        'version_number',
        'phase',
        'tabs',
        'submitted_at',
        'returned_by',
        'returned_by_name',
        'returned_by_role',
        'returned_at',
        'return_remarks',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Records a version snapshot upon document submission.
     * - On first submit: creates Version 1 (v1).
     * - If the user unsubmitted on their own (no supervisor/TWG return on the latest version) and resubmits:
     *   updates the current version snapshot in place without incrementing the version number.
     * - If the latest version was returned by a supervisor or TWG: increments current_version (v2, v3...)
     *   so previous inputs and comments remain preserved in the older version.
     */
    public function recordSubmissionSnapshot(string $folderId, string $phase = 'target'): int
    {
        $folder = $this->db->table('document_folders')->where('id', $folderId)->get()->getRowArray();
        if (!$folder) {
            return 1;
        }

        $now = date('Y-m-d H:i:s');
        $latest = $this->where('folder_id', $folderId)
            ->orderBy('version_number', 'DESC')
            ->first();

        if (!$latest) {
            $newVersion = 1;
            $this->insert([
                'folder_id'      => $folderId,
                'version_number' => $newVersion,
                'phase'          => $phase,
                'tabs'           => $folder['tabs'] ?? null,
                'submitted_at'   => $now,
            ]);
        } elseif (
            (!empty($latest['returned_at']) || !empty($latest['returned_by']) || !empty($latest['returned_by_name']))
            && $this->hasContentChangedSinceLatest($folderId, $folder, $latest)
        ) {
            // Previous submission was returned AND content (inputs, rubrics, or comments) changed -> create a new version (v2, v3, etc.)
            $newVersion = (int)$latest['version_number'] + 1;
            $this->insert([
                'folder_id'      => $folderId,
                'version_number' => $newVersion,
                'phase'          => $phase,
                'tabs'           => $folder['tabs'] ?? null,
                'submitted_at'   => $now,
            ]);
        } else {
            // Re-submitting without a return OR re-submitting with zero changes to inputs/rubrics/comments -> keep current version
            $newVersion = max(1, (int)$latest['version_number']);
            $this->update($latest['id'], [
                'phase'            => $phase,
                'tabs'             => $folder['tabs'] ?? null,
                'submitted_at'     => $now,
                'returned_by'      => null,
                'returned_by_name' => null,
                'returned_by_role' => null,
                'returned_at'      => null,
                'return_remarks'   => null,
            ]);
        }

        $this->db->table('document_folders')
            ->where('id', $folderId)
            ->update([
                'current_version' => $newVersion,
                'updated_at'      => $now,
            ]);

        return $newVersion;
    }

    /**
     * Checks whether any document content (inputs, rubrics, metadata, remarks) or comments
     * changed compared to the latest recorded version snapshot.
     */
    private function hasContentChangedSinceLatest(string $folderId, array $folder, array $latest): bool
    {
        $oldTabs = is_string($latest['tabs'] ?? null) ? json_decode($latest['tabs'], true) : ($latest['tabs'] ?? []);
        $newTabs = is_string($folder['tabs'] ?? null) ? json_decode($folder['tabs'], true) : ($folder['tabs'] ?? []);

        $normOld = $this->normalizeTabsForComparison($oldTabs);
        $normNew = $this->normalizeTabsForComparison($newTabs);

        if (json_encode($normOld) !== json_encode($normNew)) {
            return true;
        }

        if ($this->db->tableExists('document_comments')) {
            $verNum = max(1, (int)($latest['version_number'] ?? 1));
            $baselineTime = !empty($latest['returned_at'])
                ? $latest['returned_at']
                : ($latest['submitted_at'] ?? '');

            if (!empty($baselineTime)) {
                $changedComments = $this->db->table('document_comments')
                    ->where('document_id', $folderId)
                    ->where('version', $verNum)
                    ->groupStart()
                        ->where('created_at >', $baselineTime)
                        ->orWhere('updated_at >', $baselineTime)
                    ->groupEnd()
                    ->countAllResults();

                if ($changedComments > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Normalizes a tabs array for deterministic content comparison (inputs, rubrics, rows, remarks),
     * stripping transient audit trails like revisionHistory.
     */
    private function normalizeTabsForComparison($data)
    {
        if (!is_array($data)) {
            return is_string($data) ? trim($data) : $data;
        }

        $normalized = [];
        foreach ($data as $k => $v) {
            if ($k === 'revisionHistory' || $k === 'updated_at') {
                continue;
            }
            $normalized[$k] = $this->normalizeTabsForComparison($v);
        }

        if (array_keys($normalized) !== range(0, count($normalized) - 1)) {
            ksort($normalized);
        }

        return $normalized;
    }

    /**
     * Records return metadata onto the active version snapshot when a supervisor or TWG returns the document.
     */
    public function recordReturnEvent(
        string $folderId,
        int $returnedById,
        string $returnedByName,
        string $returnedByRole,
        ?string $remarks = null,
        string $phase = 'target'
    ): void {
        $now = date('Y-m-d H:i:s');
        $folder = $this->db->table('document_folders')->where('id', $folderId)->get()->getRowArray();
        if (!$folder) {
            return;
        }

        $latest = $this->where('folder_id', $folderId)
            ->orderBy('version_number', 'DESC')
            ->first();

        if (!$latest) {
            $curVer = max(1, (int)($folder['current_version'] ?? 1));
            $this->insert([
                'folder_id'        => $folderId,
                'version_number'   => $curVer,
                'phase'            => $phase,
                'tabs'             => $folder['tabs'] ?? null,
                'submitted_at'     => $folder['target_submitted_at'] ?? ($folder['submitted_at'] ?? $now),
                'returned_by'      => $returnedById,
                'returned_by_name' => $returnedByName,
                'returned_by_role' => $returnedByRole,
                'returned_at'      => $now,
                'return_remarks'   => $remarks,
            ]);
        } else {
            $this->update($latest['id'], [
                'tabs'             => $latest['tabs'] ?: ($folder['tabs'] ?? null),
                'returned_by'      => $returnedById,
                'returned_by_name' => $returnedByName,
                'returned_by_role' => $returnedByRole,
                'returned_at'      => $now,
                'return_remarks'   => $remarks,
            ]);
        }
    }

    /**
     * Retrieves all versions for a given folder ordered newest first.
     */
    public function getFolderVersions(string $folderId): array
    {
        $rows = $this->where('folder_id', $folderId)
            ->orderBy('version_number', 'DESC')
            ->findAll();

        foreach ($rows as &$r) {
            $r['version']       = (int)($r['version_number'] ?? 1);
            $r['return_reason'] = $r['return_remarks'] ?? null;
        }
        unset($r);

        return $rows;
    }

    /**
     * Retrieves a specific version snapshot for a folder.
     */
    public function getVersionSnapshot(string $folderId, int $versionNumber): ?array
    {
        $row = $this->where('folder_id', $folderId)
            ->where('version_number', $versionNumber)
            ->first();

        if ($row) {
            $row['version']       = (int)($row['version_number'] ?? 1);
            $row['return_reason'] = $row['return_remarks'] ?? null;
        }

        return $row;
    }
}

