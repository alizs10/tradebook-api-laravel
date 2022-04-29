<?php

namespace App\Services;

use App\Models\Notification;

class NotificationsService
{
    public function send($message, $section, $user_id, $type = "warning", $status_code = 0, $seen = 0)
    {
        
        $notification = [
            'message' => $message,
            'section' => $section,
            'user_id' => $user_id,
            'type' => $type,
            'seen' => $seen,
            'status_code' => $status_code,
            'notified_at' => now()
        ];

        $result = Notification::create($notification);

        return $result ? $result : false;
    }
}
