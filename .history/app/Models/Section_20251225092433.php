<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'sections';
    protected $fillable = [
        'id',
        'name_location',
        'status',
        'total_capacity',
        'current_capacity',
        'department_id',
        'user_id',
        'administrative_id',
        'governorate_id',
    ];
    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Scope a query to only include active sections.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    // Accessor to convert status string to boolean for ToggleColumn
    protected function getStatusAttribute($value)
    {
        return $value === 'active';
    }

    // Mutator to convert boolean back to status string
    protected function setStatusAttribute($value)
    {
        $this->attributes['status'] = is_bool($value) ? ($value ? 'active' : 'inactive') : $value;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function administrative()
    {
        return $this->belongsTo(Administrative::class, 'administrative_id');
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class, 'governorate_id');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'section_id');
    }

    /**
     * Get capacity statistics for this section.
     */
    public function getCapacityStats(): array
    {
        $used = $this->applications()
            ->where('status', Application::STATUS_STRATED_TRAINING)
            ->count();

        $total = (int) ($this->total_capacity ?? 0);
        $available = max(0, $total - $used);

        return [
            'total' => $total,
            'used' => $used,
            'available' => $available,
        ];
    }

    /**
     * Check if the section is full.
     */
    public function isFull(): bool
    {
        return $this->getCapacityStats()['available'] <= 0;
    }
}
