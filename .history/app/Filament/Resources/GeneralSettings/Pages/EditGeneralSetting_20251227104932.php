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
        // Transform [1, 2] into [['institution_id' => 1], ['institution_id' => 2]]
        $data['active_institutions'] = \App\Models\Institution::where('is_active', true)
            ->pluck('id')
            ->map(fn($id) => ['institution_id' => $id])
            ->toArray();

        // Transform [1, 2] into [['college_id' => 1], ['college_id' => 2]]
        $data['active_colleges'] = \App\Models\College::where('is_active', true)
            ->pluck('id')
            ->map(fn($id) => ['college_id' => $id])
            ->toArray();

        return $data;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        // Update Institutions
        // Extract IDs from [['institution_id' => 1], ...]
        if (isset($data['active_institutions'])) {
            $institutionIds = collect($data['active_institutions'])->pluck('institution_id')->toArray();

            \App\Models\Institution::query()->update(['is_active' => false]);
            if (!empty($institutionIds)) {
                \App\Models\Institution::whereIn('id', $institutionIds)->update(['is_active' => true]);
            }
            unset($data['active_institutions']);
        }

        // Update Colleges
        // Extract IDs from [['college_id' => 1], ...]
        if (isset($data['active_colleges'])) {
            $collegeIds = collect($data['active_colleges'])->pluck('college_id')->toArray();

            \App\Models\College::query()->update(['is_active' => false]);
            if (!empty($collegeIds)) {
                \App\Models\College::whereIn('id', $collegeIds)->update(['is_active' => true]);
            }
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
