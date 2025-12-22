<?php

namespace App\Helpers;

class Constans
{
    /**
     * Role constants - stored as integers in database for easy extension
     *
     * NOTIFICATION RULES:
     * -------------------
     * ROLE_ADMIN (1):      Gets notifications for EVERYTHING (all applications)
     * ROLE_DEPARTMENT (2): Gets notified when application is for THEIR department
     * ROLE_SECTION (3):    Gets notified when application is for THEIR section
     * ROLE_MOH (4):        Ministry of Health - view only (no auto-notifications)
     * ROLE_COLLEGE (5):    College Supervisor - view only (no auto-notifications)
     * ROLE_HOA (6):        Gets notified for anything in THEIR administrative
     * ROLE_HOM (7):        Gets notified when anything in MEDICAL department gets added
     * ROLE_GTM (8):        Gets notifications for ANY application added
     */
    public const ROLE_ADMIN = 1; //SUPER_ADMIN - Gets ALL notifications
    public const ROLE_DEPARTMENT = 2; //DEPARTMENT HEAD - Gets notifications for THEIR department
    public const ROLE_SECTION = 3; //SECTION HEAD - Gets notifications for THEIR section
    public const ROLE_MOH = 4; //MINISTRY OF HEALTH - View only
    public const ROLE_COLLEGE = 5; //COLLEGE SUPERVISOR - View only
    public const ROLE_HOA = 6; //HEAD OF ADMINISTRATION - Gets notifications for THEIR administrative
    public const ROLE_HOM = 7; //HEAD OF MEDICAL - Gets notifications for MEDICAL departments
    public const ROLE_GTM = 8; //GENERAL TRAINING MANAGER - Gets ALL notifications


    /**
     * Application status constants
     */
    public const STATUS_NEW = 1;
    public const STATUS_INITIAL_APPROVE = 2;
    public const STATUS_CONFIRMATION = 3;
    public const STATUS_WAITING_LIST = 4;
    public const STATUS_STRATED_TRAINING = 5;
    public const STATUS_ENDED_TRAINING = 6;
    public const STATUS_REJECTED = 7;
    public const STATUS_DROPPED = 8;
    public const STATUS_UNKNOWN = 9;

    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_INITIAL_APPROVE,
        self::STATUS_CONFIRMATION,
        self::STATUS_WAITING_LIST,
        self::STATUS_STRATED_TRAINING,
        self::STATUS_ENDED_TRAINING,
        self::STATUS_REJECTED,
        self::STATUS_DROPPED,
        self::STATUS_UNKNOWN,
    ];

    /**
     * Role labels
     */
    public const ROLE_LABELS = [
        self::ROLE_ADMIN => 'مدير النظام',
        self::ROLE_DEPARTMENT => 'إداري',
        self::ROLE_SECTION => 'رئيس قسم',
        self::ROLE_MOH => 'وزارة الصحة',
        self::ROLE_COLLEGE => 'مشرف كلية',
        self::ROLE_HOA => 'رئيس الإدارة',
        self::ROLE_HOM => 'رئيس الطب',
        self::ROLE_GTM => 'مدير التدريب العام',
    ];

    /**
     * Training Type constants
     */
    public const TRAINING_TYPE_UNIVERSITY = 1;
    public const TRAINING_TYPE_PRACTICE = 2;

    public const TRAINING_TYPES = [
        self::TRAINING_TYPE_UNIVERSITY => 'تدريب جامعي',
        self::TRAINING_TYPE_PRACTICE => 'مزاولة مهنة',
    ];

    /**
     * Helper to calculate capacity and usage
     */
    public static function getCapacityStats(?int $administrativeId = null, ?int $departmentId = null, ?int $sectionId = null): array
    {
        // 1. Total Capacity from Sections
        $sectionsQuery = \App\Models\Sections::query();
        if ($administrativeId) $sectionsQuery->where('administrative_id', $administrativeId);
        if ($departmentId) $sectionsQuery->where('department_id', $departmentId);
        if ($sectionId) $sectionsQuery->where('id', $sectionId);

        $totalCapacity = $sectionsQuery->sum('total_capacity');

        // 2. Used Capacity (Active Applications)
        // Assuming 'Used' means currently in training
        $appsQuery = \App\Models\Applications::query()
            ->where('status', self::STATUS_STRATED_TRAINING); // Active only

        if ($administrativeId) $appsQuery->where('administrative_id', $administrativeId);
        if ($departmentId) $appsQuery->where('department_id', $departmentId);
        if ($sectionId) $appsQuery->where('section_id', $sectionId);

        $used = $appsQuery->count();
        $available = max(0, $totalCapacity - $used);

        return [
            'total' => $totalCapacity,
            'used' => $used,
            'available' => $available,
        ];
    }
}
