<?php

namespace App\Helpers;

class Constans
{
    /**
     * Role constants - stored as integers in database for easy extension
     */
    public const ROLE_ADMIN = 1; //SUPER_ADMIN
    public const ROLE_DEPARTMENT = 2; //DEPARTMENT HEAD
    public const ROLE_SECTION = 3; //SECTION HEAD
    public const ROLE_MOH = 4; //MINISTRY OF HEALTH
    public const ROLE_COLLEGE = 5; //COLLEGE SUPERVISOR
    public const ROLE_HOA = 6; //HEAD OF ADMINISTRATION
    public const ROLE_HOM = 7; //HEAD OF MEDICAL
    public const ROLE_GTM = 8; //GENERAL TRAINING MANAGER


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
}
