<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Departments extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'departments';
    protected $fillable = [
        'title',
        'user_id',
        'head_of_department',
        'medical_head_user_id',
        'is_medical',
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function medicalHead()
    {
        return $this->belongsTo(User::class, 'medical_head_user_id');
    }

    public function departments()
    {
        return $this->hasMany(Departments::class, 'department_id');
    }

    public function applications()
    {
        return $this->hasMany(Applications::class, 'department_id');
    }
}
