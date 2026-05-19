<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use App\Models\College;
use App\Models\Department;
use App\Models\User;
use Filament\Notifications\Notification;
use STS\FilamentImpersonate\Actions\Impersonate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    private bool $passwordChanged = false;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record) {
            $data['college_id'] = $this->record->college?->id ?? null;
            $data['institution_id'] = $this->record->college?->institution_id ?? null;
            $data['managed_department_ids'] = $this->record->managedDepartments()->pluck('id')->map(fn($id) => (int) $id)->all();
        }
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['password'])) {
            // if ($this->shouldHashPassword($data['password'])) {
            //     $data['password'] = bcrypt($data['password']);
            // }
            $data['remember_token'] = Str::random(60); // Invalidate all sessions
            $this->passwordChanged = true;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $state = $this->form->getRawState();
        if (!empty($state['college_id']) && $this->record && $this->record->role == User::ROLE_COLLEGE) {
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
        if ($this->record && $this->record->role != User::ROLE_COLLEGE) {
            College::where('user_id', $this->record->id)->update(['user_id' => null]);
        }

        if ($this->record && $this->record->role == User::ROLE_ASSISTANT_TRAINING_MANAGER) {
            $this->syncManagedDepartments((array) ($state['managed_department_ids'] ?? []));
        } elseif ($this->record) {
            Department::where('assistant_training_manager_id', $this->record->id)
                ->update(['assistant_training_manager_id' => null]);
        }
    }

    private function syncManagedDepartments(array $departmentIds): void
    {
        $departmentIds = array_values(array_filter(array_map('intval', $departmentIds)));

        if (empty($departmentIds)) {
            Department::where('assistant_training_manager_id', $this->record->id)
                ->update(['assistant_training_manager_id' => null]);

            return;
        }

        $taken = Department::whereIn('id', $departmentIds)
            ->whereNotNull('assistant_training_manager_id')
            ->where('assistant_training_manager_id', '!=', $this->record->id)
            ->exists();

        if ($taken) {
            Notification::make()
                ->title('الدائرة معيّنة مسبقاً')
                ->body('واحدة أو أكثر من الدوائر المحددة معيّنة بالفعل لمساعد مدير تدريب آخر.')
                ->danger()
                ->send();

            return;
        }

        Department::where('assistant_training_manager_id', $this->record->id)
            ->whereNotIn('id', $departmentIds)
            ->update(['assistant_training_manager_id' => null]);

        Department::whereIn('id', $departmentIds)
            ->update(['assistant_training_manager_id' => $this->record->id]);
    }

    // private function shouldHashPassword(?string $password): bool
    // {
    //     if (!filled($password)) {
    //         return false;
    //     }
    //
    //     $algo = password_get_info($password)['algo'] ?? null;
    //
    //     return empty($algo);
    // }

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
