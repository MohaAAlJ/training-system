<?php

namespace App\Policies;

use App\Models\Sections;
use App\Models\User;

class SectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() ||
            $user->isGeneralTrainingManager() ||
            $user->isDepartment() ||
            $user->isAdministrative() ||
            $user->isMedicalManager();
    }

    public function view(User $user, Sections $model): bool
    {
        if ($user->isAdmin() || $user->isGeneralTrainingManager()) {
            return true;
        }

        if ($user->isDepartment()) {
            return $model->department_id === $user->department?->id;
        }

        if ($user->isAdministrative()) {
            return $model->administrative_id === $user->administrative?->id;
        }

        if ($user->isMedicalManager()) {
            $adminId = \App\Models\Administrative::where('medical_head_user_id', $user->id)->value('id');
            return $model->administrative_id === $adminId && $model->department?->is_medical === true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Sections $model): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Sections $model): bool
    {
        return $user->isAdmin();
    }
}
