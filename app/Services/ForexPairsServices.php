<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ForexPairsServices
{
    public function getAllSymbols()
    {
        $api_key = '2wPfFVkei0OHqdcIzwn5JZR';
        $url = "https://fcsapi.com/api-v3/forex/list?type=forex&access_key={$api_key}";

        $response = Http::get($url)['response'];

        $symbols = [];

        foreach ($response as $symbol) {
            array_push($symbols, array(
                'name' => $symbol['symbol'],
                'type' => 1,
                'status' => 1
            ));
        }

        return $symbols;
    }
}