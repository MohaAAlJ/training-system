<?php

namespace App\Filament\Resources\Application\Pages;

use App\Filament\Resources\Application\Pages\CreateApplication;
use Filament\Actions\CreateAction;
use App\Filament\Resources\ApplicationResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListApplication extends ListRecords
{
    protected static string $resource = ApplicationResource::class;

    protected function getActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة طلب')
                ->visible(static fn() => Auth::user()?->isAdmin() || Auth::user()?->isCollegeSupervisor() ?? false),
        ];
    }
}
