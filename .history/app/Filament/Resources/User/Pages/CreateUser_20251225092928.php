<?php

namespace App\Filament\Resources\User\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\College;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $state = $this->form->getState();
        if (!empty($state['college_id']) && $this->record && $this->record->role == \App\Helpers\Constants::ROLE_COLLEGE) {
            $college = College::find($state['college_id']);
            if ($college) {
                $college->user_id = $this->record->id;
                $college->save();
            }
        }
    }
}






