<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ApplicationStatus: int implements HasLabel, HasColor
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

    public function getLabel(): ?string
    {
        return match ($this) {
            self::NEW => 'جديد',
            self::INITIAL_APPROVE => 'موافقة مبدئية',
            self::CONFIRMATION => 'تأكيد',
            self::WAITING_LIST => 'قائمة الانتظار',
            self::STARTED_TRAINING => 'بدء التدريب',
            self::ENDED_TRAINING => 'إنهاء التدريب',
            self::REJECTED => 'رفض',
            self::DROPPED => 'منسحب',
            self::UNKNOWN => 'غير معروف',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::NEW => 'info',
            self::INITIAL_APPROVE, self::CONFIRMATION => 'primary',
            self::WAITING_LIST => 'warning',
            self::STARTED_TRAINING => 'success',
            self::ENDED_TRAINING => 'gray',
            self::REJECTED, self::DROPPED => 'danger',
            default => 'gray',
        };
    }
}
