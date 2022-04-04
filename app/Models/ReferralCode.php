<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReferralCode extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "referral_codes";

    protected $fillable = ['user_id', 'referral_code', 'code_status'];


    public function user()
    {
        return $this->BelongsTo(User::class, 'user_id');
    }
}
