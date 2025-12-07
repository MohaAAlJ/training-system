<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
class Departments extends Model
{
    use SoftDeletes, HasFactory;
    
    protected $table = 'departments';
    protected $fillable = [
        'name_location',
        'status',
        'total_capacity',
        'user_id',
        'administrative_id',
    ];
    protected $casts = [
        'status' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function administrative()
    {
        return $this->belongsTo(Administratives::class, 'administrative_id');
    }

    public function applications()
    {
        return $this->hasMany(Applications::class, 'department_id');
    }
}
