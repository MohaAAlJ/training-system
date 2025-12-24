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
        'id',
        'title',
        'user_id',
        'is_medical',
        'medical_head_user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'is_medical' => 'boolean',
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
        return $this->hasMany(Sections::class, 'administrative_id');
    }



    /**
     * Compatibility relationship for Filament forms/tables expecting `medicalHead`.
     * Maps to the `medical_head_user_id` foreign key (points to `users.id`).
     */
    public function medicalHead()
    {
        return $this->belongsTo(User::class, 'medical_head_user_id');
    }

    /**
     * Get title with governorate name (from sections)
     */
    public function getNameWithGovernorateAttribute(): string
    {
        $section = $this->sections()->with('governorate')->first();
        $govName = $section?->governorate?->name;

        if (is_array($govName)) {
            $govName = $govName['ar'] ?? reset($govName);
        }

        return $this->title . ($govName ? " - {$govName}" : '');
    }
}
