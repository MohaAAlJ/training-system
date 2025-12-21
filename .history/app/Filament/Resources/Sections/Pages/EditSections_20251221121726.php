<?php

namespace App\Filament\Resources\Sections\Pages;

use App\Filament\Resources\Sections\SectionsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSections extends EditRecord
{
    protected static string $resource = SectionsResource::class;

    public function mount(string|int $record): void
    {
        parent::mount($record);
        $section = $this->getRecord();
        $this->authorize('update', $section);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
