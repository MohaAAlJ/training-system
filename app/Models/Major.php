<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Major extends Model
{
    /** @use HasFactory<\Database\Factories\MajorFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['id', 'name'];

    /** Relations */

    public function institutions(): BelongsToMany
    {
        return $this->belongsToMany(Institution::class);
    }

    public function colleges(): BelongsToMany
    {
        return $this->belongsToMany(College::class);
    }

    public function trainees(): HasMany
    {
        return $this->hasMany(Trainee::class);
    }
}
