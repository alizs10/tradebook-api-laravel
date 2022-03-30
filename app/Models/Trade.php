<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trade extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "user_id",
        "account_id",
        "pair_id",
        "contract_type",
        "status",
        "entry_price",
        "exit_price",
        "leverage",
        "margin",
        "pnl",
        "profit",
        "trade_date",
    ];

    protected $appends = ['pair_name'];

    public function pair() {
        return $this->belongsTo(Pair::class, 'pair_id');
    }

    public function getPairNameAttribute()
    {
        return $this->attributes['pair_name'] = $this->pair->name;
    }

    

}
