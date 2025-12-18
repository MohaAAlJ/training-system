<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Administrative extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'administratives';

    protected $fillable = [
        'name',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user associated with this administrative.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all sections under this administrative.
     */
    public function sections()
    {
        return $this->hasMany(Sections::class, 'Administrative_id');
    }

    /**
     * Get all departments under this administrative.
     */
    public function departments()
    {
        return $this->hasMany(Departments::class, 'administrative_id');
    }
}
