<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pair extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'type', 'status'];

    public function trades()
    {
        return $this->hasMany(Trade::class, 'pair_id');
    }
}
