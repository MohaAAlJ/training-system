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
        $setting = \App\Models\GeneralSetting::first();

        if (!$setting) {
            $setting = \App\Models\GeneralSetting::create([
                'hoa_can_edit_section' => false,
                'hoa_can_enable_section' => false,
                'dept_head_can_edit_section' => false,
                'dept_head_can_enable_section' => false,
            ]);
        }

        redirect(GeneralSettingResource::getUrl('edit', ['record' => $setting]));
    }
}
