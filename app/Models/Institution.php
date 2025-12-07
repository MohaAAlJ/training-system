<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Institution extends Model
{
    /** @use HasFactory<\Database\Factories\InstitutionFactory> */
    use HasFactory, SoftDeletes, HasTranslations;

    public $translatable = ['name'];

    /** Relations */

    public function institutionDepartments(): HasMany
    {
        return $this->hasMany(InstitutionDepartment::class);
    }

    public function institutionMajors(): HasManyThrough
    {
        return $this->hasManyThrough(InstitutionMajor::class, InstitutionDepartment::class);
    }

    public function studentPersonals(): HasMany
    {
        return $this->hasMany(StudentPersonal::class);
    }

}
