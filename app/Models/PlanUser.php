<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanUser extends Pivot
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'plan_id', 'valid_for', 'type'];
}
