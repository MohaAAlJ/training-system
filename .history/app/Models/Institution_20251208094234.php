<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name'];

    // هذا السطر مهم جداً
    // يخبر لاراڤيل أن هذا العمود يجب معاملته كمصفوفة برمجياً وكـ JSON في قاعدة البيانات
    protected $casts = [
        'name' => 'array',
    ];

    // العلاقة مع التخصصات
    public function majors()
    {
        return $this->belongsToMany(Major::class, 'institution_major');
    }
}
