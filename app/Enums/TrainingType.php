<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TrainingType: int implements HasLabel, HasColor
{
    case UNIVERSITY = 1;
    case PRACTICE = 2;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::UNIVERSITY => 'تدريب جامعي',
            self::PRACTICE => 'مزاولة مهنة',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::UNIVERSITY => 'info',
            self::PRACTICE => 'success',
        };
    }
}
