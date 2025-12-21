<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Administratives extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'administratives';
    protected $fillable = [
        'title',
        'hoa',
        'is_medical',
        'medical_hoa',
    ];
    protected $casts = [
        'is_medical' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function hoaUser()
    {
        return $this->belongsTo(User::class, 'hoa');
    }

    public function medicalHoaUser()
    {
        return $this->belongsTo(User::class, 'medical_hoa');
    }

    public function sections()
    {
        return $this->hasMany(Sections::class, 'administrative_id');
    }
}
