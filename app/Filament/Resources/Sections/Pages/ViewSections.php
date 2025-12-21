<?php

namespace App\Filament\Resources\Sections\Pages;

use App\Filament\Resources\Sections\SectionsResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSections extends ViewRecord
{
    protected static string $resource = SectionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
