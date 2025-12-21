<?php

namespace App\Helpers;

class Constans
{
    /**
     * الأدوار - Roles (Stored as Integers 1-5)
     */
    public const ROLE_SYSTEM_ADMIN = 1;           // مدير النظام العام
    public const ROLE_ADMINISTRATIVE_MANAGER = 2;   // رئيس الادارة (مثل رئيس مستشفى الأمل)
    public const ROLE_DEPARTMENT_MANAGER = 3;       // رئيس الدائرة (مثل رئيس دائرة الصيدلة)
    public const ROLE_SECTION_HEAD = 4;             // رئيس القسم (المسؤول الميداني المباشر)
    public const ROLE_COLLEGE_SUPERVISOR = 5;       // مشرف الكلية/الجامعة
    public const ROLE_MOH = 6;                      // وزارة الصحة
    public const ROLE_TRAINING_MANAGER = 7;         // مدير التدريب
    public const ROLE_MEDICAL_MANAGER = 8;          // مدير طبي

    /**
     * أنواع التدريب - Training Types (Stored as Integers 1-2)
     */
    public const TRAINING_TYPE_UNIVERSITY = 1;      // تدريب جامعي
    public const TRAINING_TYPE_PROFESSIONAL = 2;    // مزاولة مهنة

    /**
     * تسميات أنواع التدريب (Labels)
     */
    public const TRAINING_TYPE_LABELS = [
        self::TRAINING_TYPE_UNIVERSITY => 'تدريب جامعي',
        self::TRAINING_TYPE_PROFESSIONAL => 'مزاولة مهنة',
    ];

    /**
     * حالات الطلب - Application Statuses (Stored as Integers 1-8)
     */
    public const STATUS_NEW = 1;               // طلب جديد
    public const STATUS_INITIAL_APPROVE = 2;   // موافقة مبدئية
    public const STATUS_CONFIRMATION = 3;      // تأكيد القبول
    public const STATUS_WAITING = 4;           // قائمة الانتظار
    public const STATUS_START_TRAINING = 5;    // بدأ التدريب
    public const STATUS_COMPLETED = 6;         // مكتمل
    public const STATUS_DROPPED = 7;           // منسحب / ملغي
    public const STATUS_UNKNOWN = 8;           // حالة معلقة / غير معروف

    /**
     * مصفوفة الحالات للاستخدام في القوائم المنسدلة (Select Inputs)
     */
    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_INITIAL_APPROVE,
        self::STATUS_CONFIRMATION,
        self::STATUS_WAITING,
        self::STATUS_START_TRAINING,
        self::STATUS_COMPLETED,
        self::STATUS_DROPPED,
        self::STATUS_UNKNOWN,
    ];

    /**
     * تسميات الأدوار (Labels)
     */
    public const ROLE_LABELS = [
        self::ROLE_SYSTEM_ADMIN => 'مدير النظام',
        self::ROLE_ADMINISTRATIVE_MANAGER => 'رئيس الادارة',
        self::ROLE_DEPARTMENT_MANAGER => 'رئيس الدائرة',
        self::ROLE_SECTION_HEAD => 'رئيس القسم',
        self::ROLE_COLLEGE_SUPERVISOR => 'مشرف الكلية',
        self::ROLE_MOH => 'وزارة الصحة',
        self::ROLE_TRAINING_MANAGER => 'مدير التدريب',
        self::ROLE_MEDICAL_MANAGER => 'مدير طبي',
    ];

    /**
     * تسميات حالات الطلب (Labels)
     */
    public const STATUS_LABELS = [
        self::STATUS_NEW => 'طلب جديد',
        self::STATUS_INITIAL_APPROVE => 'موافقة مبدئية',
        self::STATUS_CONFIRMATION => 'تأكيد القبول',
        self::STATUS_WAITING => 'جاهز للتدريب',
        self::STATUS_START_TRAINING => 'بدأ التدريب',
        self::STATUS_COMPLETED => 'انتهى التدريب',
        self::STATUS_DROPPED => 'منقطع / ملغي',
        self::STATUS_UNKNOWN => 'معلق / غير محدد',
    ];

    /**
     * ألوان الحالات (للعرض في الـ Blade)
     */
    public const STATUS_COLORS = [
        self::STATUS_NEW => 'warning',
        self::STATUS_INITIAL_APPROVE => 'info',
        self::STATUS_CONFIRMATION => 'primary',
        self::STATUS_WAITING => 'dark',
        self::STATUS_START_TRAINING => 'success',
        self::STATUS_COMPLETED => 'secondary',
        self::STATUS_DROPPED => 'danger',
        self::STATUS_UNKNOWN => 'light',
    ];
}
