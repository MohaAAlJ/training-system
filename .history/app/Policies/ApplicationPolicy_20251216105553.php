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
        if ($user->isDepartmentHead()) {
            return $application->department_id === $user->department?->id;
        }

        // College Supervisor: can approve/reject applications from their college trainees
        if ($user->isCollegeSupervisor()) {
            return $application->trainee->college_id === $user->college?->id;
        }

        // Administrative Manager: cannot edit, only view
        if ($user->isAdministrative()) {
            return false;
        }

        // Ministry: can approve/reject professional practice applications
        if ($user->isMinistry()) {
            return $application->status === 'professional_practice';
        }

        return false;
    }

    public function delete(User $user, Applications $Applications): bool
    {
        return $user->isAdmin();
    }
}
