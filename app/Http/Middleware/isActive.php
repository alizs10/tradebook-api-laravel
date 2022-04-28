<?php

namespace App\Http\Middleware;

use App\Models\Notification;
use App\Services\NotificationsService;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class isActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $notificationsService = new NotificationsService();
        $user = Auth::user();

        $validUntil = $user->plansValues->valid_until;
        $today = Carbon::now();

        $diff = $today->diffInDays($validUntil);
        $message = "";
        if ($today > $validUntil) {
            $user->update(["status" => 0]);
            $user->plansValues()->update(['valid_for' => 0]);

            
            if (empty($user->plans->toArray())) {
                $is_user_notified = Notification::where(['user_id' => $user->id, 'status_code' => 1])->latest()->first();
                if (!$is_user_notified || $is_user_notified->notified_at->addDays(5) < $today) {
                    $message = "شما اشتراکی ندارید، برای استفاده از امکانات اپلیکیشن تریدبوک باید اشتراک تهیه کنید";
                    $notificationsService->send($message, "home", $user->id, "warning", 1);
                }
            } else {
                $is_user_notified = Notification::where(['user_id' => $user->id, 'status_code' => 2])->latest()->first();
                if (!$is_user_notified || $is_user_notified->notified_at->addDays(5) < $today) {
                    $message = "اشتراک شما به پایان رسیده است، برای استفاده از امکانات اپلیکیشن تریدبوک باید اشتراک خود را تمدید کنید";
                    $notificationsService->send($message, "home", $user->id, "error", 2);
                }
            }

        } else {
            $user->status == 0 && $user->update(['status' => 1]);
            $user->plansValues->valid_for = $diff;
            $user->plansValues()->update(['valid_for' => $diff]);
            if ($diff < 5) {
                $is_user_notified = Notification::where(['user_id' => $user->id, 'status_code' => 3])->latest()->first();
                if (!$is_user_notified || $is_user_notified->notified_at->addDays(1) < $today) {
                    $message = "اشتراک شما بزودی به پایان خواهد رسید، برای استفاده از امکانات اپلیکیشن تریدبوک نسبت به تمدید اشتراک خود اقدام کنید";
                    $notificationsService->send($message, "home", $user->id, "warning", 3);
                }
            }
        }

        return $next($request);
    }
}
