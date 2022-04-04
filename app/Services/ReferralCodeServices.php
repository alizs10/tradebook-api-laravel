<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\PlanUser;
use App\Models\PlanUserValue;
use App\Models\ReferralCode;
use Illuminate\Support\Facades\DB;

class ReferralCodeServices
{

    protected $response = [];
    protected $gift = 30;

    public function apply($referral_code, $new_referral_code)
    {
        $check = $this->check($referral_code);

        if ($check) {
            $gift = $this->giveGift($referral_code, $new_referral_code);

            if ($gift) {
                $this->response = [
                    'status' => true,
                    'message' => 'gifts are given successfully'
                ];  
            } else {
                $this->response = [
                    'status' => false,
                    'message' => "couldn't give the gifts"
                ];
            }
        }
        
        
       
        return $this->response;

    }

    // check -> set gift -> return response

    protected function check($referral_code)
    {
        $founded_referral_code = ReferralCode::where([
            'referral_code' => $referral_code,
            'code_status' => 0
        ])->first();

        $result = !empty($founded_referral_code) ? $founded_referral_code->update(['code_status' => 1]) : false;

        if (!$result) {
            $this->response = [
                'status' => false,
                'message' => "referral code is used or uncurrect"
            ];

            return false;
        }

        return true;
        
    }

    protected function giveGift($referral_code, $new_referral_code)
    {
        $plan = Plan::where('valid_for', $this->gift)->first();

        $user = ReferralCode::where('referral_code', $referral_code)->first()->user;
        $new_user = ReferralCode::where('referral_code', $new_referral_code)->first()->user;

        DB::transaction(function () use($plan, $user, $new_user) {

            $user_plan = PlanUser::create([
                'plan_id' => $plan->id,
                'user_id' => $user->id,
                'valid_for' => $plan->valid_for,
                'type' => 1
            ]);
            $new_user_plan = PlanUser::create([
                'plan_id' => $plan->id,
                'user_id' => $new_user->id,
                'valid_for' => $plan->valid_for,
                'type' => 1
            ]);

            $user_plans_values = PlanUserValue::where('user_id', $user->id)->first();
            $user_plans_values->valid_for += $user_plan->valid_for;
            $user_plans_values->valid_until = $user_plans_values->valid_until->addDays($user_plan->valid_for);
            $user_plans_values->save();
        
            $new_user_plans_values = PlanUserValue::where('user_id', $new_user->id)->first();
            $new_user_plans_values->valid_for += $new_user_plan->valid_for;
            $new_user_plans_values->valid_until = $new_user_plans_values->valid_until->addDays($new_user_plan->valid_for);
            $new_user_plans_values->save();

        });

        return true;
    }
}
