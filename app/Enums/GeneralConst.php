<?php

namespace App\Enums;

enum GeneralConst
{
    public const INACTIVE = 0;
    public const ACTIVE = 1;

    public static function getStatusLabel(int $status): string
    {
        return match ($status) {
            self::ACTIVE => 'نشط',
            self::INACTIVE => 'غير نشط',
            default => (string) $status,
        };
    }

    public static function getStatusColor(int $status): string
    {
        return match ($status) {
            self::ACTIVE => 'success',
            self::INACTIVE => 'danger',
            default => 'gray',
        };
    }
}
