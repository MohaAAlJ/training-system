<?php

namespace App\Models;

use App\Enums\GeneralConst;
use App\Models\Application;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
    use SoftDeletes, HasFactory;

    // =========================================================================
    // CONSTANTS
    // =========================================================================

    /**
     * Minimum capacity value allowed for sections.
     * Used for validation in admin forms.
     */
    public const CAPACITY_MIN = 1;

    // =========================================================================
    // MODEL SETUP
    // =========================================================================

    protected $table = 'sections';
    protected $fillable = [
        'id',
        'name',
        'capacity',
        'active',
        'user_id',
        'administrative_id',
        'department_id',
    ];
    protected $casts = [
        'capacity' => 'integer',
    ];



    protected static function booted(): void
    {
        static::updating(function (Section $section) {
            if ($section->isDirty('capacity')) {
                $used = Application::where('section_id', $section->id)
                    ->where('status', Application::STATUS_STARTED_TRAINING)
                    ->count();

                if ($section->capacity < $used) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'capacity' => "لا يمكن تقليل السعة الكلية ({$section->capacity}) عن العدد المستخدم حالياً ({$used}).",
                    ]);
                }
            }
        });
    }


    /**Scope */
    public function scopeActive($query)
    {
        return $query->where('active', GeneralConst::ACTIVE);
    }

    public function scopeWithRegisteredCount($query)
    {
        return $query->addSelect([
            'registered_count' => Application::query()
                ->selectRaw('COUNT(*)')
                ->whereColumn('section_id', 'sections.id')
                ->whereIn('status', [
                    Application::STATUS_STARTED_TRAINING,
                    Application::STATUS_ENDED_TRAINING,
                ]),
        ]);
    }

    /** Relations */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'department_section');
    }

    public function administrative()
    {
        return $this->belongsTo(Administrative::class);
    }
    public function applications()
    {
        return $this->hasMany(Application::class);
    }
    // =========================================================================
    // CAPACITY METHODS
    // =========================================================================

    /**
     * Calculate fresh capacity statistics for this specific section.
     *
     * @return array{total: int, used: int, available: int, is_full: bool}
     */
    /**
     * Calculate capacity statistics from a query builder.
     * Centralized logic used by Section, Administrative, and Department.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation $query
     * @return array{total: int, used: int, available: int, is_full: bool}
     */
    public static function getStatsFromQuery(\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation $query): array
    {
        // 1. Get IDs and Total Capacity
        // Clone query to avoid modifying the original reference for subsequent operations if needed
        $sections = $query->clone()->select(['sections.id', 'sections.capacity'])->get();

        $total = $sections->sum('capacity');
        $sectionIds = $sections->pluck('id');

        // 2. Count Active Applications
        $used = Application::whereIn('section_id', $sectionIds)
            ->where('status', Application::STATUS_STARTED_TRAINING)
            ->count();

        $available = max(0, $total - $used);

        return [
            'total' => $total,
            'used' => $used,
            'available' => $available,
            'is_full' => $total <= 0 || $available <= 0,
        ];
    }

    /**
     * Get capacity statistics for this specific section.
     *
     * @return array{total: int, used: int, available: int, is_full: bool}
     */
    public function getCapacityStats(): array
    {
        return self::getStatsFromQuery(self::query()->where('id', $this->id));
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

    /**
     * Check if this section has any capacity configured (capacity > 0).
     *
     * @return bool
     */
    public function hasCapacity(): bool
    {
        return ($this->capacity ?? 0) > 0;
    }
}
