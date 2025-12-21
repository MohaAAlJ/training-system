<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Governorate extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'governorates';

    protected $fillable = [
        'name',
    ];

    public $translatable = ['name'];

    /**
     * Get the Arabic name of the governorate.
     */
    public function getNameArAttribute(): ?string
    {
        return $this->getTranslation('name', 'ar');
    }
}
