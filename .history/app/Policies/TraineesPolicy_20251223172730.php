<?php

namespace App\Policies;

use App\Models\Trainees;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TraineesPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Trainees $trainee): bool
    {
        if ($user->isAdmin() || $user->isMinistry() || $user->isGeneralTrainingManager()) {
            return true;
        }

        if ($user->isCollegeSupervisor()) {
            return $trainee->college_id === $user->college?->id;
        }

        if ($user->isSectionHead()) {
            return $trainee->applications()
                ->where('section_id', $user->sections?->id)
                ->whereIn('status', [
                    \App\Helpers\Constans::STATUS_STRATED_TRAINING,
                    \App\Helpers\Constans::STATUS_ENDED_TRAINING
                ])
                ->exists();
        }

        if ($user->isAdministrative()) {
            return $trainee->applications()
                ->where('administrative_id', $user->administrative?->id)
                ->whereIn('status', [
                    \App\Helpers\Constans::STATUS_STRATED_TRAINING,
                    \App\Helpers\Constans::STATUS_ENDED_TRAINING
                ])
                ->exists();
        }

        if ($user->isDepartment()) {
            return $trainee->applications()
                ->where('department_id', $user->department?->id)
                ->whereIn('status', [
                    \App\Helpers\Constans::STATUS_STRATED_TRAINING,
                    \App\Helpers\Constans::STATUS_ENDED_TRAINING
                ])
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isCollegeSupervisor();
    }

    public function update(User $user, Trainees $trainee): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Trainees $trainee): bool
    {
        return $user->isAdmin() || $user->isGeneralTrainingManager();
    }
}
