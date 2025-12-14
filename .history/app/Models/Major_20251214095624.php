<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Major extends Model
{
    /** @use HasFactory<\Database\Factories\MajorFactory> */
    use HasFactory, SoftDeletes, HasTranslations;

    protected $fillable = ['name', 'code'];
    public $translatable = ['name'];

    /** Relations */

    public function colleges(): BelongsToMany
    {
        return $this->belongsToMany(College::class, 'college_major');
    }

    public function trainees(): HasMany
    {
        return $this->hasMany(Trainees::class, 'major_id');
    }
}
