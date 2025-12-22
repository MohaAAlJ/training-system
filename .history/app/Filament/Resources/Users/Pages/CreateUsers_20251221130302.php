<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UsersResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\College;

class CreateUsers extends CreateRecord
{
    protected static string $resource = UsersResource::class;

    public function mount(): void
    {
        $this->authorize('create', \App\Models\User::class);
        parent::mount();
    }

    protected function afterCreate(): void
    {
        $state = $this->form->getState();
        if (!empty($state['college_id']) && $this->record && $this->record->role == \App\Helpers\Constans::) {
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
    }
}
