<?php

namespace App\Models;

use App\Enums\GeneralConst;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Administrative extends Model
{
    use SoftDeletes, HasFactory;

    // =========================================================================
    // SETUP
    // =========================================================================

    protected $table = 'administratives';

    protected $fillable = [
        'id',
        'name',
        'moh_facility_id',
        'address',
        'user_id',
        'is_medical',
        'medical_head_user_id',
        'governorate_id',
        'active',
    ];

    protected $casts = [];



    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Get the user associated with this administrative.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    /**
     * Get all Section under this administrative.
     */
    public function sections()
    {
        return $this->hasMany(Section::class, 'administrative_id');
    }

    /**
     * Get all Applications for this administrative unit (through sections).
     * Since applications no longer have administrative_id, we go through sections.
     */
    public function applications()
    {
        return $this->hasManyThrough(
            Application::class,
            Section::class,
            'administrative_id', // Foreign key on sections table
            'section_id',        // Foreign key on applications table
            'id',                // Local key on administratives table
            'id'                 // Local key on sections table
        );
    }

    /**
     * Compatibility relationship for Filament forms/tables expecting `medicalHead`.
     * Maps to the `medical_head_user_id` foreign key (points to `users.id`).
     */
    public function medicalHead()
    {
        return $this->belongsTo(User::class, 'medical_head_user_id');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope a query to only include active Administrative units.
     */
    public function scopeActive($query)
    {
        return $query->where('active', GeneralConst::ACTIVE);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    /**
     * Get title with governorate name (from Section)
     */
    public function getNameWithGovernorateAttribute(): string
    {
        $govName = $this->governorate?->name;

        return $this->name . ($govName ? " - {$govName}" : '');
    }

    // =========================================================================
    // CAPACITY METHODS
    // =========================================================================

    /**
     * Get capacity statistics for this administrative unit.
     *
     * @return array{total: int, used: int, available: int, is_full: bool}
     */
    public function getCapacityStats(): array
    {
        return Section::getStatsFromQuery($this->sections()->active());
    }

    /**
     * Check if at full capacity (cannot accept new applications).
     *
     * @return bool
     */
    public function isFull(): bool
    {
        return $this->getCapacityStats()['is_full'];
    }

    /**
     * Check if has available capacity (can accept new applications).
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return !$this->isFull();
    }

    // =========================================================================
    // BOOT & EVENTS
    // =========================================================================

    protected static function booted(): void
    {
        static::updated(function (Administrative $administrative) {
            if ($administrative->wasChanged('active') && !$administrative->active) {
                // If administrative is disabled, disable all its sections
                $administrative->sections()->update(['active' => false]);
            }
        });
    }
}
