<?php

namespace App\Models;

use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'verification_code',
        'email_verified_at',
        'profile_photo_path',
        'password',
        'status',
        'is_admin'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    protected $appends = ['referral_code'];

    public function accounts()
    {
        return $this->hasMany(Account::class, 'user_id');
    }

    public function notes()
    {
        return $this->hasMany(Note::class, 'user_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    public function referral()
    {
        return $this->hasOne(ReferralCode::class, 'user_id');
    }

    public function plansValues()
    {
        return $this->hasOne(PlanUserValue::class, 'user_id');
    }

    public function getReferralCodeAttribute()
    {
        return $this->attributes['referral_code'] = $this->referral->referral_code;
    }

    public function discounts()
    {
        return $this->belongsToMany(Discount::class)->using(DiscountUser::class);
    }

    public function plans()
    {
        return $this->belongsToMany(Plan::class)->using(PlanUser::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }
}
