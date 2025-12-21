<?php

namespace App\Filament\Resources\Departments\Pages;

use App\Filament\Resources\Departments\DepartmentsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDepartments extends CreateRecord
{
    protected static string $resource = DepartmentsResource::class;

    public function mount(): void
    {
        $this->authorize('create', \App\Models\Departments::class);
        parent::mount();
    }

    protected function getCreatedNotificationTitle(): ?string
{
    return 'تمت العملية بنجاح'; // or "Completed"
}
}
