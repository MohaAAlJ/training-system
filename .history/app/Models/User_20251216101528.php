<?php

namespace App\Models;

use App\Helpers\Constans;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'status'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['role' => 'integer'];

    // --- العلاقات (للوصول للبيانات المرتبطة بالمستخدم) ---

    // 1. علاقة مدير الإدارة (Administrative)
    public function administrative(): HasOne
    {
        return $this->hasOne(Administratives::class, 'user_id');
    }

    // 2. علاقة رئيس القسم (Department)
    public function department(): HasOne
    {
        return $this->hasOne(Departments::class, 'user_id');
    }

    // 3. علاقة مشرف الكلية (College)
    public function college(): HasOne
    {
        return $this->hasOne(College::class, 'user_id');
    }

    // --- دوال فحص الصلاحيات (Helpers) ---

    public function isAdmin(): bool
    {
        return $this->role === Constans::ROLE_ADMIN;
    }

    public function isAdministrative(): bool
    {
        return $this->role === Constans::ROLE_ADMINISTRATIVE;
    }

    public function isDepartmentHead(): bool
    {
        return $this->role === Constans::ROLE_DEPARTMENT;
    }

    public function isCollegeSupervisor(): bool
    {
        return $this->role === Constans::ROLE_COLLEGE;
    }

    public function isMinistry(): bool
    {
        return $this->role === Constans::ROLE_MOH;
    }

    // السماح بالدخول للوحة التحكم
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->status === 'active';
    }
}