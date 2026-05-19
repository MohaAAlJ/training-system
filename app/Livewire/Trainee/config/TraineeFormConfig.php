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
    public const UNIVERSITY = 1;
    public const PRACTICE = 2;

    /**
     * Age constraints
     */
    public const MIN_AGE = 18;
    public const MAX_AGE = 60;

    /**
     * Training hours constraints
     */
    public const MIN_TRAINING_HOURS = 50;
    public const MAX_TRAINING_HOURS = 999;

    /**
     * Get all validation rules
     */
    public static function getValidationRules(): array
    {
        return [
            'trainingType' => ['required', 'integer', \Illuminate\Validation\Rule::in([self::UNIVERSITY, self::PRACTICE])],
            'nationalId' => ['required', 'digits:9', 'regex:' . self::NATIONAL_ID_REGEX],
            'fullName' => ['required', 'string', 'min:6', 'max:255', 'regex:' . self::NAME_REGEX],
            'gender' => ['required', \Illuminate\Validation\Rule::enum(\App\Enums\Gender::class)],
            'phoneNumber' => ['required', 'regex:' . self::PHONE_REGEX],
            'dob' => [
                'required',
                'date',
                'before_or_equal:' . now()->subYears(self::MIN_AGE)->format('Y-m-d'),
                'after_or_equal:' . now()->subYears(self::MAX_AGE)->format('Y-m-d')
            ],
            'governorateId' => ['required', 'exists:governorates,id'],
            'street' => ['nullable', 'string', 'max:255'],
            'institutionId' => ['required_if:trainingType,' . self::UNIVERSITY, 'exists:institutions,id'],
            'majorId' => ['required_if:trainingType,' . self::UNIVERSITY, 'exists:majors,id'],
            'universityNumber' => ['required_if:trainingType,' . self::UNIVERSITY, 'numeric', 'digits_between:1,10'],
            'administrativeId' => ['required', 'exists:administratives,id'],
            'departmentId' => ['required', 'exists:departments,id'],
            'sectionId' => ['required', 'exists:sections,id'],
            'trainingHours' => ['required', 'integer', 'min:' . self::MIN_TRAINING_HOURS, 'max:' . self::MAX_TRAINING_HOURS],
            'letterFile' => ['nullable', 'file', 'max:' . (self::MAX_FILE_SIZE_MB * 1024), 'mimes:' . implode(',', self::ALLOWED_FILE_TYPES)],
            'termsApproval' => ['required', 'accepted'],
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
            'fullName.min' => 'الاسم الكامل يجب أن يكون 6 حروف على الأقل.',
            'fullName.regex' => 'الاسم يجب أن يحتوي على حروف ومسافات فقط.',
            'gender.required' => 'الجنس مطلوب.',
            'phoneNumber.required' => 'رقم الجوال مطلوب.',
            'phoneNumber.regex' => 'صيغة رقم الجوال غير صحيحة. استخدم 9705XXXXXXXX أو 9725XXXXXXXX',
            'dob.required' => 'تاريخ الميلاد مطلوب.',
            'dob.before_or_equal' => 'يجب أن يكون عمرك ' . self::MIN_AGE . ' سنة على الأقل.',
            'dob.after_or_equal' => 'يجب أن يكون عمرك ' . self::MAX_AGE . ' سنة كحد أقصى.',
            'governorateId.required' => 'المحافظة مطلوبة.',
            'institutionId.required_if' => 'اختر المؤسسة التعليمية.',
            'majorId.required_if' => 'اختر التخصص.',
            'universityNumber.required_if' => 'رقم الطالب الجامعي مطلوب.',
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
