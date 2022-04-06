<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
    use HasFactory, SoftDeletes;


    protected $fillable = ['code', 'value', 'plan_id', 'user_id', 'status'];

    protected $appends = ['plan_name', 'user_name'];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function plan() {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function getPlanNameAttribute() {
        return $this->attributes['plan_name'] = !empty($this->plan->name) ? $this->plan->name : "all plans";
    }
    public function getUserNameAttribute() {
        return $this->attributes['user_name'] = !empty($this->user->name) ? $this->user->name : "public";
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->using(DiscountUser::class);
    }
}
