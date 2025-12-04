<?php

namespace App\Filament\Resources\TrainingRequests\Pages;

use App\Filament\Resources\TrainingRequests\TrainingRequestsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrainingRequests extends ListRecords
{
    protected static string $resource = TrainingRequestsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
