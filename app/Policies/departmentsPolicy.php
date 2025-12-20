<?php

namespace App\Policies;

use App\Models\Departments;
use App\Models\User;

class DepartmentsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Departments $department): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Departments $department): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Departments $department): bool
    {
        return $user->isAdmin();
    }
}
