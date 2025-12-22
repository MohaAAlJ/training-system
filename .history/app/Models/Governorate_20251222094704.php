<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Governorate extends Model
{
    use HasFactory;

    protected $table = 'governorates';

    protected $fillable = [
        'name',
    ];

    // هذا السطر مهم جداً لكي يقبل المودل مصفوفة ويحولها لـ JSON
    protected $casts = [
        'name' => 'array',
    ];
}