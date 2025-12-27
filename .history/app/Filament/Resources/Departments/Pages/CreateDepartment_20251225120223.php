<?php

namespace App\Filament\Resources\Department\Pages;

use App\Filament\Resources\DepartmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDepartment extends CreateRecord
{
    protected static string $resource = DepartmentResource::class;
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تمت العملية بنجاح'; // or "Completed"
    }
}
