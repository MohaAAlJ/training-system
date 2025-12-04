<?php

namespace App\Filament\Resources\Administratives\Pages;

use App\Filament\Resources\Administratives\AdministrativesResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAdministratives extends ViewRecord
{
    protected static string $resource = AdministrativesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
