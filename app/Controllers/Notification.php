<?php

namespace App\Controllers;

use App\Models\NotificationModel;

class Notification extends BaseController
{
    /**
     * GET /notifications
     * Fetches unread count and recent notifications list formatted for the UI.
     */
    public function index()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return $this->respond(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $model = new NotificationModel();
        $unreadCount = $model->getUnreadCount($userId);
        $notifications = $model->getUserNotifications($userId, 25);

        $formatted = array_map(function ($n) {
            $senderName = null;
            if (!empty($n['sender_first_name']) || !empty($n['sender_last_name'])) {
                $senderName = trim(($n['sender_first_name'] ?? '') . ' ' . ($n['sender_last_name'] ?? ''));
            }

            return [
                'id'            => (int) $n['id'],
                'type'          => $n['type'],
                'title'         => $n['title'],
                'message'       => $n['message'],
                'link'          => !empty($n['link']) ? site_url($n['link']) : null,
                'raw_link'      => $n['link'] ?? null,
                'icon'          => $n['icon'] ?? 'bell',
                'is_read'       => !is_null($n['read_at']),
                'read_at'       => $n['read_at'],
                'created_at'    => $n['created_at'],
                'time_ago'      => time_ago_str($n['created_at']),
                'sender_name'   => $senderName,
                'sender_avatar' => $n['sender_avatar'] ?? null,
            ];
        }, $notifications);

        return $this->respond([
            'status'        => 'success',
            'unread_count'  => $unreadCount,
            'notifications' => $formatted,
        ]);
    }

    /**
     * POST /notifications/read/(:num)
     * Marks a single notification as read.
     */
    public function markAsRead($id)
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return $this->respond(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $model = new NotificationModel();
        $model->markAsRead((int) $id, $userId);

        $unreadCount = $model->getUnreadCount($userId);

        return $this->respond([
            'status'       => 'success',
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * POST /notifications/read-all
     * Marks all notifications as read for current user.
     */
    public function markAllAsRead()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return $this->respond(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $model = new NotificationModel();
        $model->markAllAsRead($userId);

        return $this->respond([
            'status'       => 'success',
            'unread_count' => 0,
        ]);
    }
}
