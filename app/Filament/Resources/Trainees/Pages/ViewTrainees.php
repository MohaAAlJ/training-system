<?php

namespace App\Filament\Resources\Trainees\Pages;

use App\Filament\Resources\Trainees\TraineesResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTrainees extends ViewRecord
{
    protected static string $resource = TraineesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
