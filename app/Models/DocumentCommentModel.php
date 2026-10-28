<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentCommentModel extends Model
{
    protected $table            = 'document_comments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'document_id',
        'version',
        'row_id',
        'field_name',
        'parent_id',
        'user_id',
        'author_name',
        'author_role',
        'author_avatar_color',
        'comment_text',
        'status',
        'resolved_by',
        'resolved_at'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Fetch all comments for a document (scoped by version), structured as threads with nested replies per cell.
     */
    public function getDocumentThreads(string $docId, ?int $version = null): array
    {
        if ($version === null) {
            $folder = $this->db->table('document_folders')->select('current_version')->where('id', $docId)->get()->getRowArray();
            $version = max(1, (int)($folder['current_version'] ?? 1));
        }

        $allComments = $this->where('document_id', $docId)
            ->where('version', $version)
            ->where('status !=', 'deleted')
            ->orderBy('created_at', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        if (empty($allComments)) {
            return [];
        }

        // Group comments by their target cell: (row_id, field_name)
        // The first comment on that cell becomes the root thread;
        // all subsequent comments on that cell (explicit replies or parallel comments)
        // are aggregated into that thread's replies in chronological order.
        $threadsByCell = [];

        foreach ($allComments as $comment) {
            $comment['id']        = (int)$comment['id'];
            $comment['parent_id'] = $comment['parent_id'] !== null ? (int)$comment['parent_id'] : null;
            $comment['user_id']   = (int)$comment['user_id'];
            $comment['replies']   = [];

            $fieldName = !empty($comment['field_name']) ? $comment['field_name'] : 'row';
            $cellKey   = $comment['row_id'] . '::' . $fieldName;

            if (!isset($threadsByCell[$cellKey])) {
                $threadsByCell[$cellKey] = $comment;
            } else {
                $threadsByCell[$cellKey]['replies'][] = $comment;
            }
        }

        return array_values($threadsByCell);
    }
}
