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
        'total_capacity',
        'current_capacity',
        'location',
    ];
    protected $casts = [
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

    public function Application()
    {
        return $this->hasMany(Application::class, 'department_id');
    }

    public function Section()
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

    /**
     * Get capacity statistics for this department by summing its Section.
     */
    public function getCapacityStats(): array
    {
        $stats = [
            'total' => 0,
            'used' => 0,
            'available' => 0,
        ];

        foreach ($this->Section as $section) {
            $sStats = $section->getCapacityStats();
            $stats['total'] += $sStats['total'];
            $stats['used'] += $sStats['used'];
            $stats['available'] += $sStats['available'];
        }

        return $stats;
    }

    /**
     * Check if the department is full.
     */
    public function isFull(): bool
    {
        return $this->getCapacityStats()['available'] <= 0;
    }
}
