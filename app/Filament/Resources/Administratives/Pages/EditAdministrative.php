<?php

namespace App\Filament\Resources\Administratives\Pages;

use App\Filament\Resources\Administratives\AdministrativeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAdministrative extends EditRecord
{
    protected static string $resource = AdministrativeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('toggle_status')
                ->label(fn ($record) => $record->active ? 'تعطيل الإدارة' : 'تفعيل الإدارة')
                ->icon(fn ($record) => $record->active ? 'heroicon-m-x-circle' : 'heroicon-m-check-circle')
                ->color(fn ($record) => $record->active ? 'danger' : 'success')
                ->requiresConfirmation()
                ->modalHeading('تغيير حالة الإدارة')
                ->modalDescription(fn ($record) => $record->active ? '⚠️ تحذير: عند تعطيل الإدارة سيتم تعطيل كافة الأقسام التابعة لها تلقائياً.' : 'تنبيه هام: تفعيل الإدارة لن يقوم بتفعيل الأقسام تلقائياً. لا تنسَ إعادة تفعيل الأقسام التابعة لها يدوياً.')
                ->action(fn ($record) => $record->update(['active' => !$record->active])),
            \Filament\Actions\Action::make('save')
                ->label('حفظ التغييرات')
                ->action('save')
                ->icon('heroicon-m-check')
                ->color('primary')
                ->keyBindings(['mod+s']),
            $this->getCancelFormAction()
                ->label('إلغاء'),
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم تحديث بيانات الإدارة بنجاح';
    }
}
