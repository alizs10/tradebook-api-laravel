<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_id',
        'reference_id',
        'user_id',
        'order_id',
        'amount',
        'payment_date',
        'status',
        'type'
    ];

    protected $appends = ['plan_name', 'user_name'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function getPlanNameAttribute()
    {
        return $this->attributes['plan_name'] = $this->order->plan->name;
    }
    public function getUserNameAttribute()
    {
        return $this->attributes['user_name'] = $this->user->name;
    }
}
