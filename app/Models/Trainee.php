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
        'college_id',
        'full_name',
        'phone_number',
        'dob',
        'gender',
        'governorate_id',
        'street',
        'institution_id',
        'major_id',
        'university_number',
        'training_hours',
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

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
