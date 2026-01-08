<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'departments';
    protected $fillable = [
        'id',
        'title',
        'user_id',
        'head_of_department',
        'medical_head_user_id',
        'is_medical',
        'status',
        'location',
    ];
    protected $casts = [
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function medicalHead()
    {
        return $this->belongsTo(User::class, 'medical_head_user_id');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'department_id');
    }

    public function sections()
    {
        return $this->hasMany(Section::class, 'department_id');
    }

    public function headOfDepartment()
    {
        return $this->belongsTo(User::class, 'head_of_department');
    }

    /**
     * Safely notify the head of department if set.
     */
    public function notifyHead($notification): void
    {
        $user = $this->headOfDepartment;

        if (! $user || ! ($user->id ?? null)) {
            return;
        }

        $user->notify($notification);
    }

    public function scopeMedical($query)
    {
        return $query->where('is_medical', true);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Get capacity statistics for this department by summing its sections.
     * Returns: total, used, available, is_full
     */
    public function getCapacityStats(): array
    {
        // Sum total capacity from all sections (excluding soft-deleted)
        $total = (int) $this->sections()->withoutTrashed()->sum('capacity');

        // Count only active (started training) applications
        $used = Application::where('department_id', $this->id)
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
