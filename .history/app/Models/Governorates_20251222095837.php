<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Governorates extends Model
{
    use HasFactory;

    protected $table = 'governorates';

    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'name' => 'array',
    ];
}