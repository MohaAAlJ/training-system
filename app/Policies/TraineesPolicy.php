<?php

namespace App\Policies;

use App\Models\Trainees;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TraineesPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Trainees $trainee): bool
    {
        if ($user->isAdmin() || $user->isMinistry()) {
            return true;
        }

        if ($user->isCollegeSupervisor()) {
            return $trainee->college_id === $user->college?->id;
        }

        if ($user->isDepartmentHead()) {
            return $trainee->applications()
                ->where('department_id', $user->department?->id)
                ->exists();
        }

        if ($user->isAdministrative()) {
            return $trainee->applications()->whereHas('department', function ($q) use ($user) {
                $q->where('administrative_id', $user->administrative?->id);
            })->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isCollegeSupervisor();
    }

    public function update(User $user, Trainees $trainee): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCollegeSupervisor()) {
            return $trainee->college_id === $user->college?->id;
        }

        return false;
    }

    public function delete(User $user, Trainees $trainee): bool
    {
        return $user->isAdmin();
    }
}
