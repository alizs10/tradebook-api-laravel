<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\PlanUserValue;
use App\Services\NotificationsService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PlansRemainingDays implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $notificationsService = new NotificationsService();
        $usersPlansValues = PlanUserValue::all();
        $today = Carbon::now();


        foreach ($usersPlansValues as $userPlansValues) {
            $validUntil = $userPlansValues->valid_until;
            $diff = $today->diffInDays($validUntil);
            $message = "";

            if ($today > $validUntil) {
                $userPlansValues->user()->update(["status" => 0]);
                $userPlansValues->update(['valid_for' => 0]);

                if (!empty($userPlansValues->user->plans->toArray())) {
                    $is_user_notified = Notification::where(['user_id' => $userPlansValues->user->id, 'status_code' => 2])->latest()->first();
                    if (!$is_user_notified || $is_user_notified->notified_at->addDays(5) < $today) {
                        $message = "اشتراک شما به پایان رسیده است، برای استفاده از امکانات اپلیکیشن تریدبوک باید اشتراک خود را تمدید کنید";
                        $notificationsService->send($message, "home", $userPlansValues->user->id, "error", 2);
                    }
                }
            } else {
                $userPlansValues->user->status == 0 && $userPlansValues->user()->update(['status' => 1]);
                $userPlansValues->update(['valid_for' => $diff]);
                if ($diff < 5) {
                    $is_user_notified = Notification::where(['user_id' => $userPlansValues->user->id, 'status_code' => 3])->latest()->first();
                    if (!$is_user_notified || $is_user_notified->notified_at->addDays(1) < $today) {
                        $message = "اشتراک شما بزودی به پایان خواهد رسید، برای استفاده از امکانات اپلیکیشن تریدبوک نسبت به تمدید اشتراک خود اقدام کنید";
                        $notificationsService->send($message, "home", $userPlansValues->user->id, "warning", 3);
                    }
                }
            }
        }
    }
}
