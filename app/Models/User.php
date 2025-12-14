<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use app\Helpers\Constans;
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    // Role constants and labels moved to \App\Helpers\Constans


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
        return $this->role === Constans::ROLE_ADMIN;
    }

    /**
     * Check if user is administrative (can see/edit everything)
     */
    public function isAdministrative(): bool
    {
        return $this->role === Constans::ROLE_ADMINISTRATIVE;
    }

    /**
     * Check if user is department head (can see/edit only their department)
     */
    public function isDepartment(): bool
    {
        return $this->role === Constans::ROLE_DEPARTMENT;
    }

    /**
     * Check if user is MOH (can only view professional type applications)
     */
    public function isMOH(): bool
    {
        return $this->role === Constans::ROLE_MOH;
    }

    /**
     * Check if user is institution/college supervisor (can only view their faculty training requests)
     */
    public function isCollage(): bool
    {
        return $this->role === Constans::ROLE_COLLAGE;
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
        return Constans::ROLE_LABELS[$this->role] ?? 'غير معروف';
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

    public function college()
    {
        return $this->hasOne(College::class);
    }
}
