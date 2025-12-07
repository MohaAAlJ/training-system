<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Departments extends Model
{
    use SoftDeletes,HasFactory;
    protected $table = "departments";
    protected $fillable = [
        'id',
        'name',
        'description',
        'status',
        'head_of_department',
        'location',
        'administrative_id',
        'total_capacity',
        'available_capacity',
        'total_trainees',

    ];
    public function departments(): BelongsTo
{
    return $this->belongsTo(User::class);
}

}
