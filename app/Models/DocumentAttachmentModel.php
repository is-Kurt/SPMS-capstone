<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentAttachmentModel extends Model
{
    protected $table            = 'document_attachments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'document_id',
        'row_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'is_link',
        'link_url',
        'uploaded_by',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Get all active attachments for a document grouped by row_id.
     *
     * @param string|int $documentId
     * @return array [row_id => [attachments]]
     */
    public function getAttachmentsGroupedByRow(string|int $documentId): array
    {
        $rows = $this->select('document_attachments.*, u.first_name as uploader_first_name, u.last_name as uploader_last_name')
                     ->join('users u', 'u.id = document_attachments.uploaded_by', 'left')
                     ->where('document_attachments.document_id', (string) $documentId)
                     ->where('document_attachments.deleted_at IS NULL')
                     ->orderBy('document_attachments.created_at', 'ASC')
                     ->findAll();

        $grouped = [];
        foreach ($rows as $row) {
            $rowId = $row['row_id'];
            if (!isset($grouped[$rowId])) {
                $grouped[$rowId] = [];
            }
            $row['uploader_name'] = trim(($row['uploader_first_name'] ?? '') . ' ' . ($row['uploader_last_name'] ?? ''));
            $isLink = !empty($row['is_link']) || !empty($row['link_url']) || str_starts_with($row['file_type'] ?? '', 'link/');
            $row['is_link'] = $isLink;
            if ($isLink) {
                $row['link_url'] = $row['link_url'] ?: $row['file_path'];
                $url = strtolower($row['link_url']);
                if (str_contains($url, 'drive.google.com') || str_contains($url, 'docs.google.com')) {
                    $row['link_provider'] = 'google_drive';
                    $row['formatted_size'] = 'Google Drive';
                } elseif (str_contains($url, 'onedrive.live.com') || str_contains($url, 'sharepoint.com')) {
                    $row['link_provider'] = 'onedrive';
                    $row['formatted_size'] = 'OneDrive';
                } elseif (str_contains($url, 'dropbox.com')) {
                    $row['link_provider'] = 'dropbox';
                    $row['formatted_size'] = 'Dropbox';
                } else {
                    $row['link_provider'] = 'web_link';
                    $row['formatted_size'] = 'Web Link';
                }
            } else {
                $row['link_provider'] = null;
                $row['formatted_size'] = $this->formatBytes((int)$row['file_size']);
            }
            $grouped[$rowId][] = $row;
        }

        return $grouped;
    }

    /**
     * Format byte sizes into readable strings (e.g. 1.5 MB).
     *
     * @param int $bytes
     * @return string
     */
    public function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 0) . ' KB';
        }
        return $bytes . ' B';
    }
}
