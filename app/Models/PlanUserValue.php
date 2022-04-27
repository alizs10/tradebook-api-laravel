<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanUserValue extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "user_plans_values";

    protected $fillable = ['user_id', 'valid_for', 'valid_until'];

    protected $appends = ['user_email', 'user_name'];

    protected $dates = ['valid_until'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getUserNameAttribute()
    {
        return $this->attributes['user_name'] = $this->user->name;
    }
    
    public function getUserEmailAttribute()
    {
        return $this->attributes['user_email'] = $this->user->email;
    }
}
