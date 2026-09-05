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
     * Returns total unread notifications for a user.
     */
    public function getUnreadCount(int $userId): int
    {
        return $this->where('user_id', $userId)
                    ->where('read_at IS NULL')
                    ->countAllResults();
    }

    /**
     * Retrieves recent notifications for a user with sender details.
     */
    public function getUserNotifications(int $userId, int $limit = 20): array
    {
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
