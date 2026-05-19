<?php

namespace App\Filament\Resources\Trainees\Pages;

use App\Filament\Resources\Trainees\TraineeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTrainee extends EditRecord
{
    protected static string $resource = TraineeResource::class;
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
            ViewAction::make(),
            DeleteAction::make()->visible(fn() => !$this->getRecord()->trashed() && (\Illuminate\Support\Facades\Auth::user()?->isAdmin() || \Illuminate\Support\Facades\Auth::user()?->isTrainingManagerLike())),
            RestoreAction::make()->visible(fn() => $this->getRecord()->trashed() && (\Illuminate\Support\Facades\Auth::user()?->isAdmin() || \Illuminate\Support\Facades\Auth::user()?->isTrainingManagerLike())),
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
        return 'تم تحديث بيانات المتدرب بنجاح';
    }
}
