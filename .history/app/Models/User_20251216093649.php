<?php

namespace App\Models;

use App\Helpers\Constans; // استدعاء ملف الثوابت
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'integer', // ضمان التعامل مع الدور كرقم
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Filament Access Control
    |--------------------------------------------------------------------------
    */
    public function canAccessPanel(Panel $panel): bool
    {
        // السماح بالدخول فقط إذا كان الحساب فعالاً
        return $this->status === 'active';
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships (العلاقات)
    |--------------------------------------------------------------------------
    | هذه العلاقات تربط المستخدم بموقعه الإداري
    */

    /**
     * علاقة المستخدم كمدير مديرية (إداري)
     */
    public function administrative(): HasOne
    {
        return $this->hasOne(Administrative::class, 'user_id');
    }

    /**
     * علاقة المستخدم كرئيس قسم
     */
    public function department(): HasOne
    {
        return $this->hasOne(Department::class, 'user_id');
    }

    /**
     * علاقة المستخدم كمشرف كلية
     */
    public function college(): HasOne
    {
        return $this->hasOne(College::class, 'user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods (دوال الفحص)
    |--------------------------------------------------------------------------
    | هذه الدوال سنستخدمها بكثرة داخل الـ Policies
    */

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

    public function isMinistryEmployee(): bool
    {
        return $this->role === Constans::ROLE_MOH;
    }

    /**
     * فحص ذكي: هل هذا المستخدم هو "مدير طبي"؟
     * الشرط: رتبته إداري + المديرية المربوط بها هي "إدارة طبية"
     */
    public function isMedicalManager(): bool
    {
        // يجب أن يكون إدارياً أولاً، ولديه مديرية مرتبطة، وتلك المديرية طبية
        return $this->isAdministrative() && 
               $this->administrative()->exists() && 
               $this->administrative->is_medical;
    }

    /**
     * فحص ذكي: هل هذا المستخدم هو "مدير تدريب عام" (غير طبي)؟
     */
    public function isGeneralManager(): bool
    {
        return $this->isAdministrative() && 
               $this->administrative()->exists() && 
               !$this->administrative->is_medical;
    }
}