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
        'user_id',
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
        return $this->hasMany(Departments::class, 'administrative_id');
    }
}
