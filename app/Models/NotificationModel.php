<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table            = 'notifications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'sender_id',
        'type',
        'title',
        'message',
        'link',
        'icon',
        'read_at',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Automatically removes orphaned notifications pointing to deleted or non-existent folders.
     */
    public function pruneStaleNotifications(?int $userId = null): int
    {
        $builder = $this->db->table($this->table)->select('id, link');
        if ($userId !== null) {
            $builder->where('user_id', $userId);
        }
        $builder->groupStart()
                ->like('link', 'ratings/show/')
                ->orLike('link', 'folders/')
                ->groupEnd();

        $rows = $builder->get()->getResultArray();
        if (empty($rows)) {
            return 0;
        }

        $toDelete = [];
        foreach ($rows as $row) {
            $link = $row['link'] ?? '';
            $parts = explode('/', trim($link, '/'));
            $folderId = end($parts);
            if (!empty($folderId) && $folderId !== 'folders') {
                $exists = $this->db->table('document_folders')
                             ->where('id', $folderId)
                             ->where('deleted_at IS NULL')
                             ->countAllResults();
                if ($exists === 0) {
                    $toDelete[] = $row['id'];
                }
            }
        }

        if (!empty($toDelete)) {
            $this->db->table($this->table)->whereIn('id', $toDelete)->delete();
        }

        return count($toDelete);
    }

    /**
     * Returns total unread notifications for a user.
     */
    public function getUnreadCount(int $userId): int
    {
        $this->pruneStaleNotifications($userId);

        return $this->where('user_id', $userId)
                    ->where('read_at IS NULL')
                    ->countAllResults();
    }

    /**
     * Retrieves recent notifications for a user with sender details.
     */
    public function getUserNotifications(int $userId, int $limit = 20): array
    {
        $this->pruneStaleNotifications($userId);

        return $this->select('notifications.*, u.first_name as sender_first_name, u.last_name as sender_last_name, u.email as sender_email, u.avatar_image as sender_avatar')
                    ->join('users u', 'u.id = notifications.sender_id', 'left')
                    ->where('notifications.user_id', $userId)
                    ->orderBy('notifications.created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(int $id, int $userId): bool
    {
        return (bool) $this->where('id', $id)
                           ->where('user_id', $userId)
                           ->set(['read_at' => date('Y-m-d H:i:s')])
                           ->update();
    }

    /**
     * Mark all notifications for a user as read.
     */
    public function markAllAsRead(int $userId): bool
    {
        return (bool) $this->where('user_id', $userId)
                           ->where('read_at IS NULL')
                           ->set(['read_at' => date('Y-m-d H:i:s')])
                           ->update();
    }

    /**
     * Delete a single notification for a user.
     */
    public function deleteNotification(int $id, int $userId): bool
    {
        return (bool) $this->where('id', $id)
                           ->where('user_id', $userId)
                           ->delete();
    }

    /**
     * Clear all notifications for a user.
     */
    public function clearAllNotifications(int $userId): bool
    {
        return (bool) $this->where('user_id', $userId)->delete();
    }

    /**
     * Convenient helper to insert a notification.
     */
    public function createNotification(int $userId, array $data): int|false
    {
        $now = date('Y-m-d H:i:s');
        $record = [
            'user_id'    => $userId,
            'sender_id'  => $data['sender_id'] ?? null,
            'type'       => $data['type'] ?? 'system',
            'title'      => $data['title'] ?? 'Notification',
            'message'    => $data['message'] ?? '',
            'link'       => $data['link'] ?? null,
            'icon'       => $data['icon'] ?? 'bell',
            'read_at'    => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        return $this->insert($record);
    }
}
