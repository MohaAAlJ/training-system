<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramSubscriber extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'name',
        'username',
        'is_active',
        'wants_activities',
        'wants_errors',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'wants_activities' => 'boolean',
        'wants_errors' => 'boolean',
    ];
}
