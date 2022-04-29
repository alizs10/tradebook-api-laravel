<?php

namespace App\Http\Controllers\api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationsService;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{

    public function index()
    {
        $notifications = Notification::where(["status_code" => 4])->orderBy("updated_at", "desc")->get();

        return response([
            "message" => "notifications sent by admin loaded successfully",
            "notifications" => $notifications
        ], 200);
    }

    public function send(Request $request)
    {
        $request->validate([
            'message' => "required|string|max:255|min:3",
            'section' => "required|in:home,accounts,profile",
            'user_id' => "nullable|numeric|exists:users,id",
            'type' => "required|in:success,warning,primary,error,info",
            'seen' => "required|numeric|in:0,1"
        ]);

        $user_id = $request->filled("user_id") ? $request->user_id : null;

        $notificationsServices = new NotificationsService;
        $notification = $notificationsServices->send($request->message, $request->section, $user_id, $request->type, 4, $request->seen);

        if (!$notification) {
            return response([
                "message" => "something went wrong, try again",
                "status" => $notification
            ], 200);
        }

        return response([
            "message" => "notification sent successfully",
            "notification" => $notification
        ], 200);
    }

    public function update(Request $request, Notification $notification)
    {
        $request->validate([
            'message' => "required|string|max:255|min:3",
            'section' => "required|in:home,accounts,profile",
            'user_id' => "nullable|numeric|exists:users,id",
            'type' => "required|in:success,warning,primary,error,info",
            'seen' => "required|numeric|in:0,1"
        ]);

        $inputs = $request->all();

        $result = $notification->update($inputs);

        if (!$result) {
            return response([
                "message" => "something went wrong, try again",
                "status" => $result
            ], 200);
        }

        return response([
            "message" => "notification updated successfully",
            "notification" => $notification
        ], 200);
    }

    public function destroy(Notification $notification)
    {
        $result = $notification->delete();

        if (!$result) {
            return response([
                "message" => "something went wrong, try again",
                "status" => $result
            ], 200);
        }

        return response([
            "message" => "notification deleted successfully",
            "status" => $result
        ], 200);
    }
}
