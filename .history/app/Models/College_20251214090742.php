<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class College extends Model {
    use HasFactory, HasTranslations;
    
    protected $fillable = ['name', 'institution_id', 'user_id'];
    public $translatable = ['name'];

    public function institution() {
        return $this->belongsTo(Institution::class);
    }

    public function user() { // المشرف
        return $this->belongsTo(User::class);
    }

    // العلاقة مع التخصصات (الجدول الوسيط الجديد)
    public function majors() {
        return $this->belongsToMany(Major::class, 'college_major');
    }
}
