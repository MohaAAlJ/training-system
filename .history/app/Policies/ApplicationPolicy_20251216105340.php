<?php

namespace App\Policies;

use App\Models\Applications;
use App\Models\User;
use App\Helpers\Constans;

class ApplicationPolicy
{

    public function viewAny(User $user): bool
    {
        // All authenticated users can view their respective applications
        return $user->isAdmin() || 
               $user->isCollegeSupervisor() || 
               $user->isDepartmentHead() || 
               $user->isAdministrative() || 
               $user->isMinistry();
    }


    public function view(User $user, Applications $Applications): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCollegeSupervisor()) {
            return $Applications->trainee->college_id === $user->college?->id;
        }

        if ($user->isDepartmentHead()) {
            return $Applications->department_id === $user->department?->id;
        }

        if ($user->isAdministrative()) {
            return $Applications->department->administrative_id === $user->administrative?->id;
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
        if ($user->isAdmin()) {
            return true;
        }

        // Department Head: can only change status (not edit other fields)
        if ($user->isDepartmentHead()) {
            return $Applications->department_id === $user->department?->id;
        }

        // College Supervisor: can create and approve applications
        if ($user->isCollegeSupervisor()) {
            return $Applications->trainee->college_id === $user->college?->id;
        }

        // Administrative: cannot edit, only view
        if ($user->isAdministrative()) {
            return false;
        }

        // Ministry: cannot edit, only view
        if ($user->isMinistry()) {
            return false;
        }

        return false;
    }

    public function delete(User $user, Applications $Applications): bool
    {
        return $user->isAdmin();
    }
}
