<?php

namespace App\Filament\Resources\Administratives\Pages;

use App\Filament\Resources\Administratives\AdministrativesResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdministratives extends CreateRecord
{
    protected static string $resource = AdministrativesResource::class;

    public function mount(): void
    {
        $this->authorize('create', \App\Models\Administratives::class);
        parent::mount();
    }
}
