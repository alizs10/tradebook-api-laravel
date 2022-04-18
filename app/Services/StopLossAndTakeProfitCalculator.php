<?php

namespace App\Services;

use App\Models\Statistic;
use App\Models\StatisticValue;
use App\Models\Trade;

class StopLossAndTakeProfitCalculator
{
    protected $account;
    protected $stopLossArray = [];
    protected $takeProfitArray = [];
    protected $rawData = [];
    protected $takeProfitAverage = 0;
    protected $stopLossAverage = 0;
    protected $idealStopLoss = 1;
    protected $idealTakeProfit = 1;
    protected $takeProfitDeviation;
    protected $stopLossDeviation;
    protected $stopLossCoeffiecientOfVariation;
    protected $takeProfitCoeffiecientOfVariation;
    protected $statusArray = [
        0 => 'بسیار خوب',
        1 => 'خوب',
        2 => 'نرمال',
        3 => 'بد',
        4 => 'بسیار بد',
    ];
    protected $stopLossStatus;
    protected $takeProfitStatus;

    public function __construct($account)
    {
        $this->account = $account;
    }


    public function calculate()
    {
        $trades = Trade::where(['account_id' => $this->account->id, 'status' => 1])->get();
        $statistic = Statistic::where(['name' => 'balance_data'])->first();
        $statistic_value = StatisticValue::where(['statistic_id' => $statistic->id, 'account_id' => $this->account->id])->first();
        $balanceData = json_decode($statistic_value['value'], true);


        foreach ($trades as $trade) {
            $matchData = $balanceData[0];
            foreach ($balanceData as $data) {
                if ($data['date'] < $trade->trade_date && $data['date'] > $matchData['date']) {
                    $matchData = $data;
                }
            }

            // array_push($this->rawData, array(['match data' => $matchData, 'trade date' => $trade->trade_date]));

            if ($trade->profit > 0) {
                $takeProfitPercentage = ($trade->profit / $matchData['balance']) * 100;
                array_push($this->takeProfitArray, $takeProfitPercentage);
            } else {
                $stopLossPercentage = ($trade->profit / $matchData['balance']) * 100;
                array_push($this->stopLossArray, $stopLossPercentage);
            }
        }

        $this->stopLossAverage = count($this->stopLossArray) !== 0 ? array_sum($this->stopLossArray) / count($this->stopLossArray) : "قابل محاسبه نیست";
        $this->takeProfitAverage = count($this->takeProfitArray) !== 0 ? array_sum($this->takeProfitArray) / count($this->takeProfitArray) : "قابل محاسبه نیست";

        $this->deviationCalculator($this->stopLossArray, $this->takeProfitArray);
        $this->setStatus();
        return [
            'takeProfitAverage' => $this->takeProfitAverage,
            'stopLossAverage' => $this->stopLossAverage,
            'stopLossDeviation' => $this->stopLossDeviation,
            'takeProfitDeviation' => $this->takeProfitDeviation,
            'stopLossCV' => number_format((float)$this->stopLossCoeffiecientOfVariation, 2, '.', ''),
            'takeProfitCV' => number_format((float)$this->takeProfitCoeffiecientOfVariation, 2, '.', ''),
            'stopLossStatus' => $this->stopLossStatus,
            'takeProfitStatus' => $this->takeProfitStatus,
            'stopLossIdealValue' => $this->idealStopLoss,
            'takeProfitIdealValue' => $this->idealTakeProfit
        ];
    }

    public function deviationCalculator(array $stopLossArray, array $takeProfitArray)
    {
        if (empty($stopLossArray)) {
            $this->stopLossDeviation = "داده ناکافی";
        }
        if (empty($takeProfitArray)) {
            $this->takeProfitDeviation = "داده ناکافی";
        }

        if (!empty($stopLossArray)) {
            $deviationsOfEachStopLossArray = [];
            foreach ($stopLossArray as $stopLossData) {
                array_push($deviationsOfEachStopLossArray, ($stopLossData - $this->stopLossAverage) ** 2);
            }

            $stopLossVariance = array_sum($deviationsOfEachStopLossArray) / count($deviationsOfEachStopLossArray);
            $standardStopLossesDeviation = sqrt($stopLossVariance);
            $this->stopLossDeviation = $standardStopLossesDeviation;
            $this->stopLossCoeffiecientOfVariation = abs($standardStopLossesDeviation / $this->stopLossAverage);
        }

        if (!empty($takeProfitArray)) {
            $deviationsOfEachTakeProfitArray = [];
            foreach ($takeProfitArray as $takeProfitData) {
                array_push($deviationsOfEachTakeProfitArray, ($takeProfitData - $this->takeProfitAverage) ** 2);
            }

            $takeProfitVariance = array_sum($deviationsOfEachTakeProfitArray) / count($deviationsOfEachTakeProfitArray);
            $standardTakeProfitsDeviation = sqrt($takeProfitVariance);
            $this->takeProfitDeviation = $standardTakeProfitsDeviation;
            $this->takeProfitCoeffiecientOfVariation = abs($standardTakeProfitsDeviation / $this->takeProfitAverage);
        }
    }

    public function setStatus()
    {
        if (!is_numeric($this->stopLossAverage)) {
            $this->stopLossStatus = "داده ناکافی";
        } else {
            $stopLossDeviaionFromIdeal = $this->idealStopLoss - abs($this->stopLossAverage);
            if ($stopLossDeviaionFromIdeal >= 2) {
                $this->stopLossStatus = [
                    'status' => $this->statusArray[4],
                    'hint' => 'حد ضرر های بالاتر از 3 درصد مختص حساب هایی با بالانس بسیار کم است و اگر حرفه ای نیستید توصیه میشود که استراتژی خود را تغییر دهید.'
                ];
            }
            if ($stopLossDeviaionFromIdeal >= 0.5 && $stopLossDeviaionFromIdeal < 2) {
                $this->stopLossStatus = [
                    'status' => $this->statusArray[3],
                    'hint' => 'حد ضرر های بین 1.5 درصد تا 3 درصد مختص حساب هایی با بالانس کم است و اگر حرفه ای نیستید توصیه میشود که استراتژی خود را تغییر دهید.'
                ];
            }
            if ($stopLossDeviaionFromIdeal >= 0 && $stopLossDeviaionFromIdeal < 0.5) {
                $this->stopLossStatus = [
                    'status' => $this->statusArray[2],
                    'hint' => 'سعی کنید حد ضرر خود را به عدد 1 نزدیک تر کنید و از معاملات با ریسک بالا خودداری کنید.'
                ];
            }
            if ($stopLossDeviaionFromIdeal >= -0.5 && $stopLossDeviaionFromIdeal < 0) {
                $this->stopLossStatus = [
                    'status' => $this->statusArray[1],
                    'hint' => 'در وضعیت مطلوبی قرار دارید. سعی کنید ثبات داشته باشید. اگر برآیند مثبتی دارید، میتوانید معاملاتی با ریسک بالاتر هم باز کنید.'
                ];
            }
            if ($stopLossDeviaionFromIdeal < -0.5) {
                $this->stopLossStatus = [
                    'status' => $this->statusArray[0],
                    'hint' => 'در وضعیت بسیار مطلوبی قرار دارید. اگر برآیند شما مثبت است، میتوانید ریسک های بالاتری را هم بپذیرید.'
                ];
            }
        }
        if (!is_numeric($this->takeProfitAverage)) {
            $this->takeProfitStatus = "داده ناکافی";
        } else {
            $takeProfitDeviaionFromIdeal = $this->idealTakeProfit - $this->takeProfitAverage;
            if ($takeProfitDeviaionFromIdeal >= 2) {
                $this->takeProfitStatus = [
                    'status' => $this->statusArray[0],
                    'hint' => 'در وضعیت بسیار مطلوبی قرار دارید ، اما مغرور نشوید.'
                ];
            }
            if ($takeProfitDeviaionFromIdeal >= 0.5 && $takeProfitDeviaionFromIdeal < 2) {
                $this->takeProfitStatus = [
                    'status' => $this->statusArray[1],
                    'hint' => 'در وضعیت مطلوبی قرار دارید. سعی کنید ثبات داشته باشید و مغرور نشوید.'
                ];
            }
            if ($takeProfitDeviaionFromIdeal >= 0 && $takeProfitDeviaionFromIdeal < 0.5) {
                $this->takeProfitStatus = [
                    'status' => $this->statusArray[2],
                    'hint' => 'در وضعیت نرمالی قرار دارید اما سعی کنید معاملاتی با حد سود های بیشتر باز کنید.'
                ];
            }
            if ($takeProfitDeviaionFromIdeal >= -0.5 && $takeProfitDeviaionFromIdeal < 0) {
                $this->takeProfitStatus = [
                    'status' => $this->statusArray[3],
                    'hint' => 'اگر برآیند شما مثبت نیست، توصیه میکنیم حتماً استراتژی معاملاتی خود را تغییر دهید.'
                ];
            }
            if ($takeProfitDeviaionFromIdeal < -0.5) {
                $this->takeProfitStatus = [
                    'status' => $this->statusArray[4],
                    'hint' => 'اگر برآیند شما مثبت نیست، توصیه میکنیم حتماً استراتژی معاملاتی خود را تغییر دهید و در روند کار خود تجدید نظر کنید.'
                ];
            }
        }
    }
}
