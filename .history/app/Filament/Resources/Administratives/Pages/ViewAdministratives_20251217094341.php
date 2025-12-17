<?php

namespace App\Filament\Resources\Administratives\Pages;

use App\Filament\Resources\Administratives\AdministrativesResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewAdministratives extends ViewRecord
{
    protected static string $resource = AdministrativesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make()->visible(fn() => Auth::user()?->isAdmin() ?? false),
            ForceDeleteAction::make()->visible(fn() => Auth::user()?->isAdmin() ?? false),
        ];
    }
}
