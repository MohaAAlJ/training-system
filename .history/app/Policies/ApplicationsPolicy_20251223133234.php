<?php

namespace App\Policies;

use App\Models\Applications;
use App\Models\User;
use App\Helpers\Constans;

class ApplicationsPolicy
{

    public function viewAny(User $user): bool
    {
        return true;
    }


    public function view(User $user, Applications $Applications): bool
    {
        if ($user->isAdmin() || $user->isGeneralTrainingManager()) {
            return true;
        }

        if ($user->isCollegeSupervisor()) {
            return $Applications->trainee->college_id === $user->college?->id;
        }

        if ($user->isSectionHead()) {
            return $Applications->section_id === $user->Sections?->id;
        }

        if ($user->isDepartment()) {
            return $Applications->department->department_id === $user->department?->department_id;
        }

        if ($user->isMinistry()) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isCollegeSupervisor();
    }


    public function update(User $user, Applications $Applications): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSectionHead()) {
            return $Applications->section_id === $user->Sections?->id;
        }

        if ($user->isDepartment()) {

            if ($user->isGeneralTrainingManager()) {
                return true;
            }

            return $Applications->department->administrative_id === $user->administrative?->id;
        }

        return false;
    }

    public function delete(User $user, Applications $Applications): bool
    {
        return $user->isAdmin() || $user->isGeneralTrainingManager();
    }
}
