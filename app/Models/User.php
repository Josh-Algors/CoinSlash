<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'category_id',
        'phone_email_verified',
        'phone',
        'status',
        'gender',
        'profile_image',
        'marital_status',
        'dob',
        'weight',
        'height',
        'age',
        'google_id',
        'facebook_id',
        'country_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'category_id'
        
    ];

    public $guard_name = 'api';

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'phone_email_verified_at' => 'datetime',
    ];

    protected $appends = [
        'cat'
    ];

    public function cat(){
        return $this->belongsTo(UserCategory::class, 'category_id', 'id');
    }

    public function getCatAttribute(){
       return $this->cat()->first() ? $this->cat()->first()->name : null;
    }
}
