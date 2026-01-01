<?php

namespace App\Filament\Resources\Trainee\Pages;

use App\Filament\Resources\TraineeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrainee extends ListRecords
{
    protected static string $resource = TraineeResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
