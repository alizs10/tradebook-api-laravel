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

        if ($accountsCount > 0) {
            foreach ($user->accounts as $account) {
                $tradesCount += count($account->trades);
            }
        }

        return response([
            'accountsCount' => $accountsCount,
            'notesCount' => $notesCount,
            'tradesCount' => $tradesCount,
            'validFor' => $validFor
        ], 200);
    }
}
