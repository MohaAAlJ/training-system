<?php

namespace App\Policies;

use App\Models\Sections;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SectionsPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() ||
            $user->isGeneralTrainingManager() ||
            $user->isAdministrative() ||
            $user->isMedicalManager() ||
            $user->isDepartmentHead() ||
            $user->isSectionHead();
    }

    public function view(User $user, Sections $sections): bool
    {
        if ($user->isAdmin() || $user->isGeneralTrainingManager()) {
            return true;
        }

        if ($user->isSectionHead()) {
            // Assuming HOS is linked to the section via the 'hos' column or user->section relation
            return $user->section?->id === $sections->id || $sections->hos === $user->id;
        }

        if ($user->isDepartmentHead()) {
            return $user->department?->id === $sections->department_id;
        }

        if ($user->isMedicalManager()) {
            return $sections->administrative_id === $user->medicalAdministrative?->id &&
                $sections->department?->is_medical === true;
        }

        if ($user->isAdministrative()) {
            return $user->administrative?->id === $sections->administrative_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isGeneralTrainingManager();
    }

    public function update(User $user, Sections $sections): bool
    {
        return $user->isAdmin() || $user->isGeneralTrainingManager();
    }

    public function delete(User $user, Sections $sections): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Sections $sections): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Sections $sections): bool
    {
        return $user->isAdmin();
    }
}
