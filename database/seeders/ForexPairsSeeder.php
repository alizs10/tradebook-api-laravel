<?php

namespace Database\Seeders;

use App\Models\Pair;
use App\Services\ForexPairsServices;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ForexPairsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $service = new ForexPairsServices;

        $data = $service->getAllSymbols();

        $data = collect($data);

        $chunks = $data->chunk(50);

        foreach ($chunks as $chunk) {
            Pair::insert($chunk->toArray());
        }
    }
}
