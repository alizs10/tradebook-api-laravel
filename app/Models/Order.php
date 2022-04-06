<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'plan_id',
        'amount',
        'discount_id',
        'discount_amount',
        'total_amount',
        'order_date',
        'status'
    ];

    protected $appends = ['plan_name', 'user_name'];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function plan() {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function discount() {
        return $this->belongsTo(Discount::class, 'discount_id');
    }

    public function getPlanNameAttribute() {
        return $this->attributes['plan_name'] = $this->plan->name;
    }
    public function getUserNameAttribute() {
        return $this->attributes['user_name'] = $this->user->name;
    }
}
