<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Department;
use App\Models\Section;
use App\Models\College;
use App\Models\Administrative;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = ['id', 'user_name', 'name', 'email', 'password', 'role', 'status'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = [
        'role' => 'integer',
        'status' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Status constants
     */
    public const STATUS_ACTIVE = true;
    public const STATUS_INACTIVE = false;

    /**
     * Role constants
     */
    public const ROLE_ADMIN = 1;
    public const ROLE_DEPARTMENT = 2;
    public const ROLE_SECTION = 3;
    public const ROLE_MOH = 4;
    public const ROLE_COLLEGE = 5;
    public const ROLE_HOA = 6;
    public const ROLE_HOM = 7;
    public const ROLE_GTM = 8;

    public const ROLE_LABELS = [
        self::ROLE_ADMIN => 'مدير النظام',
        self::ROLE_DEPARTMENT => 'إداري',
        self::ROLE_SECTION => 'رئيس قسم',
        self::ROLE_MOH => 'وزارة الصحة',
        self::ROLE_COLLEGE => 'مشرف كلية',
        self::ROLE_HOA => 'رئيس الإدارة',
        self::ROLE_HOM => 'رئيس الطب',
        self::ROLE_GTM => 'مدير التدريب العام',
    ];

    /*
    public const STATUS_NEW = 1;
    public const STATUS_INITIAL_APPROVE = 2;
    public const STATUS_CONFIRMATION = 3;
    public const STATUS_WAITING_LIST = 4;
    public const STATUS_STRATED_TRAINING = 5;
    public const STATUS_ENDED_TRAINING = 6;
    public const STATUS_REJECTED = 7;
    public const STATUS_DROPPED = 8;
    public const STATUS_UNKNOWN = 9;
*/
    public function Department(): HasOne
    {
        return $this->hasOne(Department::class, 'user_id');
    }

    public function Section(): HasOne
    {
        return $this->hasOne(Section::class, 'user_id');
    }

    public function College(): HasOne
    {
        return $this->hasOne(College::class, 'user_id');
    }

    public function Administrative(): HasOne
    {
        return $this->hasOne(Administrative::class, 'user_id');
    }

    public function trainees()
    {
        return $this->College?->trainees();
    }

    /**
     * Relationship for Administrative Medical Head.
     */
    public function administrativeMedicalHead(): HasOne
    {
        return $this->hasOne(Administrative::class, 'medical_head_user_id');
    }


    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isDepartment(): bool
    {
        return $this->role === self::ROLE_DEPARTMENT;
    }

    public function isDepartmentHead(): bool
    {
        return $this->isDepartment();
    }

    public function isAdministrative(): bool
    {
        return $this->role === self::ROLE_HOA;
    }

    public function isSectionHead(): bool
    {
        return $this->role === self::ROLE_SECTION;
    }

    public function isCollegeSupervisor(): bool
    {
        return $this->role === self::ROLE_COLLEGE;
    }

    public function isHOA(): bool
    {
        return $this->role === self::ROLE_HOA;
    }

    public function isMinistry(): bool
    {
        return $this->role === self::ROLE_MOH;
    }

    public function isMedicalManager(): bool
    {
        return $this->role === self::ROLE_HOM;
    }

    public function isGeneralTrainingManager(): bool
    {
        return $this->role === self::ROLE_GTM;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->status === 'active';
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLE_LABELS[$this->role] ?? 'غير محدد';
    }

    /**
     * Scope a query to only include users who are not assigned as heads.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $currentUserId The ID of the user currently assigned to the record being edited.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFree($query, $currentUserId = null)
    {
        return $query->where(function ($q) use ($currentUserId) {
            $q->whereDoesntHave('Department')
                ->whereDoesntHave('Section')
                ->whereDoesntHave('College')
                ->whereDoesntHave('Administrative')
                ->whereDoesntHave('administrativeMedicalHead');

            if ($currentUserId) {
                $q->orWhere('id', $currentUserId);
            }
        });
    }

    /**
     * Get ordered options for head selection.
     */
    public static function getHeadOptions($role, $currentUserId = null)
    {
        $options = [];

        // 1. Current user if editing
        if ($currentUserId) {
            $currentUser = self::find($currentUserId);
            if ($currentUser) {
                $options[$currentUser->id] = $currentUser->name . ' (الحالي)';
            }
        }

        // 2. Free users (excluding current)
        $freeUsers = self::where('role', $role)
            ->free()
            ->where('id', '!=', $currentUserId ?? 0)
            ->orderBy('name')
            ->get();

        foreach ($freeUsers as $user) {
            $options[$user->id] = $user->name;
        }

        // 3. Busy users
        $busyUsers = self::where('role', $role)
            ->where(function ($q) use ($currentUserId) {
                $q->where(function ($sq) {
                    $sq->has('Department')
                        ->orHas('Section')
                        ->orHas('College')
                        ->orHas('Administrative')
                        ->orHas('administrativeMedicalHead');
                })
                    ->where('id', '!=', $currentUserId ?? 0);
            })
            ->orderBy('name')
            ->get();

        foreach ($busyUsers as $user) {
            $options[$user->id] = $user->name . ' (مشغول)';
        }

        return $options;
    }
}
