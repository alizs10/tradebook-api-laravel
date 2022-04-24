<?php

namespace App\Sevices;

use App\Models\Pair;

class PairsServices
{
    public function removeDuplicates(array $symbols)
    {
        $unduplicatedData = [];

        $databasePairs = Pair::where("type", 0)->get();

        if (count($databasePairs) == 0) {
            return false;
        }
        $databaseSymbols = [];

        foreach ($databasePairs as $data) {
            array_push($databaseSymbols, $data["name"]);
        }

        foreach ($symbols as $symbol) {
            if (!in_array($symbol["name"], $databaseSymbols)) {
                array_push($unduplicatedData, $symbol);
            }
        }

        return $unduplicatedData;
    }
}
