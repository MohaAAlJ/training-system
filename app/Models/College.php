<?php

namespace App\Models;

use App\Enums\GeneralConst;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class College extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'colleges';
    protected $fillable = ['id', 'name', 'institution_id', 'user_id', 'active', 'add_application'];

    protected $casts = [

    ];

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
        return $this->belongsToMany(Major::class, 'college_major');
    }

    public function trainees()
    {
        return $this->hasMany(Trainee::class, 'college_id');
    }
}
