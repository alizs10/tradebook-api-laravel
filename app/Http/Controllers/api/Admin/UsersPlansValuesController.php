<?php

namespace App\Http\Controllers\api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlanUserValue;


class UsersPlansValuesController extends Controller
{
    public function index()
    {
        $users_plans_values = PlanUserValue::all();

        return response([
            'message' => "users plans values loaded successfully",
            'users_plans_values' => $users_plans_values
        ], 200);
    }
}
