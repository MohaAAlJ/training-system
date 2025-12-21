<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditApplications extends EditRecord
{
    protected static string $resource = ApplicationsResource::class;

    public static function authorizeAccess(): void
    {
        $user = Auth::user();

        if ($user && $user->isGeneralTrainingManager()) {
            abort(403, 'Unauthorized');
        }

        parent::authorizeAccess();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('حفظ التعديلات')
                ->action(fn() => $this->save())
                ->color('primary'),

            Action::make('cancel')
                ->label('إلغاء')
                ->url(ApplicationsResource::getUrl('index'))
                ->color('gray')
                ->outlined(),

            DeleteAction::make()->visible(fn() => Auth::user()?->isAdmin()),
            ForceDeleteAction::make()->visible(fn() => Auth::user()?->isAdmin()),
            RestoreAction::make()->visible(fn() => Auth::user()?->isAdmin()),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        return ApplicationsResource::getUrl('index');
    }
}
