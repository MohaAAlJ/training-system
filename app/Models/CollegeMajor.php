<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot; // يفضل استخدام Pivot للجدوال الوسيطة

class CollegeMajor extends Pivot
{
    use HasFactory;

    protected $table = 'college_major';

    public $timestamps = false;

    protected $fillable = [
        'college_id',
        'major_id'
    ];
}





