<?php

namespace App\Policies;

use App\Models\GeneralSetting;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GeneralSettingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, GeneralSetting $generalSetting): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, GeneralSetting $generalSetting): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, GeneralSetting $generalSetting): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, GeneralSetting $generalSetting): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, GeneralSetting $generalSetting): bool
    {
        return $user->isAdmin();
    }
}
