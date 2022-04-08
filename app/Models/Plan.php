<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'valid_for', 'price'];

    public function users()
    {
        return $this->belongsToMany(User::class)->using(PlanUser::class);
    }
}
