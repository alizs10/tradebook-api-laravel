<?php

namespace App\Http\Middleware;

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

        $user = Auth::user();

        $validUntil = $user->plansValues->valid_until;
        $today = Carbon::now();

        $diff = $today->diffInDays($validUntil);

        if ($today > $validUntil) {
            $user->status = 0;
            $user->plansValues->valid_for = 0;
            $user->save();
            $user->plansValues()->update(['valid_for' => 0]);
        } else {
            $user->status == 0 && $user->update(['status' => 1]);
            $user->plansValues->valid_for = $diff;
            $user->plansValues()->update(['valid_for' => $diff]);
        }

        return $next($request);
    }
}
