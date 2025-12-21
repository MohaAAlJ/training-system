<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sections extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'sections';

    protected $fillable = [
        'name_location',
        'administrative_id',
        'department_id',
        'governorate_id',
        'hos',
        'total_capacity',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean', // In SQL it is tinyint(4) default 1, typically mapped to boolean or integer. Adjusting logic to match typical filament toggle.
    ];

    public function administrative()
    {
        return $this->belongsTo(Administratives::class, 'administrative_id');
    }

    public function department()
    {
        return $this->belongsTo(Departments::class, 'department_id');
    }

    public function governorate()
    {
        return $this->belongsTo(Governorates::class, 'governorate_id');
    }

    public function hosUser()
    {
        return $this->belongsTo(User::class, 'hos');
    }

    public function applications()
    {
        return $this->hasMany(Applications::class, 'section_id');
    }
}
