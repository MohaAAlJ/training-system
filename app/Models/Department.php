<?php

namespace App\Models;

use App\Enums\GeneralConst;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'departments';
    protected $fillable = [
        'id',
        'name',
        'user_id',
        'is_medical',
        'active',
    ];
    protected $casts = [
    ];

    /**Scope */
    public function scopeActive($query)
    {
        return $query->where('active', GeneralConst::ACTIVE);
    }

    /** Relations */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'department_id');
    }

    public function sections()
    {
        return $this->hasMany(Section::class, 'department_id');
    }

    /**
     * Get capacity statistics for this department by summing its sections.
     * Returns: total, used, available, is_full
     */
    public function getCapacityStats(): array
    {
        // Sum total capacity from all active sections
        $total = (int) $this->sections()->active()->sum('capacity');

        // Count only active (started training) applications
        $used = Application::whereIn('section_id', $this->sections()->select('id'))
            ->where('status', Application::STATUS_STARTED_TRAINING)
            ->count();

        $available = max(0, $total - $used);

        return [
            'total' => $total,
            'used' => $used,
            'available' => $available,
            'is_full' => $total > 0 && $available <= 0
        ];
    }
}
