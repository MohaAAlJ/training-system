<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;

class TrainingRequests extends Model
{
    //
    use HasFactory, SoftDeletes, HasTranslations;
    protected $fillable = [
        'student_id',
        'institution_name',
        'major',
        'start_date',
        'end_date',
        'status',
        'full_name',
        'phone_number',
        'major_level',
        'address',
        'reason_for_rejection',

    ];
}
