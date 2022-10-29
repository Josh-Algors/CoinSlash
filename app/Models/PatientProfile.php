<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientProfile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'patients_profile';

    protected $fillable = [
        "user_id",
        "weight",
        "height",
        "age",
        "heart_rate",
        "blood_group",
        "blood_pressure",
        "glucose_level",
        "allergies",
        "chronic_diseases",
        "medications",
        "surgeries",
        "injuries",
        "pregnant",
        "pre-existing_conditions",
    ];

}
