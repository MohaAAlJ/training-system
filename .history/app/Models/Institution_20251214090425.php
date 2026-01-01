<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Institution extends Model
{
    /** @use HasFactory<\Database\Factories\InstitutionFactory> */
    use HasFactory, SoftDeletes, HasTranslations;

    protected $fillable = ['name'];

    public $translatable = ['name'];

    protected $casts = [
        'name' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relations

    public function colleges() {
        return $this->hasMany(College::class);
    }

    // إذا أردت الوصول للتخصصات عبر الكليات (HasManyThrough)
    public function majors() {
        return $this->hasManyThrough(Major::class, College::class); 
        // ملاحظة: هذا يتطلب تعديل بسيط إذا كنت تستخدم جدول وسيط
    }

    public function trainees(): HasMany
    {
        return $this->hasMany(Trainees::class, 'institution_id');
    }
}
