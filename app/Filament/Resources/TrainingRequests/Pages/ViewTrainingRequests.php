<?php

namespace App\Filament\Resources\TrainingRequests\Pages;

use App\Filament\Resources\TrainingRequests\TrainingRequestsResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTrainingRequests extends ViewRecord
{
    protected static string $resource = TrainingRequestsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
