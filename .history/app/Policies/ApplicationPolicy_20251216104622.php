<?php

namespace App\Policies;

use App\Models\Applications;
use App\Models\User;
use App\Helpers\Constans;

class ApplicationPolicy
{
    /**
     * View any applications
     */
    public function viewAny(User $user): bool
    {
        // All roles can view their respective applications
        return $user->isAdmin() || 
               $user->isCollegeSupervisor() || 
               $user->isDepartmentHead() || 
               $user->isAdministrative() || 
               $user->isMinistry();
    }

    /**
     * View a specific application
     */
    public function view(User $user, Applications $application): bool
    {
        // Super Admin: can view everything
        if ($user->isAdmin()) {
            return true;
        }

        // Department Head: can only view applications in their department
        if ($user->isDepartmentHead()) {
            return $application->department_id === $user->department?->id;
        }

        // College Supervisor: can only view applications from trainees in their college
        if ($user->isCollegeSupervisor()) {
            return $application->trainee->college_id === $user->college?->id;
        }

        // Administrative Manager
        if ($user->isAdministrative()) {
            // Medical Manager: only sees departments under their administration
            if ($user->isMedicalManager()) {
                return $application->department->administrative_id === $user->administrative?->id;
            }
            
            // General Training Manager: sees all applications in the system
            if ($user->isGeneralTrainingManager()) {
                return true;
            }
        }

        // Ministry: can view professional practice applications
        if ($user->isMinistry()) {
            return $application->status === 'professional_practice';
        }

        return false;
    }

    /**
     * Create applications
     */
    public function create(User $user): bool
    {
        // Only Admin and College Supervisors can create applications
        return $user->isAdmin() || $user->isCollegeSupervisor();
    }

    /**
     * Update applications
     */
    public function update(User $user, Applications $application): bool
    {
        // Super Admin: can update everything
        if ($user->isAdmin()) {
            return true;
        }

        // Department Head: can only change application status (not edit other fields)
        // This restriction should be enforced in the form/controller
        if ($user->isDepartmentHead()) {
            return $application->department_id === $user->department?->id;
        }

        // College Supervisor: can create and approve/reject applications from their college trainees
        if ($user->isCollegeSupervisor()) {
            return $application->trainee->college_id === $user->college?->id;
        }

        // Administrative Manager: cannot edit, only view
        // (This is enforced by returning false)
        if ($user->isAdministrative()) {
            return false;
        }

        // Ministry: can approve/reject professional practice applications
        if ($user->isMinistry()) {
            return $application->status === 'professional_practice';
        }

        return false;
    }

    /**
     * Delete applications
     */
    public function delete(User $user, Applications $application): bool
    {
        // Only Super Admin can delete applications
        return $user->isAdmin();
    }

    /**
     * Force Delete applications
     */
    public function forceDelete(User $user, Applications $application): bool
    {
        // Only Super Admin can force delete applications
        return $user->isAdmin();
    }

    /**
     * Restore applications
     */
    public function restore(User $user, Applications $application): bool
    {
        // Only Super Admin can restore applications
        return $user->isAdmin();
    }
}
