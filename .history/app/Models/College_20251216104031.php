<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;

class College extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = ['name', 'institution_id', 'user_id'];
    public $translatable = ['name'];

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
        return $this->hasMany(Trainees::class, 'college_id');
    }
