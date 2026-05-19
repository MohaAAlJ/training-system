<?php

namespace App\Policies;

use App\Models\Section;
use App\Models\User;

use App\Settings\TrainingSettings;

class SectionPolicy
{
    public function __construct(
        protected TrainingSettings $settings
    ) {}

    public function viewAny(User $user): bool
    {
        return $user->isAdmin() ||
            $user->isGeneralTrainingManager() ||
            $user->isAssistantTrainingManager() ||
            $user->isMonitor() ||
            $user->isDepartment() ||
            $user->isAdministrative() ||
            $user->isMedicalManager();
    }

    public function view(User $user, Section $model): bool
    {
        if ($user->isAdmin() || $user->isGeneralTrainingManager() || $user->isMonitor()) {
            return true;
        }

        if ($user->isAssistantTrainingManager()) {
            return $model->departments->pluck('id')->intersect($user->managedDepartmentIds())->isNotEmpty();
        }

        if ($user->isDepartment()) {
            return $model->departments->contains($user->department?->id);
        }

        if ($user->isAdministrative()) {
            return $model->administrative_id === $user->administrative?->id;
        }

        if ($user->isMedicalManager()) {
            $adminId = \App\Models\Administrative::where('medical_head_user_id', $user->id)->value('id');
            return $model->administrative_id === $adminId && $model->departments->where('is_medical', true)->isNotEmpty();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Section $model): bool
    {
        if ($user->isAdmin() || $user->isGeneralTrainingManager()) {
            return true;
        }

        if ($user->isAssistantTrainingManager()) {
            return $model->departments->pluck('id')->intersect($user->managedDepartmentIds())->isNotEmpty();
        }

        // Check HOA permissions
        if ($user->isHOA()) {
            if ($model->administrative_id === $user->administrative?->id) {
                return $this->settings->hoa_can_edit_section || $this->settings->hoa_can_enable_section;
            }
        }

        // Check Department Head permissions
        if ($user->isDepartment()) {
            if ($model->departments->contains($user->department?->id)) {
                return $this->settings->dept_head_can_edit_section || $this->settings->dept_head_can_enable_section;
            }
        }

        // Check Medical Manager permissions
        // if ($user->isMedicalManager()) {
        //     $adminId = \App\Models\Administrative::where('medical_head_user_id', $user->id)->value('id');
        //     return $model->administrative_id === $adminId && $model->department?->is_medical === true;
        // }

        return false;
    }

    /**
     * Determine if the user can specifically edit section details (form).
     */
    public function editDetails(User $user, Section $model): bool
    {
        if ($user->isAdmin() || $user->isGeneralTrainingManager()) return true;

        if ($user->isAssistantTrainingManager()) {
            return $model->departments->pluck('id')->intersect($user->managedDepartmentIds())->isNotEmpty();
        }

        if ($user->isHOA() && $model->administrative_id === $user->administrative?->id) {
            return $this->settings->hoa_can_edit_section;
        }

        if ($user->isDepartment() && $model->departments->contains($user->department?->id)) {
            return $this->settings->dept_head_can_edit_section;
        }

        return false;
    }

    /**
     * Determine if the user can specifically toggle status.
     */
    public function toggleActive(User $user, Section $model): bool
    {
        if ($user->isAdmin()) return true;

        if ($user->isHOA() && $model->administrative_id === $user->administrative?->id) {
            return $this->settings->hoa_can_enable_section;
        }

        if ($user->isDepartment() && $model->departments->contains($user->department?->id)) {
            return $this->settings->dept_head_can_enable_section;
        }

        // if ($user->isMedicalManager()) {
        //     $adminId = \App\Models\Administrative::where('medical_head_user_id', $user->id)->value('id');
        //     return $model->administrative_id === $adminId && $model->department?->is_medical === true;
        // }

        return false;
    }
    public function delete(User $user, Section $model): bool
    {
        return $user->isAdmin();
    }
}
