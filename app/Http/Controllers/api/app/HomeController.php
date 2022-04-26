<?php

namespace App\Http\Controllers\api\app;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $accountsCount = count($user->accounts);
        $notesCount = count($user->notes);
        $tradesCount = 0;
        $validFor = $user->plansValues->valid_for;
        $planName = !empty($user->plans->toArray()) ? $user->plans->last()->name : "بدون اشتراک";

        if ($accountsCount > 0) {
            foreach ($user->accounts as $account) {
                $tradesCount += count($account->trades);
            }
        }

        return response([
            ["name" => "حساب ها", "value" => $accountsCount],
            ["name" => "یادداشت ها", "value" => $notesCount],
            ["name" => "ترید ها", "value" => $tradesCount],
            ["name" => "روزهای باقی مانده", "value" => $validFor],
            ["name" => "اشتراک", "value" => $planName]
        ], 200);
    }
}
