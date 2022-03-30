<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StatisticValue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['statistic_id', 'account_id', 'value'];

    protected $appends = ['statistic_name'];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function statistic()
    {
        return $this->belongsTo(Statistic::class, 'statistic_id');
    }

    public function getStatisticNameAttribute()
    {
        return $this->attributes['statistic_name'] = $this->statistic->name;
    }
}
