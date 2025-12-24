<?php

namespace App\Policies;

use App\Models\Sections;
use App\Models\User;

class SectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isDepartment() || $user->isHOA();
    }

    public function view(User $user, Sections $model): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSectionHead()) {
            return $model->department_id === $user->department?->id;
        }

        if ($user->isHOA()) {
            return $model->administrative_id === $user->administrative?->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Sections $model): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Sections $model): bool
    {
        return $user->isAdmin();
    }
}
