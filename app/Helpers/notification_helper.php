<?php

use App\Models\NotificationModel;

if (!function_exists('notify_user')) {
    /**
     * Dispatch an in-app notification to a user.
     *
     * @param int $recipientId User ID receiving the notification
     * @param array $payload [
     *   'sender_id' => int|null,
     *   'type'      => string, // e.g. target_submitted, target_approved, target_returned, eval_submitted, eval_approved, eval_returned, twg_approved
     *   'title'     => string,
     *   'message'   => string,
     *   'link'      => string|null,
     *   'icon'      => string|null, // 'check', 'alert', 'file', 'award', 'clock', 'bell'
     * ]
     * @return int|false Notification ID or false on failure
     */
    function notify_user(int $recipientId, array $payload): int|false
    {
        if ($recipientId <= 0) {
            return false;
        }

        // Avoid notifying self if recipient is the sender
        if (isset($payload['sender_id']) && $payload['sender_id'] == $recipientId) {
            return false;
        }

        $model = new NotificationModel();
        return $model->createNotification($recipientId, $payload);
    }
}

if (!function_exists('time_ago_str')) {
    /**
     * Converts a datetime string to human friendly relative time (e.g. "5m ago", "2h ago", "Yesterday").
     */
    function time_ago_str(?string $datetime): string
    {
        if (empty($datetime)) return 'Just now';
        $time = strtotime($datetime);
        if (!$time) return 'Just now';

        $diff = time() - $time;
        if ($diff < 60) return 'Just now';
        if ($diff < 3600) return floor($diff / 60) . 'm ago';
        if ($diff < 86400) return floor($diff / 3600) . 'h ago';
        if ($diff < 172800) return 'Yesterday';
        if ($diff < 604800) return floor($diff / 86400) . 'd ago';
        return date('M d', $time);
    }
}
