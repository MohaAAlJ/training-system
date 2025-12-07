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
        'head_of_administrative',
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function departments()
    {
        return $this->hasMany(Departments::class, 'administrative_id');
    }
}
