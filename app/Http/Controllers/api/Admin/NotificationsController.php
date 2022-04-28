<?php

namespace App\Http\Controllers\api\Admin;

use App\Http\Controllers\Controller;
use App\Services\NotificationsService;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'message' => "required|string|max:255|min:3",
            'section' => "required|in:home,accounts,profile",
            'user_id' => "nullable|numeric|exists:users,id",
            'type' => "required|in:success,warning,primary,error,info",
            'status_code' => "required|numeric|in:0,1,2,3,4"
        ]);

        $user_id = $request->filled("user_id") ? $request->user_id : null;

        $notificationsServices = new NotificationsService;
        $is_sent = $notificationsServices->send($request->message, $request->section, $user_id, $request->type, $request->status_code);

        if (!$is_sent) {
            return response([
                "message" => "something went wrong, try again",
                "status" => $is_sent 
            ], 200);
        }

        return response([
            "message" => "notification sent successfully",
            "status" => $is_sent 
        ], 200);
    }
}
