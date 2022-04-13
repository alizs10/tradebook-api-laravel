<?php

namespace App\Services;

use App\Models\User;

class NotificationsService
{
    public function send($message, $section, $user_id, $type = "warning", $status_code = 0)
    {
        $notification = [
            'message' => $message,
            'section' => $section,
            'user_id' => $user_id,
            'type' => $type,
            'seen' => 0,
            'status_code' => $status_code,
            'notified_at' => now()
        ];

        $result = User::create($notification);

        return $result ? true : false;
    }
}