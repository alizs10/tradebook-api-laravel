<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\User;

class PlansServices
{

    public function giveUser($user_id, $plan_id)
    {
        $user = User::find($user_id);
        $plan = Plan::find($plan_id);

        $user_old_valid_for = $user->plansValues->valid_for;
        $user_old_valid_until = $user->plansValues->valid_until;
        $user_new_valid_for = $plan->valid_for + $user_old_valid_for;
        $user_new_valid_until = $user_old_valid_until->addDays($plan->valid_for);

        $user->plans()->attach($plan->id, ['valid_for' => $plan->valid_for, 'type' => 0]);
        $result = $user->plansValues()->update(['valid_for' => $user_new_valid_for, 'valid_until' => $user_new_valid_until]);

        return $result ? true : false;
    }
}
