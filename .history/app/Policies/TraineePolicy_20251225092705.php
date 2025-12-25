<?php

namespace App\Policies;

use App\Models\Trainee;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TraineePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Trainee $trainee): bool
    {
        if ($user->isAdmin() || $user->isMinistry() || $user->isGeneralTrainingManager()) {
            return true;
        }

        if ($user->isCollegeSupervisor()) {
            return $trainee->college_id === $user->college?->id;
        }

        if ($user->isSectionHead()) {
            return $trainee->Application()
                ->where('section_id', $user->Section?->id)
                ->whereIn('status', [
                    \App\Helpers\Constants::STATUS_STRATED_TRAINING,
                    \App\Helpers\Constants::STATUS_ENDED_TRAINING
                ])
                ->exists();
        }

        if ($user->isAdministrative()) {
            return $trainee->Application()
                ->where('administrative_id', $user->administrative?->id)
                ->whereIn('status', [
                    \App\Helpers\Constants::STATUS_STRATED_TRAINING,
                    \App\Helpers\Constants::STATUS_ENDED_TRAINING
                ])
                ->exists();
        }

        if ($user->isDepartment()) {
            return $trainee->Application()
                ->where('department_id', $user->department?->id)
                ->whereIn('status', [
                    \App\Helpers\Constants::STATUS_STRATED_TRAINING,
                    \App\Helpers\Constants::STATUS_ENDED_TRAINING
                ])
                ->exists();
        }

        if ($user->isMedicalManager()) {
            $adminId = \App\Models\Administrative::where('medical_head_user_id', $user->id)->value('id');
            return $trainee->Application()
                ->where('administrative_id', $adminId)
                ->whereHas('department', fn($q) => $q->where('is_medical', true))
                ->whereIn('status', [
                    \App\Helpers\Constants::STATUS_STRATED_TRAINING,
                    \App\Helpers\Constants::STATUS_ENDED_TRAINING
                ])
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isCollegeSupervisor();
    }

    public function update(User $user, Trainee $trainee): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Trainee $trainee): bool
    {
        return $user->isAdmin() || $user->isGeneralTrainingManager();
    }
}





