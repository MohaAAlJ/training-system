<?php

namespace App\Filament\Resources\Applications\Pages;

use filament\App\Filament\Resources\Applications\Pages\CreateApplications;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Applications\ApplicationsResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationsResource::class;

    protected function getActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة طلب')
                ->visible(static fn() => Auth::user()?->isAdmin() || Auth::user()?->isCollegeSupervisor() ?? false),
        ];
    }
}
