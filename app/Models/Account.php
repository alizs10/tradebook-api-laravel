<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'balance', 'type', 'user_id', 'account_created_at'];

    protected $appends = ['statistic_values'];


    public function trades()
    {
        return $this->hasMany(Trade::class, 'account_id')->orderBy('trade_date', 'desc');
    }

    public function values()
    {
        return $this->hasMany(StatisticValue::class, 'account_id');
    }

    public function getStatisticValuesAttribute()
    {
        return $this->attributes['statistic_values'] = $this->values;
    }
}
