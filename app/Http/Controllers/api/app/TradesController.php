<?php

namespace App\Http\Controllers\api\app;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Trade;
use App\Services\TradeServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TradesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Account $account)
    {
        $user = Auth::user();
        if (!$this->validateUserOwnsAccount($user, $account)) {
            return response([
                'message' => 'there in no such account'
            ], 422);
        }

        $trades = $account->trades;
        return response([
            'trades' => $trades
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
    public function store(Request $request, Account $account)
    {
        $user = Auth::user();
        if (!$this->validateUserOwnsAccount($user, $account)) {
            return response([
                'message' => 'there in no such account'
            ], 422);
        }

        $request->validate([
            'pair_id' => 'required|numeric|exists:pairs,id',
            'margin' => 'nullable|numeric',
            'profit' => 'nullable|numeric',
            'leverage' => 'required|integer|min:0',
            'entry_price' => 'required|numeric|min:0',
            'exit_price' => 'required|numeric|min:0',
            'status' => 'required|numeric|in:0,1',
            'contract_type' => 'required|numeric|in:0,1',
            'trade_date' => 'required|date'
        ]);

        $inputs = $request->all();
        $inputs['user_id'] = $user->id;
        $inputs['account_id'] = $account->id;
        $calculated_inputs = TradeServices::TradeCalculator($inputs);

        $result = DB::transaction(function () use ($calculated_inputs) {
            $trade = Trade::create($calculated_inputs);
            TradeServices::updateStatistics($trade->account_id);

            return $trade;
        });

        return response([
            'message' => 'trade created successfully',
            'trade' => $result

        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
    public function update(Request $request, Account $account, Trade $trade)
    {
        $user = Auth::user();
        if (!$this->validateUserOwnsAccount($user, $account)) {
            return response([
                'message' => 'there in no such account'
            ], 422);
        }

        if (!$this->validateAccountContainsTrade($account, $trade)) {
            return response([
                'message' => 'there in no such trade'
            ], 422);
        }


        $request->validate([
            'pair_id' => 'required|numeric|exists:pairs,id',
            'margin' => 'nullable|numeric',
            'profit' => 'nullable|numeric',
            'leverage' => 'required|integer|min:0',
            'entry_price' => 'required|numeric|min:0',
            'exit_price' => 'required|numeric|min:0',
            'status' => 'required|numeric|in:0,1',
            'contract_type' => 'required|numeric|in:0,1',
            'trade_date' => 'required|date',
        ]);




        $inputs = $request->all();
        $inputs['user_id'] = $user->id;
        $inputs['account_id'] = $account->id;
        $calculated_inputs = TradeServices::TradeCalculator($inputs);

        $result = DB::transaction(function () use ($calculated_inputs, $trade, $account) {
            $trade->update($calculated_inputs);
            TradeServices::updateStatistics($account->id);
            return $trade;
        });

        return response([
            'message' => 'trade updated successfully',
            'status' => $result

        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Account $account, Trade $trade)
    {
        $user = Auth::user();
        if (!$this->validateUserOwnsAccount($user, $account)) {
            return response([
                'message' => 'there in no such account'
            ], 422);
        }

        if (!$this->validateAccountContainsTrade($account, $trade)) {
            return response([
                'message' => 'there in no such trade'
            ], 422);
        }

        $result = DB::transaction(function () use ($trade, $account) {
            $result = $trade->delete();
            TradeServices::updateStatistics($account->id);

            return $result;
        });

        return response([
            'message' => 'trade deleted successfully',
            'status' => $result
        ], 200);
    }

    public function updateOpenTradesPrice(Request $request, Account $account)
    {
        $user = Auth::user();
        if (!$this->validateUserOwnsAccount($user, $account)) {
            return response([
                'message' => 'there in no such account'
            ], 422);
        }

        $request->validate([
            'pair_id' => 'required|numeric|exists:pairs,id',
            'exit_price' => 'required|numeric|min:0'
        ]);

        $inputs = $request->only(['pair_id', 'exit_price']);

        $openTrades = Trade::where(['pair_id' => $inputs['pair_id'], 'status' => 0, 'account_id' => $account->id])->get()->makeHidden(['pair_name']);

        if (!empty($openTrades)) {

            $result = DB::transaction(function () use ($openTrades, $inputs, $account) {
                foreach ($openTrades as $openTrade) {
                    $tradeInstance = $openTrade;
                    $tradeInstance->exit_price = $inputs['exit_price'];
                    $tradeInstance->margin = json_decode($tradeInstance->margin, true);
                    $calculated_inputs = TradeServices::TradeCalculator($tradeInstance->toArray());

                    $result = $openTrade->update($calculated_inputs);
                }

                TradeServices::updateStatistics($account->id);
                return $result;
            });
        }

        return response([
            'message' => 'prices updated successfully',
            'trades' => $openTrades,
            'result' => $result
        ], 200);
    }

    protected function validateUserOwnsAccount($user, $account)
    {
        if ($user->id === $account->user_id)
            return true;
        return false;
    }

    protected function validateAccountContainsTrade($account, $trade)
    {
        if ($account->id === $trade->account_id)
            return true;
        return false;
    }
}
