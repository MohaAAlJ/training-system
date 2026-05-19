<?php

namespace App\Models;

use App\Enums\GeneralConst;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot; // يفضل استخدام Pivot للجدوال الوسيطة

class CollegeMajor extends Pivot
{
    use HasFactory;

    protected $table = 'college_major';

    public $timestamps = false;

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $fillable = [
        'college_id',
        'major_id',
        'active',
    ];

    /** Scopes */
    public function scopeActive($query)
    {
        return $query->where('active', GeneralConst::ACTIVE);
    }
}
