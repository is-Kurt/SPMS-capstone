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
     * @param int $documentId
     * @return array [row_id => [attachments]]
     */
    public function getAttachmentsGroupedByRow(int $documentId): array
    {
        $rows = $this->select('document_attachments.*, u.first_name as uploader_first_name, u.last_name as uploader_last_name')
                     ->join('users u', 'u.id = document_attachments.uploaded_by', 'left')
                     ->where('document_attachments.document_id', $documentId)
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
            $row['formatted_size'] = $this->formatBytes($row['file_size']);
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
