<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditApplication extends EditRecord
{
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('حفظ التغييرات')
                ->action('save')
                ->icon('heroicon-m-check')
                ->color('primary')
                ->keyBindings(['mod+s']),
            $this->getCancelFormAction()
                ->label('إلغاء'),
            DeleteAction::make()
                ->label('رفض')
                ->visible(fn() => !$this->getRecord()->trashed() && (Auth::user()?->isAdmin() || Auth::user()?->isGeneralTrainingManager())),
            RestoreAction::make()
                ->visible(fn() => $this->getRecord()->trashed() && (Auth::user()?->isAdmin() || Auth::user()?->isGeneralTrainingManager())),
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
        return 'تم تحديث بيانات طلب المتدرب بنجاح';
    }
}
