<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum Gender: int implements HasLabel, HasColor, HasIcon
{
    case MALE = 1;
    case FEMALE = 2;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::MALE => 'ذكر',
            self::FEMALE => 'أنثى',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::MALE => 'info',
            self::FEMALE => 'fuchsia',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::MALE => 'heroicon-m-user',
            self::FEMALE => 'heroicon-m-user',
        };
    }
}
