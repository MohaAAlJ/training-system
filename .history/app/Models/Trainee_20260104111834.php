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
        'governorate_id',
        'street',
        'institution_id',
        'major_id',
        'training_hours',
    ];
    protected $casts = [
        'dob' => 'date',
    ];

    protected $dates = ['dob'];

    public function governorate()
    {
        return $this->belongsTo(Governorate::class, 'governorate_id');
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }

    public function major()
    {
        return $this->belongsTo(Major::class, 'major_id');
    }

    public function Application()
    {
        return $this->hasMany(Application::class, 'trainee_id');
    }
}
