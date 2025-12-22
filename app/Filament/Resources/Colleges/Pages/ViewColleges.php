<?php

namespace App\Filament\Resources\Colleges\Pages;

use App\Filament\Resources\Colleges\CollegesResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewColleges extends ViewRecord
{
    protected static string $resource = CollegesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
