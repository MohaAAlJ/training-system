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

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Resources\GeneralSettings\Widgets\InstitutionsStatusWidget::class,
            \App\Filament\Resources\GeneralSettings\Widgets\CollegesStatusWidget::class,
        ];
    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
