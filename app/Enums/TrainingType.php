<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * TrainingType Enum
 *
 * Defines the types of training available in the system.
 * Replaces hardcoded constants in Application model with proper enum.
 *
 * Usage:
 *   TrainingType::UNIVERSITY->value      // Returns 1
 *   TrainingType::UNIVERSITY->label()    // Returns 'تدريب جامعي'
 *   TrainingType::from(1)                // Returns TrainingType::UNIVERSITY
 */
enum TrainingType: int
{
    case UNIVERSITY = 1;
    case PRACTICE = 2;

    /**
     * Get the Arabic label for this training type
     */
    public function label(): string
    {
        return match ($this) {
            self::UNIVERSITY => 'تدريب جامعي',
            self::PRACTICE => 'مزاولة مهنة',
        };
    }

    /**
     * Check if this is a medical training type (filters departments)
     */
    public function isMedical(): bool
    {
        return $this === self::PRACTICE;
    }

    /**
     * Get all training type values as array
     */
    public static function values(): array
    {
        return array_map(fn (self $type) => $type->value, self::cases());
    }

    /**
     * Get all training types as key-value array for dropdowns
     */
    public static function toArray(): array
    {
        return [
            self::UNIVERSITY->value => self::UNIVERSITY->label(),
            self::PRACTICE->value => self::PRACTICE->label(),
        ];
    }
}
