<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Pair;
use App\Models\Trade;

class TradeServices
{

    static public function TradeCalculator(array $trade)
    {
        
        $trade['pnl'] = ((floatval($trade['exit_price']) - floatval($trade['entry_price'])) / floatval($trade['entry_price'])) * 100 * floatval($trade['leverage']);
        $trade['pnl'] = intval($trade['contract_type']) === 1 ? $trade['pnl'] *= (-1) : $trade['pnl'];

        $trade['profit'] = ($trade['pnl'] * floatval($trade['margin']['margin'])) / 100;

        return $trade;
    }

    static public function updateStatistics($acc_id)
    {
        $acc = Account::find($acc_id);
        $trades = array_reverse($acc->trades->toArray());

        //fave pair
        $pairs = Trade::where('account_id', $acc_id)->get(['pair_id'])->makeHidden(['pair_name'])->toArray();
        if ($pairs) {
            $pairIDs = array_column($pairs, 'pair_id');
            $favePairID = array_search(max(array_count_values($pairIDs)), array_count_values($pairIDs));
            $favePairName = Pair::find($favePairID)->name;
            $favePair = ['name' => $favePairName, 'count' => max(array_count_values($pairIDs))];
        } else {
            $favePair = ['name' => 'no trades', 'count' => 0];
        }

       

        $wins = 0;
        $pnls = 0;
        $minusPnls = 0;
        $allTrades = count($trades);
        $updated_balance = $acc->balance;
        $balance_data = array(['date' => date('Y-m-d', strtotime($acc->account_created_at)), 'balance' => $updated_balance]);
        $bestPnl = ['value' => 0, 'pair_name' => null];
        $worstPnl = ['value' => 0, 'pair_name' => null];

        $positiveProfit = 0;
        $negetiveProfit = 0;

        

        if (!empty($trades)) {
            foreach ($trades as $trade) {
                $bestPnl = $trade['pnl'] > $bestPnl['value'] ? ['value' => $trade['pnl'], 'pair_name' => $trade['pair_name']] : $bestPnl;
                $worstPnl = $trade['pnl'] < $worstPnl['value'] ? ['value' => $trade['pnl'], 'pair_name' => $trade['pair_name']] : $worstPnl;
                $trade['pnl'] > 0 && $wins++;
                $trade['pnl'] > 0 && $pnls += $trade['pnl'];
                $trade['pnl'] < 0 && $minusPnls += $trade['pnl'];
                $trade['profit'] > 0 ? $positiveProfit += $trade['profit'] : $negetiveProfit += $trade['profit'];
                $updated_balance += $trade['profit'];
                array_push($balance_data, array('date' => date('Y-m-d', strtotime($trade['trade_date'])), 'balance' => $updated_balance));
            }
        }


        $losts = $allTrades - $wins;
        $winRatio = ($allTrades === 0) ? 0 : ($wins / $allTrades) * 100;
        $averagePnl = ($wins === 0) ? 0 : ($pnls / $wins);
        $averageMinusPnl = ($losts === 0) ? 0 : ($minusPnls / $losts);
        $profit = $updated_balance - $acc->balance;
        $profitPercentage = $acc->balance !== 0 ? (($updated_balance - $acc->balance)/$acc->balance)*100 : 0;

        

        foreach ($acc->statistic_values as $statistic_value) {
            switch ($statistic_value->statistic->name) {
                case 'balance_data':
                    $statistic_value->update(['value' => json_encode($balance_data)]);
                    break;
                case 'updated_balance':
                    $statistic_value->update(['value' => $updated_balance]);
                    break;
                case 'win_ratio':
                    $statistic_value->update(['value' => $winRatio]);
                    break;
                case 'average_pnl':
                    $statistic_value->update(['value' => $averagePnl]);
                    break;
                case 'average_minus_pnl':
                    $statistic_value->update(['value' => $averageMinusPnl]);
                    break;
                case 'all_trades':
                    $statistic_value->update(['value' => $allTrades]);
                    break;
                case 'best_pnl':
                    $statistic_value->update(['value' => json_encode($bestPnl)]);
                    break;
                case 'worst_pnl':
                    $statistic_value->update(['value' => json_encode($worstPnl)]);
                    break;
                case 'wins':
                    $statistic_value->update(['value' => $wins]);
                    break;
                case 'losts':
                    $statistic_value->update(['value' => $losts]);
                    break;
                case 'fave_pair':
                    $statistic_value->update(['value' => json_encode($favePair)]);
                    break;
                case 'profit':
                    $statistic_value->update(['value' => $profit]);
                    break;
                case 'profit_percentage':
                    $statistic_value->update(['value' => $profitPercentage]);
                    break;
                case 'positive_profit':
                    $statistic_value->update(['value' => $positiveProfit]);
                    break;
                case 'negetive_profit':
                    $statistic_value->update(['value' => $negetiveProfit]);
                    break;
            }
        }

    }
}
