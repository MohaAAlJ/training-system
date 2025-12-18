<?php

namespace App\Helpers;

class Constans
{
    /**
     * Role constants - stored as integers in database for easy extension
     */
    public const ROLE_ADMIN = 1;
    public const ROLE_ADMINISTRATIVE = 2;
    public const ROLE_DEPARTMENT = 3;
    public const ROLE_MOH = 4;
    public const ROLE_COLLEGE = 5;

    /**
     * Application status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_WAITING = 'waiting';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_PAUSED = 'paused';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_WAITING,
        self::STATUS_ACTIVE,
        self::STATUS_COMPLETED,
        self::STATUS_REJECTED,
        self::STATUS_PAUSED,
    ];

    /**
     * Role labels
     */
    public const ROLE_LABELS = [
        self::ROLE_ADMIN => 'مدير النظام',
        self::ROLE_ADMINISTRATIVE => 'إداري',
        self::ROLE_DEPARTMENT => 'رئيس قسم',
        self::ROLE_MOH => 'وزارة الصحة',
        self::ROLE_COLLEGE => 'مشرف كلية',
    ];
}
