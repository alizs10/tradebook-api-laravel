<?php

namespace App\Http\Controllers\api\app;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Statistic;
use App\Models\StatisticValue;
use App\Services\StopLossAndTakeProfitCalculator;
use App\Services\TradeServices;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $accounts = Auth::user()->accounts;

        return response([
            'accounts' => $accounts
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:1',
            'balance' => 'required|numeric|min:1',
            'account_created_at' => 'required|date',
            'type' => 'required|numeric|in:0,1',
        ]);


        $inputs = $request->only(['name', 'balance', 'type', 'account_created_at']);
        $inputs['user_id'] = Auth::user()->id;

        try {
            DB::transaction(function () use ($inputs) {

                $account = Account::create($inputs);

                $statistics = Statistic::all();

                foreach ($statistics as $statistic) {
                    StatisticValue::create([
                        'account_id' => $account->id,
                        'statistic_id' => $statistic->id,
                        'value' => 0
                    ]);
                }

                TradeServices::updateStatistics($account->id);
            });
        } catch (\Throwable $e) {
            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'message' => 'account created successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Account $account)
    {
        $user = Auth::user();

        if (!$this->validateUserOwnsAccount($user, $account)) {
            return response([
                'message' => 'there in no such account'
            ], 422);
        }

        return response([
            'account' => $account
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Account $account)
    {
        $request->validate([
            'name' => 'required|string|min:1',
            'balance' => 'required|numeric|min:1',
            'account_created_at' => 'required|date',
            'type' => 'required|numeric|in:0,1'
        ]);

        $user = Auth::user();

        if (!$this->validateUserOwnsAccount($user, $account)) {
            return response([
                'message' => 'there in no such account'
            ], 422);
        }

        $inputs = $request->only(['name', 'type', 'balance', 'account_created_at']);
        $result = $account->update($inputs);
        TradeServices::updateStatistics($account->id);

        return response([
            'message' => 'account updated successfully',
            'account' => $account
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Account $account)
    {
        $user = Auth::user();
        if (!$this->validateUserOwnsAccount($user, $account)) {
            return response([
                'message' => 'there in no such account'
            ], 422);
        }

        $account->delete();

        return response([
            'message' => 'account deleted successfully',
        ], 200);
    }

    public function UpdateStopLossAndTakeProfit(Account $account)
    {
        $user = Auth::user();

        if (!$this->validateUserOwnsAccount($user, $account)) {
            return response([
                'message' => 'there in no such account'
            ], 422);
        }

        
        
        $result = DB::transaction(function () use ($account) {
            $calculator = new StopLossAndTakeProfitCalculator($account);
            $data = $calculator->calculate();
            
            $stopLossStatistic = Statistic::where(['name' => 'stop_losses_average'])->first();
            $takeProfitStatistic = Statistic::where(['name' => 'take_profits_average'])->first();
            $stopLossStatisticValue = StatisticValue::where(['account_id' => $account->id, 'statistic_id' => $stopLossStatistic->id])->first();
            $takeProfitStatisticValue = StatisticValue::where(['account_id' => $account->id, 'statistic_id' => $takeProfitStatistic->id])->first();

            $stopLossUpdateArray = [
                'value' => $data['stopLossAverage'],
                'ideal_value' => $data['stopLossIdealValue'],
                'cv' => $data['stopLossCV']*100,
                'status' => $data['stopLossStatus']['status'],
                'hint' => $data['stopLossStatus']['hint']
            ];

            $takeProfitUpdateArray = [
                'value' => $data['takeProfitAverage'],
                'ideal_value' => $data['takeProfitIdealValue'],
                'cv' => $data['takeProfitCV']*100,
                'status' => $data['takeProfitStatus']['status'],
                'hint' => $data['takeProfitStatus']['hint']
            ];

            $stopLossStatisticValue->update([
                'value' => json_encode($stopLossUpdateArray)
            ]);
            $takeProfitStatisticValue->update([
                'value' => json_encode($takeProfitUpdateArray)
            ]);

            return [$stopLossUpdateArray, $takeProfitUpdateArray];
        });

        return response([
            'message' => 'stop loss and take profit updated successfully',
            'stopLoss' => $result[0],
            'takeProfit' => $result[1],
            'updated_at' => Carbon::now('Asia/Tehran')
        ], 200);
    }

    protected function validateUserOwnsAccount($user, $account)
    {
        if ($user->id === $account->user_id)
            return true;
        return false;
    }
}
