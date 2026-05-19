<?php

namespace App\Models;

use App\Enums\GeneralConst;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Department;
use App\Models\Section;
use App\Models\College;
use App\Models\Administrative;
use Lab404\Impersonate\Models\Impersonate;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Support\Facades\Storage;

use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, Impersonate;

    // =========================================================================
    // CONSTANTS: ROLES
    // =========================================================================
    public const ROLE_ADMIN = 1;
    public const ROLE_DEPARTMENT = 2;
    public const ROLE_SECTION = 3;
    public const ROLE_MOH = 4;
    public const ROLE_COLLEGE = 5;
    public const ROLE_HOA = 6;
    public const ROLE_HOM = 7;
    public const ROLE_GTM = 8;
    public const ROLE_MONITOR = 9;
    public const ROLE_ASSISTANT_TRAINING_MANAGER = 10;
    public const ROLE_LABELS = [
        self::ROLE_ADMIN => 'مدير النظام',
        self::ROLE_DEPARTMENT => 'مدير الدائرة',
        self::ROLE_SECTION => 'مسؤول قسم',
        self::ROLE_MOH => 'وزارة الصحة',
        self::ROLE_COLLEGE => 'مشرف كلية',
        self::ROLE_HOA => 'المدير الإداري',
        self::ROLE_HOM => 'المدير الطبي',
        self::ROLE_GTM => 'مدير التدريب',
        self::ROLE_MONITOR => 'المشرف العام',
        self::ROLE_ASSISTANT_TRAINING_MANAGER => 'مساعد مدير التدريب',
    ];

    // =========================================================================
    // SETUP
    // =========================================================================

    protected $fillable = [
        'id',
        'user_name',
        'name',
        'email',
        'phone_number',
        'password',
        'role',
        'active',
        'avatar_url',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $casts = [
        'password' => 'hashed',
        'last_login_at' => 'datetime',
    ];

    public function getFilamentAvatarUrl(): ?string
    {
        $avatarColumn = config('filament-edit-profile.avatar_column', 'avatar_url');
        return $this->$avatarColumn ? Storage::url($this->$avatarColumn) : null;
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function department(): HasOne
    {
        return $this->hasOne(Department::class);
    }

    public function mohDepartment(): HasOne
    {
        return $this->hasOne(Department::class, 'moh_dept_user_id');
    }

    public function section(): HasOne
    {
        return $this->hasOne(Section::class);
    }

    public function college(): HasOne
    {
        return $this->hasOne(College::class);
    }

    public function administrative(): HasOne
    {
        return $this->hasOne(Administrative::class);
    }

    /**
     * Relationship for Administrative Medical Head.
     */
    public function administrativeMedicalHead(): HasOne
    {
        return $this->hasOne(Administrative::class, 'medical_head_user_id');
    }

    public function managedDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'assistant_training_manager_id');
    }

    public function trainees()
    {
        return $this->college?->trainees();
    }

    public function receivedMessages(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Mailbox::class, 'mailbox_user')
            ->withPivot('read_at', 'is_deleted')
            ->wherePivot('is_deleted', false)
            ->withTimestamps();
    }

    public function sentMessages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Mailbox::class, 'sender_id');
    }

    public function unreadMessagesCount(): int
    {
        return $this->receivedMessages()->wherePivotNull('read_at')->count();
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getRoleLabelAttribute(): string
    {
        return self::ROLE_LABELS[$this->role] ?? 'غير محدد';
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('active', GeneralConst::ACTIVE);
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
            $q->whereDoesntHave('department')
                ->whereDoesntHave('section')
                ->whereDoesntHave('college')
                ->whereDoesntHave('administrative')
                ->whereDoesntHave('administrativeMedicalHead');

            if ($currentUserId) {
                $q->orWhere('id', $currentUserId);
            }
        });
    }

    // =========================================================================
    // HELPERS: ROLES
    // =========================================================================

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isDepartment(): bool
    {
        return $this->role === self::ROLE_DEPARTMENT;
    }

    public function isMohDepartmentHead(): bool
    {
        return $this->role == self::ROLE_MOH;
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
        return $this->role == self::ROLE_MOH;
    }

    public function isMedicalManager(): bool
    {
        return $this->role === self::ROLE_HOM;
    }

    public function isGeneralTrainingManager(): bool
    {
        return $this->role === self::ROLE_GTM;
    }

    public function isAssistantTrainingManager(): bool
    {
        return $this->role === self::ROLE_ASSISTANT_TRAINING_MANAGER;
    }

    public function isTrainingManagerLike(): bool
    {
        return $this->isGeneralTrainingManager() || $this->isAssistantTrainingManager();
    }

    public function isMonitor(): bool
    {
        return $this->role === self::ROLE_MONITOR;
    }

    public function managedDepartmentIds(): array
    {
        if (! $this->isAssistantTrainingManager()) {
            return [];
        }

        // Fetch managed departments, limited to active and visible departments.
        $departments = $this->managedDepartments()
            ->active()
            ->visible()
            ->get(['id']);

        return $departments
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->all();
    }

    public function canManageApplication(Application $application): bool
    {
        if ($this->isGeneralTrainingManager() || $this->isAdmin()) {
            return true;
        }

        if (! $this->isAssistantTrainingManager()) {
            return false;
        }

        $managedDepartmentIds = $this->managedDepartmentIds();
        if (empty($managedDepartmentIds)) {
            return false;
        }

        $applicationDepartmentIds = $application->section?->departments
            ? $application->section->departments->pluck('id')->map(fn($id) => (int) $id)->all()
            : $application->section?->departments()->pluck('departments.id')->map(fn($id) => (int) $id)->all();

        return ! empty(array_intersect($managedDepartmentIds, $applicationDepartmentIds ?? []));
    }

    // =========================================================================
    // FILAMENT / PANEL ACCESS
    // =========================================================================

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->active == GeneralConst::ACTIVE;
    }

    /**
     * Determine if the user can impersonate other users.
     * Only superadmin (ROLE_ADMIN) can impersonate.
     */
    public function canImpersonate(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Determine if the user can be impersonated.
     * Allow impersonating any active user except the superadmin himself.
     */
    public function canBeImpersonated(): bool
    {
        return $this->active == true && !$this->isAdmin();
    }

    // =========================================================================
    // STATIC HELPERS
    // =========================================================================

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
                    $sq->has('department')
                        ->orHas('section')
                        ->orHas('college')
                        ->orHas('administrative')
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

    // =========================================================================
    // BOOT & EVENTS
    // =========================================================================

    protected static function booted()
    {
        parent::booted();

        static::created(function (self $user) {
            // try {
            //     app(\App\Services\Telegram\TelegramMonitorService::class)->handleUserCreated($user);
            // } catch (\Throwable $e) {}
        });

        static::updated(function (self $user) {
            // try {
            //     app(\App\Services\Telegram\TelegramMonitorService::class)->handleUserUpdated($user);
            // } catch (\Throwable $e) {}
        });

        static::deleted(function (self $user) {
            // try {
            //     app(\App\Services\Telegram\TelegramMonitorService::class)->handleUserDeleted($user);
            // } catch (\Throwable $e) {}
        });
    }
}
