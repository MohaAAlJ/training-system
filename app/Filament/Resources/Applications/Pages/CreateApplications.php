<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateApplications extends CreateRecord
{
    protected static string $resource = ApplicationsResource::class;
    protected function getCreatedNotificationTitle(): ?string
{
    return 'تمت العملية بنجاح'; // or "Completed"
}

}
