<?php

namespace Database\Seeders;

use App\Models\Statistic;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StatisticsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       Statistic::insert([
        ["name" => "balance_data", "created_at" => now()],
        ["name" => "updated_balance", "created_at" => now()],
        ["name" => "win_ratio", "created_at" => now()],
        ["name" => "average_pnl", "created_at" => now()],
        ["name" => "average_minus_pnl", "created_at" => now()],
        ["name" => "all_trades", "created_at" => now()],
        ["name" => "best_pnl", "created_at" => now()],
        ["name" => "worst_pnl", "created_at" => now()],
        ["name" => "wins", "created_at" => now()],
        ["name" => "losts", "created_at" => now()],
        ["name" => "fave_pair", "created_at" => now()],
        ["name" => "profit", "created_at" => now()],
        ["name" => "profit_percentage", "created_at" => now()],
        ["name" => "positive_profit", "created_at" => now()],
        ["name" => "negetive_profit", "created_at" => now()],
        ["name" => "stop_losses_average", "created_at" => now()],
        ["name" => "take_profits_average", "created_at" => now()]
       ]);
    }
}
