<?php

namespace App\Models;

use App\Models\Application;

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
        'department_id',
        'user_id',
        'administrative_id',
        'governorate_id',
    ];
    protected $casts = [
        'status' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::updating(function (Section $section) {
            if ($section->isDirty('total_capacity')) {
                $used = \App\Models\Application::where('section_id', $section->id)
                    ->where('status', \App\Models\Application::STATUS_STARTED_TRAINING)
                    ->count();

                if ($section->total_capacity < $used) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'total_capacity' => "لا يمكن تقليل السعة الكلية ({$section->total_capacity}) عن العدد المستخدم حالياً ({$used}).",
                    ]);
                }
            }
        });
    }

    /**
     * Scope a query to only include active Section.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', true);
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

    public function Application()
    {
        return $this->hasMany(Application::class, 'section_id');
    }

    /**
     * Get capacity statistics for this section.
     * Returns: total, used, available, is_full
     */
    public function getCapacityStats(): array
    {
        $total = (int) ($this->total_capacity ?? 0);
        $used = Application::where('section_id', $this->id)
            ->where('status', Application::STATUS_STARTED_TRAINING)
            ->count();
        $available = max(0, $total - $used);

        return [
            'total' => $total,
            'used' => $used,
            'available' => $available,
            'is_full' => $available <= 0,
        ];
    }
}
