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

        protected $fillable = ['name'];
        public $translatable = ['name'];    /** Relations */

    public function institutionMajors(): HasMany
    {
        return $this->hasMany(InstitutionMajor::class, 'institution_id');
    }

    public function trainees(): HasMany
    {
        return $this->hasMany(Trainees::class, 'institution_id');
    }

}
