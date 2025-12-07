<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class InstitutionDepartment extends Model
{
    /** @use HasFactory<\Database\Factories\InstitutionDepartmentFactory> */
    use HasFactory, SoftDeletes, HasTranslations;

    public $translatable = ['name'];

    /** Relations */

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function institutionMajors(): HasMany
    {
        return $this->hasMany(InstitutionMajor::class);
    }
    
    public function studentPersonals(): HasMany
    {
        return $this->hasMany(StudentPersonal::class);
    }
}
