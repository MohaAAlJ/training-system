<?php

namespace App\Filament\Resources\Department\Pages;

use App\Filament\Resources\DepartmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDepartment extends ListRecords
{
    protected static string $resource = DepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
