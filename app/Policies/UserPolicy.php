<?php

namespace App\Policies;

use App\Models\User;
use App\Helpers\Constans;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Global before hook: admins can do everything.
     */
    public function before(User $user, $ability)
    {
        // Grant all abilities to full-access users (admins and administrative)
        if ($user->hasFullAccess()) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        // Admins covered by before(); administrative users can list users
        return $user->hasFullAccess() || $user->isDepartment() || $user->isCollege() || $user->isMOH();
    }

    public function view(User $user, $model): bool
    {
        // If viewing a User model: allow full access or self
        if ($model instanceof User) {
            return $user->hasFullAccess() || $user->id === $model->id;
        }

        // If viewing an Application (or other model), allow full access or specific rules
        if ($model instanceof \App\Models\Applications) {
            // Full-access always allowed
            if ($user->hasFullAccess()) {
                return true;
            }

            // MOH users can view professional applications
            if ($user->isMOH()) {
                return true;
            }

            // College users can view applications for trainees from their college
            if ($user->isCollege()) {
                $trainee = $model->trainee;
                if ($trainee && $user->college && $trainee->college_id === $user->college->id) {
                    return true;
                }
                return false;
            }

            return false;
        }

        // Default: allow only full access
        return $user->hasFullAccess();
    }

    public function create(User $user): bool
    {
        // For creating Users: require full access
        // For creating Applications (policy mapped to this class) we also allow full access
        return $user->hasFullAccess() || $user->isCollege();
    }

    public function update(User $user, $model): bool
    {
        if ($model instanceof User) {
            return $user->hasFullAccess() || $user->id === $model->id;
        }

        if ($model instanceof \App\Models\Applications) {
            if ($user->hasFullAccess()) {
                return true;
            }

            // College users can update applications only for their college
            if ($user->isCollege()) {
                $trainee = $model->trainee;
                return $trainee && $user->college && $trainee->college_id === $user->college->id;
            }

            return false;
        }

        return $user->hasFullAccess();
    }

    public function delete(User $user, $model): bool
    {
        // Only full-access roles can delete users or applications
        return $user->hasFullAccess();
    }

    public function restore(User $user, $model): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, $model): bool
    {
        return $user->isAdmin();
    }
}
