<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Trainee extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'trainees';
    protected $fillable = [
        'id',
        'national_id',
        'full_name',
        'phone_number',
        'dob',
        'gender',
        'governorate_id',
        'street',
    ];
    protected $casts = [
        'dob' => 'date',
        'gender' => \App\Enums\Gender::class,
    ];

    protected $dates = ['dob'];

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
