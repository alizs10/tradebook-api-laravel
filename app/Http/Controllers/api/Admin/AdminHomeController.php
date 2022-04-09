<?php

namespace App\Http\Controllers\api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pair;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;

class AdminHomeController extends Controller
{
    public function index()
    {

        $payments = Payment::where('status', 1)->get();

        $usersCount = count(User::all());
        $paymentsCount = count($payments);
        $pairsCount = count(Pair::all());
        $income = 0;

        foreach ($payments as $payment) {
            $income += $payment->amount;
        }

        $plansCount = count(Plan::all());


        return response([
            'usersCount' => $usersCount,
            'paymentsCount' => $paymentsCount,
            'pairsCount' => $pairsCount,
            'income' => $income,
            'plansCount' => $plansCount
        ], 200);
    }
}
