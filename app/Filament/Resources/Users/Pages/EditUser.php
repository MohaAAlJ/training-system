<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use App\Models\College;
use STS\FilamentImpersonate\Actions\Impersonate;
use Illuminate\Support\Facades\Auth;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record) {
            $data['college_id'] = $this->record->college?->id ?? null;
            $data['institution_id'] = $this->record->college?->institution_id ?? null;
        }
        return $data;
    }

    protected function afterSave(): void
    {
        $state = $this->form->getRawState();
        if (!empty($state['college_id']) && $this->record && $this->record->role == \App\Models\User::ROLE_COLLEGE) {
            $college = College::find($state['college_id']);
            if ($college) {
                // First, clear any other colleges that this user might have been assigned to
                College::where('user_id', $this->record->id)
                    ->where('id', '!=', $college->id)
                    ->update(['user_id' => null]);

                $college->user_id = $this->record->id;
                $college->save();
            }
        }

        // If role changed away from college, clear any college pointing to this user
        if ($this->record && $this->record->role != \App\Models\User::ROLE_COLLEGE) {
            College::where('user_id', $this->record->id)->update(['user_id' => null]);
        }
    }

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
            Impersonate::make()
                ->record($this->getRecord())
                ->visible(fn() => Auth::user()?->canImpersonate() && $this->getRecord()->canBeImpersonated()),
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
        return 'تم تحديث بيانات المستخدم بنجاح';
    }
}

