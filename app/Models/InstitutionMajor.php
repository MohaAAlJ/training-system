<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class InstitutionMajor extends Model
{
    /** @use HasFactory<\Database\Factories\InstitutionMajorFactory> */
    use HasFactory, SoftDeletes, HasTranslations;

    public $translatable = ['name'];

    /** Relations */

    public function institutionDepartment(): BelongsTo
    {
        return $this->belongsTo(InstitutionDepartment::class);
    }

    public function studentPersonals(): HasMany
    {
        return $this->hasMany(StudentPersonal::class);
    }
}
