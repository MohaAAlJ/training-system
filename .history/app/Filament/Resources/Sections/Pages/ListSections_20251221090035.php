<?php

namespace App\Filament\Resources\Sections\Pages;

use App\Filament\Resources\Sections\SectionsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSections extends ListRecords
{
    protected static string $resource = SectionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
