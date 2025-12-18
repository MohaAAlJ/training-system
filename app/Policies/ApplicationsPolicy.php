<?php

namespace App\Policies;

use App\Models\Applications;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApplicationsPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // All users can view applications
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Applications $applications): bool
    {
        return true; // All users can view applications
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Allow all users to create for now
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Applications $applications): bool
    {
        return true; // Allow all users to update for now
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Applications $applications): bool
    {
        return $user->hasFullAccess(); // Only full access can delete
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Applications $applications): bool
    {
        return $user->hasFullAccess();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Applications $applications): bool
    {
        return $user->hasFullAccess();
    }
}
