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
            $data['college_id'] = $this->record->college?->id ?? null;
            $data['institution_id'] = $this->record->college?->institution_id ?? null;
        }
        return $data;
    }

    protected function afterSave(): void
    {
        $state = $this->form->getState();
        if (!empty($state['college_id']) && $this->record && $this->record->role == \App\Helpers\Constans::ROLE_COLLEGE) {
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
        if ($this->record && $this->record->role != \App\Helpers\Constans::ROLE_COLLEGE) {
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
