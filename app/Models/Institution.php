<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    /** @use HasFactory<\Database\Factories\InstitutionFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['id','name'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relations

    public function colleges() {
        return $this->hasMany(College::class);
    }

    public function majors() {
        return $this->hasManyThrough(
            Major::class,
            College::class,
            'institution_id',
            'college_id'
        );
    }

    public function Trainee(): HasMany
    {
        return $this->hasMany(Trainee::class, 'institution_id');
    }
}





