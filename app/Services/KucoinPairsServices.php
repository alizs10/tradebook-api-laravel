<?php

namespace App\Services;

use App\Sevices\PairsServices;
use Illuminate\Support\Facades\Http;

class KucoinPairsServices
{
    public function getAllSymbols(PairsServices $pairsServices)
    {
        $response = Http::get('https://api.kucoin.com/api/v1/symbols')['data'];

        $symbols = [];

        foreach ($response as $symbol) {
            array_push($symbols, array(
                'name' => str_replace("-", "", $symbol['symbol']),
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
