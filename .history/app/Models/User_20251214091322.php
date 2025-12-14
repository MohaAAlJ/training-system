<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Role constants - stored as integers in database for easy extension
     */
    public const ROLE_ADMIN = 1;
    public const ROLE_ADMINISTRATIVE = 2;
    public const ROLE_DEPARTMENT = 3;
    public const ROLE_MOH = 4;
    public const ROLE_INSTITUTION = 5;

    /**
     * Role labels in Arabic
     */
    public const ROLE_LABELS = [
        self::ROLE_ADMIN => 'مدير النظام',
        self::ROLE_ADMINISTRATIVE => 'إداري',
        self::ROLE_DEPARTMENT => 'رئيس قسم',
        self::ROLE_MOH => 'وزارة الصحة',
        self::ROLE_INSTITUTION => 'مشرف كلي',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'role',
    ];

    /**
     * Check if user is admin (can see/edit everything)
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is administrative (can see/edit everything)
     */
    public function isAdministrative(): bool
    {
        return $this->role === self::ROLE_ADMINISTRATIVE;
    }

    /**
     * Check if user is department head (can see/edit only their department)
     */
    public function isDepartment(): bool
    {
        return $this->role === self::ROLE_DEPARTMENT;
    }

    /**
     * Check if user is MOH (can only view professional type applications)
     */
    public function isMOH(): bool
    {
        return $this->role === self::ROLE_MOH;
    }

    /**
     * Check if user is institution/college supervisor (can only view their faculty training requests)
     */
    public function isInstitution(): bool
    {
        return $this->role === self::ROLE_INSTITUTION;
    }

    /**
     * Check if user has full access (admin or administrative)
     */
    public function hasFullAccess(): bool
    {
        return $this->isAdmin() || $this->isAdministrative();
    }

    /**
     * Get role label
     */
    public function getRoleLabelAttribute(): string
    {
        return self::ROLE_LABELS[$this->role] ?? 'غير معروف';
    }

    public function departments()
    {
        return $this->hasMany(Departments::class, 'user_id');
    }

    public function administratives()
    {
        return $this->hasMany(Administratives::class, 'user_id');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'string',
            'role' => 'integer',
        ];
    }
}
