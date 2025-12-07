<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trainees extends Model
{
    use SoftDeletes, HasFactory;
    
    protected $table = 'trainees';
    protected $fillable = [
        'national_id',
        'full_name',
        'phone_number',
        'dob',
        'location',
        'institution_id',
    ];
    protected $casts = [
        'dob' => 'date',
    ];

    /** Relations */

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }

    public function majors(): BelongsToMany
    {
        return $this->belongsToMany(Major::class, 'trainee_major', 'trainee_id', 'major_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Applications::class, 'trainee_id');
    }
}
