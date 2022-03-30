<?php

namespace Database\Seeders;

use App\Models\Pair;
use App\Services\BinancePairsServices;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BinancePairsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $service = new BinancePairsServices;

        $data = $service->getAllSymbols();

        $data = collect($data);

        $chunks = $data->chunk(200);

        foreach ($chunks as $chunk) {
            Pair::insert($chunk->toArray());
        }
    }
}
