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
            return $Application->section_id === $user->section?->id &&
                in_array($Application->status, [
                    Application::STATUS_STARTED_TRAINING,
                    Application::STATUS_ENDED_TRAINING
                ]);
        }

        if ($user->isAdministrative()) {
            return $Application->section?->administrative_id === $user->administrative?->id &&
                in_array($Application->status, [
                    Application::STATUS_STARTED_TRAINING,
                    Application::STATUS_ENDED_TRAINING
                ]);
        }

        if ($user->isDepartment()) {
            return $Application->section?->department_id === $user->department?->id &&
                in_array($Application->status, [
                    Application::STATUS_STARTED_TRAINING,
                    Application::STATUS_ENDED_TRAINING
                ]);
        }

        if ($user->isMedicalManager()) {
            $adminId = \App\Models\Administrative::where('medical_head_user_id', $user->id)->value('id');
            return $Application->section?->administrative_id === $adminId &&
                $Application->section?->department?->is_medical === true &&
                in_array($Application->status, [
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
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCollegeSupervisor()) {
            $college = $user->college;
            if (!$college || !$college->add_application) {
                return false;
            }

            $institution = $college->institution;
            if (!$institution || !$institution->add_application) {
                return false;
            }

            return true;
        }

        return false;
    }


    public function update(User $user, Application $Application): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCollegeSupervisor()) {
            return $Application->trainee->college_id === $user->college?->id;
        }

        return false;
    }

    public function delete(User $user, Application $Application): bool
    {
        return $user->isAdmin() || $user->isGeneralTrainingManager();
    }

    public function downloadAbsorptionPaper(User $user, Application $application): bool
    {
        return $user->isMinistry() &&
            in_array($application->status, [
                Application::STATUS_INITIAL_APPROVE,
                Application::STATUS_CONFIRMATION,
                Application::STATUS_STARTED_TRAINING,
                Application::STATUS_ENDED_TRAINING,
            ]) &&
            $application->training_type === Application::PRACTICE;
    }
}
