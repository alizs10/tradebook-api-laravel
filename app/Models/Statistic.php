<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Statistic extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name'];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function value()
    {
        return $this->hasOne(StatisticValue::class, 'statistic_id');
    }
}
