<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\ReferralCode;
use Illuminate\Support\Facades\DB;

class ReferralCodeServices
{
    protected $gift = 30;
    protected $referral_code;
    protected $new_referral_code;
    protected $response = [];

    public function __construct($referral_code, $new_referral_code)
    {
        $this->referral_code = ReferralCode::where([
            'referral_code' => $referral_code,
            'code_status' => 0
        ])->first();
        $this->new_referral_code = ReferralCode::where([
            'referral_code' => $new_referral_code,
            'code_status' => 0
        ])->first();
    }

    public function apply()
    {
        $check = $this->check();

        if ($check) {
            $gift = $this->giveGifts();

            if ($gift) {
                $this->referral_code->update(['code_status' => 1]);
                $this->response = [
                    'status' => true,
                    'message' => "هدیه ی {$this->gift} روزه به دلیل استفاده از کد معرف برای شما با موفقیت فعال شد"
                ];  
            } else {
                $this->response = [
                    'status' => false,
                    'message' => "هدیه ی {$this->gift} روزه به دلیل استفاده از کد معرف برای شما فعال نشد. با پشتیبانی تماس بگیرید"
                ];
            }
        }

        return $this->response;
    }

    protected function check()
    {
        $result = !empty($this->referral_code) ? true : false;

        if (!$result) {
            $this->response = [
                'status' => false,
                'message' => "کد معرف صحیح نمی باشد یا قبلا استفاده شده است"
            ];
        }

        return $result;
        
    }

    protected function giveGifts()
    {
        $plan = Plan::where('valid_for', $this->gift)->first();

        $user = $this->referral_code->user;
        $new_user = $this->new_referral_code->user;

        DB::transaction(function () use($plan, $user, $new_user) {

           $planServices = new PlansServices;
           $planServices->giveUser($user->id, $plan->id, 1);
           $planServices->giveUser($new_user->id, $plan->id, 1);

        });

        return true;
    }
}
