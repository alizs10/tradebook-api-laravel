<?php

namespace Database\Seeders;

use App\Models\Pair;
use App\Services\KucoinPairsServices;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KucoinPairsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $service = new KucoinPairsServices;

        $data = $service->getAllSymbols();

        $data = collect($data);

        $chunks = $data->chunk(200);

        foreach ($chunks as $chunk) {
            Pair::insert($chunk->toArray());
        }
    }
}
