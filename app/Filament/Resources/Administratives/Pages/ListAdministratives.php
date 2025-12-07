<?php

namespace App\Filament\Resources\Administratives\Pages;

use App\Filament\Resources\Administratives\AdministrativesResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdministratives extends ListRecords
{
    protected static string $resource = AdministrativesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
