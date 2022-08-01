<?php

namespace App\Services;

use App\Services\PairsServices;
use Illuminate\Support\Facades\Http;

class BinancePairsServices
{
    public function getAllSymbols()
    {
        $pairsServices = new PairsServices;
        $response = Http::get('https://api.binance.com/api/v3/exchangeInfo')['symbols'];

        $symbols = [];

        foreach ($response as $symbol) {
            array_push($symbols, array(
                'name' => $symbol['symbol'],
                'type' => 0,
                'status' => 1
            ));
        }

        $unduplicatedData = $pairsServices->removeDuplicates($symbols);

        if (!$unduplicatedData) {
            return $symbols;
        }

        return $unduplicatedData;
    }

   
}
