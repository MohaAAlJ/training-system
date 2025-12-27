<?php

namespace App\Policies;

use App\Models\Section;
use App\Models\User;

class SectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() ||
            $user->isGeneralTrainingManager() ||
            $user->isDepartment() ||
            $user->isAdministrative() ||
            $user->isMedicalManager();
    }

    public function view(User $user, Section $model): bool
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
            $adminId = \App\Models\Administrative::where('medical_head_user_id', $user->id)->value('id');
            return $model->administrative_id === $adminId && $model->department?->is_medical === true;
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

        $settings = \App\Models\GeneralSetting::instance();

        // Check HOA permissions
        if ($user->isHOA()) {
            // Can update if: owned by their unit AND (can_edit OR can_enable)
            // Note: We allow access to 'update' if they have either permission,
            // verifying specific field access is done in Resource/Form.
            if ($model->administrative_id === $user->administrative?->id) {
                return $settings->hoa_can_edit_section || $settings->hoa_can_enable_section;
            }
        }

        // Check Department permissions
        if ($user->isDepartment()) {
            if ($model->department_id === $user->department?->id) {
                return $settings->dept_head_can_edit_section || $settings->dept_head_can_enable_section;
            }
        }

        return false;
    }

    /**
     * Determine if the user can specifically edit section details (form).
     */
    public function editDetails(User $user, Section $model): bool
    {
        if ($user->isAdmin() || $user->isGeneralTrainingManager()) return true;

        $settings = \App\Models\GeneralSetting::instance();

        if ($user->isHOA() && $model->administrative_id === $user->administrative?->id) {
            return $settings->hoa_can_edit_section;
        }

        if ($user->isDepartment() && $model->department_id === $user->department?->id) {
            return $settings->dept_head_can_edit_section;
        }

        return false;
    }

    /**
     * Determine if the user can specifically toggle status.
     */
    public function toggleStatus(User $user, Section $model): bool
    {
        if ($user->isAdmin() || $user->isGeneralTrainingManager()) return true;

        $settings = \App\Models\GeneralSetting::instance();

        if ($user->isHOA() && $model->administrative_id === $user->administrative?->id) {
            return $settings->hoa_can_enable_section;
        }

        if ($user->isDepartment() && $model->department_id === $user->department?->id) {
            return $settings->dept_head_can_enable_section;
        }

        return false;
    }


    public function delete(User $user, Section $model): bool
    {
        return $user->isAdmin();
    }
}





