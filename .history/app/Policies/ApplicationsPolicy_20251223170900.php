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

        if ($user->isMinistry()) {
            return $Applications->training_type === Constans::TRAINING_TYPE_PRACTICE &&
                in_array((int)$Applications->status, [
                    Constans::STATUS_STRATED_TRAINING,
                    Constans::STATUS_ENDED_TRAINING,
                    C
                ]);
        }

        if ($user->isSectionHead()) {
            return $Applications->section_id === $user->sections?->id &&
                in_array((int)$Applications->status, [
                    Constans::STATUS_STRATED_TRAINING,
                    Constans::STATUS_ENDED_TRAINING
                ]);
        }

        if ($user->isAdministrative()) {
            return $Applications->administrative_id === $user->administrative?->id &&
                in_array((int)$Applications->status, [
                    Constans::STATUS_STRATED_TRAINING,
                    Constans::STATUS_ENDED_TRAINING
                ]);
        }

        if ($user->isDepartment()) {
            return $Applications->department_id === $user->department?->id &&
                in_array((int)$Applications->status, [
                    Constans::STATUS_STRATED_TRAINING,
                    Constans::STATUS_ENDED_TRAINING
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
        return $user->isAdmin() || $user->isGeneralTrainingManager();
    }

    public function delete(User $user, Applications $Applications): bool
    {
        return $user->isAdmin() || $user->isGeneralTrainingManager();
    }
}
