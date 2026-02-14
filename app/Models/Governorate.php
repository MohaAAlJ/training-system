<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Governorate extends Model
{
    use HasFactory;

    protected $table = 'governorates';

    protected $fillable = [
        'name',
    ];

    public function administratives()
    {
        return $this->hasMany(Administrative::class);
    }

    public function trainees()
    {
        return $this->hasMany(Trainee::class);
    }

    protected $casts = [];
}
