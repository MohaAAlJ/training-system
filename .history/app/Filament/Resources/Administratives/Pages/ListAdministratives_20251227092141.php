<?php

namespace App\Filament\Resources\Administratives\Pages;

use App\Filament\Resources\AdministrativeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdministratives extends ListRecords
{
    protected static string $resource = AdministrativeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
