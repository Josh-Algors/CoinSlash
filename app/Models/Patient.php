<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'patients';

    protected $fillable = [
        "user_id",
        "name",
        "email",
        "phone",
        "country",
        "is_verified",
        "referral_code"
    ];

}
