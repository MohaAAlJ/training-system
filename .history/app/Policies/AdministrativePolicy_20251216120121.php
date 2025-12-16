<?php

namespace App\Policies;

use App\Models\Administratives;
use App\Models\User;

class AdministrativePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Administratives $model): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Administratives $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * 5. الحذف (delete)
     * للأدمن فقط.
     */
    public function delete(User $user, Administratives $model): bool
    {
        return $user->isAdmin();
    }
}
