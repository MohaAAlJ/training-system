<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Departments extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'departments';
    protected $fillable = [
        'title',
        'is_medical',
        'hod',
    ];
    protected $casts = [
        'is_medical' => 'boolean',
    ];

    public function hodUser()
    {
        return $this->belongsTo(User::class, 'hod');
    }

    public function sections()
    {
        return $this->hasMany(Sections::class, 'department_id');
    }

    public function applications()
    {
        return $this->hasMany(Applications::class, 'department_id');
    }
}
