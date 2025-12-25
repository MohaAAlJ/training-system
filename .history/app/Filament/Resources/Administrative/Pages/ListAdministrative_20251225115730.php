<?php

namespace App\Filament\Resources\Administrative\Pages;

use App\Filament\Resources\Administrative\AdministrativeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdministrative extends ListRecords
{
    protected static string $resource = AdministrativeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
