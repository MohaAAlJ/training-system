<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name'];

    protected $casts = [
        'name' => 'array',
    ];

    // العلاقة مع التخصصات
    public function majors()
    {
        return $this->belongsToMany(Major::class, 'institution_major');
    }
}
