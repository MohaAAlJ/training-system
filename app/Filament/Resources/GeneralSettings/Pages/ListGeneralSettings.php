<?php

namespace App\Filament\Resources\GeneralSettings\Pages;

use App\Filament\Resources\GeneralSettings\GeneralSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGeneralSettings extends ListRecords
{
    protected static string $resource = GeneralSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action
        ];
    }

    public function mount(): void
    {
        // Ensure the singleton record exists
        if (\App\Models\GeneralSetting::count() === 0) {
            \App\Models\GeneralSetting::create([
                'hoa_can_edit_section' => false,
                'hoa_can_enable_section' => false,
                'dept_head_can_edit_section' => false,
                'dept_head_can_enable_section' => false,
            ]);
        }

        parent::mount();
    }
}
