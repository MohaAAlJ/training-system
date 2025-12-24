<?php

namespace App\Models;

use App\Helpers\Constans;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Departments;
use App\Models\Sections;
use App\Models\College;
use App\Models\Administrative;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = ['id', 'name', 'email', 'password', 'role', 'status'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = [
        'role' => 'integer',
        'password' => 'hashed',
    ];


    public function department(): HasOne
    {
        return $this->hasOne(Departments::class, 'user_id');
    }

    public function sections(): HasOne
    {
        return $this->hasOne(Sections::class, 'user_id');
    }

    public function college(): HasOne
    {
        return $this->hasOne(College::class, 'user_id');
    }

    public function administrative(): HasOne
    {
        return $this->hasOne(Administrative::class, 'user_id');
    }

    public function trainees()
    {
        return $this->college()?->trainees();
    }


    public function isAdmin(): bool
    {
        return $this->role === Constans::ROLE_ADMIN;
    }

    public function isDepartment(): bool
    {
        return $this->role === Constans::ROLE_DEPARTMENT;
    }

    public function isDepartmentHead(): bool
    {
        return $this->isDepartment();
    }

    public function isAdministrative(): bool
    {
        return $this->role === Constans::ROLE_HOA;
    }

    public function isSectionHead(): bool
    {
        return $this->role === Constans::ROLE_SECTION;
    }

    public function isCollegeSupervisor(): bool
    {
        return $this->role === Constans::ROLE_COLLEGE;
    }

    public function isHOA(): bool
    {
        return $this->role === Constans::ROLE_HOA;
    }

    public function isMinistry(): bool
    {
        return $this->role === Constans::ROLE_MOH;
    }

    public function isMedicalManager(): bool
    {
        return $this->role === Constans::ROLE_HOM;
    }

    public function isGeneralTrainingManager(): bool
    {
        return $this->role === Constans::ROLE_GTM;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->status === 'active';
    }

    public function getRoleLabelAttribute(): string
    {
        return Constans::ROLE_LABELS[$this->role] ?? 'غير محدد';
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
                ->whereDoesntHave('sections')
                ->whereDoesntHave('college')
                ->whereDoesntHave('administrative')
                ->whereDoesntHave('administrativeMedicalHead');

            if ($currentUserId) {
                $q->orWhere('id', $currentUserId);
            }
        });
    }
}
