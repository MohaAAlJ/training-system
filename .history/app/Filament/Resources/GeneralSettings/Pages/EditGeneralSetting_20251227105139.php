<?php

namespace App\Filament\Resources\GeneralSettings\Pages;

use App\Filament\Resources\GeneralSettings\GeneralSettingResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditGeneralSetting extends EditRecord
{
    protected static string $resource = GeneralSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No delete action
        ];
    }
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['active_institutions'] = \App\Models\Institution::where('is_active', true)->pluck('id')->toArray();
        $data['active_colleges'] = \App\Models\College::where('is_active', true)->pluck('id')->toArray();

        return $data;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        // Update Institutions
        if (isset($data['active_institutions'])) {
            \App\Models\Institution::query()->update(['is_active' => false]);
            \App\Models\Institution::whereIn('id', $data['active_institutions'])->update(['is_active' => true]);
            unset($data['active_institutions']);
        }

        // Update Colleges
        if (isset($data['active_colleges'])) {
            \App\Models\College::query()->update(['is_active' => false]);
            \App\Models\College::whereIn('id', $data['active_colleges'])->update(['is_active' => true]);
            unset($data['active_colleges']);
        }

        $record->update($data);

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
