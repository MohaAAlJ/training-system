<?php

namespace App\Helpers;

class Constans
{
    /**
     * الأدوار - Roles (Stored as Integers 1-5)
     */
    public const ROLE_SYSTEM_ADMIN = 1;         // مدير النظام العام
    public const ROLE_ADMINISTRATIVE_MANAGER = 2; // مدير المنشأة (مثل مدير مستشفى الأمل)
    public const ROLE_DEPARTMENT_MANAGER = 3;     // مدير الدائرة العامة (مثل مدير عام الصيدلة)
    public const ROLE_SECTION_HEAD = 4;           // رئيس الشعبة/الفرع (المسؤول الميداني المباشر)
    public const ROLE_COLLEGE_SUPERVISOR = 5;     // مشرف الكلية/الجامعة

    /**
     * حالات الطلب - Application Statuses (Stored as Integers 1-8)
     */
    public const STATUS_NEW = 1;           // قيد الانتظار (تحت المراجعة الأولية)
    public const STATUS_INITIAL_APPROVE = 2;     // موافقة الدائرة العامة (مثل الإدارة العامة للصيدلة)
    public const STATUS_CONFIRMATION_APPROVED = 3;    // موافقة إدارة المنشأة (مثل المستشفى)
    public const STATUS_ACTIVE = 4;            // قيد التدريب (بدأ التدريب فعلياً)
    public const STATUS_COMPLETED = 5;         // مكتمل (أنهى الساعات المطلوبة)
    public const STATUS_REJECTED = 6;          // مرفوض
    public const STATUS_WAITING = 7;           // قائمة الانتظار
    public const STATUS_PAUSED = 8;            // موقف مؤقتاً

    /**
     * مصفوفة الحالات للاستخدام في القوائم المنسدلة (Select Inputs)
     */
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_DEPT_APPROVED,
        self::STATUS_ADMIN_APPROVED,
        self::STATUS_ACTIVE,
        self::STATUS_COMPLETED,
        self::STATUS_REJECTED,
        self::STATUS_WAITING,
        self::STATUS_PAUSED,
    ];

    /**
     * تسميات الأدوار (Labels)
     */
    public const ROLE_LABELS = [
        self::ROLE_SYSTEM_ADMIN => 'مدير النظام',
        self::ROLE_ADMINISTRATIVE_MANAGER => 'مدير المنشأة/المستشفى',
        self::ROLE_DEPARTMENT_MANAGER => 'مدير الدائرة العامة',
        self::ROLE_SECTION_HEAD => 'رئيس الشعبة/الفرع',
        self::ROLE_COLLEGE_SUPERVISOR => 'مشرف كلية',
    ];

    /**
     * تسميات حالات الطلب (Labels)
     */
    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'قيد الانتظار',
        self::STATUS_DEPT_APPROVED => 'موافقة الدائرة العامة',
        self::STATUS_ADMIN_APPROVED => 'موافقة إدارة المنشأة',
        self::STATUS_ACTIVE => 'قيد التدريب حالياً',
        self::STATUS_COMPLETED => 'مكتمل',
        self::STATUS_REJECTED => 'مرفوض',
        self::STATUS_WAITING => 'قائمة الانتظار',
        self::STATUS_PAUSED => 'موقف مؤقتاً',
    ];

    /**
     * ألوان الحالات (للعرض في الـ Blade)
     */
    public const STATUS_COLORS = [
        self::STATUS_PENDING => 'warning',
        self::STATUS_DEPT_APPROVED => 'info',
        self::STATUS_ADMIN_APPROVED => 'primary',
        self::STATUS_ACTIVE => 'success',
        self::STATUS_COMPLETED => 'secondary',
        self::STATUS_REJECTED => 'danger',
        self::STATUS_WAITING => 'dark',
        self::STATUS_PAUSED => 'light',
    ];
}
