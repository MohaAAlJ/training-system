<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\College;
use App\Models\Department;
use App\Models\User;
use Filament\Notifications\Notification;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    // protected function mutateFormDataBeforeSave(array $data): array
    // {
    //     if (!empty($data['password']) && $this->shouldHashPassword($data['password'])) {
    //         $data['password'] = bcrypt($data['password']);
    //     }

    //     return $data;
    // }

    protected function afterCreate(): void
    {
        $state = $this->form->getRawState();
        if (!empty($state['college_id']) && $this->record && $this->record->role == User::ROLE_COLLEGE) {
            $college = College::find($state['college_id']);
            if ($college) {
                $college->user_id = $this->record->id;
                $college->save();
            }
        }

        if ($this->record && $this->record->role == User::ROLE_ASSISTANT_TRAINING_MANAGER) {
            $this->syncManagedDepartments((array) ($state['managed_department_ids'] ?? []));
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
}
