<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use function PHPUnit\Framework\isNull;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['message', 'user_id', 'notified_at', 'seen', 'section', 'type', 'status_code'];

    protected $dates = ['notified_at'];

    protected $appends = ['user_name'];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function getUserNameAttribute()
    {
        return $this->attributes["user_name"] = !empty($this->user->name) ? $this->user->name : "عمومی";
    }
}
