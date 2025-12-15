<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UsersResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\College;

class CreateUsers extends CreateRecord
{
    protected static string $resource = UsersResource::class;

    protected function afterCreate(): void
    {
        $state = $this->form->getState();
        if (!empty($state['college_id']) && $this->record && $this->record->role == \App\Helpers\Constans::ROLE_COLLEGE) {
            $college = College::find($state['college_id']);
            if ($college) {
                $college->user_id = $this->record->id;
                $college->save();
            }
        }
    }
}
