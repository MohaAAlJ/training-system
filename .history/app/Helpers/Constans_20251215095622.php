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
     * Role labels in Arabic
     */
    public const ROLE_LABELS = [
        self::ROLE_ADMIN => 'مدير النظام',
        self::ROLE_ADMINISTRATIVE => 'إداري',
        self::ROLE_DEPARTMENT => 'رئيس قسم',
        self::ROLE_MOH => 'وزارة الصحة',
        self::ROLE_COLLEGE => 'مشرف كلية',
    ];
}
