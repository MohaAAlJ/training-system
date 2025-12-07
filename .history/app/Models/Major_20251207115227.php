<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Major extends Model
{
    /** @use HasFactory<\Database\Factories\MajorFactory> */
    use HasFactory, SoftDeletes, HasTranslations;

    protected $fillable = ['name', 'code'];
    public $translatable = ['name'];

    /** Relations */

    public function institutions(): BelongsToMany
    {
        return $this->belongsToMany(Institution::class, 'institution_major', 'major_id', 'institution_id');
    }

    public function trainees(): BelongsToMany
    {
        return $this->belongsToMany(Trainees::class, 'trainee_major', 'major_id', 'trainee_id');
    }
}
