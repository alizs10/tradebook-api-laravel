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

    protected $dates = ['valid_until'];
}
