<?php

namespace App\Filament\Resources\Application\Pages;

use App\Filament\Resources\ApplicationResource;
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
            Action::make('save')
                ->label('حفظ التعديلات')
                ->action(fn() => $this->save())
                ->color('primary'),

            Action::make('cancel')
                ->label('إلغاء')
                ->url(ApplicationResource::getUrl('index'))
                ->color('gray')
                ->outlined(),

            DeleteAction::make()->visible(fn() => Auth::user()?->isAdmin()),
            ForceDeleteAction::make()->visible(fn() => Auth::user()?->isAdmin()),
            RestoreAction::make()->visible(fn() => Auth::user()?->isAdmin()),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        return ApplicationResource::getUrl('index');
    }
}
