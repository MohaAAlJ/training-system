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
            return $Applications->department_id === $user->department?->id &&
                in_array((int)$Applications->status, [
                    \App\Helpers\Constans::STATUS_STRATED_TRAINING,
                    \App\Helpers\Constans::STATUS_ENDED_TRAINING
                ]);
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
        if ($user->isAdmin() || $user->isGeneralTrainingManager()) {
            return true;
        }

        if ($user->isSectionHead()) {
            return $Applications->section_id === $user->Sections?->id;
        }

        // Allow MOH update if status is 2 (STATUS_INITIAL_APPROVE)
        if ($user->isMinistry() && (int)$Applications->status === Constans::STATUS_INITIAL_APPROVE) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Applications $Applications): bool
    {
        return $user->isAdmin() || $user->isGeneralTrainingManager();
    }
}
