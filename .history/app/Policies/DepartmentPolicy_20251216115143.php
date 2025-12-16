<?php

namespace App\Policies;

use App\Models\Departments;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Departments $model): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Departments $model): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Departments $model): bool
    {
        return $user->isAdmin();
    }
}

