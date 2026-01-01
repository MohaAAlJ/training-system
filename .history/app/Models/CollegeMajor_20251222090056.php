<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot; // يفضل استخدام Pivot للجدوال الوسيطة

class CollegeMajor extends Pivot
{
    use HasFactory;

    // تحديد اسم الجدول لأن الاسم الافتراضي قد يكون مختلفاً
    protected $table = 'college_major';

    // لأن هذا الجدول لا يحتوي على created_at و updated_at (حسب المايجريشن تبعك)
    public $timestamps = false;

    protected $fillable = [
        'college_id',
        'major_id'
    ];
}
