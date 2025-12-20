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

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = ['name', 'email', 'password', 'role', 'status'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = [
        'role' => 'integer',
        'password' => 'hashed',
    ];


    public function administrative(): HasOne
    {
        return $this->hasOne(Administratives::class, 'user_id');
    }

    public function department(): HasOne
    {
        return $this->hasOne(Departments::class, 'user_id');
    }

    public function college(): HasOne
    {
        return $this->hasOne(College::class, 'user_id');
    }

    public function trainees()
    {
        return $this->college()?->trainees();
    }


    public function isAdmin(): bool
    {
        return $this->role === Constans::ROLE_SYSTEM_ADMIN;
    }

    public function isAdministrative(): bool
    {
        return $this->role === Constans::ROLE_DEPARTMENT_MANAGER;
    }

    public function isDepartmentHead(): bool
    {
        return $this->role === Constans::ROLE_DEPARTMENT_MANAGER;
    }

    public function isCollegeSupervisor(): bool
    {
        return $this->role === Constans::ROLE_COLLEGE_SUPERVISOR;
    }

    pu

    public function isMinistry(): bool
    {
        return $this->role === Constans::ROLE_MOH;
    }

    public function isMedicalManager(): bool
    {
        return $this->isAdministrative() && $this->administrative?->is_medical === true;
    }

    public function isGeneralTrainingManager(): bool
    {
        return $this->isAdministrative() && $this->administrative?->is_medical === false;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->status === 'active';
    }

    public function getRoleLabelAttribute(): string
    {
        return Constans::ROLE_LABELS[$this->role] ?? 'غير محدد';
    }
}
