<?php

namespace App\Controllers;

use App\Models\DocumentCommentModel;
use App\Models\DocumentModel;
use App\Models\DocumentFolderModel;
use App\Models\UserModel;
use App\Models\NotificationModel;

class Comment extends BaseController
{
    protected DocumentCommentModel $commentModel;
    protected DocumentModel $docModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->commentModel = new DocumentCommentModel();
        $this->docModel     = new DocumentModel();
    }

    /**
     * GET /document/comments?doc_id=...
     * Fetch all threaded comments for the document.
     */
    public function getThreads()
    {
        return $this->tryOrFail(function() {
            $docId = $this->request->getGet('doc_id');
            if (!$docId) {
                return $this->respondError('Document ID is required.', 400);
            }

            $versionParam = $this->request->getGet('version');
            $version = ($versionParam !== null && $versionParam !== '') ? max(1, (int)$versionParam) : null;

            $threads = $this->commentModel->getDocumentThreads($docId, $version);
            return $this->respond([
                'status'  => 'success',
                'threads' => $threads
            ]);
        });
    }

    /**
     * POST /document/comment/create
     * Create a new root comment on a cell or row.
     */
    public function create()
    {
        return $this->tryOrFail(function() {
            $userId  = session()->get('user_id');
            if (!$userId) {
                return $this->respondError('Unauthorized. Please log in.', 401);
            }

            $docId       = $this->request->getPost('doc_id');
            $rowId       = $this->request->getPost('row_id');
            $fieldName   = $this->request->getPost('field_name') ?: 'row';
            $commentText = trim($this->request->getPost('comment_text') ?? '');

            if (!$docId || !$rowId || empty($commentText)) {
                return $this->respondError('Document ID, Row ID, and comment text are required.', 400);
            }

            // Check document existence & permissions
            $doc = $this->docModel->getDocumentWithFolderInfo($docId);
            if (!$doc) {
                return $this->respondError('Document not found.', 404);
            }

            // Check if cycle is archived
            if (!empty($doc['folder_deleted_at'])) {
                return $this->respondError('Cannot comment on an archived cycle.', 403);
            }

            $userModel = new UserModel();
            $user = $userModel->find($userId);
            $sysRole = session()->get('role') ?? 'Employee';
            $authorRole = $this->resolveAuthorRole($user ?: [], $sysRole, $doc);

            $authorName = session()->get('username') 
                ?: (trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: 'User');
            $avatarColor = session()->get('avatar_color') ?: '#059669';

            $currentVersion = max(1, (int)($doc['current_version'] ?? 1));

            // Check if a thread already exists on this cell in the current version
            $existingRoot = $this->commentModel
                ->where('document_id', $docId)
                ->where('version', $currentVersion)
                ->where('row_id', $rowId)
                ->where('field_name', $fieldName)
                ->where('status !=', 'deleted')
                ->orderBy('id', 'ASC')
                ->first();

            $parentId = $existingRoot ? (int)$existingRoot['id'] : null;

            $commentId = $this->commentModel->insert([
                'document_id'         => $docId,
                'version'             => $currentVersion,
                'row_id'              => $rowId,
                'field_name'          => $fieldName,
                'parent_id'           => $parentId,
                'user_id'             => $userId,
                'author_name'         => $authorName,
                'author_role'         => $authorRole,
                'author_avatar_color' => $avatarColor,
                'comment_text'        => $commentText,
                'status'              => 'open',
            ]);

            // Notify document owner if comment was made by someone else
            if ((string)$doc['owner_id'] !== (string)$userId) {
                (new NotificationModel())->insert([
                    'user_id'    => (int)$doc['owner_id'],
                    'sender_id'  => $userId,
                    'type'       => 'comment',
                    'title'      => 'New Comment on Deliverable',
                    'message'    => "{$authorName} ({$authorRole}) left a comment on your document.",
                    'link'       => "folders/{$doc['document_folder_id']}?doc_id={$docId}",
                    'icon'       => 'chat',
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            // Retrieve updated unified thread for this cell
            $threads = $this->commentModel->getDocumentThreads($docId, $currentVersion);
            $unifiedThread = null;
            foreach ($threads as $t) {
                if ($t['row_id'] === $rowId && ($t['field_name'] ?? 'row') === $fieldName) {
                    $unifiedThread = $t;
                    break;
                }
            }

            return $this->respond([
                'status'  => 'success',
                'comment' => $unifiedThread
            ]);
        });
    }

    /**
     * POST /document/comment/reply
     * Add a reply to an existing comment thread.
     */
    public function reply()
    {
        return $this->tryOrFail(function() {
            $userId = session()->get('user_id');
            if (!$userId) {
                return $this->respondError('Unauthorized. Please log in.', 401);
            }

            $parentId    = (int)$this->request->getPost('parent_id');
            $docId       = $this->request->getPost('doc_id');
            $replyText   = trim($this->request->getPost('reply_text') ?? '');

            if (!$parentId || !$docId || empty($replyText)) {
                return $this->respondError('Parent ID, Document ID, and reply text are required.', 400);
            }

            $parent = $this->commentModel->find($parentId);
            if (!$parent || $parent['document_id'] !== $docId) {
                return $this->respondError('Parent comment thread not found.', 404);
            }

            $doc = $this->docModel->getDocumentWithFolderInfo($docId);
            if (!empty($doc['folder_deleted_at'])) {
                return $this->respondError('Cannot reply on an archived cycle.', 403);
            }

            $userModel = new UserModel();
            $user = $userModel->find($userId);
            $sysRole = session()->get('role') ?? 'Employee';
            $authorRole = $this->resolveAuthorRole($user ?: [], $sysRole, $doc ?: []);

            $authorName = session()->get('username') 
                ?: (trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: 'User');
            $avatarColor = session()->get('avatar_color') ?: '#059669';

            $replyId = $this->commentModel->insert([
                'document_id'         => $docId,
                'version'             => max(1, (int)($parent['version'] ?? ($doc['current_version'] ?? 1))),
                'row_id'              => $parent['row_id'],
                'field_name'          => $parent['field_name'],
                'parent_id'           => $parentId,
                'user_id'             => $userId,
                'author_name'         => $authorName,
                'author_role'         => $authorRole,
                'author_avatar_color' => $avatarColor,
                'comment_text'        => $replyText,
                'status'              => 'open',
            ]);

            $savedReply = $this->commentModel->find($replyId);
            $savedReply['id'] = (int)$savedReply['id'];
            $savedReply['parent_id'] = (int)$savedReply['parent_id'];
            $savedReply['user_id'] = (int)$savedReply['user_id'];

            // Notify parent comment author if reply is from someone else
            if ((int)$parent['user_id'] !== (int)$userId) {
                (new NotificationModel())->insert([
                    'user_id'    => (int)$parent['user_id'],
                    'sender_id'  => $userId,
                    'type'       => 'comment_reply',
                    'title'      => 'Reply to Your Comment',
                    'message'    => "{$authorName} ({$authorRole}) replied to your comment thread.",
                    'link'       => "folders/" . ($doc['document_folder_id'] ?? '') . "?doc_id={$docId}",
                    'icon'       => 'chat',
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            return $this->respond([
                'status' => 'success',
                'reply'  => $savedReply
            ]);
        });
    }

    /**
     * POST /document/comment/resolve
     * Toggle thread resolved / open status.
     */
    public function resolve()
    {
        return $this->tryOrFail(function() {
            $userId = session()->get('user_id');
            if (!$userId) {
                return $this->respondError('Unauthorized.', 401);
            }

            $commentId = (int)$this->request->getPost('comment_id');
            $newStatus = $this->request->getPost('status') === 'resolved' ? 'resolved' : 'open';

            $comment = $this->commentModel->find($commentId);
            if (!$comment) {
                return $this->respondError('Comment not found.', 404);
            }

            $this->commentModel->update($commentId, [
                'status'      => $newStatus,
                'resolved_by' => ($newStatus === 'resolved') ? $userId : null,
                'resolved_at' => ($newStatus === 'resolved') ? date('Y-m-d H:i:s') : null,
            ]);

            return $this->respond([
                'status'         => 'success',
                'comment_status' => $newStatus
            ]);
        });
    }

    /**
     * POST /document/comment/delete
     * Delete a comment or reply (only author or Admin).
     */
    public function delete()
    {
        return $this->tryOrFail(function() {
            $userId  = session()->get('user_id');
            $sysRole = session()->get('role');
            if (!$userId) {
                return $this->respondError('Unauthorized.', 401);
            }

            $commentId = (int)$this->request->getPost('comment_id');
            $comment   = $this->commentModel->find($commentId);

            if (!$comment) {
                return $this->respondError('Comment not found.', 404);
            }

            // Only original author or Admin can delete
            if ((int)$comment['user_id'] !== (int)$userId && $sysRole !== 'Admin') {
                return $this->respondError('You do not have permission to delete this comment.', 403);
            }

            // If it's a parent comment, delete its replies too
            if ($comment['parent_id'] === null) {
                $this->commentModel->where('parent_id', $commentId)->delete();
            }

            $this->commentModel->delete($commentId);

            return $this->respond([
                'status' => 'success',
                'message' => 'Comment deleted successfully.'
            ]);
        });
    }

    /**
     * Helper to compute a clean human-readable role badge for display.
     */
    protected function resolveAuthorRole(array $user, string $sysRole, array $doc): string
    {
        $userId = session()->get('user_id');
        $ownerId = $doc['owner_id'] ?? null;

        if ($ownerId && (string)$ownerId === (string)$userId) {
            return 'Ratee / Employee';
        }
        if ($sysRole === 'PMT') {
            return 'PMT Evaluator';
        }
        if ($sysRole === 'TWG') {
            return 'TWG Reviewer';
        }
        if ($sysRole === 'Admin') {
            return 'Administrator';
        }

        $pos = strtolower($user['position_title'] ?? session()->get('position') ?? '');
        if (str_contains($pos, 'dean')) {
            return 'College Dean';
        }
        if (str_contains($pos, 'vice president') || str_contains($pos, 'vp')) {
            return 'Vice President';
        }
        if (str_contains($pos, 'chair') || str_contains($pos, 'head')) {
            return 'Department Head';
        }
        if ($sysRole === 'Supervisor') {
            return 'Immediate Supervisor';
        }

        return 'Reviewer';
    }
}
