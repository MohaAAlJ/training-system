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
            $user->isMedicalManager() ||
            $user->isSectionHead();
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
            return $model->department?->is_medical === true;
        }

        if ($user->isSectionHead()) {
            return $model->user_id === $user->id;
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
