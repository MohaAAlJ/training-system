<?php

namespace App\Livewire\Trainee\Config;

/**
 * TraineeForm Configuration
 * 
 * Centralized configuration for the TraineeForm Livewire component.
 * Separates configuration from business logic for better maintainability.
 * 
 * USAGE:
 *   $config = TraineeFormConfig::getValidationRules();
 *   $config = TraineeFormConfig::getPhoneRegex();
 */
class TraineeFormConfig
{
    /**
     * Validation regex patterns
     */
    public const PHONE_REGEX = '/^97(0|2)5\d{8}$/';
    public const NAME_REGEX = '/^[\p{L}\s]+$/u';
    public const NATIONAL_ID_REGEX = '/^[0-9]{9}$/';

    /**
     * Form configuration
     */
    public const CACHE_TTL_MINUTES = 5;
    public const FILE_STORAGE_PATH = 'applications';
    public const MAX_FILE_SIZE_MB = 5;
    public const ALLOWED_FILE_TYPES = ['pdf', 'jpg', 'jpeg', 'png'];

    /**
     * Training types
     */
    public const TRAINING_TYPE_UNIVERSITY = 1;
    public const TRAINING_TYPE_PRACTICE = 2;

    /**
     * Age constraints
     */
    public const MIN_AGE = 20;
    public const MAX_AGE = 60;

    /**
     * Training hours constraints
     */
    public const MIN_TRAINING_HOURS = 50;
    public const MAX_TRAINING_HOURS = 1000;

    /**
     * Get all validation rules
     */
    public static function getValidationRules(): array
    {
        return [
            'trainingType' => 'required|integer|in:' . self::TRAINING_TYPE_UNIVERSITY . ',' . self::TRAINING_TYPE_PRACTICE,
            'nationalId' => 'required|digits:9|regex:' . self::NATIONAL_ID_REGEX,
            'fullName' => 'required|string|regex:' . self::NAME_REGEX . '|max:100',
            'phoneNumber' => 'required|regex:' . self::PHONE_REGEX,
            'dob' => 'required|date|before_or_equal:' . now()->subYears(self::MIN_AGE)->format('Y-m-d') . '|after_or_equal:' . now()->subYears(self::MAX_AGE)->format('Y-m-d'),
            'governorateId' => 'required|exists:governorates,id',
            'street' => 'required|string|max:255',
            'institutionId' => 'required_if:trainingType,' . self::TRAINING_TYPE_UNIVERSITY . '|exists:institutions,id',
            'majorId' => 'required_if:trainingType,' . self::TRAINING_TYPE_UNIVERSITY . '|exists:majors,id',
            'administrativeId' => 'required|exists:administratives,id',
            'departmentId' => 'required|exists:departments,id',
            'sectionId' => 'required|exists:sections,id',
            'trainingHours' => 'required|integer|min:' . self::MIN_TRAINING_HOURS . '|max:' . self::MAX_TRAINING_HOURS,
            'termsApproval' => 'required|accepted',
        ];
    }

    /**
     * Get validation messages in Arabic
     */
    public static function getValidationMessages(): array
    {
        return [
            'trainingType.required' => 'نوع التدريب مطلوب.',
            'nationalId.required' => 'رقم الهوية مطلوب.',
            'nationalId.digits' => 'رقم الهوية يجب أن يتكون من 9 أرقام.',
            'nationalId.regex' => 'صيغة رقم الهوية غير صحيحة.',
            'fullName.required' => 'الاسم الكامل مطلوب.',
            'fullName.regex' => 'الاسم يجب أن يحتوي على حروف ومسافات فقط.',
            'phoneNumber.required' => 'رقم الجوال مطلوب.',
            'phoneNumber.regex' => 'صيغة رقم الجوال غير صحيحة. استخدم 9705XXXXXXXX أو 9725XXXXXXXX',
            'dob.required' => 'تاريخ الميلاد مطلوب.',
            'dob.before_or_equal' => 'يجب أن يكون عمرك ' . self::MIN_AGE . ' سنة على الأقل.',
            'dob.after_or_equal' => 'يجب أن يكون عمرك ' . self::MAX_AGE . ' سنة كحد أقصى.',
            'governorateId.required' => 'المحافظة مطلوبة.',
            'street.required' => 'العنوان مطلوب.',
            'institutionId.required_if' => 'اختر المؤسسة التعليمية.',
            'majorId.required_if' => 'اختر التخصص.',
            'administrativeId.required' => 'مكان التدريب مطلوب.',
            'departmentId.required' => 'القسم مطلوب.',
            'sectionId.required' => 'التخصص مطلوب.',
            'trainingHours.required' => 'عدد ساعات التدريب مطلوب.',
            'trainingHours.min' => 'يجب أن تكون ساعات التدريب ' . self::MIN_TRAINING_HOURS . ' على الأقل.',
            'trainingHours.max' => 'لا يمكن أن تتجاوز ساعات التدريب ' . self::MAX_TRAINING_HOURS . '.',
            'termsApproval.required' => 'يجب قبول الشروط والأحكام.',
        ];
    }

    /**
     * Get phone regex pattern
     */
    public static function getPhoneRegex(): string
    {
        return self::PHONE_REGEX;
    }

    /**
     * Get name regex pattern
     */
    public static function getNameRegex(): string
    {
        return self::NAME_REGEX;
    }

    /**
     * Get national ID regex pattern
     */
    public static function getNationalIdRegex(): string
    {
        return self::NATIONAL_ID_REGEX;
    }

    /**
     * Get allowed file types for upload
     */
    public static function getAllowedFileTypes(): array
    {
        return self::ALLOWED_FILE_TYPES;
    }

    /**
     * Get max file size in MB
     */
    public static function getMaxFileSize(): int
    {
        return self::MAX_FILE_SIZE_MB;
    }

    /**
     * Get cache TTL in minutes
     */
    public static function getCacheTtl(): int
    {
        return self::CACHE_TTL_MINUTES;
    }
}
