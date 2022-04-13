<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['message', 'user_id', 'notified_at', 'seen', 'section', 'type', 'status_code'];

    protected $dates = ['notified_at'];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
