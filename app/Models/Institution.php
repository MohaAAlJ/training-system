<?php

namespace App\Models;

use App\Enums\GeneralConst;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    /** @use HasFactory<\Database\Factories\InstitutionFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['id', 'name', 'active', 'add_application'];

    protected $casts = [
 
    ];

    
    /**Scope */
    public function scopeActive($query)
    {
        return $query->where('active', GeneralConst::ACTIVE);
    }

    /** Relations */

    public function colleges()
    {
        return $this->hasMany(College::class);
    }

    public function majors()
    {
        return $this->hasManyThrough(
            Major::class,
            College::class,
            'institution_id',
            'college_id'
        );
    }

    public function trainees(): HasMany
    {
        return $this->hasMany(Trainee::class, 'institution_id');
    }

}
