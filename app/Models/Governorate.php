<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Governorate extends Model
{
    use HasFactory;

    protected $table = 'governorates';

    protected $fillable = [
        'name_en',
    ];

    /**
     * Get the Arabic name of the governorate from translations.
     */
    public function getNameArAttribute(): ?string
    {
        return __('translation.governorates.' . $this->id);
    }

    /**
     * Get all governorates with their Arabic names.
     */
    public static function getWithArNames()
    {
        return self::all()->mapWithKeys(function ($gov) {
            return [$gov->id => $gov->name_ar];
        });
    }
}
