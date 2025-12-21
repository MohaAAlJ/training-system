<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UsersResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use App\Models\College;

class EditUsers extends EditRecord
{
    protected static string $resource = UsersResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record) {
            $data['college_id'] = $this->record->college_id ?? $this->record->college?->id ?? null;
            $data['institution_id'] = $this->record->institution_id ?? $this->record->institution?->id ?? null;
        }
        return $data;
    }

    protected function afterSave(): void
    {
        $state = $this->form->getState();
        if (!empty($state['college_id']) && $this->record && $this->record->role == \App\Helpers\Constans::ROLE_COLLEGE_SUPERVISOR) {
            $college = College::find($state['college_id']);
            if ($college) {
                $college->user_id = $this->record->id;
                $college->save();
            }
        }
        // Also persist institution_id/college_id on the user record
        if ($this->record) {
            $updated = false;
            if (!empty($state['college_id']) && $this->record->college_id !== ($state['college_id'] ?? null)) {
                $this->record->college_id = $state['college_id'];
                $updated = true;
            }
            if (!empty($state['institution_id']) && $this->record->institution_id !== ($state['institution_id'] ?? null)) {
                $this->record->institution_id = $state['institution_id'];
                $updated = true;
            }
            if (isset($state['role']) && $this->record->role !== ($state['role'] ?? null)) {
                $this->record->role = $state['role'];
                $updated = true;
            }
            if ($updated) {
                $this->record->save();
            }
        }
        // If role changed away from college, clear any college pointing to this user
        if ($this->record && $this->record->role != \App\Helpers\Constans::ROLE_COLLEGE_SUPERVISOR) {
            College::where('user_id', $this->record->id)->update(['user_id' => null]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
