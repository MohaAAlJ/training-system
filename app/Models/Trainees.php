<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Trainees extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'trainees';
    protected $fillable = [
        'national_id',
        'full_name',
        'phone_number',
        'dob',
        'location',
        'institution_id',
        'institution_major_id',
    ];

    protected $dates = ['dob'];

    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }

    public function institutionMajor()
    {
        return $this->belongsTo(InstitutionMajor::class, 'institution_major_id');
    }
    public function applications()
    {
        return $this->hasMany(Applications::class, 'trainee_id');
    }
}
