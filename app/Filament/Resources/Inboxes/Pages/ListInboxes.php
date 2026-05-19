<?php

namespace App\Filament\Resources\Inboxes\Pages;

use App\Filament\Resources\Inboxes\InboxResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInboxes extends ListRecords
{
    protected static string $resource = InboxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
