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
        if ($user->isAdmin()) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        // Admins covered by before(); administrative users can list users
        return $user->hasFullAccess() || $user->isDepartment() || $user->isCollage() || $user->isMOH();
    }

    public function view(User $user, User $model): bool
    {
        // Allow viewing self or any user if has full access
        return $user->hasFullAccess() || $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        // Only full-access roles may create users
        return $user->hasFullAccess();
    }

    public function update(User $user, User $model): bool
    {
        // Full access or editing own profile
        return $user->hasFullAccess() || $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        // Only full-access roles can delete users
        return $user->hasFullAccess();
    }

    public function restore(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->isAdmin();
    }
}
