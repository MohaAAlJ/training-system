<?php

namespace App\Models;

use App\Enums\GeneralConst;
use App\Models\CollegeMajor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class College extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'colleges';
    protected $fillable = ['id', 'name', 'institution_id', 'user_id', 'active', 'add_application'];

    protected $casts = [];

    /**Scope */
    public function scopeActive($query)
    {
        return $query->where('active', GeneralConst::ACTIVE);
    }

    /** Relations */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function majors()
    {
        return $this->belongsToMany(Major::class, 'college_major')
            ->using(CollegeMajor::class)
            ->withPivot('active');
    }

    public function activeMajors()
    {
        return $this->belongsToMany(Major::class, 'college_major')
            ->using(CollegeMajor::class)
            ->withPivot('active')
            ->wherePivot('active', true);
    }

    public function trainees()
    {
        return $this->hasManyThrough(
            Trainee::class,
            Application::class,
            'college_id', // Foreign key on applications table...
            'id', // Foreign key on trainees table...
            'id', // Local key on colleges table...
            'trainee_id' // Local key on applications table...
        );
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
