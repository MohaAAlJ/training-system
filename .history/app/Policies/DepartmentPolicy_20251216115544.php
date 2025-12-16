<?php

namespace App\Policies;

use App\Models\Departments;
use App\Models\User;

class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isAdministrative();
    }

    public function view(User $user, Departments $model): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isAdministrative()) {
            return $model->administrative_id === $user->administrative?->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Departments $model): bool
    {
        return $user->isAdmin();
    }

    // 5. الحذف: للأدمن فقط
    public function delete(User $user, Departments $model): bool
    {
        return $user->isAdmin();
    }
}
