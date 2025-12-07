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

        protected $fillable = ['institution_id', 'name', 'code'];
        public $translatable = ['name'];    /** Relations */

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }

    public function trainees(): HasMany
    {
        return $this->hasMany(Trainees::class, 'institution_major_id');
    }
}
