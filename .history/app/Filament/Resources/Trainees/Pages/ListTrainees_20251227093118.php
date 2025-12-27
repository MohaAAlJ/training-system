<?php

namespace App\Filament\Resources\Trainees\Pages;

use App\Filament\Resources\Trainees\TraineeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrainees extends ListRecords
{
    protected static string $resource = TraineeResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
