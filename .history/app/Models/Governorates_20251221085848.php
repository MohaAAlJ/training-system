<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Governorates extends Model
{
    use HasTranslations;

    protected $table = 'governorates';
    protected $fillable = ['name'];
    public $translatable = ['name'];

    protected $casts = [
        'name' => 'array',
    ];
}
