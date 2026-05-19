<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Application $Application): bool
    {
        if ($user->isAdmin() || $user->isGeneralTrainingManager() || $user->isMonitor()) {
            return true;
        }

        if ($user->isAssistantTrainingManager()) {
            return $user->canManageApplication($Application);
        }

        $activeStatuses = [
            Application::STATUS_WAITING_LIST,
            Application::STATUS_STARTED_TRAINING,
            Application::STATUS_ENDED_TRAINING,
        ];

        if ($user->isCollegeSupervisor()) {
            return $Application->training_type == Application::UNIVERSITY
                && $Application->college_id == $user->college?->id;
        }

        if ($user->isSectionHead()) {
            return $Application->section_id == $user->section?->id
                && in_array($Application->status, $activeStatuses);
        }

        if ($user->isAdministrative()) {
            return $Application->section?->administrative_id == $user->administrative?->id
                && in_array($Application->status, $activeStatuses);
        }

        if ($user->isDepartment()) {
            return $Application->section?->departments->contains($user->department?->id)
                && in_array($Application->status, $activeStatuses);
        }

        if ($user->isMedicalManager()) {
            $adminId = \App\Models\Administrative::where('medical_head_user_id', $user->id)->value('id');
            return $Application->section?->administrative_id == $adminId
                && $Application->section?->departments->where('is_medical', true)->isNotEmpty()
                && in_array($Application->status, $activeStatuses);
        }

        if ($user->isMinistry()) {
            $mohStatuses = [
                Application::STATUS_NEW,
                Application::STATUS_INITIAL_APPROVE,
                Application::STATUS_CONFIRMATION,
                Application::STATUS_WAITING_LIST,
                Application::STATUS_STARTED_TRAINING,
                Application::STATUS_ENDED_TRAINING,
                Application::STATUS_REJECTED,
            ];

            if ($Application->training_type != Application::PRACTICE || !in_array($Application->status, $mohStatuses)) {
                return false;
            }

            if ($user->mohDepartment) {
                return $Application->section?->departments->contains($user->mohDepartment->id);
            }

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
            if (! $college || ! $college->add_application) {
                return false;
            }

            $institution = $college->institution;
            if (! $institution || ! $institution->add_application) {
                return false;
            }

            return true;
        }

        if ($user->isMinistry()) {
            return true;
        }

        return false;
    }

    public function update(User $user, Application $Application): bool
    {
        if ($user->isAdmin() || $user->isGeneralTrainingManager()) {
            return true;
        }

        if ($user->isAssistantTrainingManager()) {
            return $user->canManageApplication($Application);
        }

        if ($user->isCollegeSupervisor() || $user->isMinistry()) {
            $allowedStatuses = [
                Application::STATUS_NEW,
                Application::STATUS_INITIAL_APPROVE,
                Application::STATUS_CONFIRMATION,
            ];

            return in_array($Application->status, $allowedStatuses);
        }

        return false;
    }

    public function delete(User $user, Application $Application): bool
    {
        return $user->isAdmin();
    }

    public function downloadAbsorptionPaper(User $user, Application $application): bool
    {
        return $user->isMinistry()
            && in_array($application->status, [
                Application::STATUS_INITIAL_APPROVE,
                Application::STATUS_CONFIRMATION,
                Application::STATUS_STARTED_TRAINING,
                Application::STATUS_ENDED_TRAINING,
            ])
            && $application->training_type === Application::PRACTICE;
    }

    public function cancel(User $user, Application $application): bool
    {
        // Only Admin, GTM, College Supervisor, and MOH can cancel
        if (!($user->isAdmin() || $user->isTrainingManagerLike() || $user->isCollegeSupervisor() || $user->isMinistry())) {
            return false;
        }

        // Can only cancel STARTED_TRAINING applications
        if ($user->isAssistantTrainingManager() && ! $user->canManageApplication($application)) {
            return false;
        }

        return $application->status === Application::STATUS_STARTED_TRAINING;
    }
}
