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
        'governorate_id',
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

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    /**
     * Get all Section under this administrative.
     */
    public function Section()
    {
        return $this->hasMany(Section::class, 'administrative_id');
    }

    /**
     * Get all Application for this administrative unit.
     */
    public function Application()
    {
        return $this->hasMany(Application::class, 'administrative_id');
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
     * Get title with governorate name (from Section)
     */
    public function getNameWithGovernorateAttribute(): string
    {
        $govName = Governorate::get

        GetGovName
        if (is_array($govName)) {
            $govName = $govName['ar'] ?? reset($govName);
        }

        return $this->title . ($govName ? " - {$govName}" : '');
    }

    /**
     * Get capacity statistics for this administrative unit by summing its sections.
     * Returns: total, used, available, is_full
     */
    public function getCapacityStats(): array
    {
        // Sum total capacity from all sections
        $total = (int) $this->Section()->sum('total_capacity');

        // Count all active applications in this administrative unit
        $used = Application::where('administrative_id', $this->id)
            ->where('status', Application::STATUS_STARTED_TRAINING)
            ->count();

        $available = max(0, $total - $used);

        return [
            'total' => $total,
            'used' => $used,
            'available' => $available,
            'is_full' => $available <= 0
        ];
    }
}
