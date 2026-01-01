<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;
use App\Helpers\Constants;

class ApplicationPolicy
{

    public function viewAny(User $user): bool
    {
        return true;
    }


    public function view(User $user, Application $Application): bool
    {
        if ($user->isAdmin() || $user->isGeneralTrainingManager()) {
            return true;
        }

        if ($user->isCollegeSupervisor()) {
            return $Application->trainee->college_id === $user->college?->id;
        }

        if ($user->isSectionHead()) {
            return $Application->section_id === $user->Section?->id &&
                in_array((int)$Application->status, [
                    Application::STATUS_STARTED_TRAINING,
                    Application::STATUS_ENDED_TRAINING
                ]);
        }

        if ($user->isAdministrative()) {
            return $Application->administrative_id === $user->administrative?->id &&
                in_array((int)$Application->status, [
                    Application::STATUS_STARTED_TRAINING,
                    Application::STATUS_ENDED_TRAINING
                ]);
        }

        if ($user->isDepartment()) {
            return $Application->department_id === $user->department?->id &&
                in_array((int)$Application->status, [
                    Application::STATUS_STARTED_TRAINING,
                    Application::STATUS_ENDED_TRAINING
                ]);
        }

        if ($user->isMedicalManager()) {
            $adminId = \App\Models\Administrative::where('medical_head_user_id', $user->id)->value('id');
            return $Application->administrative_id === $adminId &&
                $Application->department?->is_medical === true &&
                in_array((int)$Application->status, [
                    Application::STATUS_STARTED_TRAINING,
                    Application::STATUS_ENDED_TRAINING
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


    public function update(User $user, Application $Application): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Application $Application): bool
    {
        return $user->isAdmin() || $user->isGeneralTrainingManager();
    }
}
