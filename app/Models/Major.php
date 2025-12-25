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

    protected $fillable = ['id','name', 'code'];

    /** Relations */

    public function institutions(): BelongsToMany
    {
        return $this->belongsToMany(Institution::class, 'institution_major');
    }

    public function colleges(): BelongsToMany
    {
        return $this->belongsToMany(College::class, 'college_major');
    }

    public function Trainee(): HasMany
    {
        return $this->hasMany(Trainee::class, 'major_id');
    }


}





