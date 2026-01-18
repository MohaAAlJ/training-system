<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * ApplicationStatus Enum
 *
 * Defines all possible application statuses in the training system.
 * Replaces hardcoded constants in Application model with proper enum.
 *
 * Usage:
 *   ApplicationStatus::NEW->value           // Returns 1
 *   ApplicationStatus::NEW->label()         // Returns 'جديد'
 *   ApplicationStatus::from(1)              // Returns ApplicationStatus::NEW
 */
enum ApplicationStatus: int
{
    case NEW = 1;
    case INITIAL_APPROVE = 2;
    case CONFIRMATION = 3;
    case WAITING_LIST = 4;
    case STARTED_TRAINING = 5;
    case ENDED_TRAINING = 6;
    case REJECTED = 7;
    case DROPPED = 8;
    case UNKNOWN = 9;

    /**
     * Get the Arabic label for this status
     */
    public function label(): string
    {
        return match ($this) {
            self::NEW => 'جديد',
            self::INITIAL_APPROVE => 'موافقة أولية',
            self::CONFIRMATION => 'تأكيد',
            self::WAITING_LIST => 'قائمة الانتظار',
            self::STARTED_TRAINING => 'التدريب نشط',
            self::ENDED_TRAINING => 'التدريب انتهى',
            self::REJECTED => 'مرفوض',
            self::DROPPED => 'منسحب',
            self::UNKNOWN => 'غير محدد',
        };
    }

    /**
     * Get the user-facing message for this status
     *
     * @param int|null $trainingType Optional training type for context-specific messages
     */
    public function message(?int $trainingType = null): string
    {
        return match ($this) {
            self::NEW => 'لديك طلب قيد الانتظار',
            self::INITIAL_APPROVE => $trainingType === TrainingType::UNIVERSITY->value
                ? 'لديك طلب في انتظار القبول الجامعي'
                : 'لديك طلب في انتظار قبول الوزارة',
            self::CONFIRMATION => 'لديك طلب في انتظار التأكيد',
            self::WAITING_LIST => 'لديك طلب في قائمة الانتظار',
            self::STARTED_TRAINING => 'لديك تدريب نشط',
            self::ENDED_TRAINING => 'لديك طلب منتهي',
            self::REJECTED => 'لديك طلب سابق لايمكنك اصادر طلب جديد',
            self::DROPPED => 'لديك طلب منسحب',
            self::UNKNOWN => 'لا يمكنك تقديم طلب جديد في هذا الوقت',
        };
    }

    /**
     * Get all status values as array
     */
    public static function values(): array
    {
        return array_map(fn (self $status) => $status->value, self::cases());
    }

    /**
     * Check if this status is a final/terminal state
     */
    public function isTerminal(): bool
    {
        return in_array($this, [self::ENDED_TRAINING, self::REJECTED, self::DROPPED]);
    }

    /**
     * Check if re-application is possible from this status
     */
    public function allowsReapplication(): bool
    {
        return $this === self::ENDED_TRAINING;
    }
}
