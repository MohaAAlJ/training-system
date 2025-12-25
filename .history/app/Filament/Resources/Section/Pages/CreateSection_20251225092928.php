<?php

namespace App\Filament\Resources\Section\Pages;

use App\Filament\Resources\Section\SectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSection extends CreateRecord
{
    protected static string $resource = SectionResource::class;
    protected function getCreatedNotificationTitle(): ?string
{
    return 'تمت العملية بنجاح'; // or "Completed"
}
}






